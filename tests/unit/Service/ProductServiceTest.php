<?php

namespace Test\App\Service;

use App\Dto\RequestProductDto;
use App\Entity\Product;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;
    private EntityRepository&MockObject $productRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->productRepository = $this->createMock(EntityRepository::class);
    }

    public function testItWillFindProductByDimensions(): void
    {
        $dto = new RequestProductDto(
            1,
            5,
            4,
            6,
            5,
        );

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($this->productRepository);

        $product = new Product(
            4,
            5,
            6,
            5,
        );

        $this->productRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'dimension1' => 4,
                'dimension2' => 5,
                'dimension3' => 6,
                'weight' => 5,
            ])
            ->willReturn($product);

        $service = new ProductService($this->entityManager);

        $createdNewProducts = false;

        $result = $service->findByDimensionsOrCreate($dto, $createdNewProducts);
        $this->assertInstanceOf(Product::class, $result);
        $this->assertFalse($createdNewProducts);
    }

    public function testItWillCreateNewProductByDimensions(): void
    {
        $dto = new RequestProductDto(
            1,
            5,
            4,
            6,
            5,
        );

        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($this->productRepository);

        $product = new Product(
            4,
            5,
            6,
            5,
        );

        $this->productRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'dimension1' => 4,
                'dimension2' => 5,
                'dimension3' => 6,
                'weight' => 5,
            ])
            ->willReturn(null);

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($product);

        $service = new ProductService($this->entityManager);
        $createdNewProducts = false;

        $result = $service->findByDimensionsOrCreate($dto, $createdNewProducts);
        $this->assertInstanceOf(Product::class, $result);
        $this->assertTrue($createdNewProducts);
    }
}
