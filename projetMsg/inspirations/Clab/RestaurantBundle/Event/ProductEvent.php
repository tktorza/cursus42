<?php

namespace Clab\RestaurantBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Clab\RestaurantBundle\Entity\Product;

class ProductEvent extends Event
{
    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getProduct()
    {
        return $this->product;
    }
}
