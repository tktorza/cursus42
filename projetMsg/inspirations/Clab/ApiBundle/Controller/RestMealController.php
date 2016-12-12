<?php

namespace Clab\ApiBundle\Controller;

use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\MealSlot;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Meal;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestMealController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/menu/{menuId}/all",
     *      description="Get meal list for restaurant menu",
     *      requirements={
     *          {"name"="menuId", "dataType"="integer", "required"=true, "description"="Id menu"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("menu", class="ClabRestaurantBundle:RestaurantMenu", options={"id" = "menuId"})
     */
    public function getForRestaurantMenuAction(RestaurantMenu $menu)
    {
        $meals = $this->get('app_restaurant.meal_manager')->getForRestaurantMenu($menu);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($meals, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *     resource="/api/v1/meals/restaurant/{restaurantId}",
     *      description="Get meal list for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      parameters={
     *      {"name"="light", "dataType"="boolean", "required"=false, "description"="wheter to return data in light format, valid values are 0, 1, true, false"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getForRestaurantAction(Request $request, Restaurant $restaurant)
    {
        $action = 'getForRestaurant';

        if ($request->get('light') && boolval($request->get('light'))) {
            $action = 'getForRestaurantAsArray';
        }

        $meals = $this->get('app_restaurant.meal_manager')->$action($restaurant);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($meals, 'json', SerializationContext::create()->setGroups(array('pro')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/chainstore/{chainstoreId}",
     *      description="Get meal list for chainstore",
     *      requirements={
     *          {"name"="chainstoreId", "dataType"="integer", "required"=true, "description"="Id client"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("client", class="ClabBoardBundle:Client", options={"id" = "chainstoreId"})
     */
    public function getForChainStoreAction(Client $client)
    {
        $meals = $this->get('app_restaurant.meal_manager')->getForChainStore($client);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($meals, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/{mealId}/slot/{slotId}",
     *      description="add slot to meal",
     *      requirements={
     *          {"name"="mealId", "dataType"="integer", "required"=true, "description"="Id meal"},
     *          {"name"="slotId", "dataType"="integer", "required"=true, "description"="Id mealSlot"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal", options={"id" = "mealId"})
     * @ParamConverter("mealSlot", class="ClabRestaurantBundle:MealSlot", options={"id" = "slotId"})
     */
    public function addSlotToMealAction(Meal $meal, MealSlot $mealSlot)
    {
        $this->get('app_restaurant.meal_manager')->addSlotToMeal($meal, $mealSlot);

        return new JsonResponse('Correctly added', 200);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/{mealId}/slot/{slotId}",
     *      description="remove slot from meal",
     *      requirements={
     *          {"name"="mealId", "dataType"="integer", "required"=true, "description"="Id meal"},
     *          {"name"="slotId", "dataType"="integer", "required"=true, "description"="Id mealSlot"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal", options={"id" = "mealId"})
     * @ParamConverter("mealSlot", class="ClabRestaurantBundle:MealSlot", options={"id" = "slotId"})
     */
    public function removeSlotFromMealAction(Meal $meal, MealSlot $mealSlot)
    {
        $this->get('app_restaurant.meal_manager')->removeSlotFromMeal($meal, $mealSlot);

        return new JsonResponse('Correctly removed', 200);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/restaurant/{restaurantId}/slot",
     *      description="create meal slot for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function createMealSlotForRestaurantAction(Restaurant $restaurant)
    {
        $this->get('app_restaurant.meal_manager')->createMealSlotForRestaurant($restaurant);

        return new JsonResponse('Correctly created', 200);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/chainstore/{chainstoreId}/slot",
     *      description="create meal slot for client",
     *      requirements={
     *          {"name"="chainstoreId", "dataType"="integer", "required"=true, "description"="Id client"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("client", class="ClabBoardBundle:Client", options={"id" = "chainstoreId"})
     */
    public function createMealSlotForChainStoreAction(Client $client)
    {
        $this->get('app_restaurant.meal_manager')->createMealSlotForChainStore($client);

        return new JsonResponse('Correctly created', 200);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/restaurant/{restaurantId}/slot",
     *      description="Get meal slots for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getSlotForRestaurantAction(Restaurant $restaurant)
    {
        $slots = $this->get('app_restaurant.meal_manager')->getSlotForRestaurant($restaurant);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($slots, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/chainstore/{chainstoreId}/slot",
     *      description="Get meal slots for client",
     *      requirements={
     *          {"name"="chainstoreId", "dataType"="integer", "required"=true, "description"="Id client"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("client", class="ClabBoardBundle:Client", options={"id" = "chainstoreId"})
     */
    public function getSlotForChainStoreAction(Client $client)
    {
        $slots = $this->get('app_restaurant.meal_manager')->getSlotForChainStore($client);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($slots, 'json');

        return new Response($response);
    }

    /**
     * Unused.
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getOneSlotForRestaurantAction(Restaurant $restaurant, $slug)
    {
        $slots = $this->get('app_restaurant.meal_manager')->getOneSlotForRestaurant($restaurant, $slug);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($slots, 'json');

        return new Response($response);
    }

    /**
     * Unused.
     *
     * @ParamConverter("client", class="ClabBoardBundle:Client", options={"id" = "id"})
     */
    public function getOneSlotForChainStore(Client $client, $slug)
    {
        $slots = $this->get('app_restaurant.meal_manager')->getOneSlotForChainStore($client, $slug);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($slots, 'json');

        return new Response($response);
    }

    /**
     * Get available meals for given menu Id.
     *
     * ### Response format ###
     *
     *     [
     *       {
     *         "id": 999,
     *         "created": "2015-06-25 10:07:05",
     *         "updated": "2016-02-24 17:36:23",
     *         "name": "Le Sister Act + Dessert ou Chips + Boisson",
     *         "slug": "le-sister-act-dessert-ou-chips-boisson",
     *         "description": "Cream cheese, Concombre, Tomate, Oignons crispy, Avocat & Roquette + Dessert ou Chips + Boisson\r\nPain et sauce au choix.\r\nSi vous souhaitez remplacer l'un des ingrédients (hors viande), indiquez-le nous en commentaire à la fin de votre commande.",
     *         "price": 9.9,
     *         "slots": [
     *              {
     *              "id": 4,
     *              "created": "2015-06-25 11:01:01",
     *              "updated": "2016-02-24 17:36:23",
     *              "name": "Le Sister Act",
     *              "slug": "le-sister-act",
     *              "products": [
     *                  {
     *                      "id": 17572,
     *                      "name": "Le Furniker",
     *                      "slug": "le-furniker",
     *                      "supplement": 2
     *                  },
     *                  ...
     *              ]
     *              },
     *              ...
     *          ]
     *       },
     *       ...
     *     ]
     *
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/menu/{menuId}",
     *      description="Get meals available for a restaurant menu",
     *      requirements={
     *          {"name"="menuId", "dataType"="integer", "required"=true, "description"="Id menu"},
     *      }
     * )
     * @ParamConverter("menu", class="ClabRestaurantBundle:RestaurantMenu", options={"id" = "menuId"})
     */
    public function getAvailableforRestaurantMenuAction(RestaurantMenu $menu)
    {
        $meals = $this->get('app_restaurant.meal_manager')->getAvailableForRestaurantMenu($menu);
        $result = $this->formatMeals($meals);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($meals, 'json', SerializationContext::create()->setGroups(array('pro')));

        return new Response($response);
    }

    /**
     * Get available meals for given restaurant Id.
     *
     * ### Response format ###
     *
     *     [
     *       {
     *         "id": 999,
     *         "created": "2015-06-25 10:07:05",
     *         "updated": "2016-02-24 17:36:23",
     *         "name": "Le Sister Act + Dessert ou Chips + Boisson",
     *         "slug": "le-sister-act-dessert-ou-chips-boisson",
     *         "description": "Cream cheese, Concombre, Tomate, Oignons crispy, Avocat & Roquette + Dessert ou Chips + Boisson\r\nPain et sauce au choix.\r\nSi vous souhaitez remplacer l'un des ingrédients (hors viande), indiquez-le nous en commentaire à la fin de votre commande.",
     *         "price": 9.9,
     *         "slots": [
     *              {
     *              "id": 4,
     *              "created": "2015-06-25 11:01:01",
     *              "updated": "2016-02-24 17:36:23",
     *              "name": "Le Sister Act",
     *              "slug": "le-sister-act",
     *              "products": [
     *                  {
     *                      "id": 17572,
     *                      "name": "Le Furniker",
     *                      "slug": "le-furniker",
     *                      "supplement": 2
     *                  },
     *                  ...
     *              ]
     *              },
     *              ...
     *          ]
     *       },
     *       ...
     *     ]
     *
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/restaurant/{restaurantId}",
     *      description="Get meal available for a restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *      },
     *      parameters={
     *          {"name"="light", "dataType"="boolean", "required"=false, "description"="wheter to return data in light format, valid values are 0, 1, true, false"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getAvailableforRestaurantAction(Request $request, Restaurant $restaurant)
    {

        $light = ($request->get('light') && boolval($request->get('light')));

        try {
            $meals = $this->get('app_restaurant.meal_manager')->getForRestaurant($restaurant, array('hasSlots' => true));
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        $result = $this->formatMeals($meals,$light);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($result, 'json', SerializationContext::create()->setGroups(array('pro')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/{mealId}",
     *      description="Remove meal",
     *      requirements={
     *          {"name"="mealId", "dataType"="integer", "required"=true, "description"="Id meal"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Meal\RestMealType",
     *      output="JSON RESPONSE"
     * )
     * @ParamConverter("menu", class="ClabRestaurantBundle:Meal", options={"id" = "mealId"})
     */
    public function removeAction(Meal $meal)
    {
        $this->get('app_restaurant.meal_manager')->remove($meal);

        return new JsonResponse('Meal #'.$meal->getId().' correctly removed', 200);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/{mealId}/menu/{menuId}",
     *      description="Remove meal from a menu",
     *      requirements={
     *
     *          {"name"="mealId", "dataType"="integer", "required"=true, "description"="Id meal"},
     *          {"name"="menuId", "dataType"="integer", "required"=true, "description"="Id menu"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Meal\RestMealType",
     *      output="JSON Response"
     * )
     * @ParamConverter("menu", class="ClabRestaurantBundle:Meal", options={"id" = "mealId"})
     * @ParamConverter("menu", class="ClabRestaurantBundle:Menu", options={"id" = "menuId"})
     */
    public function removeForRestaurantMenuAction(Meal $meal, RestaurantMenu $menu)
    {
        $this->get('app_restaurant.meal_manager')->removeForRestaurantMenu($meal, $menu);

        return new JsonResponse('Meal #'.$meal->getId().' correctly removed for menu #'.$menu->getId(), 200);
    }

    /**
     * Get available meal for given meal Id.
     *
     * ### Response format ###
     *
     *       {
     *         "id": 999,
     *         "created": "2015-06-25 10:07:05",
     *         "updated": "2016-02-24 17:36:23",
     *         "name": "Le Sister Act + Dessert ou Chips + Boisson",
     *         "slug": "le-sister-act-dessert-ou-chips-boisson",
     *         "description": "Cream cheese, Concombre, Tomate, Oignons crispy, Avocat & Roquette + Dessert ou Chips + Boisson\r\nPain et sauce au choix.\r\nSi vous souhaitez remplacer l'un des ingrédients (hors viande), indiquez-le nous en commentaire à la fin de votre commande.",
     *         "price": 9.9,
     *         "slots": [
     *              {
     *              "id": 4,
     *              "created": "2015-06-25 11:01:01",
     *              "updated": "2016-02-24 17:36:23",
     *              "name": "Le Sister Act",
     *              "slug": "le-sister-act",
     *              "products": [
     *                  {
     *                      "id": 17572,
     *                      "name": "Le Furniker",
     *                      "slug": "le-furniker",
     *                      "description": "le furniker",
     *                      "cover": "url",
     *                      "supplement": 2
     *                  },
     *                  ...
     *              ]
     *              },
     *              ...
     *          ]
     *       }
     *
     * @ApiDoc(
     *      section="Meals",
     *      resource="/api/v1/meals/{mealId}",
     *      description="Get one meal info",
     *      requirements={
     *          {"name"="mealId", "dataType"="integer", "required"=true, "description"="meal Id"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal", options={"id" = "mealId"})
     */
    public function getMealAction(Meal $meal)
    {
        $result = $this->formatMeals(array($meal));

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($result[0], 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    public function formatMeals($meals, $light = false)
    {
        $result = array();
        $cacheManager = $this->get('liip_imagine.cache.manager');

        foreach ($meals as $k => $meal) {
            $result[$k]['id'] = $meal->getId();
            $result[$k]['created'] = $meal->getCreated();
            $result[$k]['updated'] = $meal->getUpdated();
            $result[$k]['name'] = $meal->getName();
            $result[$k]['slug'] = $meal->getSlug();
            $result[$k]['description'] = $meal->getDescription();
            $result[$k]['price'] = $meal->getPrice();
            $result[$k]['current_price'] = floatval($meal->getPrice()+0.0);
            $result[$k]['delivery_price'] = $meal->getDeliveryPrice();
            $result[$k]['price_on_site'] = $meal->getPriceOnSite();

            $result[$k]['tax'] = $meal->getTax();
            $result[$k]['tax_on_site'] = $meal->getTaxOnSite();
            $result[$k]['tax_delivery'] = $meal->getTaxDelivery();

            $result[$k]['cover'] = $cacheManager->getBrowserPath($meal->getGallery()->getCover()->getWebPath(), 'square_180');

            foreach ($meal->getSlots() as $key => $slot) {
                $result[$k]['slots'][$key]['id'] = $slot->getId();
                $result[$k]['slots'][$key]['created'] = $slot->getCreated();
                $result[$k]['slots'][$key]['updated'] = $slot->getUpdated();
                $result[$k]['slots'][$key]['name'] = $slot->getName();
                $result[$k]['slots'][$key]['slug'] = $slot->getSlug();

                foreach ($slot->getProductCategories() as $category) {
                    foreach ($category->getProducts() as $product) {
                        if (!in_array($product->getId(), $slot->getDisabledProducts())) {
                            if ($light) {
                                $result[$k]['slots'][$key]['products'][] = array(
                                    'id' => $product->getId(),
                                    'name' => $product->getName(),
                                    'slug' => $product->getSlug(),
                                    'cover' => $product->getCover(),
                                    'tax' => $product->getTax(),
                                    'tax_delivery' => $product->getTaxDelivery(),
                                    'tax_on_site' => $product->getTaxOnSite(),
                                    'original_price' => $product->getPrice(),
                                    'supplement' => (isset($slot->getCustomPrices()[$product->getId()]) ? floatval($slot->getCustomPrices()[$product->getId()]) : null),
                                    'printers' => $product->getPrinters()
                                );
                            } else {
                                $result[$k]['slots'][$key]['products'][] = array(
                                    'id' => $product->getId(),
                                    'name' => $product->getName(),
                                    'slug' => $product->getSlug(),
                                    'description' => $product->getDescription(),
                                    'cover' => $product->getCover(),
                                    'tax' => $product->getTax(),
                                    'tax_delivery' => $product->getTaxDelivery(),
                                    'tax_on_site' => $product->getTaxOnSite(),
                                    'options' => $product->getOptions(),
                                    'original_price' => $product->getPrice(),
                                    'supplement' => (isset($slot->getCustomPrices()[$product->getId()]) ? floatval($slot->getCustomPrices()[$product->getId()]) : null),
                                    'printers' => $product->getPrinters()
                                );
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }
}
