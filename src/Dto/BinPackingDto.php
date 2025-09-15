<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class BinPackingDto
{
    public function __construct(
        public int $packingId,
        public float $volumeUtilization,
    ) {}
}
