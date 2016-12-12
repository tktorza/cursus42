<?php

namespace Clab\LocationBundle\Service;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

use Clab\LocationBundle\Entity\Address;

class AddressListener
{
    protected $container;
    protected $em;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof Address) {
            $this->em = $args->getEntityManager();
            $this->updateCoordinates($entity);

            $entityManager->flush();
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $env = $this->container->get( 'kernel' )->getEnvironment();
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof Address) {
            $this->em = $args->getEntityManager();
            $this->updateCoordinates($entity);

            //$entityManager->flush();
        }
    }

    public function updateCoordinates($address)
    {
        $geocoder = $this->container->get('app_location.location_manager')->getGeocoder();
        $response = $geocoder->geocode($address->getStreet() . ' ' . $address->getZip() . ' ' . $address->getCity());

        if($response->getStatus() == 'OK') {
            $results = $response->getResults();
            $location = $results[0];
        }

        if(isset($location) && $location) {
            $coordinates = $location->getGeometry()->getLocation();

            $latitude = $coordinates->getLatitude();
            $longitude = $coordinates->getLongitude();

            $address->setLatitude($latitude);
            $address->setLongitude($longitude);
        }
    }
}
