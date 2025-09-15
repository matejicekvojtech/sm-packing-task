<?php

declare(strict_types=1);

namespace App\Factory;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class SerializerFactory
{
    public static function create(): SerializerInterface
    {
        $phpDocExtractor = new PhpDocExtractor();
        $reflectionExtractor = new ReflectionExtractor();

        $propertyInfo = new PropertyInfoExtractor(
            typeExtractors: [$phpDocExtractor, $reflectionExtractor],
        );

        return new Serializer(
            [new ObjectNormalizer(propertyTypeExtractor: $propertyInfo)],
            [new JsonEncoder()],
        );
    }
}
