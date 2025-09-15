<?php

namespace Test\App\Provider;

use App\Entity\MinimalPackaging;
use App\Provider\DatabaseMinimalPackagingProvider;
use App\Service\MinimalPackagingService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Test\App\TestDataTrait;

class DatabaseMinimalPackagingProviderTest extends TestCase
{
    use TestDataTrait;

    private MinimalPackagingService&MockObject $minimalPackagingService;

    public function setUp(): void
    {
        $this->minimalPackagingService = $this->createMock(MinimalPackagingService::class);
    }

    public function testItWillFindMinimalPackaging(): void
    {
        $minimalPackaging = new MinimalPackaging(
            $this->getPackagings()[0],
            [1],
            50,
        );
        $this->minimalPackagingService->expects($this->once())
            ->method('getByProducts')
            ->with($this->getProducts())
            ->willReturn(
                $minimalPackaging,
            );

        $provider = new DatabaseMinimalPackagingProvider($this->minimalPackagingService);

        $result = $provider->findMinimalPackaging($this->getProducts());
        $this->assertInstanceOf(MinimalPackaging::class, $result);
    }

    public function testItWillNotFindMinimalPackaging(): void
    {
        $this->minimalPackagingService->expects($this->once())
            ->method('getByProducts')
            ->with($this->getProducts())
            ->willReturn(
                null,
            );

        $provider = new DatabaseMinimalPackagingProvider($this->minimalPackagingService);

        $result = $provider->findMinimalPackaging($this->getProducts());
        $this->assertNull($result);
    }
}
