<?php

namespace App\Tests\Service;

use App\Entity\Product;
use App\Entity\Coupon;
use App\Service\PriceCalculator;
use PHPUnit\Framework\TestCase;

/**
 * Тестовый класс для проверки функциональности класса PriceCalculator.
 */
class PriceCalculatorTest extends TestCase
{
    private $priceCalculator;

    /**
     * Устанавливает начальные данные для каждого теста.
     */
    protected function setUp(): void
    {
        $this->priceCalculator = new PriceCalculator();
    }

    /**
     * Тестирует расчет цены без купона.
     */
    public function testCalculateWithoutCoupon()
    {
        $product = new Product();
        $product->setName('Iphone');
        $product->setDescription('Iphone');
        $product->setPrice(100.0);

        $result = $this->priceCalculator->calculate($product, 'DE123456789');
        $expectedPrice = 100.0 + (100.0 * 0.19); // Цена + 19% налог

        $this->assertEquals($expectedPrice, $result);
    }

    /**
     * Тестирует расчет цены с процентным купоном.
     */
    public function testCalculateWithPercentageCoupon()
    {
        $product = new Product();
        $product->setName('Iphone');
        $product->setDescription('Iphone');
        $product->setPrice(100.0);

        $coupon = new Coupon();
        $coupon->setCode('P10');
        $coupon->setDiscount(10);
        $coupon->setIsPercentage(true);

        $result = $this->priceCalculator->calculate($product, 'DE123456789', $coupon);
        $expectedPrice = (100.0 - (100.0 * 0.10)) + ((100.0 - (100.0 * 0.10)) * 0.19); // Цена - 10% скидка + 19% налог

        $this->assertEquals($expectedPrice, $result);
    }

    /**
     * Тестирует расчет цены с фиксированным купоном.
     */
    public function testCalculateWithFixedAmountCoupon()
    {
        $product = new Product();
        $product->setName('Iphone');
        $product->setDescription('Iphone');
        $product->setPrice(100.0);

        $coupon = new Coupon();
        $coupon->setCode('P10');
        $coupon->setDiscount(10);
        $coupon->setIsPercentage(false);

        $result = $this->priceCalculator->calculate($product, 'DE123456789', $coupon);
        $expectedPrice = (100.0 - 10) + ((100.0 - 10) * 0.19); // Цена - 10 Евро скидка + 19% налог

        $this->assertEquals($expectedPrice, $result);
    }

    /**
     * Тестирует расчет налога с некорректным налоговым номером.
     */
    public function testCalculateTaxWithInvalidTaxNumber()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid tax number.');

        $product = new Product();
        $product->setPrice(100.0);

        $this->priceCalculator->calculate($product, 'XX123456789');
    }
}
