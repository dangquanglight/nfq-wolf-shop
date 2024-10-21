<?php

declare(strict_types=1);

namespace WolfShop\Tests\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use WolfShop\Entity\Item;
use WolfShop\Factory\ItemQualityFactory;
use WolfShop\Repository\ItemRepository;
use WolfShop\Service\WolfService;

class WolfServiceTest extends KernelTestCase
{
    public function testUpdateQualityWithEmptyRecord(): void
    {
        $kernel = self::bootKernel();

        $itemRepositoryMock = $this->createMock(ItemRepository::class);
        $itemRepositoryMock->expects($this->once())->method('findAllWithIterable')->willReturn(new \ArrayIterator([]));

        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock->expects($this->once())->method('getRepository')->willReturn($itemRepositoryMock);

        $itemFactoryMock = $this->createMock(ItemQualityFactory::class);
        $itemFactoryMock->expects($this->exactly(0))->method('calculate')->willReturnSelf();

        $container = $kernel->getContainer();
        $container->set(ItemQualityFactory::class, $itemFactoryMock);

        $wolfService = new WolfService($entityManagerMock);
        $wolfService->updateQuality();
    }

    public function testUpdateQuality(): void
    {
        $items = [];
        for ($i = 0; $i < 200; $i++) {
            $item = new Item();

            $item->setName('Item ' . $i);
            $item->setQuality(rand(5, 50));
            $item->setSellIn(rand(10, 100));

            $items[] = $item;
        }

        // Clone a whole new variable in different memory address
        $originItems = array_map(fn ($o) => clone $o, $items);

        $itemRepositoryMock = $this->createMock(ItemRepository::class);
        $itemRepositoryMock->expects($this->once())
            ->method('findAllWithIterable')
            ->willReturn(new \ArrayIterator($items));

        $entityManagerMock = $this->createMock(EntityManager::class);
        $entityManagerMock->expects($this->once())
            ->method('getRepository')
            ->with(Item::class)
            ->willReturn($itemRepositoryMock);
        $entityManagerMock->expects($this->exactly(3))->method('flush')->willReturnSelf();
        $entityManagerMock->expects($this->exactly(2))->method('clear')->willReturnSelf();

        $wolfService = new WolfService($entityManagerMock);
        $wolfService->updateQuality();

        foreach ($originItems as $key => $originalItem) {
            $this->assertEquals($originalItem->getQuality() - 1, $items[$key]->getQuality());
            $this->assertEquals($originalItem->getSellIn() - 1, $items[$key]->getSellIn());
        }
    }
}
