<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * 
 * Ограничение для проверки корректности налогового номера.
 */
class TaxNumber extends Constraint
{
    /**
     * Сообщение об ошибке, отображаемое при некорректном налоговом номере.
     *
     * @var string
     */
    public $message = 'The tax number "{{ value }}" is not valid.';
}
