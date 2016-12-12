<?php

namespace Clab\RestaurantBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Clab\RestaurantBundle\Entity\Meal;

class MealEvent extends Event
{
    protected $meal;

    public function __construct(Meal $meal)
    {
        $this->meal = $meal;
    }

    public function getMeal()
    {
        return $this->meal;
    }
}
