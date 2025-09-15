<?php

declare(strict_types=1);

namespace App\Provider;

use App\Entity\MinimalPackaging;
use App\Entity\Packaging;
use App\Entity\Product;
use App\Factory\BoxpackerFactory;
use Doctrine\ORM\EntityManagerInterface;
use DVDoug\BoxPacker\Exception\NoBoxesAvailableException;
use Symfony\Contracts\Cache\CacheInterface;

readonly class BoxpackerMinimalPackagingProvider implements MinimalPackagingProviderInterface
{
    use GetPackagingsTrait;

    public function __construct(
        EntityManagerInterface $entityManager,
        CacheInterface $cache,
        private BoxpackerFactory $boxpackerFactory,
    ) {
        $this->packagingRepository = $entityManager->getRepository(Packaging::class);
        $this->cache = $cache;
    }

    public function findMinimalPackaging(array $products): ?MinimalPackaging
    {
        $packagings = $this->getAllPackagings();

        if (empty($packagings)) {
            return null;
        }

        $packer = $this->boxpackerFactory->createPacker();

        foreach ($packagings as $packaging) {
            $packer->addBox(
                $this->boxpackerFactory->createBox($packaging),
            );
        }

        foreach ($products as $product) {
            $packer->addItem(
                $this->boxpackerFactory->createItem($product),
            );
        }

        try {
            $packedBoxes = $packer->pack();
        } catch (NoBoxesAvailableException) {
            return null;
        }

        if ($packedBoxes->count() > 1) {
            throw new MinimalPackagingProviderException('Could not pack into just one box');
        }

        if ($packedBoxes->count() < 1) {
            return null;
        }

        $packedBox = $packedBoxes->top();
        $packagingId = (int) $packedBox->box->getReference();

        $packaging = $this->getPackaging($packagingId);
        if (null === $packaging) {
            throw new MinimalPackagingProviderException(
                sprintf('Calculation returned packaging with id [%d] but no such was found.', $packagingId),
            );
        }

        $productIds = array_map(
            static fn(Product $product) => $product->getId(),
            $products,
        );

        return new MinimalPackaging(
            $packaging,
            $productIds,
            $packedBox->getVolumeUtilisation(),
        );
    }
}
