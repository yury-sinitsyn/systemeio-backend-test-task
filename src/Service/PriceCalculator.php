<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Coupon;

/**
 * Сервис для расчета цены продукта с учетом налога и возможных скидок.
 */
class PriceCalculator
{
    /**
     * Рассчитывает окончательную цену продукта с учетом налога и возможных скидок.
     *
     * @param Product $product Продукт для расчета цены
     * @param string $taxNumber Налоговый номер для расчета налога
     * @param Coupon|null $coupon Купон на скидку, если имеется
     * @return float Окончательная цена продукта
     * @throws \Exception Если налоговый номер некорректен
     */
    public function calculate(Product $product, string $taxNumber, ?Coupon $coupon = null): float
    {
        $price = $product->getPrice();        

        if ($coupon) {
            if ($coupon->isPercentage()) {
                $discount = $price * ($coupon->getDiscount() / 100);
            } else {
                $discount = $coupon->getDiscount();
            }
            $price -= $discount;
        }

        $tax = $this->calculateTax($price, $taxNumber);

        return $price + $tax;
    }

    /**
     * Рассчитывает налог на основе цены продукта и налогового номера.
     *
     * @param float $price Цена продукта
     * @param string $taxNumber Налоговый номер для расчета налога
     * @return float Рассчитанный налог
     * @throws \Exception Если налоговый номер некорректен
     */
    private function calculateTax(float $price, string $taxNumber): float
    {
        $countryCode = substr($taxNumber, 0, 2);
        switch ($countryCode) {
            case 'DE':
                return $price * 0.19;
            case 'IT':
                return $price * 0.22;
            case 'FR':
                return $price * 0.20;
            case 'GR':
                return $price * 0.24;
            default:
                throw new \Exception('Invalid tax number.');
        }
    }
}
