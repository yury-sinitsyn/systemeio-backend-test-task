<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class PurchaseRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('integer')]
    public $product;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Length(min: 2)]
    public $taxNumber;

    #[Assert\Type('string')]
    public $couponCode;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public $paymentProcessor;
}
