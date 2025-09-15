<?php

declare(strict_types=1);

namespace App\Provider;

use App\Entity\MinimalPackaging;
use App\Service\MinimalPackagingService;

readonly class DatabaseMinimalPackagingProvider implements MinimalPackagingProviderInterface
{
    public function __construct(
        private MinimalPackagingService $minimalPackagingService,
    ) {}

    public function findMinimalPackaging(array $products): ?MinimalPackaging
    {
        return $this->minimalPackagingService->getByProducts($products);
    }
}
