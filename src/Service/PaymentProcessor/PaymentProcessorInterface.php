<?php

namespace App\Service\PaymentProcessor;

/**
 * Интерфейс PaymentProcessorInterface определяет метод для выполнения платежей.
 */
interface PaymentProcessorInterface
{
    /**
     * Выполняет платеж на указанную сумму.
     *
     * @param float $amount Сумма платежа
     * @return bool Возвращает true, если платеж был успешным, иначе false
     */
    public function pay(float $amount): bool;
}
