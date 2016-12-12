<?php

namespace Clab\ApiBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\TimeSheet;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Clab\ApiBundle\Form\Type\Foodtruck\RestEditEventType;
use Clab\LocationBundle\Entity\Address;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class RestFoodtruckController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Foodtruck List",
     *      requirements={
     *      }
     * )
     */
    public function listAction()
    {
        $foodtrucks = $this->get('clab_ttt.foodtruck_manager')->fetchAll();

        foreach ($foodtrucks as $foodtruck) {
            $this->refactorPlanning($foodtruck);
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($foodtrucks, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Foodtruck List",
     *      requirements={
     *      }
     * )
     */
    public function searchAction(Request $request)
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $distanceMin = $request->get('distanceMin') ? $this->getRequest()->get('distanceMin') : 0;
        $distanceMax = $request->get('distanceMax') ? $this->getRequest()->get('distanceMax') : 2;

        if (!$latitude || !$longitude) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Missing latitude or longitude');
        }

        $foodtrucks = $this->get('clab_ttt.foodtruck_manager')->searchLocation($latitude, $longitude, $distanceMin, $distanceMax);

        foreach ($foodtrucks as $foodtruck) {
            $this->refactorPlanning($foodtruck);
        }

        return new JsonResponse($foodtrucks);
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Foodtruck search by location",
     *      requirements={
     *      }
     * )
     */
    public function searchByLocationAction(Request $request)
    {
        $latitude = $request->get('latitude');
        $longitude = $request->get('longitude');
        $day = date_create_from_format('Y-m-d H:i:s', $request->get('day').' 00:00:00');
        $start = date_create_from_format('H-i', $request->get('start'));
        $end = date_create_from_format('H-i', $request->get('end'));
        $page = $request->get('page') ? $request->get('page') : 1;

        if (!$day || !$start || !$end) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Missing time');
        }

        if (!$latitude || !$longitude) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Missing latitude or longitude');
        }

        $foodtrucks = $this->get('clab_ttt.foodtruck_manager')->searchByLocation($day, $start, $end, $latitude, $longitude, $page);

        return new JsonResponse($foodtrucks);
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Foodtruck quick list",
     *      requirements={
     *      }
     * )
     */
    public function quickListAction()
    {
        $restaurants = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Restaurant')
                ->findBy(array('isTtt' => true, 'isMobile' => true));

        $results = array();
        foreach ($restaurants as $restaurant) {
            $results[] = array('id' => $restaurant->getId(), 'name' => $restaurant->getName(), 'slug' => $restaurant->getSlug(), 'cover' => $restaurant->getCover());
        }

        return new JsonResponse($results);
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Foodtruck List",
     *      requirements={
     *      }
     * )
     */
    public function getAction($id)
    {
        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->find(array('id' => $id, 'duration' => '20 days'));

        if (!$foodtruck) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Foodtruck introuvable');
        }

        $this->refactorPlanning($foodtruck);
        $restaurant = $foodtruck->getRestaurant();
        $lastestPosts = $this->getDoctrine()->getManager()->getRepository('ClabSocialBundle:SocialPost')
            ->getLatestByRestaurant($restaurant, 5);

        $catalog = array(
            'categories' => $restaurant->getAvailableProductCategories(),
            'meals' => $restaurant->getAvailableMeals(),
        );

        $staff = $restaurant->getStaffMembers();
        $helper = $this->get('vich_uploader.templating.helper.uploader_helper');
        foreach ($staff as $member) {
            if ($member->getImageName()) {
                $path = $helper->asset($member, 'image');
                $member->setApiCover($this->getRequest()->getHost().$path);
            } else {
                $member->setApiCover('www.gravatar.com/avatar/171d9ca31c825bab050c605e9474b95c?r=g&d=mm&s=400');
            }
        }

        $params = array(
            'foodtruck' => $foodtruck,
            'lastestPosts' => $lastestPosts,
            'catalog' => $catalog,
            'reviews' => $restaurant->getReviews(),
            'staff' => $staff,
            'bookmark' => false,
        );

        if ($restaurant->getSocialProfile()) {
            $params['socialProfile'] = $restaurant->getSocialProfile();
        }

        $sessionManager = $this->get('api.session_manager');
        if ($sessionManager->isAuthenticated()) {
            if ($sessionManager->getUser() && $this->get('app_user.user_manager')->isInFavorite($sessionManager->getUser(), $restaurant)) {
                $params['bookmark'] = true;
            }
        }

        return new JsonResponse($params);
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Get today events",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getTodayEventsAction(Restaurant $restaurant)
    {
        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($restaurant, '0 day', false);

        return $this->restManager->getResponse(array('events' => $this->getTodayEventsArray($foodtruck->getPlanning())));
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Event validation",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="date", "dataType"="timestamp", "required"=true, "description"="Day timestamp"},
     *          {"name"="start", "dataType"="timestamp", "required"=true, "description"="Event start timestamp"}
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function validateAction(Restaurant $restaurant)
    {
        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($restaurant, '0 day', false);
        $validationManager = $this->get('clab_ttt.validation_manager');

        $date = $this->getRequest()->request->get('date');
        $start = (int) $this->getRequest()->request->get('start');

        $found = false;
        if (array_key_exists($date, $foodtruck->getPlanning())) {
            foreach ($foodtruck->getPlanning()[$date] as $event) {
                if ($event['start']->getTimestamp() == $start) {
                    $found = true;
                    $validationManager->validate($foodtruck, $event, null, true);
                }
            }
        }

        if (!$found) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'L\'évènement n\'a pas été trouvé');
        }

        $foodtruck->validatePlanning();
        $result = $this->getTodayEventsArray($foodtruck->getPlanning());

        return new JsonResponse($result);
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Publish event validation",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="date", "dataType"="timestamp", "required"=true, "description"="Day timestamp"},
     *          {"name"="start", "dataType"="timestamp", "required"=true, "description"="Event start timestamp"}
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function publishAction(Request $request, Restaurant $restaurant)
    {
        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($restaurant, '0 day', false);
        $validationManager = $this->get('clab_ttt.validation_manager');

        $date = $request->request->get('date');
        $start = (int) $request->request->get('start');
        $message = $request->get('message');

        $found = false;
        if (array_key_exists($date, $foodtruck->getPlanning())) {
            foreach ($foodtruck->getPlanning()[$date] as $event) {
                if ($event['start']->getTimestamp() == $start && isset($event['validation']) && $event['validation']) {
                    $found = true;
                    $validationManager->publishValidation($foodtruck, $event, $message);
                }
            }
        }

        if (!$found) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'L\'évènement n\'a pas été trouvé');
        }

        return new JsonResponse('Published', 200);
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Cancel event validation",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="date", "dataType"="timestamp", "required"=true, "description"="Day timestamp"},
     *          {"name"="start", "dataType"="timestamp", "required"=true, "description"="Event start timestamp"}
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function cancelAction(Request $request, Restaurant $restaurant)
    {
        $em = $this->getDoctrine()->getManager();

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($restaurant, '0 day', false);

        $date = $request->request->get('date');
        $start = (int) $request->request->get('start');

        $day = date_create('now');
        $day->setTimestamp($date);
        $startTime = date_create('now');
        $startTime->setTimestamp($start);

        $validations = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:TimesheetValidation')
            ->findBy(array('restaurant' => $foodtruck->getRestaurant()->getId(), 'date' => $day, 'start' => $startTime));

        foreach ($validations as $validation) {
            $this->getRestaurant()->removeTimesheetValidation($validation);
            $em->remove($validation);
        }

        try {
            $validationRequests = $this->getDoctrine()->getManager()->getRepository('ClabTTTBundle:ValidationRequest')
                    ->findBy(array(
                        'restaurant' => $this->getRestaurant(),
                        'date' => $day,
                        'start' => $startTime,
                    ));

            foreach ($validationRequests as $validationRequest) {
                $validationRequest->setIsValidated(false);
            }
            $em->flush();
        } catch (\Exception $e) {
        }

        $em->flush();

        $foodtruck->validatePlanning();

        $this->get('clab_ttt.planning_print_manager')->planningPrint($foodtruck->getRestaurant());
        $result = $this->getTodayEventsArray($foodtruck->getPlanning());

        return new JsonResponse($result);
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Close event",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="date", "dataType"="timestamp", "required"=true, "description"="Day timestamp"},
     *          {"name"="start", "dataType"="timestamp", "required"=true, "description"="Event start timestamp"}
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function closeAction(Request $request, Restaurant $restaurant)
    {
        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($restaurant, '0 day', false);
        $validationManager = $this->get('clab_ttt.validation_manager');

        $date = $request->request->get('date');
        $start = (int) $request->request->get('start');

        $found = false;
        if (array_key_exists($date, $foodtruck->getPlanning())) {
            foreach ($foodtruck->getPlanning()[$date] as $event) {
                if ($event['start']->getTimestamp() == $start) {
                    $found = true;
                    $validationManager->quickClose($this->getRestaurant(), $event);
                }
            }
        }

        if (!$found) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'L\'évènement n\'a pas été trouvé');
        }

        return new JsonResponse('Closed', 200);
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      description="Edit event",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="date", "dataType"="timestamp", "required"=true, "description"="Day timestamp"},
     *          {"name"="start", "dataType"="timestamp", "required"=true, "description"="Event start timestamp"},
     *          {"name"="new_start", "dataType"="timestamp", "required"=true, "description"="New event start timestamp"},
     *          {"name"="new_end", "dataType"="timestamp", "required"=true, "description"="New event end timestamp"},
     *          {"name"="name", "dataType"="string", "required"=true, "description"="New event address name"},
     *          {"name"="street", "dataType"="string", "required"=true, "description"="New event street"},
     *          {"name"="zip", "dataType"="string", "required"=true, "description"="New event zip code"},
     *          {"name"="city", "dataType"="string", "required"=true, "description"="New event city"}
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function editAction(Request $request, Restaurant $restaurant)
    {
        $em = $this->getDoctrine()->getManager();

        $form = new RestEditEventType();
        $form = $this->createForm($form, null, array('method' => $request->getMethod()));
        $form->submit($this->getRequest(), 'PATCH' !== $request->getMethod());

        if ($form->isValid()) {
            $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($restaurant, '0 day', false);
            $validationManager = $this->get('clab_ttt.validation_manager');
            $found = false;

            if (array_key_exists($form->get('date')->getData(), $foodtruck->getPlanning())) {
                foreach ($foodtruck->getPlanning()[$form->get('date')->getData()] as $event) {
                    if ($event['start']->getTimestamp() == $form->get('start')->getData()) {
                        $found = true;

                        $address = new Address();
                        $address->setName($form->get('name')->getData());
                        $address->setStreet($form->get('street')->getData());
                        $address->setZip($form->get('zip')->getData());
                        $address->setCity($form->get('city')->getData());

                        $start = new \DateTime();
                        $start->setTimestamp($form->get('new_start')->getData());
                        $end = new \DateTime();
                        $end->setTimestamp($form->get('new_end')->getData());
                        $newEvent = array(
                            'address' => $address,
                            'timestamp' => $form->get('date')->getData(),
                            'start' => $start,
                            'end' => $end,
                        );

                        $validationManager->quickEdit($restaurant, $event, $newEvent);
                    }
                }
            }

            if (!$found) {
                return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'L\'évènement n\'a pas été trouvé');
            }

            $em->flush();

            return new JsonResponse('', 200);
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);
    }

    /**
     * @ApiDoc(
     *      section="Foodtruck",
     *      resource=true,
     *      description="Delete event",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id event"}
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     * @ParamConverter("timesheet", class="ClabRestaurantBundle:Timesheet")
     */
    public function deleteAction(Restaurant $restaurant, TimeSheet $timeSheet)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($timeSheet);
        $em->flush();

        $this->get('clab_ttt.planning_print_manager')->planningPrint($restaurant);

        return new JsonResponse('Removed', 200);
    }

    public static function refactorPlanning($foodtruck)
    {
        $newplanning = array();
        foreach ($foodtruck->getPlanning() as $events) {
            foreach ($events as $event) {
                $newplanning[] = $event;
            }
        }

        $foodtruck->setPlanning($newplanning);
    }

    public function getTodayEventsArray($planning)
    {
        $now = date_create('today');
        if (array_key_exists($now->getTimestamp(), $planning)) {
            $events = $planning[$now->getTimestamp()];
        } else {
            $events = array();
        }
        $result = array_values($events);

        return new JsonResponse($result);
    }
}
