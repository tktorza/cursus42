<?php

namespace Clab\RestaurantBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Clab\RestaurantBundle\Entity\MealSlot;

class MealSlotEvent extends Event
{
    protected $mealSlot;

    public function __construct(MealSlot $mealSlot)
    {
        $this->mealSlot = $mealSlot;
    }

    public function getMealSlot()
    {
        return $this->mealSlot;
    }
}
