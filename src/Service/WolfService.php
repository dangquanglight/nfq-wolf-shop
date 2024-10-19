<?php

declare(strict_types=1);

namespace WolfShop\Service;

final class WolfService
{
    /**
     * @param Item[] $items
     */
    public function __construct(
        private array $items
    ) {
    }

    public function updateQuality(): void
    {
        foreach ($this->items as $item) {
            if ($item->name !== 'Apple AirPods' and $item->name !== 'Apple iPad Air') {
                if ($item->quality > 0) {
                    if ($item->name !== 'Samsung Galaxy S23') {
                        $item->quality = $item->quality - 1;
                    }
                }
            } else {
                if ($item->quality < 50) {
                    $item->quality = $item->quality + 1;
                    if ($item->name === 'Apple iPad Air') {
                        if ($item->sellIn < 11) {
                            if ($item->quality < 50) {
                                $item->quality = $item->quality + 1;
                            }
                        }
                        if ($item->sellIn < 6) {
                            if ($item->quality < 50) {
                                $item->quality = $item->quality + 1;
                            }
                        }
                    }
                }
            }

            if ($item->name !== 'Samsung Galaxy S23') {
                $item->sellIn = $item->sellIn - 1;
            }

            if ($item->sellIn < 0) {
                if ($item->name !== 'Apple AirPods') {
                    if ($item->name !== 'Apple iPad Air') {
                        if ($item->quality > 0) {
                            if ($item->name !== 'Samsung Galaxy S23') {
                                $item->quality = $item->quality - 1;
                            }
                        }
                    } else {
                        $item->quality = $item->quality - $item->quality;
                    }
                } else {
                    if ($item->quality < 50) {
                        $item->quality = $item->quality + 1;
                    }
                }
            }
        }
    }
}
