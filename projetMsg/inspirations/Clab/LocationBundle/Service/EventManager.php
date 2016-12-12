<?php

namespace Clab\LocationBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

class EventManager
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
        $this->repository = $this->em->getRepository('ClabLocationBundle:Event');
    }

    public function findAll()
    {
        return $this->repository->findBy(array('is_deleted' => false), array('name' => 'asc'));
    }

    public function find($id)
    {
        return $this->repository->findOneBy(array('id' => $id, 'is_deleted' => false));
    }

    public function findBySlug($slug)
    {
        return $this->repository->findOneBy(array('slug' => $slug, 'is_deleted' => false));
    }

    public function getUpcomingForPlace($place)
    {
        return $this->repository->getUpcomingForPlace($place);
    }

    public function getFoodtrucks($event)
    {
        $timesheets = $this->em->getRepository('ClabRestaurantBundle:TimeSheet')
            ->findBy(array('event' => $event));

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
                $foodtrucks[] = $foodtruckManager->createFoodtruck($restaurant, '3 months', false, array('event' => $event));
            }
        }

        return $foodtrucks;
    }

    /* Manager */
    public function findAllForManager($user)
    {
        $events = array();

        foreach ($user->getEvents() as $event) {
            if($event->isAvailable()) {
                $events[] = $event;
            }
        }

        return $events;
    }

    public function findForManager($id, $user)
    {
        $event = $this->repository->findOneBy(array('id' => $id, 'is_deleted' => false));

        if($event->isAllowed($user)) {
            return $event;
        }

        return null;
    }
}
