<?php

declare(strict_types=1);

namespace WolfShop\Service\Item;

use WolfShop\Entity\Item;
use WolfShop\Interface\ItemQualityServiceInterface;

class AppleAirPodsService implements ItemQualityServiceInterface
{
    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * "Apple AirPods" actually increases in Quality the older it gets
     *
     * @return integer
     */
    public function calculate(): int
    {
        $newQuality = $currentQuality = $this->item->getQuality();
        if ($currentQuality < ItemQualityServiceInterface::MAX_QUALITY) {
            $newQuality = $currentQuality + 1;
        }

        $newSellIn = $this->item->getSellIn() - 1;
        if ($newSellIn < 0 && $newQuality < ItemQualityServiceInterface::MAX_QUALITY) {
            $newQuality = $newQuality + 1;
        }

        return $newQuality;
    }
}
