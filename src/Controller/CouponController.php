<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Coupon;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * CouponController отвечает за обработку запросов, связанных с купонами.
 */
class CouponController extends AbstractController
{
    private $entityManager;
    private $validator;
    private $logger;

    /**
     * Конструктор.
     *
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * Обрабатывает запрос на добавление нового купона.
     *
     * @Route("/add-coupon", name="add_coupon", methods={"POST"})
     * 
     * @param Request $request HTTP-запрос
     * @return JsonResponse HTTP-ответ
     */
    #[Route('/add-coupon', name: 'add_coupon', methods: ['POST'])]
    public function addCoupon(Request $request): JsonResponse
    {
        $this->logger->info('Adding a new coupon.');

        $data = json_decode($request->getContent(), true);

        // Проверяем, существует ли купон с указанным кодом.
        $existingCoupon = $this->entityManager->getRepository(Coupon::class)->findOneBy(['code' => $data['code']]);
        if ($existingCoupon) {
            $this->logger->error('Coupon code already exists.');
            return new JsonResponse(['error' => 'Coupon code already exists'], 400);
        }

        // Создаем новый объект Coupon и устанавливаем его свойства из данных запроса.
        $coupon = new Coupon();
        $coupon->setCode($data['code']);
        $coupon->setDiscount($data['discount']);
        $coupon->setIsPercentage($data['isPercentage']);

        // Валидируем объект Coupon.
        $errors = $this->validator->validate($coupon);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            $this->logger->error('Validation errors: ' . json_encode($errorMessages));
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        // Сохраняем купон в базе данных.
        $this->entityManager->persist($coupon);
        $this->entityManager->flush();

        $this->logger->info('Coupon added successfully.');
        return new JsonResponse(['status' => 'Coupon added successfully'], 201);
    }
}
