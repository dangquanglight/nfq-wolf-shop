<?php

declare(strict_types=1);

namespace WolfShop\Tests\Factory;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WolfShop\Entity\Item;
use WolfShop\Factory\ItemQualityFactory;
use WolfShop\Service\Item\AppleAirPodsService;
use WolfShop\Service\Item\AppleIpadAirService;
use WolfShop\Service\Item\DefaultService;
use WolfShop\Service\Item\SamsungGalaxyS23Service;

class ItemQualityFactoryTest extends KernelTestCase
{
    public function testInitWithDefaultService(): void
    {
        $item = new Item();
        $item->setName('test');

        $itemQualityHandler = new ItemQualityFactory($item);
        $this->assertEquals(DefaultService::class, get_class($itemQualityHandler->getHandler()));
    }

    public function testInitWithAppleIpadAirService(): void
    {
        $item = new Item();
        $item->setName('Apple iPad Air');

        $itemQualityHandler = new ItemQualityFactory($item);
        $this->assertEquals(AppleIpadAirService::class, get_class($itemQualityHandler->getHandler()));
    }

    public function testInitWithAppleAirPodsService(): void
    {
        $item = new Item();
        $item->setName('Apple AirPods');

        $itemQualityHandler = new ItemQualityFactory($item);
        $this->assertEquals(AppleAirPodsService::class, get_class($itemQualityHandler->getHandler()));
    }

    public function testInitWithSamsungGalaxyS23Service(): void
    {
        $item = new Item();
        $item->setName('Samsung Galaxy S23');

        $itemQualityHandler = new ItemQualityFactory($item);
        $this->assertEquals(SamsungGalaxyS23Service::class, get_class($itemQualityHandler->getHandler()));
    }
}
