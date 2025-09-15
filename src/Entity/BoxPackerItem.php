<?php

declare(strict_types=1);

namespace App\Entity;

use DVDoug\BoxPacker\Item;
use DVDoug\BoxPacker\Rotation;

readonly class BoxPackerItem implements Item
{
    private int $width;
    private int $length;
    private int $depth;
    private int $weight;

    public function __construct(
        private int $id,
        float $width,
        float $length,
        float $depth,
        float $weight,
    ) {
        $this->width = (int) ($width * 100);
        $this->length = (int) ($length * 100);
        $this->depth = (int) ($depth * 100);
        $this->weight = (int) ($weight * 100);
    }

    public function getDescription(): string
    {
        return (string) $this->id;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getDepth(): int
    {
        return $this->depth;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getAllowedRotation(): Rotation
    {
        return Rotation::BestFit;
    }
}
