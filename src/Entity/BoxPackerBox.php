<?php

declare(strict_types=1);

namespace App\Entity;

use DVDoug\BoxPacker\Box;

readonly class BoxPackerBox implements Box
{
    private int $width;
    private int $length;
    private int $depth;
    private int $maxWeight;

    public function __construct(
        private int $id,
        float $width,
        float $length,
        float $depth,
        float $maxWeight,
    ) {
        $this->width = (int) ($width * 100);
        $this->length = (int) ($length * 100);
        $this->depth = (int) ($depth * 100);
        $this->maxWeight = (int) ($maxWeight * 100);
    }

    public function getReference(): string
    {
        return (string) $this->id;
    }

    public function getOuterWidth(): int
    {
        return $this->width;
    }

    public function getOuterLength(): int
    {
        return $this->length;
    }

    public function getOuterDepth(): int
    {
        return $this->depth;
    }

    public function getEmptyWeight(): int
    {
        return 0;
    }

    public function getInnerWidth(): int
    {
        return $this->width;
    }

    public function getInnerLength(): int
    {
        return $this->length;
    }

    public function getInnerDepth(): int
    {
        return $this->depth;
    }

    public function getMaxWeight(): int
    {
        return $this->maxWeight;
    }
}
