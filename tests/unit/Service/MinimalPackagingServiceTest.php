<?php

namespace Test\App\Service;

use App\Entity\MinimalPackaging;
use App\Service\MinimalPackagingService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\App\TestDataTrait;

class MinimalPackagingServiceTest extends TestCase
{
    use TestDataTrait;

    private EntityManagerInterface&MockObject $entityManager;
    private EntityRepository&MockObject $minimalPackagingRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->minimalPackagingRepository = $this->createMock(EntityRepository::class);
    }

    public function testItWillFindPersistedPackagingByProducts(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(MinimalPackaging::class)
            ->willReturn($this->minimalPackagingRepository);

        $this->minimalPackagingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'productIdsMap' => '1',
            ])
            ->willReturn(
                new MinimalPackaging(
                    $this->getPackagings()[0],
                    [1],
                    50,
                ),
            );

        $service = new MinimalPackagingService(
            $this->entityManager,
        );

        $result = $service->getByProducts($this->getProducts());
        $this->assertInstanceOf(MinimalPackaging::class, $result);
    }

    public function testItWillNotFindPersistedPackagingByProducts(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(MinimalPackaging::class)
            ->willReturn($this->minimalPackagingRepository);

        $this->minimalPackagingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with([
                'productIdsMap' => '1',
            ])
            ->willReturn(null);

        $service = new MinimalPackagingService(
            $this->entityManager,
        );

        $result = $service->getByProducts($this->getProducts());
        $this->assertNull($result);
    }

    public function testItWillPersistMinimalPackagingForProducts(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(MinimalPackaging::class)
            ->willReturn($this->minimalPackagingRepository);

        $minimalPackaging = new MinimalPackaging(
            $this->getPackagings()[0],
            [1],
            50,
        );

        $this->entityManager->expects($this->once())
            ->method('persist')
            ->with($minimalPackaging);

        $this->entityManager->expects($this->once())
            ->method('flush');

        $service = new MinimalPackagingService($this->entityManager);
        $result = $service->persistForProducts($this->getPackagings()[0], $this->getProducts(), 50);

        $this->assertInstanceOf(MinimalPackaging::class, $result);
    }

    public function testItWillThrowExceptionOnNoProductsForPackaging(): void
    {
        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(MinimalPackaging::class)
            ->willReturn($this->minimalPackagingRepository);


        $service = new MinimalPackagingService($this->entityManager);

        $this->expectException(\LogicException::class);
        $service->persistForProducts($this->getPackagings()[0], [], 50);
    }


}
