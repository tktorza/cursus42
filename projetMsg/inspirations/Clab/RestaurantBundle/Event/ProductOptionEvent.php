<?php

namespace Clab\RestaurantBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Clab\RestaurantBundle\Entity\ProductOption;

class ProductOptionEvent extends Event
{
    protected $productOption;

    public function __construct(ProductOption $productOption)
    {
        $this->productOption = $productOption;
    }

    public function getProductOption()
    {
        return $this->productOption;
    }
}
