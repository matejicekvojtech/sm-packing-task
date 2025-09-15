<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

final readonly class RequestProductDto
{
    #[NotBlank]
    public int $id;

    #[GreaterThan(0)]
    public float $width;

    #[GreaterThan(0)]
    public float $height;

    #[GreaterThan(0)]
    public float $length;

    #[GreaterThan(0)]
    public float $weight;

    public function __construct(
        int $id,
        float|int $width,
        float|int $height,
        float|int $length,
        float|int $weight,
    ) {
        $this->id = $id;
        $this->width = (float) $width;
        $this->height = (float) $height;
        $this->length = (float) $length;
        $this->weight = (float) $weight;
    }

    /**
     * @return float[]
     */
    public function getSortedDimensions(): array
    {
        $dimensions = [
            $this->width,
            $this->height,
            $this->length,
        ];
        sort($dimensions);

        return $dimensions;
    }
}
