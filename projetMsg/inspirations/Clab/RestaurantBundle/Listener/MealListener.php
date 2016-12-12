<?php

namespace Clab\RestaurantBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Event\MealEvent;

class MealListener
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $meal = $args->getEntity();

        if ($meal instanceof Meal) {
            $event = new MealEvent($meal);
            $this->eventDispatcher->dispatch('app_restaurant.meal_created', $event);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $meal = $args->getEntity();

        if ($meal instanceof Meal) {
            $event = new MealEvent($meal);
            $this->eventDispatcher->dispatch('app_restaurant.meal_updated', $event);
        }
    }
}
