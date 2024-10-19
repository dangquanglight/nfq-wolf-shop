<?php

declare(strict_types=1);

namespace WolfShop\Interface;

use WolfShop\Entity\Item;

interface ItemQualityServiceInterface
{
    public const MAX_QUALITY = 50;

    public const MIN_QUALITY = 0;


    public function __construct(Item $item);

    /**
     * Calculate item quality
     *
     * @return integer
     */
    public function calculate(): int;
}
