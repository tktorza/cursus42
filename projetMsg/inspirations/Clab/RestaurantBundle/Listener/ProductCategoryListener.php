<?php

namespace Clab\RestaurantBundle\Listener;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ProductCategoryListener
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }
}
