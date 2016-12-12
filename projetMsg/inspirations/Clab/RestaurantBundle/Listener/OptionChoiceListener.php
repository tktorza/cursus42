<?php

namespace Clab\RestaurantBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Event\OptionChoiceEvent;

class OptionChoiceListener
{
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $optionChoice = $args->getEntity();

        if ($optionChoice instanceof OptionChoice) {
            $event = new OptionChoiceEvent($optionChoice);
            $this->eventDispatcher->dispatch('app_restaurant.option_choice_created', $event);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $optionChoice = $args->getEntity();

        if ($optionChoice instanceof OptionChoice) {
            $event = new OptionChoiceEvent($optionChoice);
            $this->eventDispatcher->dispatch('app_restaurant.option_choice_updated', $event);
        }
    }
}
