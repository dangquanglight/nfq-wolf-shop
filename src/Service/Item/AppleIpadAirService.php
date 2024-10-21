<?php

declare(strict_types=1);

namespace WolfShop\Service\Item;

use WolfShop\Entity\Item;
use WolfShop\Interface\ItemQualityServiceInterface;

class AppleIpadAirService implements ItemQualityServiceInterface
{
    private Item $item;

    public function __construct(Item $item)
    {
        $this->item = $item;
    }

    /**
     * Quality increases by 2 when there are 10 days or less and by 3 when there are 5 days or less but
     *
     * @return integer
     */
    public function calculate(): int
    {
        $newQuality = $currentQuality = $this->item->getQuality();
        $currentSellIn = $this->item->getSellIn();

        if ($currentQuality < ItemQualityServiceInterface::MAX_QUALITY) {
            if ($currentSellIn < 6) {
                $newQuality = $newQuality + 3;
            } elseif ($currentSellIn < 11) {
                $newQuality = $newQuality + 2;
            } else {
                $newQuality = $currentQuality + 1;
            }
        }

        $newSellIn = $this->item->getSellIn() - 1;
        if ($newSellIn < 0) {
            $newQuality = ItemQualityServiceInterface::MIN_QUALITY;
        }

        return $newQuality;
    }
}
