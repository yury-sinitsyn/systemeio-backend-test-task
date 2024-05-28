<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Product;
use App\Entity\Coupon;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PriceCalculator;
use App\Service\PaymentProcessor\PaymentProcessorInterface;
use App\Service\PaymentProcessor\PaypalPaymentProcessorAdapter;
use App\Service\PaymentProcessor\StripePaymentProcessorAdapter;
use App\DTO\PurchaseRequest;
use Psr\Log\LoggerInterface;

/**
 * OrderController отвечает за обработку запросов, связанных с покупками и оплатами.
 */
class OrderController extends AbstractController
{
    private $entityManager;
    private $priceCalculator;
    private $validator;
    private $logger;

    /**
     * Конструктор.
     *
     * @param EntityManagerInterface $entityManager
     * @param PriceCalculator $priceCalculator
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, PriceCalculator $priceCalculator, ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->priceCalculator = $priceCalculator;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * Обрабатывает запрос на выполнение покупки.
     *
     * @Route("/purchase", name="purchase", methods={"POST"})
     * 
     * @param Request $request HTTP-запрос
     * @return JsonResponse HTTP-ответ
     */
    #[Route('/purchase', name: 'purchase', methods: ['POST'])]
    public function purchase(Request $request): JsonResponse
    {
        $this->logger->info('Processing purchase.');

        $data = json_decode($request->getContent(), true);
        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->product = $data['product'] ?? null;
        $purchaseRequest->taxNumber = $data['taxNumber'] ?? null;
        $purchaseRequest->couponCode = $data['couponCode'] ?? null;
        $purchaseRequest->paymentProcessor = $data['paymentProcessor'] ?? null;

        // Валидация данных запроса
        $errors = $this->validator->validate($purchaseRequest);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            $this->logger->error('Validation errors: ' . json_encode($errorMessages));
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        // Поиск продукта в базе данных
        $product = $this->entityManager->getRepository(Product::class)->find($purchaseRequest->product);
        if (!$product) {
            $this->logger->error('Product not found.');
            return new JsonResponse(['error' => 'Product not found'], 404);
        }

        // Поиск купона в базе данных, если указан
        $coupon = null;
        if (!empty($purchaseRequest->couponCode)) {
            $coupon = $this->entityManager->getRepository(Coupon::class)->findOneBy(['code' => $purchaseRequest->couponCode]);
            if (!$coupon) {
                $this->logger->error('Coupon not found.');
                return new JsonResponse(['error' => 'Coupon not found'], 404);
            }
        }

        // Расчет окончательной цены с учетом налога и скидки
        try {
            $finalPrice = $this->priceCalculator->calculate($product, $purchaseRequest->taxNumber, $coupon);
        } catch (\Exception $e) {
            $this->logger->error('Error calculating price: ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        // Получение платежного процессора и выполнение оплаты
        try {
            $paymentProcessor = $this->getPaymentProcessor($purchaseRequest->paymentProcessor);
        } catch (\Exception $e) {
            $this->logger->error('Error getting payment processor: ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        $paymentSuccess = $paymentProcessor->pay($finalPrice);

        if ($paymentSuccess) {
            $this->logger->info('Payment processed successfully.');
            return new JsonResponse(['status' => 'success'], 200);
        } else {
            $this->logger->error('Payment failed.');
            return new JsonResponse(['error' => 'Payment failed'], 400);
        }
    }

    /**
     * Получает соответствующий платежный процессор на основе строки.
     *
     * @param string $processor Название платежного процессора
     * @return PaymentProcessorInterface Платежный процессор
     * @throws \Exception Если платежный процессор не найден
     */
    private function getPaymentProcessor(string $processor): PaymentProcessorInterface
    {
        switch ($processor) {
            case 'paypal':
                return new PaypalPaymentProcessorAdapter();
            case 'stripe':
                return new StripePaymentProcessorAdapter();
            default:
                throw new \Exception('Invalid payment processor.');
        }
    }
}
