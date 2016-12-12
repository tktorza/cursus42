<?php

namespace Clab\RestaurantBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Clab\RestaurantBundle\Entity\ProductCategory;

class ProductCategoryEvent extends Event
{
    protected $category;

    public function __construct(ProductCategory $category)
    {
        $this->category = $category;
    }

    public function getProductCategory()
    {
        return $this->category;
    }
}
