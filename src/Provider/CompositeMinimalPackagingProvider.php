<?php

declare(strict_types=1);

namespace App\Provider;

use App\Entity\MinimalPackaging;
use App\Registry\ServiceRegistry;

readonly class CompositeMinimalPackagingProvider implements MinimalPackagingProviderInterface
{
    public function __construct(
        private ServiceRegistry $minimalPackagingProviderRegistry,
    ) {}

    public function findMinimalPackaging(array $products): ?MinimalPackaging
    {
        if (empty($products)) {
            return null;
        }

        $minimalPackaging = null;

        /** @var MinimalPackagingProviderInterface $provider */
        foreach ($this->minimalPackagingProviderRegistry->all() as $provider) {
            $minimalPackaging = $provider->findMinimalPackaging($products);

            if (null !== $minimalPackaging) {
                return $minimalPackaging;
            }
        }

        return $minimalPackaging;
    }
}
