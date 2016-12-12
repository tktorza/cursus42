<?php

namespace Clab\RestaurantBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Clab\RestaurantBundle\Entity\ProductOption;
use Clab\RestaurantBundle\Event\ProductOptionEvent;

class ProductOptionListener
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $productOption = $args->getEntity();

        if ($productOption instanceof ProductOption) {
            $event = new ProductOptionEvent($productOption);
            $this->eventDispatcher->dispatch('app_restaurant.product_option_created', $event);
        }
    }
}
