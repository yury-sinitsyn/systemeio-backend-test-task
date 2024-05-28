<?php

namespace App\Service\PaymentProcessor;

use SystemeIo\TestForCandidates\PaymentProcessor\StripePaymentProcessor as BaseStripePaymentProcessor;

/**
 * Адаптер для использования StripePaymentProcessor в рамках PaymentProcessorInterface.
 */
class StripePaymentProcessorAdapter implements PaymentProcessorInterface
{
    private $stripePaymentProcessor;

    /**
     * Конструктор.
     *
     * Инициализирует экземпляр базового StripePaymentProcessor.
     */
    public function __construct()
    {
        $this->stripePaymentProcessor = new BaseStripePaymentProcessor();
    }

    /**
     * Выполняет платеж через StripePaymentProcessor.
     *
     * @param float $amount Сумма платежа
     * @return bool Возвращает true, если платеж был успешным, иначе false
     */
    public function pay(float $amount): bool
    {
        return $this->stripePaymentProcessor->processPayment($amount);
    }
}
