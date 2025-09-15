<?php

declare(strict_types=1);

namespace App\Factory;

use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final readonly class ValidatorFactory
{
    public static function create(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator()
        ;
    }
}
