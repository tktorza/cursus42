<?php

namespace Clab\RestaurantBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Clab\RestaurantBundle\Entity\Restaurant as RestaurantEntity;
use Clab\LocationBundle\Entity\Address;
use Clab\RestaurantBundle\Entity\Deal;

class Restaurant
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $restaurant = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($restaurant instanceof RestaurantEntity) {
            if (!$restaurant->getAddress()) {
                $address = new Address();
                $restaurant->setAddress($address);
                $entityManager->persist($address);
            }

            if (!$restaurant->getDeal()) {
                $deal = new Deal();
                $restaurant->setDeal($deal);
                $deal->addStatusHistory(RestaurantEntity::STORE_STATUS_NEW, $restaurant->getCreated()->getTimestamp());
                $entityManager->persist($deal);
            }

            $entityManager->flush();

            if (!$restaurant->getSubscriptionTerms()) {
                $this->container->get('app_admin.subscription_manager')->initSubscriptionTerms($restaurant);
            }

            if (!$restaurant->getSocialProfile()) {
                $this->container->get('clab.social_manager')->initSocialProfile($restaurant);
            }
        }
    }
}
