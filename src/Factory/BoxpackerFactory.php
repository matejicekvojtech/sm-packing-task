<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\BoxPackerBox;
use App\Entity\BoxPackerItem;
use App\Entity\Packaging;
use App\Entity\Product;
use DVDoug\BoxPacker\Box;
use DVDoug\BoxPacker\Item;
use DVDoug\BoxPacker\Packer;

readonly class BoxpackerFactory
{
    public function createBox(Packaging $packaging): Box
    {
        return new BoxPackerBox(
            (int) $packaging->getId(),
            $packaging->getWidth(),
            $packaging->getLength(),
            $packaging->getHeight(),
            $packaging->getMaxWeight(),
        );
    }

    public function createItem(Product $product): Item
    {
        return new BoxPackerItem(
            (int) $product->getWarehouseItemId(),
            $product->getDimension1(),
            $product->getDimension2(),
            $product->getDimension3(),
            $product->getWeight(),
        );
    }

    public function createPacker(): Packer
    {
        return new Packer();
    }
}
