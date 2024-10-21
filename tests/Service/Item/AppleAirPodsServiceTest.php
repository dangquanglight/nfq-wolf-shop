<?php

declare(strict_types=1);

namespace WolfShop\Tests\Service\Item;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WolfShop\Entity\Item;
use WolfShop\Service\Item\AppleAirPodsService;

class AppleAirPodsServiceTest extends KernelTestCase
{
    public function testCalculateWithZeroQuality(): void
    {
        $item = new Item();
        $item->setQuality(0);

        $defaultService = new AppleAirPodsService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(2, $result);
    }

    public function testCalculate(): void
    {
        $item = new Item();
        $item->setQuality(5);
        $item->setSellIn(1);

        $defaultService = new AppleAirPodsService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(6, $result);
    }

    public function testCalculateWithZeroSellIn(): void
    {
        $item = new Item();
        $item->setQuality(5);
        $item->setSellIn(0);

        $defaultService = new AppleAirPodsService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(7, $result);
    }

    public function testCalculateWithMaxQuality(): void
    {
        $item = new Item();
        $item->setQuality(50);
        $item->setSellIn(1);

        $defaultService = new AppleAirPodsService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(50, $result);
    }

    public function testCalculateWithMaxQualityAndZeroSellIn(): void
    {
        $item = new Item();
        $item->setQuality(50);
        $item->setSellIn(0);

        $defaultService = new AppleAirPodsService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(50, $result);
    }
}
