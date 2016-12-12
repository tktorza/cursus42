<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\MealSlot;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Meal;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RestMealController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for restaurant menu",
     *      requirements={
     *          {"name"="menuId", "dataType"="integer", "required"=true, "description"="Id menu"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("menu", class="ClabRestaurantBundle:RestaurantMenu", options={"id" = "id"})
     */
    public function getForRestaurantMenuAction(RestaurantMenu $menu)
    {
        $meals = $this->get('app_restaurant.meal_manager')->getForRestaurantMenu($menu);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($meals, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for restaurant",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getForRestaurantAction(Restaurant $restaurant)
    {
        $meals = $this->get('app_restaurant.meal_manager')->getForRestaurant($restaurant);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($meals, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for chainstore",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id client"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("client", class="ClabBoardBundle:Client", options={"id" = "id"})
     */
    public function getForChainStoreAction(Client $client)
    {
        $meals = $this->get('app_restaurant.meal_manager')->getForChainStore($client);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($meals, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for restaurant menu",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id meal"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id mealSlot"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal", options={"idMeal" = "id"})
     * @ParamConverter("mealSlot", class="ClabRestaurantBundle:MealSlot", options={"id" = "id"})
     */
    public function addSlotToMealAction(Meal $meal, MealSlot $mealSlot)
    {
        $this->get('app_restaurant.meal_manager')->addSlotToMeal($meal, $mealSlot);

        return new JsonResponse('Correctly added', 200);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for restaurant menu",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id meal"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id mealSlot"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal", options={"id" = "id"})
     * @ParamConverter("mealSlot", class="ClabRestaurantBundle:MealSlot", options={"id" = "id"})
     */
    public function removeSlotFromMealAction(Meal $meal, MealSlot $mealSlot)
    {
        $this->get('app_restaurant.meal_manager')->removeSlotFromMeal($meal, $mealSlot);

        return new JsonResponse('Correctly removed', 200);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for restaurant menu",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function createMealSlotForRestaurantAction(Restaurant $restaurant)
    {
        $this->get('app_restaurant.meal_manager')->createMealSlotForRestaurant($restaurant);

        return new JsonResponse('Correctly created', 200);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for restaurant menu",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id client"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("client", class="ClabBoardBundle:Client", options={"id" = "id"})
     */
    public function createMealSlotForChainStoreAction(Client $client)
    {
        $this->get('app_restaurant.meal_manager')->createMealSlotForChainStore($client);

        return new JsonResponse('Correctly created', 200);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for restaurant menu",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getSlotForRestaurantAction(Restaurant $restaurant)
    {
        $slots = $this->get('app_restaurant.meal_manager')->getSlotForRestaurant($restaurant);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($slots, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for restaurant menu",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id client"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("client", class="ClabBoardBundle:Client", options={"id" = "id"})
     */
    public function getSlotForChainStoreAction(Client $client)
    {
        $slots = $this->get('app_restaurant.meal_manager')->getSlotForChainStore($client);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($slots, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for restaurant menu",
     *      requirements={
     *          {"name"="menuId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="slug", "dataType"="integer", "required"=true, "description"="Slug of the slot"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getOneSlotForRestaurantAction(Restaurant $restaurant, $slug)
    {
        $slots = $this->get('app_restaurant.meal_manager')->getOneSlotForRestaurant($restaurant, $slug);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($slots, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      description="Get meal list for restaurant menu",
     *      requirements={
     *          {"name"="menuId", "dataType"="integer", "required"=true, "description"="Id client"},
     *          {"name"="slug", "dataType"="integer", "required"=true, "description"="Slug of the slot"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("client", class="ClabBoardBundle:Client", options={"id" = "id"})
     */
    public function getOneSlotForChainStore(Client $client, $slug)
    {
        $slots = $this->get('app_restaurant.meal_manager')->getOneSlotForChainStore($client, $slug);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($slots, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource=true,
     *      description="Get meal available for a restaurant menu",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id menu"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("menu", class="ClabRestaurantBundle:RestaurantMenu", options={"id" = "id"})
     */
    public function getAvailableforRestaurantMenuAction(RestaurantMenu $menu)
    {
        $meals = $this->get('app_restaurant.meal_manager')->getAvailableForRestaurantMenu($menu);
        $result = array();
        foreach ($meals as $k => $meal) {
            $result[$k]['id'] = $meal->getId();
            $result[$k]['isOnline'] = $meal->getIsOnline();
            $result[$k]['created'] = $meal->getCreated();
            $result[$k]['updated'] = $meal->getUpdated();
            $result[$k]['name'] = $meal->getName();
            $result[$k]['slug'] = $meal->getSlug();
            $result[$k]['description'] = $meal->getDescription();
            $result[$k]['price'] = $meal->getPrice();
            foreach ($meal->getSlots() as $slot) {
                $result[$k]['slots'][$slot->getId()]['created'] = $slot->getCreated();
                $result[$k]['slots'][$slot->getId()]['updated'] = $slot->getUpdated();
                $result[$k]['slots'][$slot->getId()]['name'] = $slot->getName();
                $result[$k]['slots'][$slot->getId()]['slug'] = $slot->getSlug();
                foreach ($slot->getProductCategories() as $key => $category) {
                    foreach ($category->getProducts() as $product) {
                        if (in_array($product->getId(),$slot->getDisabledProducts())) {
                            $category->removeProduct($product);
                        } else {
                            $supplement = (isset($slot->getCustomPrices()[$product->getId()])?$slot->getCustomPrices()[$product->getId()]:null);
                            $product->setSupplement($supplement);
                            $product->setCategory(null);
                        }
                    }
                    $result[$k]['slots'][$slot->getId()]['products'][$key] = $category->getProducts()->toArray();
                }
            }
        }
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($result, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource=true,
     *      description="Get meal available for a restaurant",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id menu"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\Meal"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getAvailableforRestaurantAction(Restaurant $restaurant)
    {
        try {
            $meals = $this->get('app_restaurant.meal_manager')->getForRestaurant($restaurant);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        $result = array();
        foreach ($meals as $k => $meal) {
            $result[$k]['id'] = $meal->getId();
            $result[$k]['isOnline'] = $meal->getIsOnline();
            $result[$k]['created'] = $meal->getCreated();
            $result[$k]['updated'] = $meal->getUpdated();
            $result[$k]['name'] = $meal->getName();
            $result[$k]['slug'] = $meal->getSlug();
            $result[$k]['description'] = $meal->getDescription();
            $result[$k]['price'] = $meal->getPrice();
            foreach ($meal->getSlots() as $slot) {
                $result[$k]['slots'][$slot->getId()]['created'] = $slot->getCreated();
                $result[$k]['slots'][$slot->getId()]['updated'] = $slot->getUpdated();
                $result[$k]['slots'][$slot->getId()]['name'] = $slot->getName();
                $result[$k]['slots'][$slot->getId()]['slug'] = $slot->getSlug();
                foreach ($slot->getProductCategories() as $key => $category) {
                    foreach ($category->getProducts() as $product) {
                        if (in_array($product->getId(),$slot->getDisabledProducts())) {
                            $category->removeProduct($product);
                        } else {
                            $supplement = (isset($slot->getCustomPrices()[$product->getId()])?$slot->getCustomPrices()[$product->getId()]:null);
                            $product->setSupplement($supplement);
                            $product->setCategory(null);
                        }
                    }
                    $result[$k]['slots'][$slot->getId()]['products'][$key] = $category->getProducts()->toArray();
                }
            }
        }
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($result, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource=true,
     *      description="Remove meal for a menu",
     *      requirements={
     *          {"name"="meal", "dataType"="integer", "required"=true, "description"="Id meal"}
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Meal\RestMealType",
     *      output="JSON RESPONSE"
     * )
     * @ParamConverter("menu", class="ClabRestaurantBundle:Meal", options={"id" = "id"})
     */
    public function removeAction(Meal $meal)
    {
        $this->get('app_restaurant.meal_manager')->remove($meal);

        return new JsonResponse('Meal #'.$meal->getId().' correctly removed', 200);
    }

    /**
     * @ApiDoc(
     *      section="Meals",
     *      resource=true,
     *      description="Remove meal for a menu",
     *      requirements={
     *
     *          {"name"="meal", "dataType"="integer", "required"=true, "description"="Id meal"},
     *          {"name"="menu", "dataType"="integer", "required"=true, "description"="Id menu"}
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Meal\RestMealType",
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
}
