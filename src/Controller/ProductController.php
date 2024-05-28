<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * ProductController отвечает за обработку запросов, связанных с продуктами.
 */
class ProductController extends AbstractController
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
     * Обрабатывает запрос на добавление нового продукта.
     *
     * @param Request $request HTTP-запрос
     * @return JsonResponse HTTP-ответ
     */
    #[Route('/add-product', name: 'add_product', methods: ['POST'])]
    public function addProduct(Request $request): JsonResponse
    {
        $this->logger->info('Adding a new product.');

        $data = json_decode($request->getContent(), true);

        // Создаем новый объект Product и устанавливаем его свойства из данных запроса.
        $product = new Product();
        $product->setName($data['name']);
        $product->setDescription($data['description'] ?? null);
        $product->setPrice($data['price']);

        // Валидируем объект Product.
        $errors = $this->validator->validate($product);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            $this->logger->error('Validation errors: ' . json_encode($errorMessages));
            return new JsonResponse(['errors' => $errorMessages], 400);
        }

        // Сохраняем продукт в базе данных.
        $this->entityManager->persist($product);
        $this->entityManager->flush();

        $this->logger->info('Product added successfully.');
        return new JsonResponse(['status' => 'Product added successfully'], 201);
    }
}
