<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Product;
use App\Entity\Coupon;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PriceCalculator;
use Psr\Log\LoggerInterface;

/**
 * PriceController отвечает за обработку запросов, связанных с расчетом цены продуктов.
 */
class PriceController extends AbstractController
{
    private $entityManager;
    private $priceCalculator;
    private $logger;

    /**
     * Конструктор.
     *
     * @param EntityManagerInterface $entityManager
     * @param PriceCalculator $priceCalculator
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, PriceCalculator $priceCalculator, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->priceCalculator = $priceCalculator;
        $this->logger = $logger;
    }

    /**
     * Обрабатывает запрос на расчет цены продукта.
     *
     * @param Request $request HTTP-запрос
     * @return JsonResponse HTTP-ответ
     */
    #[Route('/calculate-price', name: 'calculate_price', methods: ['POST'])]
    public function calculatePrice(Request $request): JsonResponse
    {
        $this->logger->info('Calculating price.');

        $data = json_decode($request->getContent(), true);

        // Поиск продукта в базе данных
        $product = $this->entityManager->getRepository(Product::class)->find($data['product']);
        if (!$product) {
            $this->logger->error('Product not found.');
            return new JsonResponse(['error' => 'Product not found'], 404);
        }
        
        // Поиск купона в базе данных, если указан
        $coupon = null;
        if (!empty($data['couponCode'])) {
            $coupon = $this->entityManager->getRepository(Coupon::class)->findOneBy(['code' => $data['couponCode']]);
            if (!$coupon) {
                $this->logger->error('Coupon not found.');
                return new JsonResponse(['error' => 'Coupon not found'], 404);
            }
        }

        // Расчет окончательной цены с учетом налога и скидки
        try {
            $finalPrice = $this->priceCalculator->calculate($product, $data['taxNumber'], $coupon);
        } catch (\Exception $e) {
            $this->logger->error('Error calculating price: ' . $e->getMessage());
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        $this->logger->info('Price calculated successfully.');
        return new JsonResponse(['price' => $finalPrice], 200);
    }
}
