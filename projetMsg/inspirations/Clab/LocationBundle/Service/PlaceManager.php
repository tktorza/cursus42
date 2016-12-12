<?php

namespace Clab\LocationBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

use Clab\LocationBundle\Entity\Place;

class PlaceManager
{
    protected $em;
    protected $container;
    protected $router;
    protected $repository;

    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
        $this->repository = $this->em->getRepository('ClabLocationBundle:Place');
    }

    public function findAll()
    {
        return $this->repository->findBy(array('is_deleted' => false, 'is_online' => true), array('name' => 'asc'));
    }

    public function find($id)
    {
        $place = $this->repository->findOneBy(array('id' => $id, 'is_online' => true, 'is_deleted' => false));

        return $place;
    }

    public function findBySlug($slug)
    {
        return $this->repository->findOneBy(array('slug' => $slug, 'is_deleted' => false));
    }

    public function findNearBy($address, $distance = 0.1)
    {
        $results = $this->repository->findNearBy($address, $distance);

        $places = array();
        foreach ($results as $result) {
            $result[0]->setDistance($result['distance']);
            $places[] = $result[0];
        }

        return $places;
    }

    public function getFoodtrucks($place)
    {
        $timesheets = $this->em->getRepository('ClabRestaurantBundle:Timesheet')
            ->findBy(array('place' => $place));

        $restaurants = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($timesheets as $timesheet) {
            if($timesheet->getRestaurant() && $timesheet->getRestaurant()->isMobile() && !$restaurants->contains($timesheet->getRestaurant())) {
                $restaurants->add($timesheet->getRestaurant());
            }
        }

        $foodtrucks = array();
        $foodtruckManager = $this->container->get('clab_ttt.foodtruck_manager');

        foreach ($restaurants as $restaurant) {
            if($restaurant->isTtt()) {
                $foodtrucks[] = $foodtruckManager->createFoodtruck($restaurant, '3 months', false, array('place' => $place));
            }
        }

        return $foodtrucks;
    }
}
