<?php

namespace Clab\ApiBundle\Manager;

use Clab\ApiBundle\Entity\Session;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\OrderDetail;
use Doctrine\ORM\EntityManager;

use RMS\PushNotificationsBundle\Message\iOSMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;


class PushManager
{
    protected $em;

    public function __construct(EntityManager $em,ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function pushOrder(OrderDetail $order)
    {
    	// @todo restore for app pro
        $restaurant = $order->getCart()->getRestaurant();

        $deviceIdentifiers = $this->getDeviceIdentifiersForRestaurant($restaurant);

        if(count($deviceIdentifiers) > 0) {
            $this->apnsPush($deviceIdentifiers, 'Nouvelle commande dans votre restaurant ' . $order->getRestaurant()->getName());
        }
    }

    public function apnsPush(array $deviceIdentifiers, $text)
    {
        foreach ($deviceIdentifiers as $deviceIdentifier) {
            $message = new iOSMessage();
            $message->setMessage($text);
            $message->setDeviceIdentifier($deviceIdentifier);

            $this->container->get('rms_push_notifications')->send($message);
        }
    }

    public function getDeviceIdentifiersForRestaurant(Restaurant $restaurant)
    {
        $sessions = $this->em->getRepository(Session::class)->findAllForRestaurant($restaurant);

        $deviceIdentifiers = array();
        foreach ($sessions as $session) {
            if($session->getDeviceIdentifier() && !in_array($session->getDeviceIdentifier(), $deviceIdentifiers)) {
                $deviceIdentifiers[] = $session->getDeviceIdentifier();
            }
        }

        return $deviceIdentifiers;
    }
}
