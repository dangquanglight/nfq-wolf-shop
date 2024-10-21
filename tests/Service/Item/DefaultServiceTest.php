<?php

declare(strict_types=1);

namespace WolfShop\Tests\Service\Item;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WolfShop\Entity\Item;
use WolfShop\Service\Item\DefaultService;

class DefaultServiceTest extends KernelTestCase
{
    public function testCalculateWithZeroQuality(): void
    {
        $item = new Item();
        $item->setQuality(0);

        $defaultService = new DefaultService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(0, $result);
    }

    public function testCalculate(): void
    {
        $item = new Item();
        $item->setQuality(2);
        $item->setSellIn(1);

        $defaultService = new DefaultService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(1, $result);
    }

    public function testCalculateWithSellInGreaterThanTwo(): void
    {
        $item = new Item();
        $item->setQuality(5);
        $item->setSellIn(2);

        $defaultService = new DefaultService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(4, $result);
    }

    public function testCalculateWithZeroSellInAndQualityGreaterThanOne(): void
    {
        $item = new Item();
        $item->setQuality(5);
        $item->setSellIn(0);

        $defaultService = new DefaultService($item);
        $result = $defaultService->calculate();

        $this->assertEquals(3, $result);
    }
}
