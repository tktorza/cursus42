<?php

namespace Clab\ApiOldBundle\Manager;

use Clab\ShopBundle\Entity\OrderDetail;
use Monolog\Logger;
use Doctrine\ORM\EntityManager;

use Clab\ApiOldBundle\Logger\PushLogger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PushManager
{
    protected $em;

    public function __construct(EntityManager $em,ContainerInterface $container, Logger $logger, $rootdir)
    {
        $this->em = $em;
        $this->container = $container;
        $this->logger = $logger;
        $this->rootdir = $rootdir;
    }

    public function pushOrder(OrderDetail $order)
    {
    	/* @todo restore for app pro
        $restaurant = $order->getCart()->getRestaurant();


    	$sessions = $this->container->get('api.session_manager')->getActiveSessionForRestaurant($restaurant, 'clickeatpro', 'ios');

        $deviceIdentifiers = array();
        $deviceIdentifiersString = '';
    	foreach ($sessions as $session) {
            if($session->getDeviceIdentifier() && !in_array($session->getDeviceIdentifier(), $deviceIdentifiers)) {
                $deviceIdentifiers[] = $session->getDeviceIdentifier();
                $deviceIdentifiersString .= ', ' . $session->getDeviceIdentifier();
            }
        }

        $this->logger->error('Pushto ' . $deviceIdentifiersString);

        if(count($deviceIdentifiers) > 0) {
            $this->apnsPush('clickeatpro', 1000, $deviceIdentifiers, 'Nouvelle commande dans votre restaurant ' . $order->getRestaurant()->getName());
        }*/
    }

    public function apnsPush($service, $code, array $deviceIdentifiers, $text)
    {
        $push = new \ApnsPHP\Push(
            \ApnsPHP\AbstractClass::ENVIRONMENT_PRODUCTION,
           $this->rootdir . '/cert/' . $service . '.pem'
        );
        $logger = new PushLogger();
        $push->setLogger($logger);

        //@todo in parameters.yml
        $push->setProviderCertificatePassphrase('UclickUapple75');

        $push->connect();

        foreach ($deviceIdentifiers as $deviceIdentifier) {
            $message = new \ApnsPHP\Message($deviceIdentifier);
            $message->setText($text);
            $message->setCustomIdentifier($code);
            $message->setCustomProperty($service, $service);
            $message->setCustomProperty($code, $code);
            $push->add($message);
        }

        $push->send();

        $push->disconnect();
    }

    public function getDeviceIdentifiersForRestaurant($service, $restaurant)
    {
        $sessions = $this->em->getRepository('ClabApiBundle:Session')->findBy(array(
            'isActive' => true,
            'restaurant' => $restaurant,
            'service' => $service,
            'system' => 'ios',
        ));

        $deviceIdentifiers = array();
        foreach ($sessions as $session) {
            if($session->getDeviceIdentifier() && !in_array($session->getDeviceIdentifier(), $deviceIdentifiers)) {
                $deviceIdentifiers[] = $session->getDeviceIdentifier();
            }
        }

        return $deviceIdentifiers;
    }
}
