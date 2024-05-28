<?php

namespace App\Tests\Entity;

use App\Entity\Product;
use PHPUnit\Framework\TestCase;

/**
 * Тестовый класс для проверки функциональности сущности Product.
 */
class ProductTest extends TestCase
{
    /**
     * Тестирует метод getName и setName.
     */
    public function testGetName()
    {
        $product = new Product();
        $product->setName('Iphone');

        $this->assertEquals('Iphone', $product->getName());
    }

    /**
     * Тестирует метод getDescription и setDescription.
     */
    public function testGetDescription()
    {
        $product = new Product();
        $product->setDescription('This is a great phone.');

        $this->assertEquals('This is a great phone.', $product->getDescription());
    }

    /**
     * Тестирует метод getPrice и setPrice.
     */
    public function testGetPrice()
    {
        $product = new Product();
        $product->setPrice(999.99);

        $this->assertEquals(999.99, $product->getPrice());
    }

    /**
     * Тестирует метод getId.
     */
    public function testGetId()
    {
        $product = new Product();
        $reflection = new \ReflectionClass($product);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($product, 1);

        $this->assertEquals(1, $product->getId());
    }
}
