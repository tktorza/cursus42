<?php

namespace Clab\ApiBundle\Controller;

use Clab\ApiBundle\Form\Type\Product\RestProductType;
use Clab\ApiBundle\Form\Type\Restaurant\RestRestaurantType;
use Clab\BoardBundle\Entity\Client;
use Clab\BoardBundle\Entity\UserDataBase;
use Clab\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
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
     *      resource = "/api/v1/restaurants/{restaurantId}/list",
     *      description="Get restaurant profile",
     *      requirements={
     *          {"name"="slug", "dataType"="integer", "required"=true, "description"="Slug restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function listMenuAction(Restaurant $restaurant)
    {
        $menus = $restaurant->getRestaurantMenus();
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($menus, 'json');

        return new Response($response);
    }

    /**
     * Get updated catalog for given restaurant before date.
     *
     * ### Response format ###
     *
     *     [
     *       "products":[...],
     *       "categories":[...],
     *       "options":[...],
     *       "meals":[...]
     *     ]
     *
     * @ApiDoc(
     *      section="Restaurant",
     *      resource = "/api/v1/restaurants/updated",
     *      description="Get what's update from BO",
     *      parameters={
     *          {"name"="date", "dataType"="string", "required"=true, "description"="date from wich to search updated catalog format YYYY-mm-dd"},
     *          {"name"="restaurant", "dataType"="integer", "required"=true, "description"="id of the restaurant"}
     *      },
     * )
     */
    public function updatedFromBoAction(Request $request)
    {
        $parameters = $request->query->all();
        $updated = date_create_from_format('Y-m-d', $parameters['date']);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($parameters['restaurant']);
        $results = array();
        $results['products'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Product')->getUpdatedForRestaurant($restaurant, $updated);
        $results['categories'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductCategory')->getUpdatedForRestaurant($restaurant, $updated);
        $results['options'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductOption')->getUpdatedForRestaurant($restaurant, $updated);
        $results['meals'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Meal')->getUpdatedForRestaurant($restaurant, $updated);
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($results, 'json');

        return new Response($response);
    }

    /**
     * Get created catalog for given restaurant before date.
     *
     * ### Response format ###
     *
     *     [
     *       "products":[...],
     *       "categories":[...],
     *       "options":[...],
     *       "meals":[...]
     *     ]
     *
     * @ApiDoc(
     *      section="Restaurant",
     *      resource = "/api/v1/restaurants/created",
     *      description="Get what's created from BO",
     *      parameters={
     *          {"name"="date", "dataType"="string", "required"=false, "description"="date from wich to search created catalog format YYYY-mm-dd"},
     *          {"name"="restaurant", "dataType"="integer", "required"=false, "description"="id of the restaurant"}
     *      },
     * )
     */
    public function createdFromBoAction(Request $request)
    {
        $parameters = $request->query->all();
        $created = date_create_from_format('Y-m-d', $parameters['date']);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($parameters['restaurant']);
        $results = array();
        $results['products'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Product')->getCreatedForRestaurant($restaurant, $created);
        $results['categories'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductCategory')->getCreatedForRestaurant($restaurant, $created);
        $results['options'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductOption')->getCreatedForRestaurant($restaurant, $created);
        $results['meals'] = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Meal')->getCreatedForRestaurant($restaurant, $created);
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($results, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource = "/api/v1/restaurants/taxes",
     *      description="Get all Taxes"
     * )
     */
    public function listTaxesAction()
    {
        $taxes = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Tax')->findBy(array(
           'is_online' => true,
       ));
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($taxes, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource = "/api/v1/restaurants/{restaurantId}/list",
     *      description="Get restaurant profile",
     *      requirements={
     *          {"name"="slug", "dataType"="integer", "required"=true, "description"="Slug restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function listCategoriesAction(Restaurant $restaurant)
    {
        /**
         * @var $categoryManager ProductCategoryManager
         */
        $categoryManager = $this->get('app_restaurant.product_category_manager');

        $categories = $categoryManager->getAvailableForRestaurant($restaurant);
        $serializer = $this->container->get('serializer');

        $response = $serializer->serialize($categories, 'json');

        return new Response($response);
    }
    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource = "/api/v1/restaurants/{restaurantId}/list",
     *      description="Get restaurant products"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function listProductsAction(Restaurant $restaurant)
    {
        $products = $this->get('app_restaurant.product_manager')->getAvailableForRestaurant($restaurant);
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($products, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource = "/api/v1/restaurants/{restaurantId}/list",
     *      description="Get restaurant meals"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function listMealsAction(Restaurant $restaurant)
    {
        $meals = $this->get('app_restaurant.meal_manager')->getAvailableForRestaurant($restaurant);
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($meals, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      description="Restaurant quick list",
     * )
     */
    public function quickListAction()
    {
        $restaurants = $this->get('app_restaurant.restaurant_manager')->findAllFiltered();

        $results = array();
        foreach ($restaurants as $restaurant) {
            $results[] = array('id' => $restaurant->getId(), 'name' => $restaurant->getName(), 'slug' => $restaurant->getSlug(), 'cover' => $restaurant->getCover());
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($results, 'json');

        return new Response($response);
    }
    
    /**
     * Search all restaurants in 2km range.
     *
     * @ApiDoc(
     *   section="Restaurant",
     *   resource = "/api/v1/restaurants/search",
     *   description = "Search all restaurants in 2km range",
     *      parameters={
     *          {"name"="location", "dataType"="string", "required"=false, "description"="address of the location (if
     *          not lat and lng)"},
     *          {"name"="lat", "dataType"="integer", "required"=false, "description"="latitude of the position (only
     *          if location is null)"},
     *          {"name"="lng", "dataType"="integer", "required"=false, "description"="longitude of the position (only
     *          if location is null)"},
     *          {"name"="limit", "dataType"="integer", "required"=false, "description"="search max results"},
     *          {"name"="offset", "dataType"="integer", "required"=false, "description"="offset for pagination results"},
     *          {"name"="categories", "dataType"="string", "required"=false, "description"="set categories of restaurants to search from separated by a comma ex: &categories=kebab,burger,americain"},
     *          {"name"="regimes", "dataType"="string", "required"=false, "description"="set restaurant's regimes to search from separated by a comma ex: &regimes=bio,vegetarien"},
     *          {"name"="type", "dataType"="string", "required"=false, "description"="set type of ordering for restaurant to search from, valid values are 'takeaway' or 'delivery' "},
     *          {"name"="price", "dataType"="int", "required"=false, "description"="set price range 0 / 1 / 2 / 3 / 4"},
     *          {"name"="discounts", "dataType"="int", "required"=false, "description"="true / false, have discounts"},
     *          {"name"="open", "dataType"="int", "required"=false, "description"="true, opened"},
     *          {"name"="open_today", "dataType"="int", "required"=false, "description"="true, opened"},
     *          {"name"="online_order", "dataType"="int", "required"=false, "description"="true, have online order"}
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
        $lng = $request->get('lng');
        $options['limit'] = $request->get('limit');
        $options['offset'] = $request->get('offset');
        $options['service'] = 'clickeat';

        if (!is_null($request->get('categories'))) {
            $options['categories'] = str_replace(',', '|', $request->get('categories'));
        }

        if (!is_null($request->get('regimes'))) {
            $options['regimes'] = str_replace(',', '|', $request->get('regimes'));
        }

        if (!is_null($request->get('type'))) {
            $options['type'] = $request->get('type');
        }

        if (!is_null($request->get('price'))) {
            $options['price'] = explode(',',$request->get('price'));
        }

        if (!is_null($request->get('discounts'))) {
            $options['discounts'] = $request->get('discounts');
        }

        if (!is_null($request->get('open'))) {
            $options['open'] = $request->get('open');
        }

        if (!is_null($request->get('open_today'))) {
            $options['open_today'] = $request->get('open_today');
        }

        if (!is_null($request->get('online_order'))) {
            $options['online_order'] = $request->get('online_order');
        }

        if ($location && !$lat && !$lng) {
            try {
                $coordinates = $this->get('app_location.location_manager')->getCoordinateFromAddress($location);
            } catch (\Exception $exception) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $exception->getMessage(),
                ]);
            }

            $lat = $coordinates['latitude'];
            $lng = $coordinates['longitude'];
        }

        $results = array();
        $restaurants = $this->get('app_restaurant.restaurant_manager')->findNearbyPaginatedFiltered($lat, $lng, $options);

        $timeSheetManager = $this->get('app_restaurant.timesheet_manager');
        $friends = $this->getUser() ? $this->getUser()->getFollowers() : array();
        $userFavorites = $this->getUser() ? $this->getUser()->getFavorites() : array();

        foreach ($restaurants as $key => $restaurantData) {
            $restaurant = $restaurantData[0];

            $restaurant->setDistance($restaurantData['distance']);

            $isOpen = $timeSheetManager->getOpenedStatus($restaurant->getFlatTimeSheet());
            $restaurant->setIsOpen($isOpen);
            $restaurant->setDescription(html_entity_decode(strip_tags($restaurant->getDescription())));

            $results[$key] = $restaurant;
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($results, 'json', SerializationContext::create()->setGroups(array('search')));

        //enregistrer la recherche seulement si c'est la recherche de base et non la pagination
        if ($options['offset'] == 0 && $this->getUser()) {
            $this->getUser()->addLastSearch($request->query->all());
            $this->getDoctrine()->getEntityManager()->flush();
        }

        $response = new Response($response);

        $response->setPublic();
        $response->setMaxAge(3000);
        $response->setSharedMaxAge(3000);

        return $response;
    }

    /**
     * Get one restaurant.
     *
     * @ApiDoc(
     *   section="Restaurant",
     *   resource = "/api/v1/restaurants/{restaurantId}",
     *   description = "Get one restaurant by id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return Response
     */
    public function getOneRestaurantAction(Request $request, $restaurantId)
    {
        $restaurant = $this->getDoctrine()->getRepository(Restaurant::class)->find($restaurantId);

        if (!$restaurant) {
            return new JsonResponse([
            'success' => false,
            'message' => 'No restaurant found.',
            ]);
        }

        $timeSheetManager = $this->get('app_restaurant.timesheet_manager');

        $isOpen = $timeSheetManager->getOpenedStatus($restaurant->getFlatTimeSheet());

        $restaurant->setIsOpen($isOpen);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($restaurant, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

     /** Get the best review of the restaurant.
     *
     * @ApiDoc(
     *   section="Restaurant",
     *   resource = "/api/v1/restaurants/{restaurantId}",
     *   description = "Get the best review of the restaurant",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return Response
     */
    public function getBestReviewOfRestaurantAction($restaurantId)
    {
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
        $bestReview = $this->getDoctrine()->getRepository('ClabReviewBundle:Review')->findBestForRestaurant($restaurant);

        if (is_null($bestReview)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No review available for this restaurant.',
            ]);
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($bestReview, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource = "/api/v1/restaurants/{restaurantId}/list",
     *      description="Get option category list for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\ProductOption"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function listOptionsAction(Restaurant $restaurant)
    {
        $options = $this->get('app_restaurant.product_option_manager')->getForRestaurant($restaurant);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($options, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource="/api/v1/restaurants/{restaurantId}/list",
     *      description="List of discount by restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function listDiscountsAction(Restaurant $restaurant)
    {
        $discounts = $this->get('app_shop.discount_manager')->getAvailableDiscountsByRestaurant($restaurant);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($discounts, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource="/api/v1/restaurants/{restaurantId}/clients",
     *      description="List of clients by restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getClientsAction(Restaurant $restaurant)
    {
        $repository = $this->getDoctrine()->getRepository(UserDataBase::class);
        $clients = $repository->findBy(array('restaurant' => $restaurant));

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($clients, 'json', SerializationContext::create()->setGroups(array('pro')));

        return new Response($response);
    }


    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource="/api/v1/restaurants/{restaurantId}/checkerBoard",
     *      description="edit checkerBoard of restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function updateCheckerBoardAction(Request $request, Restaurant $restaurant)
    {
        $success = false;

        if ($restaurant) {
            $checkerBoard = $request->get('checkerBoardConfig');

            if ($checkerBoard) {
                $restaurant->setCheckerBoardConfig($checkerBoard, true);

                $this->getDoctrine()->getManager()->flush();
                $success = true;
            }
        }

        return new JsonResponse([
            'success' => $success,
        ]);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource="/api/v1/restaurants/{restaurantId}/checkerBoard",
     *      description="get checkerBoard of restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getCheckerBoardAction(Request $request, Restaurant $restaurant)
    {
        $checkerBoard = $restaurant->getCheckerBoardConfig();

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($checkerBoard, 'json', SerializationContext::create()->setGroups(array('pro')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Restaurant",
     *      resource="/api/v1/restaurants/{restaurantId}",
     *      description="update restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *     parameters={
     *          {"name"="caisseDiscountLabels", "dataType"="array", "required"=false, "description"="caisseDiscountLabels of restaurant"},
     *          {"name"="caisseTags", "dataType"="array", "required"=true, "description"="caisseTagsLabels of restaurant"},
     *          {"name"="caissePrinterLabels", "dataType"="array", "required"=true, "description"="caissePrinterLabels of restaurant"}
     *     }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function updateRestaurantAction(Request $request, Restaurant $restaurant)
    {

        $form = new RestRestaurantType();

        $form = $this->createForm($form, $restaurant);
        $form->submit($request->request->all(), true);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($restaurant);
            $em->flush();

            $serializer = $this->get('serializer');
            $response = $serializer->serialize($restaurant, 'json', SerializationContext::create()->setGroups(array('pro')));

            return new Response($response);
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);
    }
}
