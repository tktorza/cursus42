<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Restaurant;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestRestaurantController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource=true,
     *      description="Get restaurant profile",
     *      requirements={
     *          {"name"="slug", "dataType"="integer", "required"=true, "description"="Slug restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function listMenuAction(Restaurant $restaurant)
    {
        $menus = $restaurant->getRestaurantMenus();
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($menus, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource=true,
     *      description="Get what's update from BO",
     *      parameters={
     *          {"name"="updated", "dataType"="string", "required"=false, "description"="update format YYYY-mm-dd"},
     *          {"name"="restaurant", "dataType"="integer", "required"=false, "description"="id of the restaurant"}
     *      },
     * )
     */
    public function updatedFromBoAction(Request $request)
    {
        $parameters = $request->query->all();
        $updated = date_create_from_format('Y-m-d', $parameters['updated']);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($parameters['restaurant']);
        $results = array();
        $results['products'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Product')->getUpdatedForRestaurant($restaurant, $updated);
        $results['categories'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductCategory')->getUpdatedForRestaurant($restaurant, $updated);
        $results['options'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductOption')->getUpdatedForRestaurant($restaurant, $updated);
        $results['meals'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Meal')->getUpdatedForRestaurant($restaurant, $updated);
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($results, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource=true,
     *      description="Get what's created from BO",
     *      parameters={
     *          {"name"="created", "dataType"="string", "required"=false, "description"="update format YYYY-mm-dd"},
     *          {"name"="restaurant", "dataType"="integer", "required"=false, "description"="id of the restaurant"}
     *      },
     * )
     */
    public function createdFromBoAction(Request $request)
    {
        $parameters = $request->query->all();
        $created = date_create_from_format('Y-m-d', $parameters['created']);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($parameters['restaurant']);
        $results = array();
        $results['products'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Product')->getCreatedForRestaurant($restaurant, $created);
        $results['categories'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductCategory')->getCreatedForRestaurant($restaurant, $created);
        $results['options'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductOption')->getCreatedForRestaurant($restaurant, $created);
        $results['meals'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Meal')->getCreatedForRestaurant($restaurant, $created);
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($results, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource=true,
     *      description="Get all Taxes",
     *      requirements={
     *      }
     * )
     */
    public function listTaxesAction()
    {
        $taxes = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Tax')->findBy(array(
           'is_online' => true,
       ));
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($taxes, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource=true,
     *      description="Get restaurant profile",
     *      requirements={
     *          {"name"="slug", "dataType"="integer", "required"=true, "description"="Slug restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function listCategoriesAction(Restaurant $restaurant)
    {
        $categories = $this->get('app_restaurant.product_category_manager')->getAvailableForRestaurant($restaurant);
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($categories, 'json');

        return new Response($response);
    }
    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource=true,
     *      description="Get restaurant profile",
     *      requirements={
     *          {"name"="menu", "dataType"="integer", "required"=true, "description"="Id du menu du restaurant"}
     *      }
     * )
     */
    public function listProductsAction(Request $request)
    {
        $menuId = $request->get('menu');
        $menu = $this->getDoctrine()->getRepository('ClabRestaurantBundle:RestaurantMenu')->find($menuId);
        $products = $this->get('app_restaurant.product_manager')->getAvailableForRestaurantMenu($menu);
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($products, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource=true,
     *      description="Get restaurant profile",
     *      requirements={
     *          {"name"="menu", "dataType"="integer", "required"=true, "description"="Id du menu du restaurant"}
     *      }
     * )
     */
    public function listMealsAction(Request $request)
    {
        $menuId = $request->get('menu');
        $menu = $this->getDoctrine()->getRepository('ClabRestaurantBundle:RestaurantMenu')->find($menuId);
        $meals = $this->get('app_restaurant.meal_manager')->getAvailableForRestaurantMenu($menu);
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($meals, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource=true,
     *      description="Add restaurant to favorite",
     *      requirements={
     *          {"name"="user", "dataType"="entity", "required"=true, "description"=" user"},
     *          {"name"="restaurant", "dataType"="entity", "required"=true, "description"=" restaurant"}
     *      }
     * )
     * @ParamConverter("user", class="ClabUserBundle:User")
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function addFavoriteAction(User $user, Restaurant $restaurant)
    {
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurant);
        $this->get('app_user.user_manager')->addFavorite($user, $restaurant->getSlug());

        return new JsonResponse('restaurant added', 200);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource=true,
     *      description="Remove restaurant to favorite",
     *      requirements={
     *          {"name"="user", "dataType"="entity", "required"=true, "description"=" user"},
     *          {"name"="restaurant", "dataType"="entity", "required"=true, "description"=" restaurant"}
     *      }
     * )
     * @ParamConverter("user", class="ClabUserBundle:User")
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function removeFavoriteAction(User $user, Restaurant $restaurant)
    {
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurant);

        $this->get('app_user.user_manager')->removeFavorite($user, $restaurant->getSlug());

        return new JsonResponse('restaurant removed', 200);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      description="Restaurant quick list",
     *      requirements={
     *      },
     * )
     */
    public function quickListAction()
    {
        $results = array();

        if (!$results) {
            $restaurants = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Restaurant')->findAllFiltered(array(
                'status_min' => Restaurant::STORE_STATUS_ACTIVE,
                'status_max' => 6999,
                'service' => 'clickeat',
            ));

            $results = array();
            foreach ($restaurants as $restaurant) {
                $results[] = array('id' => $restaurant->getId(), 'name' => $restaurant->getName(), 'slug' => $restaurant->getSlug(), 'cover' => $restaurant->getCover());
            }
        }

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($results, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      description="Search takeaway",
     *      requirements={
     *          {"name"="latitude", "dataType"="integer", "required"=true, "description"="Latitude"},
     *          {"name"="longitude", "dataType"="integer", "required"=true, "description"="Longitude"}
     *      }
     * )
     */
    public function searchTakeawayAction(Request $request, $latitude, $longitude)
    {
        $distanceMin = $request->get('distanceMin') ? $request->get('distanceMin') : 0;
        $distanceMax = $request->get('distanceMax') ? $request->get('distanceMax') : 2;

        if (!$latitude || !$longitude) {
            return $this->get('api.rest_manager')->getErrorResponse('Error', 'Missing latitude or longitude');
        }

        $restaurants = $this->get('clickeat.explore_manager')->findNearby($latitude, $longitude, $distanceMin, $distanceMax);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($restaurants, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      description="Search delivery",
     *      requirements={
     *          {"name"="latitude", "dataType"="integer", "required"=true, "description"="Latitude"},
     *          {"name"="longitude", "dataType"="integer", "required"=true, "description"="Longitude"}
     *      }
     * )
     */
    public function searchDeliveryAction($latitude, $longitude)
    {
        if (!$latitude || !$longitude) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Missing latitude or longitude');
        }

        $restaurants = $this->get('clickeat.explore_manager')->findDelivery($latitude, $longitude);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($restaurants, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      description="Search clickeat restaurant",
     *      requirements={
     *          {"name"="latitude", "dataType"="integer", "required"=true, "description"="Latitude"},
     *          {"name"="longitude", "dataType"="integer", "required"=true, "description"="Longitude"}
     *      }
     * )
     */
    public function clickeatSearchAction(Request $request, $latitude, $longitude)
    {
        $page = $request->get('page') ? $request->get('page') : 1;

        if (!$latitude || !$longitude) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Missing latitude or longitude');
        }

        $filterCategories = $request->get('categories');
        $filterExtraCategories = $request->get('extraCategories');
        $parameters = array('page' => $page, 'foodtruck' => $page == 1 ? true : false, 'categories' => $filterCategories, 'extraCategories' => $filterExtraCategories);

        $restaurants = $this->get('clickeat.explore_manager')->findNearbyPaginated($latitude, $longitude, $parameters);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($restaurants, 'json');

        return new Response($response);
    }

    /**
     * Search all restaurants in 2km range.
     *
     * @ApiDoc(
     *   section="Restaurant",
     *   resource = true,
     *   description = "Search all restaurants in 2km range",
     *      parameters={
     *          {"name"="location", "dataType"="string", "required"=false, "description"="address of the location (if
     * not lat and lng)"},
     *          {"name"="lat", "dataType"="integer", "required"=false, "description"="latitude of the position (only
     * if location is null)"},
     *          {"name"="lng", "dataType"="integer", "required"=false, "description"="longitude of the position (only
     * if location is null)"}
     *      },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return Response
     */
    public function searchRestaurantsAction(Request $request)
    {
        $location = $request->get('location');
        $lat = $request->get('lat');
        $long = $request->get('lng');
        $options['limit'] = $request->get('limit');
        $options['offset'] = $request->get('offset');
        if (!is_null($location) && is_null($lat) && is_null($long)) {
            try {
                $coordonates = $this->get('app_location.location_manager')->getCoordinateFromAddress($location);
            } catch (\Exception $ex) {
                return new JsonResponse([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
            }
            $lat = $coordonates['latitude'];
            $long = $coordonates['longitude'];
        }

        try {
            $results = array();
            $restaurants = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findNearbyPaginated($lat, $long, $options);

            foreach ($restaurants as $key => $restaurant) {
                if ($restaurant[0]->isClickeat() == true && $restaurant[0]->isMobile() == false) {
                    $results[$key] = $restaurant[0];
                    $results[$key]->setDistance($restaurant['distance']);
                    $results[$key]->setIsOpen(0);

                    $plannings = $this->container->get('app_restaurant.timesheet_manager')->getWeekDayPlanning($restaurant[0]);
                    $planningDay = array();
                    $resPlanning = null;
                    $now = new \DateTime('now');

                    foreach ($plannings as $k => $planning) {
                        if (strtoupper(date('l')) == $k) {
                            foreach ($planning as $p) {
                                $resPlanning .= $p['start']->format('H:i').'-'.$p['end']->format('H:i').' ';
                                $planningDay[] = array('start' => $p['start']->format('H:i'),'end' => $p['end']->format('H:i'));
                            }
                        }
                    }

                    if (!empty($planningDay)) {
                        foreach ($planningDay as $pday) {
                            $openingDateTime = new \DateTime($pday['start']);
                            $closingDateTime = new \DateTime($pday['end']);
                            $intervalOpen = $now->diff($openingDateTime);
                            $intervalClose = $now->diff($closingDateTime);
                            $hourClose = $intervalClose->format('%H');
                            $hourOpen = $intervalOpen->format('%H');
                            $minuteOpen = $intervalOpen->format('%I');

                            $minuteClose = (integer) $intervalClose->format('%I');
                            if (($now->format('H:i') > $pday['start']) && ($now->format('H:i') < $pday['end'])) {
                                $results[$key]->setIsOpen(0);
                            } elseif ($hourClose == '00' && $minuteClose <= 30 && $minuteClose >= 0) {
                                $results[$key]->setIsOpen(2);
                            } elseif ($hourOpen == '00' && $minuteOpen <= 30 && $minuteOpen >= 0) {
                                $results[$key]->setIsOpen(3);
                            } else {
                                $results[$key]->setIsOpen(1);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($results, 'json');

        return new Response($response);
    }

    /**
     * Get one restaurant.
     *
     * @ApiDoc(
     *   section="Restaurant",
     *   resource = true,
     *   description = "Get one restaurant by id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return Response
     */
    public function getOneRestaurantAction(Request $request)
    {
        $restaurantId = $request->get('restaurantId');
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
        if (!$restaurant) {
            return new JsonResponse([
            'success' => false,
            'message' => 'No restaurant found.',
        ]);
        }
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($restaurant, 'json');

        return new Response($response);
    }

    /**
     * Get the best review of the restaurant.
     *
     * @ApiDoc(
     *   section="Restaurant",
     *   resource = true,
     *   description = "Get the best review of the restaurant",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return Response
     */
    public function getBestReviewOfRestaurantAction(Request $request)
    {
        $restaurantId = $request->get('restaurantId');
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
        $bestReview = $this->getDoctrine()->getRepository('ClabReviewBundle:Review')->findBestForRestaurant($restaurant);
        if (is_null($bestReview)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No review available for this restaurant.',
            ]);
        }
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($bestReview, 'json');

        return new Response($response);
    }
}
