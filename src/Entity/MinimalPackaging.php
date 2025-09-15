<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class MinimalPackaging
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Packaging::class)]
    private Packaging $packaging;

    #[ORM\Column(type: Types::STRING, unique: true)]
    private string $productIdsMap;

    #[ORM\Column(type: Types::FLOAT)]
    private float $volumeUtilization;

    /**
     * @param int[] $productIds
     */
    public function __construct(
        Packaging $packaging,
        array $productIds,
        float $volumeUtilization,
    ) {
        $this->packaging = $packaging;
        $this->productIdsMap = implode('|', $productIds);
        $this->volumeUtilization = $volumeUtilization;
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

    public function getPackaging(): Packaging
    {
        return $this->packaging;
    }

    public function getProductIdsMap(): string
    {
        return $this->productIdsMap;
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeToArray(): array
    {
        $packaging = $this->packaging;

        return [
            'id' => $packaging->getId(),
            'width' => $packaging->getWidth(),
            'height' => $packaging->getHeight(),
            'length' => $packaging->getLength(),
            'max_weight' => $packaging->getMaxWeight(),
            'volume_utilization' => $this->volumeUtilization,
        ];
    }
}
