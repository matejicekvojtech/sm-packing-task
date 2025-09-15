<?php

namespace Test\App\Provider;

use App\Entity\MinimalPackaging;
use App\Entity\Packaging;
use App\Factory\BoxpackerFactory;
use App\Provider\BoxpackerMinimalPackagingProvider;
use App\Provider\MinimalPackagingProviderException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use DVDoug\BoxPacker\Box;
use DVDoug\BoxPacker\PackedBox;
use DVDoug\BoxPacker\PackedBoxList;
use DVDoug\BoxPacker\PackedItemList;
use DVDoug\BoxPacker\Packer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Test\App\TestDataTrait;

class BoxpackerMinimalPackagingProviderTest extends TestCase
{
    use TestDataTrait;

    private EntityManagerInterface&MockObject $entityManager;
    private BoxpackerFactory&MockObject $boxpackerFactory;
    private EntityRepository&MockObject $packageRepository;
    private CacheInterface&MockObject $cache;

    public function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->boxpackerFactory = $this->createMock(BoxpackerFactory::class);
        $this->packageRepository = $this->createMock(EntityRepository::class);
        $this->cache = $this->createMock(CacheInterface::class);
    }

    public function testItWillReturnNullOnNoPackagings(): void
    {
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Packaging::class)
            ->willReturn($this->packageRepository);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn([]);

        $provider = new BoxpackerMinimalPackagingProvider(
            $this->entityManager,
            $this->cache,
            $this->boxpackerFactory,
        );

        $result = $provider->findMinimalPackaging($this->getProducts());
        $this->assertNull($result);
    }

    public function testItWillCalculateMinimalPackaging(): void
    {
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Packaging::class)
            ->willReturn($this->packageRepository);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($this->getPackagings());

        $packer = $this->createMock(Packer::class);

        $this->boxpackerFactory->expects($this->once())
            ->method('createPacker')
            ->willReturn($packer);

        $this->boxpackerFactory->expects($this->once())
            ->method('createBox')
            ->with($this->getPackagings()[0]);

        $packer->expects($this->once())
            ->method('addBox');

        $this->boxpackerFactory->expects($this->once())
            ->method('createItem')
            ->with($this->getProducts()[0]);

        $packer->expects($this->once())
            ->method('addItem');

        $packedBoxes = $this->createMock(PackedBoxList::class);

        $packer->expects($this->once())
            ->method('pack')
            ->willReturn($packedBoxes);

        $packedBoxes->expects($this->exactly(2))
            ->method('count')
            ->willReturn(1);

        $packedItemList = $this->createMock(PackedItemList::class);
        $box = $this->createMock(Box::class);

        $packedBox = new PackedBox($box, $packedItemList);
        $packedBoxes->expects($this->once())
            ->method('top')
            ->willReturn($packedBox);

        $box->expects($this->once())
            ->method('getReference')
            ->willReturn('1');

        $this->packageRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn($this->getPackagings()[0]);

        $provider = new BoxpackerMinimalPackagingProvider(
            $this->entityManager,
            $this->cache,
            $this->boxpackerFactory,
        );

        $result = $provider->findMinimalPackaging($this->getProducts());
        $this->assertInstanceOf(MinimalPackaging::class, $result);
    }

    public function testItWillThrowExceptionOnMultipleBoxes(): void
    {
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Packaging::class)
            ->willReturn($this->packageRepository);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($this->getPackagings());

        $packer = $this->createMock(Packer::class);

        $this->boxpackerFactory->expects($this->once())
            ->method('createPacker')
            ->willReturn($packer);

        $this->boxpackerFactory->expects($this->once())
            ->method('createBox')
            ->with($this->getPackagings()[0]);

        $packer->expects($this->once())
            ->method('addBox');

        $this->boxpackerFactory->expects($this->once())
            ->method('createItem')
            ->with($this->getProducts()[0]);

        $packer->expects($this->once())
            ->method('addItem');

        $packedBoxes = $this->createMock(PackedBoxList::class);

        $packer->expects($this->once())
            ->method('pack')
            ->willReturn($packedBoxes);

        $packedBoxes->expects($this->once())
            ->method('count')
            ->willReturn(2);

        $provider = new BoxpackerMinimalPackagingProvider(
            $this->entityManager,
            $this->cache,
            $this->boxpackerFactory,
        );

        $this->expectException(MinimalPackagingProviderException::class);
        $provider->findMinimalPackaging($this->getProducts());
    }

    public function testItWillReturnNullWhenNoBoxWasFound(): void
    {
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Packaging::class)
            ->willReturn($this->packageRepository);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($this->getPackagings());

        $packer = $this->createMock(Packer::class);

        $this->boxpackerFactory->expects($this->once())
            ->method('createPacker')
            ->willReturn($packer);

        $this->boxpackerFactory->expects($this->once())
            ->method('createBox')
            ->with($this->getPackagings()[0]);

        $packer->expects($this->once())
            ->method('addBox');

        $this->boxpackerFactory->expects($this->once())
            ->method('createItem')
            ->with($this->getProducts()[0]);

        $packer->expects($this->once())
            ->method('addItem');

        $packedBoxes = $this->createMock(PackedBoxList::class);

        $packer->expects($this->once())
            ->method('pack')
            ->willReturn($packedBoxes);

        $packedBoxes->expects($this->exactly(2))
            ->method('count')
            ->willReturn(0);

        $provider = new BoxpackerMinimalPackagingProvider(
            $this->entityManager,
            $this->cache,
            $this->boxpackerFactory,
        );

        $result = $provider->findMinimalPackaging($this->getProducts());
        $this->assertNull($result);
    }
}
