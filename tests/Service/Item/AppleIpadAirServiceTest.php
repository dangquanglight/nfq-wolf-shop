<?php

declare(strict_types=1);

namespace WolfShop\Tests\Service\Item;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WolfShop\Entity\Item;
use WolfShop\Service\Item\AppleIpadAirService;

class AppleIpadAirServiceTest extends KernelTestCase
{
    public function testCalculateWithZeroQuality(): void
    {
        $item = new Item();
        $item->setQuality(0);

        $defaultService = new AppleIpadAirService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(0, $result);
    }

    public function testCalculate(): void
    {
        $item = new Item();
        $item->setQuality(8);
        $item->setSellIn(20);

        $defaultService = new AppleIpadAirService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(9, $result);
    }

    public function testCalculateWithSellInLessThanTen(): void
    {
        $item = new Item();
        $item->setQuality(8);
        $item->setSellIn(9);

        $defaultService = new AppleIpadAirService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(10, $result);
    }

    public function testCalculateWithSellInLessThanFive(): void
    {
        $item = new Item();
        $item->setQuality(5);
        $item->setSellIn(4);

        $defaultService = new AppleIpadAirService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(8, $result);
    }

    public function testCalculateWithZeroSellIn(): void
    {
        $item = new Item();
        $item->setQuality(8);
        $item->setSellIn(0);

        $defaultService = new AppleIpadAirService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(0, $result);
    }

    public function testCalculateWithMaxQuality(): void
    {
        $item = new Item();
        $item->setQuality(50);
        $item->setSellIn(10);

        $defaultService = new AppleIpadAirService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(50, $result);
    }

    public function testCalculateWithMaxQualityAndZeroSellIn(): void
    {
        $item = new Item();
        $item->setQuality(50);
        $item->setSellIn(0);

        $defaultService = new AppleIpadAirService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(0, $result);
    }
}
