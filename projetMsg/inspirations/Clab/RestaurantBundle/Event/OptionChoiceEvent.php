<?php

namespace Clab\RestaurantBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Clab\RestaurantBundle\Entity\OptionChoice;

class OptionChoiceEvent extends Event
{
    protected $optionChoice;

    public function __construct(OptionChoice $optionChoice)
    {
        $this->optionChoice = $optionChoice;
    }

    public function getOptionChoice()
    {
        return $this->optionChoice;
    }
}
