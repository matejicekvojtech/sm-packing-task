<?php

namespace Test\App\Provider;

use App\Client\BinPackingClient;
use App\Client\BinPackingClientException;
use App\Dto\BinPackingDto;
use App\Entity\MinimalPackaging;
use App\Entity\Packaging;
use App\Provider\BinPackingApiMinimalPackagingProvider;
use App\Provider\MinimalPackagingProviderException;
use App\Service\MinimalPackagingService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Test\App\TestDataTrait;

class BinPackingApiMinimalPackagingProviderTest extends TestCase
{
    use TestDataTrait;

    private EntityManagerInterface&MockObject $entityManager;
    private BinPackingClient&MockObject $binPackingClient;
    private MinimalPackagingService&MockObject $minimalPackagingService;
    private EntityRepository&MockObject $packageRepository;

    private CacheInterface&MockObject $cache;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->binPackingClient = $this->createMock(BinPackingClient::class);
        $this->minimalPackagingService = $this->createMock(MinimalPackagingService::class);
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

        $provider = new BinPackingApiMinimalPackagingProvider(
            $this->entityManager,
            $this->cache,
            $this->binPackingClient,
            $this->minimalPackagingService,
        );

        $result = $provider->findMinimalPackaging([]);
        $this->assertNull($result);
    }

    public function testItWillReturnMinimalPackaging(): void
    {
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Packaging::class)
            ->willReturn($this->packageRepository);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($this->getPackagings());

        $dto = new BinPackingDto(
            1,
            50,
        );

        $this->binPackingClient->expects($this->once())
            ->method('getMinimalPackaging')
            ->with(
                $this->getProducts(),
                $this->getPackagings(),
            )
            ->willReturn($dto);

        $this->packageRepository->expects($this->once())
            ->method('find')
            ->with(1)
            ->willReturn(
                $this->getPackagings()[0],
            );

        $this->minimalPackagingService->expects($this->once())
            ->method('persistForProducts')
            ->with(
                $this->getPackagings()[0],
                $this->getProducts(),
                $dto->volumeUtilization,
            )
            ->willReturn(
                new MinimalPackaging(
                    $this->getPackagings()[0],
                    [1],
                    50,
                ),
            );

        $provider = new BinPackingApiMinimalPackagingProvider(
            $this->entityManager,
            $this->cache,
            $this->binPackingClient,
            $this->minimalPackagingService,
        );

        $result = $provider->findMinimalPackaging($this->getProducts());
        $this->assertInstanceOf(MinimalPackaging::class, $result);
    }

    public function testItWillReturnNullOnGuzzleException(): void
    {
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Packaging::class)
            ->willReturn($this->packageRepository);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($this->getPackagings());

        $this->binPackingClient->expects($this->once())
            ->method('getMinimalPackaging')
            ->with(
                $this->getProducts(),
                $this->getPackagings(),
            )
            ->willThrowException(
                new ConnectException('Error connection', new Request('GET', 'test')),
            );

        $provider = new BinPackingApiMinimalPackagingProvider(
            $this->entityManager,
            $this->cache,
            $this->binPackingClient,
            $this->minimalPackagingService,
        );

        $result = $provider->findMinimalPackaging($this->getProducts());
        $this->assertNull($result);
    }

    public function testItWillThrowExceptionOnClientException(): void
    {
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Packaging::class)
            ->willReturn($this->packageRepository);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($this->getPackagings());

        $this->binPackingClient->expects($this->once())
            ->method('getMinimalPackaging')
            ->with(
                $this->getProducts(),
                $this->getPackagings(),
            )
            ->willThrowException(
                new BinPackingClientException('Could not fit into 1 box'),
            );

        $provider = new BinPackingApiMinimalPackagingProvider(
            $this->entityManager,
            $this->cache,
            $this->binPackingClient,
            $this->minimalPackagingService,
        );

        $this->expectException(MinimalPackagingProviderException::class);
        $provider->findMinimalPackaging($this->getProducts());
    }

    public function testItWillThrowExceptionOnInvalidPackagingId(): void
    {
        $this->entityManager->expects($this->once())
            ->method('getRepository')
            ->with(Packaging::class)
            ->willReturn($this->packageRepository);

        $this->cache->expects($this->once())
            ->method('get')
            ->willReturn($this->getPackagings());

        $dto = new BinPackingDto(
            2,
            50,
        );

        $this->binPackingClient->expects($this->once())
            ->method('getMinimalPackaging')
            ->with(
                $this->getProducts(),
                $this->getPackagings(),
            )
            ->willReturn($dto);

        $this->packageRepository->expects($this->once())
            ->method('find')
            ->with(2)
            ->willReturn(null);

        $provider = new BinPackingApiMinimalPackagingProvider(
            $this->entityManager,
            $this->cache,
            $this->binPackingClient,
            $this->minimalPackagingService,
        );

        $this->expectException(MinimalPackagingProviderException::class);
        $provider->findMinimalPackaging($this->getProducts());
    }
}
