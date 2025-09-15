<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Index('idx_product_dimensions', ['dimension1', 'dimension2', 'dimension3', 'weight'])]
class Product
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\Column(type: Types::FLOAT)]
    private float $dimension1;

    #[ORM\Column(type: Types::FLOAT)]
    private float $dimension2;

    #[ORM\Column(type: Types::FLOAT)]
    private float $dimension3;

    #[ORM\Column(type: Types::FLOAT)]
    private float $weight;

    private ?int $warehouseItemId = null;

    public function __construct(
        float $dimension1,
        float $dimension2,
        float $dimension3,
        float $weight,
    ) {
        $this->dimension1 = $dimension1;
        $this->dimension2 = $dimension2;
        $this->dimension3 = $dimension3;
        $this->weight = $weight;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDimension1(): float
    {
        return $this->dimension1;
    }

    public function getDimension2(): float
    {
        return $this->dimension2;
    }

    public function getDimension3(): float
    {
        return $this->dimension3;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function getWarehouseItemId(): ?int
    {
        return $this->warehouseItemId;
    }

    public function setWarehouseItemId(?int $warehouseItemId): self
    {
        $this->warehouseItemId = $warehouseItemId;

        return $this;
    }
}
