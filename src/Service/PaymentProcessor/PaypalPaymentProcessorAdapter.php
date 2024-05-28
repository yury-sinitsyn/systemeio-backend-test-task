<?php

namespace App\Service\PaymentProcessor;

use Systemeio\TestForCandidates\PaymentProcessor\PaypalPaymentProcessor as BasePaypalPaymentProcessor;

/**
 * Адаптер для использования PaypalPaymentProcessor в рамках PaymentProcessorInterface.
 */
class PaypalPaymentProcessorAdapter implements PaymentProcessorInterface
{
    private $paypalPaymentProcessor;

    /**
     * Конструктор.
     *
     * Инициализирует экземпляр базового PaypalPaymentProcessor.
     */
    public function __construct()
    {
        $this->paypalPaymentProcessor = new BasePaypalPaymentProcessor();
    }

    /**
     * Выполняет платеж через PaypalPaymentProcessor.
     *
     * @param float $amount Сумма платежа
     * @return bool Возвращает true, если платеж был успешным, иначе false
     */
    public function pay(float $amount): bool
    {
        try {
            $this->paypalPaymentProcessor->pay($amount);
            return true;
        } catch (\Exception $e) {
            // Обработка исключения
            return false;
        }
    }
}
