<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Валидатор для проверки корректности налогового номера.
 */
class TaxNumberValidator extends ConstraintValidator
{
    /**
     * Валидирует значение налогового номера.
     *
     * @param mixed $value Значение для валидации
     * @param Constraint $constraint Ограничение, применяемое к значению
     */
    public function validate($value, Constraint $constraint)
    {
        // Извлекаем код страны из налогового номера
        $countryCode = substr($value, 0, 2);
        $valid = false;

        // Проверяем налоговый номер в зависимости от кода страны
        switch ($countryCode) {
            case 'DE':
                $valid = preg_match('/^DE\d{9}$/', $value);
                break;
            case 'IT':
                $valid = preg_match('/^IT\d{11}$/', $value);
                break;
            case 'GR':
                $valid = preg_match('/^GR\d{9}$/', $value);
                break;
            case 'FR':
                $valid = preg_match('/^FR[A-Z]{2}\d{9}$/', $value);
                break;
        }

        // Если номер некорректен, добавляем нарушение
        if (!$valid) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
