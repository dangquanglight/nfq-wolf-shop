<?php

declare(strict_types=1);

namespace WolfShop\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use WolfShop\Entity\Item;

class WolfItemsFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 100; $i++) {
            $item = new Item();
            $item->setName('Item ' . $i);
            $item->setQuality(rand(0, 50));
            $item->setSellIn(rand(10, 100));

            $manager->persist($item);
        }

        $manager->flush();
    }
}
