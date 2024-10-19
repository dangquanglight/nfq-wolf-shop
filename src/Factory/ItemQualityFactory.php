<?php

declare(strict_types=1);

namespace WolfShop\Factory;

use WolfShop\Entity\Item;
use WolfShop\Enum\WolfItemNamesEnum;
use WolfShop\Interface\ItemQualityServiceInterface;
use WolfShop\Service\Item\AppleAirPodsService;
use WolfShop\Service\Item\AppleIpadAirService;
use WolfShop\Service\Item\DefaultService;
use WolfShop\Service\Item\SamsungGalaxyS23Service;

class ItemQualityFactory
{
    protected ItemQualityServiceInterface $handler;


    public function __construct(Item $item)
    {
        $this->handler = match ($item->getName()) {
            WolfItemNamesEnum::AppleIpadAir->value => new AppleIpadAirService($item),
            WolfItemNamesEnum::AppleAirPods->value => new AppleAirPodsService($item),
            WolfItemNamesEnum::SamsungGalaxyS23->value => new SamsungGalaxyS23Service($item),
            default => new DefaultService($item)
        };
    }

    /**
     * @return integer
     */
    public function calculate(): int
    {
        return $this->handler->calculate();
    }
}
