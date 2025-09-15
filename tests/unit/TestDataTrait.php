<?php

namespace Test\App;

use App\Entity\Packaging;
use App\Entity\Product;

trait TestDataTrait
{
    /**
     * @return Product[]
     */
    private function getProducts(): array
    {
        return [
            new Product(
                1.1,
                2.2,
                3.3,
                4,
            )->setId(1),
        ];
    }

    /**
     * @return Packaging[]
     */
    private function getPackagings(): array
    {
        return [
            new Packaging(
                10,
                10,
                10,
                10,
            )->setId(1),
        ];
    }
}
