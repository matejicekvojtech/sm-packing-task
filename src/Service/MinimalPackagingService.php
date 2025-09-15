<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\MinimalPackaging;
use App\Entity\Packaging;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

readonly class MinimalPackagingService
{
    /**
     * @var EntityRepository<MinimalPackaging>
     */
    private EntityRepository $minimalPackagingRepository;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->minimalPackagingRepository = $this->entityManager->getRepository(MinimalPackaging::class);
    }

    /**
     * @param Product[] $products
     */
    public function getByProducts(array $products): ?MinimalPackaging
    {
        if (empty($products)) {
            return null;
        }

        $productIds = array_map(
            static fn(Product $product) => $product->getId(),
            $products,
        );

        return $this->minimalPackagingRepository->findOneBy([
            'productIdsMap' => implode('|', $productIds),
        ]);
    }

    /**
     * @param Product[] $products
     */
    public function persistForProducts(Packaging $packaging, array $products, float $volumeUtilization): MinimalPackaging
    {
        if (empty($products)) {
            throw new \LogicException('Packaging products must not be empty');
        }

        $productIds = array_map(
            static fn(Product $product) => $product->getId(),
            $products,
        );

        $minimalPackaging = new MinimalPackaging(
            $packaging,
            $productIds,
            $volumeUtilization,
        );

        $this->entityManager->persist($minimalPackaging);
        $this->entityManager->flush();

        return $minimalPackaging;
    }
}
