<?php

namespace App\Tests\Entity;

use App\Entity\Coupon;
use PHPUnit\Framework\TestCase;

/**
 * Тестовый класс для проверки функциональности сущности Coupon.
 */
class CouponTest extends TestCase
{
    /**
     * Тестирует метод getId.
     */
    public function testGetId()
    {
        $coupon = new Coupon();
        $reflection = new \ReflectionClass($coupon);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($coupon, 1);

        $this->assertEquals(1, $coupon->getId());
    }

    /**
     * Тестирует метод getCode и setCode.
     */
    public function testGetCode()
    {
        $coupon = new Coupon();
        $coupon->setCode('P10');

        $this->assertEquals('P10', $coupon->getCode());
    }

    /**
     * Тестирует метод getDiscount и setDiscount.
     */
    public function testGetDiscount()
    {
        $coupon = new Coupon();
        $coupon->setDiscount(10.0);

        $this->assertEquals(10.0, $coupon->getDiscount());
    }

    /**
     * Тестирует метод isPercentage и setIsPercentage.
     */
    public function testIsPercentage()
    {
        $coupon = new Coupon();
        $coupon->setIsPercentage(true);

        $this->assertTrue($coupon->isPercentage());

        $coupon->setIsPercentage(false);

        $this->assertFalse($coupon->isPercentage());
    }
}
