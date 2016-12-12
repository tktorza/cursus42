<?php

namespace Clab\RestaurantBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Event\ProductEvent;

class ProductListener
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $product = $args->getEntity();

        if ($product instanceof Product) {
            $event = new ProductEvent($product);
            $this->eventDispatcher->dispatch('app_restaurant.product_created', $event);
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $product = $args->getEntity();

        if ($product instanceof Product) {
            $event = new ProductEvent($product);
            $this->eventDispatcher->dispatch('app_restaurant.product_updated', $event);
        }
    }
}
