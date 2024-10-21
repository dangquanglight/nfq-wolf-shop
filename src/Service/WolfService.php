<?php

declare(strict_types=1);

namespace WolfShop\Service;

use Doctrine\ORM\EntityManager;
use WolfShop\Entity\Item;
use WolfShop\Factory\ItemQualityFactory;

final class WolfService
{
    private const BATCH_SIZE = 100;

    public function __construct(
        private EntityManager $entityManager,
    ) {
    }

    /**
     * Update quality and sell_in for all items in database
     */
    public function updateQuality(): void
    {
        $items = $this->entityManager->getRepository(Item::class)->findAllWithIterable();

        $i = 0;
        foreach ($items as $item) {
            /** @var Item $item */
            $itemQualityHandler = new ItemQualityFactory($item);

            // Set new quality after calculation
            $item->setQuality(
                $itemQualityHandler->calculate()
            );

            // Decrease sell_in by 1
            $item->setSellIn(
                $item->getSellIn() - 1
            );

            ++$i;
            if (($i % self::BATCH_SIZE) === 0) {
                $this->entityManager->flush(); // Executes all updates.
                $this->entityManager->clear(); // Detaches all objects from Doctrine to free up memory
            }
        }

        $this->entityManager->flush(); // Persist objects that did not make up an entire batch
    }
}
