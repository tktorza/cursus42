<?php

namespace Clab\DeliveryBundle\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;

use Clab\DeliveryBundle\Entity\DeliveryDay;

class DeliveryDayListener
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof DeliveryDay) {
            $this->em = $args->getEntityManager();
            $this->updateTime($entity);

            $entityManager->flush();
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof DeliveryDay) {
            $this->em = $args->getEntityManager();
            $this->updateTime($entity);

            $entityManager->flush();
        }
    }

    public function updateTime($deliveryDay)
    {/*
        if($deliveryDay->getDeliverySchedule()) {
            $slotLength = $deliveryDay->getDeliverySchedule()->getSlotLength();
            $startMin = (int) $deliveryDay->getStart()->format('i');
            $endMin = (int) $deliveryDay->getEnd()->format('i');
            switch ($slotLength) {
                case 15:
                    if($startMin != 0 || $startMin != 15 || $startMin != 30 || $startMin != 45) {
                        $gap = $slotLength * ceil($startMin / $slotLength) - $startMin;
                    }
                    break;
                case 20:
                    break;
                case 30:
                    break;
                default:
                    break;
            }
        }

        die('ici');*/
    }
}