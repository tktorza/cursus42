<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\LocationBundle\Entity\Event;
use Clab\LocationBundle\Entity\Place;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Response;

class RestEventController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Place",
     *      description="Place List",
     *      requirements={
     *      },
     * )
     */
    public function listPlaceAction()
    {
        $places = $this->get('location.place_manager')->findAll();

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($places, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Place",
     *      description="Place get",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id place"},
     *      },
     *      output="Clab\LocationBundle\Entity\Place"
     * )
     * @ParamConverter("restaurant", class="ClabLocationBundle:Place", options={"id" = "id"})
     */
    public function getPlaceAction(Place $place)
    {
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($place, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Place",
     *      description="Place get posts",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id place"},
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabLocationBundle:Place", options={"id" = "id"})
     */
    public function getPlacePostsAction(Place $place)
    {
        $result = $this->get('clab.social_manager')->getFeedForEntity($place);
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($result, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Place",
     *      description="Place get events",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id place"},
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabLocationBundle:Place", options={"id" = "id"})
     */
    public function getPlaceEventsAction(Place $place)
    {
        $events = $this->get('location.event_manager')->getUpcomingForPlace($place);
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($events, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Place",
     *      description="Place get foodtrucks",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id place"},
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabLocationBundle:Place", options={"id" = "id"})
     */
    public function getPlaceFoodtrucksAction(Place $place)
    {
        $foodtrucks = $this->get('location.place_manager')->getFoodtrucks($place);

        foreach ($foodtrucks as $foodtruck) {
            RestFoodtruckController::refactorPlanning($foodtruck);
        }

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($foodtrucks, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Event",
     *      description="Event List",
     *      requirements={
     *      },
     * )
     */
    public function listEventAction()
    {
        $events = $this->get('location.event_manager')->findAll();
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($events, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Event",
     *      description="Event get",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id event"},
     *      },
     *      output="Clab\LocationBundle\Entity\Event"
     * )
     *
     * @ParamConverter("restaurant", class="ClabLocationBundle:Event", options={"id" = "id"})
     */
    public function getEventAction(Event $event)
    {
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($event, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Event",
     *      description="Event get posts",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id event"},
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabLocationBundle:Event", options={"id" = "id"})
     */
    public function getEventPostsAction(Event $event)
    {
        $posts = $this->get('clab.social_manager')->getFeedForEntity($event);
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($posts, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Place",
     *      description="Event get foodtrucks",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id event"},
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabLocationBundle:Event", options={"id" = "id"})
     */
    public function getEventFoodtrucksAction(Event $event)
    {
        $foodtrucks = $this->get('location.event_manager')->getFoodtrucks($event);

        foreach ($foodtrucks as $foodtruck) {
            RestFoodtruckController::refactorPlanning($foodtruck);
        }

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($foodtrucks, 'json');

        return new Response($response);
    }
}
