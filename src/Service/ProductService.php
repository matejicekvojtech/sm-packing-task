<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\RequestProductDto;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

readonly class ProductService
{
    /**
     * @var EntityRepository<Product>
     */
    private EntityRepository $productRepository;
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        $this->productRepository = $this->entityManager->getRepository(Product::class);
    }

    public function findByDimensionsOrCreate(
        RequestProductDto $productDto,
        bool &$createdNewProducts,
    ): Product {
        [$dimension1, $dimension2, $dimension3] = $productDto->getSortedDimensions();

        /** @var Product|null $product */
        $product = $this->productRepository->findOneBy([
            'dimension1' => $dimension1,
            'dimension2' => $dimension2,
            'dimension3' => $dimension3,
            'weight' => $productDto->weight,
        ]);
        if (null === $product) {
            $product = new Product(
                $dimension1,
                $dimension2,
                $dimension3,
                $productDto->weight,
            );

            $this->entityManager->persist($product);
            $createdNewProducts = true;
        }

        $product->setWarehouseItemId($productDto->id);

        return $product;
    }
}
