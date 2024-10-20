<?php

declare(strict_types=1);

namespace WolfShop\Service\Item;

use WolfShop\Entity\Item;
use WolfShop\Interface\ItemQualityServiceInterface;

class SamsungGalaxyS23Service implements ItemQualityServiceInterface
{
    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * "Samsung Galaxy S23", being a legendary item, never has to be sold or decreases in Quality
     *
     * @return integer
     */
    public function calculate(): int
    {
        return $this->item->getQuality();
    }
}
