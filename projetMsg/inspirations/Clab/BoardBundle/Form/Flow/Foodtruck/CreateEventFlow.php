<?php

namespace Clab\BoardBundle\Form\Flow\Foodtruck;

use Craue\FormFlowBundle\Form\FormFlow;
use Craue\FormFlowBundle\Form\FormFlowInterface;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

use Clab\BoardBundle\Form\Type\Foodtruck\EventAddressType;
use Clab\BoardBundle\Form\Type\Foodtruck\EventPlaceType;
use Clab\BoardBundle\Form\Type\Foodtruck\EventScheduleType;
use Clab\BoardBundle\Form\Type\Foodtruck\EventEventType;

class CreateEventFlow extends FormFlow
{
    protected $em;
    protected $container;
    protected $router;

    protected $places = array();
    protected $events = array();

    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
    }

    public function getName()
    {
        return 'board_create_event_flow';
    }

    protected function loadStepsConfig()
    {
        return array(
            array(
                'label' => 'address',
                'type' => new EventAddressType(),
            ),
            array(
                'label' => 'place',
                'type' => new EventPlaceType(),
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    return count($this->getPlaces($flow->getFormData()->getAddress())) == 0;
                },
            ),
            array(
                'label' => 'event',
                'type' => new EventEventType(),
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    return count($this->getEvents($flow->getFormData()->getPlace())) == 0;
                },
            ),
            array(
                'label' => 'schedule',
                'type' => new EventScheduleType(),
                'skip' => function($estimatedCurrentStepNumber, FormFlowInterface $flow) {
                    //return $flow->getFormData()->getEvent() !== null;
                    return false;
                },
            ),
        );
    }

    public function getFormOptions($step, array $options = array())
    {
        $options = parent::getFormOptions($step, $options);

        $formData = $this->getFormData();

        if($formData->getAddress()) {
            $this->getPlaces($formData->getAddress());
        }

        if($formData->getPlace()) {
            $this->getEvents($formData->getPlace());
        }

        if ($step === 2) {
            $options['places'] = $this->places;
        } elseif($step === 3) {
            $options['events'] = $this->events;
        }

        return $options;
    }

    public function getPlaces($address)
    {
        if($address) {
            $this->container->get('app_location.location_manager')->updateCoordinates($address);
            $this->places = $this->container->get('location.place_manager')->findNearBy($address);
        }

        return $this->places;
    }

    public function getEvents($place)
    {
        if($place) {
            $this->events = $this->container->get('location.event_manager')->getUpcomingForPlace($place);
        }

        return $this->events;
    }
}