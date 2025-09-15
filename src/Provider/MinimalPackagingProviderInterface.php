<?php

declare(strict_types=1);

namespace App\Provider;

use App\Entity\MinimalPackaging;
use App\Entity\Product;

interface MinimalPackagingProviderInterface
{
    /**
     * @param Product[] $products
     */
    public function findMinimalPackaging(array $products): ?MinimalPackaging;
}
