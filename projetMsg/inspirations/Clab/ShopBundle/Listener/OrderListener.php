<?php

namespace Clab\ShopBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

use Clab\ShopBundle\Event\OrderEvent;
use Clab\ShopBundle\Entity\OrderDetail;
use Clab\DeliveryBundle\Entity\Delivery;

class OrderListener
{
    protected $container;
    protected $em;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function onValidated(OrderEvent $event)
    {
        // moved to order manager
    }

    public function onClosed(OrderEvent $event)
    {
        // moved to order manager
    }
}
