<?php

declare(strict_types=1);

namespace WolfShop\Tests\Service\Item;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WolfShop\Entity\Item;
use WolfShop\Service\Item\SamsungGalaxyS23Service;

class SamsungGalaxyS23ServiceTest extends KernelTestCase
{
    public function testCalculateWithZeroQuality(): void
    {
        $item = new Item();
        $item->setQuality(0);

        $defaultService = new SamsungGalaxyS23Service($item);
        $result = $defaultService->calculate();

        $this->assertEquals(0, $result);
    }

    public function testCalculateWithZeroSellIn(): void
    {
        $item = new Item();
        $item->setQuality(80);
        $item->setSellIn(1);

        $defaultService = new SamsungGalaxyS23Service($item);
        $result = $defaultService->calculate();

        $this->assertEquals(80, $result);
    }

    public function testCalculate(): void
    {
        $item = new Item();
        $item->setQuality(80);
        $item->setSellIn(100);

        $defaultService = new SamsungGalaxyS23Service($item);
        $result = $defaultService->calculate();

        $this->assertEquals(80, $result);
    }
}
