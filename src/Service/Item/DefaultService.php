<?php

declare(strict_types=1);

namespace WolfShop\Service\Item;

use WolfShop\Entity\Item;
use WolfShop\Interface\ItemQualityServiceInterface;

class DefaultService implements ItemQualityServiceInterface
{
    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * @return integer
     */
    public function calculate(): int
    {
        $newQuality = $currentQuality = $this->item->getQuality();
        if ($currentQuality > 0) {
            $newQuality = $currentQuality - 1;
        }

        $newSellIn = $this->item->getSellIn() - 1;
        if ($newSellIn < 0 && $newQuality > 0) {
            $newQuality = $newQuality - 1;
        }

        return $newQuality;
    }
}
