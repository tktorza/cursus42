<?php

namespace Clab\RestaurantBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Clab\RestaurantBundle\Entity\MealSlot;
use Clab\RestaurantBundle\Event\MealSlotEvent;

class MealSlotListener
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $mealSlot = $args->getEntity();

        if ($mealSlot instanceof MealSlot) {
            $event = new MealSlotEvent($mealSlot);
            $this->eventDispatcher->dispatch('app_restaurant.meal_slot_created', $event);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $mealSlot = $args->getEntity();

        if ($mealSlot instanceof MealSlot) {
            $event = new MealSlotEvent($mealSlot);
            $this->eventDispatcher->dispatch('app_restaurant.meal_slot_updated', $event);
        }
    }
}
