<?php

namespace Clab\RestaurantBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClabRestaurantBundle extends Bundle
{
    public function boot()
    {
        // get the doctrine 2 entity manager
        $em = $this->container->get('doctrine.orm.default_entity_manager');

        // get the event manager
        $evm = $em->getEventManager();
        $evm->addEventSubscriber(new \Gedmo\Sortable\SortableListener());
    }
}
