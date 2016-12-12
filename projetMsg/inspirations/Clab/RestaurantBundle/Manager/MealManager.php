<?php

namespace Clab\RestaurantBundle\Manager;

use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\CartElement;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\MealSlot;
use Clab\RestaurantBundle\Entity\ProductCategory;
use Clab\RestaurantBundle\Event\MealEvent;
use Clab\RestaurantBundle\Event\MealSlotEvent;

class MealManager
{
    protected $em;
    protected $productManager;
    protected $restaurantMenuManager;
    protected $repository;
    protected $slotRepository;

    /**
     * @param EntityManager $em
     * @param $productManager
     * Constructor
     */
    public function __construct(EntityManager $em, $productManager, RestaurantMenuManager $restaurantMenuManager)
    {
        $this->em = $em;
        $this->productManager = $productManager;
        $this->restaurantMenuManager = $restaurantMenuManager;
        $this->repository = $this->em->getRepository('ClabRestaurantBundle:Meal');
        $this->slotRepository = $this->em->getRepository('ClabRestaurantBundle:MealSlot');
    }

    /**
     * @return Meal
     *              Create a new meal
     */
    public function create()
    {
        return new Meal();
    }

    /**
     * @param RestaurantMenu $menu
     *
     * @return Meal
     *              Create a Meal for a restaurant Menu
     */
    public function createForRestaurantMenu(RestaurantMenu $menu)
    {
        $meal = new Meal();
        $meal->addRestaurantMenu($menu);

        return $meal;
    }

    /**
     * @param Meal $meal
     *
     * @return bool
     *              Soft delete a meal : keep it in database
     */
    public function remove(Meal $meal)
    {
        $meal->setIsOnline(false);
        $meal->setIsDeleted(true);

        $this->em->flush();

        foreach ($meal->getChildrens() as $children) {
            $this->remove($children);
        }

        return true;
    }

    /**
     * @param Meal           $meal
     * @param RestaurantMenu $menu
     *
     * @return bool
     *              Soft delete a meal in a restaurant menu : disable the meal in the menu
     */
    public function removeForRestaurantMenu(Meal $meal, RestaurantMenu $menu)
    {
        $meal->setIsOnline(false);
        $meal->setIsDeleted(true);
        $meal->removeRestaurantMenu($menu);
        $this->em->flush();

        return true;
    }

    /**
     * @param RestaurantMenu $menu
     *
     * @return array|\Clab\RestaurantBundle\Entity\Meal[]
     *                                                    Display available meal in a menu
     */
    public function getForRestaurantMenu(RestaurantMenu $menu)
    {
        return $this->repository->findBy(array(
            'restaurantMenus' => $menu,
            'isDeleted' => false,
        ), array(
            'position' => 'asc',
        ));
    }

    /**
     * @param RestaurantMenu $menu
     *
     * @return array|\Clab\RestaurantBundle\Entity\Meal[]
     *                                                    Display available meal in a menu
     */
    public function getForRestaurant(Restaurant $restaurant)
    {
        $meals = $this->repository->getForRestaurant($restaurant);

        return $meals;
    }

    /**
     * @param Restaurant $restaurant
     * @return array
     */
    public function getForRestaurantAsArray(Restaurant $restaurant)
    {
        $meals = $this->repository->getForRestaurantAsArray($restaurant);

        return $meals;
    }

    /**
     * @param RestaurantMenu $menu
     *
     * @return array
     *               Display available meal in a menu
     */
    public function getAvailableForRestaurantMenu(RestaurantMenu $menu)
    {
        return $this->repository->getAvailableForRestaurantMenu($menu);
    }

    /**
     * @param RestaurantMenu $menu
     *
     * @return array
     *               Display available meal in a menu
     */
    public function getAvailableForRestaurant(Restaurant $restaurant)
    {
        return $this->repository->getAvailableForRestaurant($restaurant);
    }

    /**
     * @param Client $chainStore
     *
     * @return array
     *               Display available meal in a chainstore
     */
    public function getForChainStore(Client $chainStore)
    {
        return $this->repository->getForChainStore($chainStore);
    }

    /**
     * @param Restaurant $restaurant
     * @param $slug
     *
     * @return mixed
     *               Display one available meal for a given restaurant
     */
    public function getOneForRestaurant(Restaurant $restaurant, $slug)
    {
        return $this->repository->getOneForRestaurant($restaurant, $slug);
    }

    /**
     * @param Client $chainStore
     * @param $slug
     *
     * @return mixed
     *               Display one available meal for a given chainstore
     */
    public function getOneForChainStore(Client $chainStore, $slug)
    {
        return $this->repository->getOneForChainStore($chainStore, $slug);
    }

    /**
     * @param ArrayCollection $meals
     *
     * @return bool
     *              Reorder meals in the backoffice
     */
    public function reorder(ArrayCollection $meals)
    {
        foreach ($meals as $key => $meal) {
            $meal->setPosition($key);
        }

        $this->em->flush();

        return true;
    }

    /**
     * @param Meal     $meal
     * @param MealSlot $slot
     *
     * @return bool
     *              Add a MealSlot for a meal
     */
    public function addSlotToMeal(Meal $meal, MealSlot $slot)
    {
        $slot->addMeal($meal);
        $this->em->flush();

        foreach ($meal->getChildrens() as $children) {
            $restaurant = $children->getRestaurantMenus()->first()->getRestaurant();
            $childSlot = $this->slotRepository->findOneBy(array('restaurant' => $restaurant, 'parent' => $slot));

            $this->addSlotToMeal($children, $childSlot);
        }

        return true;
    }

    /**
     * @param Meal     $meal
     * @param MealSlot $slot
     *
     * @return bool
     *              Remove a slot from a meal
     */
    public function removeSlotFromMeal(Meal $meal, MealSlot $slot)
    {
        $slot->removeMeal($meal);
        $this->em->flush();

        foreach ($meal->getChildrens() as $children) {
            $restaurant = $children->getRestaurantMenus()->first()->getRestaurant();
            $childSlot = $this->slotRepository->findOneBy(array('restaurant' => $restaurant, 'parent' => $slot));

            $this->removeSlotFromMeal($children, $childSlot);
        }

        return true;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return MealSlot
     *                  Create a new meal slot for a given restaurant
     */
    public function createMealSlotForRestaurant(Restaurant $restaurant)
    {
        $slot = new MealSlot();
        $slot->setRestaurant($restaurant);

        return $slot;
    }

    /**
     * @param Client $chainStore
     *
     * @return MealSlot
     *                  Create a new meal slot for a given chainstore
     */
    public function createMealSlotForChainStore(Client $chainStore)
    {
        $slot = new MealSlot();
        $slot->setClient($chainStore);

        return $slot;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return array|\Clab\RestaurantBundle\Entity\MealSlot[]
     *                                                        Return all slots for a given restaurant
     */
    public function getSlotForRestaurant(Restaurant $restaurant)
    {
        return $this->slotRepository->findBy(array('restaurant' => $restaurant));
    }

    /**
     * @param Client $chainStore
     *
     * @return array|\Clab\RestaurantBundle\Entity\MealSlot[]
     *                                                        Create a new meal slot for a given chainstore
     */
    public function getSlotForChainStore(Client $chainStore)
    {
        return $this->slotRepository->findBy(array('client' => $chainStore));
    }

    /**
     * @param Restaurant $restaurant
     * @param $slug
     *
     * @return MealSlot
     *                  Get one given slot for a given restaurant
     */
    public function getOneSlotForRestaurant(Restaurant $restaurant, $slug)
    {
        return $this->slotRepository->findOneBy(array('restaurant' => $restaurant, 'slug' => $slug));
    }

    /**
     * @param Client $chainStore
     * @param $slug
     *
     * @return MealSlot
     *                  Get one given slot for a given chainstore
     */
    public function getOneSlotForChainStore(Client $chainStore, $slug)
    {
        return $this->slotRepository->findOneBy(array('client' => $chainStore, 'slug' => $slug));
    }

    /**
     * @param MealSlot $slot
     *
     * @return bool
     *              Soft delete a meal slot
     */
    public function removeMealSlot(MealSlot $slot)
    {
        foreach ($slot->getChildrens() as $children) {
            $this->em->remove($children);
        }

        $this->em->remove($slot);
        $this->em->flush();

        return true;
    }

    public function setDisabledProductsToSlot(MealSlot $slot, array $disabledProducts)
    {
        $data = array();
        foreach ($disabledProducts as $product) {
            $data[] = $product->getId();
        }

        $slot->setDisabledProducts($data);

        foreach ($slot->getChildrens() as $children) {
            $products = array();

            foreach ($disabledProducts as $product) {
                $childMenu = $children->getRestaurant()->getRestaurantMenus()->first();
                $childProduct = $this->em->getRepository('ClabRestaurantBundle:Product')->getChainStoreChildren($childMenu, $product);

                if ($childProduct) {
                    $products[] = $childProduct;
                }
            }

            $this->setDisabledProductsToSlot($children, $products);
        }
    }

    public function setCustomPricesToSlot(MealSlot $slot, array $customPrices)
    {
        $data = array();
        foreach ($customPrices as $customPrice) {
            $data[$customPrice['product']->getId()] = $customPrice['price'];
        }

        $slot->setCustomPrices($data);
    }

    public function createdFromChainStore(MealEvent $event)
    {
        $meal = $event->getMeal();
        $menu = $meal->getRestaurantMenus()->first();
        $chainStore = $menu->getChainStore();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $childMenu = $this->restaurantMenuManager->getByTypeForRestaurant($restaurant, $menu->getType());

            $child = $this->createForRestaurantMenu($childMenu);
            $child->setParent($meal);
            $child->setName($meal->getName());
            $child->setDescription($meal->getDescription());
            $child->setPrice($meal->getPrice());
            $child->setGallery($meal->getGallery());
            $child->setTax($meal->getTax());
            $this->em->persist($child);
        }

        $this->em->flush();
    }

    public function updatedFromChainStore(MealEvent $event)
    {
        $meal = $event->getMeal();
        $menu = $meal->getRestaurantMenus()->first();
        $chainStore = $menu->getChainStore();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $childMenu = $this->restaurantMenuManager->getByTypeForRestaurant($restaurant, $menu->getType());
            $child = $this->repository->getChainStoreChildren($childMenu, $meal);

            $child->setName($meal->getName());
            $child->setDescription($meal->getDescription());
            $child->setPrice($meal->getPrice());
            $child->setGallery($meal->getGallery());
            $child->setTax($meal->getTax());
        }

        $this->em->flush();
    }

    public function slotCreatedFromChainStore(MealSlotEvent $event)
    {
        $mealSlot = $event->getMealSlot();
        $chainStore = $mealSlot->getClient();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $child = $this->createMealSlotForRestaurant($restaurant);
            $child->setName($mealSlot->getName());
            $child->setParent($mealSlot);
            $this->em->persist($child);
        }

        $this->em->flush();
    }

    public function slotUpdatedFromChainStore(MealSlotEvent $event)
    {
        $mealSlot = $event->getMealSlot();
        $chainStore = $mealSlot->getClient();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $child = $this->slotRepository->findOneBy(array('restaurant' => $restaurant, 'parent' => $mealSlot));

            $categories = new ArrayCollection();
            foreach ($mealSlot->getProductCategories() as $category) {
                $childCategory = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array('restaurant' => $restaurant, 'parent' => $category));
                $categories[] = $childCategory;
            }

            if ($child) {
                $child->setName($mealSlot->getName());
                $child->setPosition($mealSlot->getPosition());
                $child->setProductCategories($categories);
            }
        }

        $this->em->flush();
    }

    /**
     * @param MealSlot       $slot
     * @param RestaurantMenu $menu
     *
     * @return array
     *               Return products for a meal slot in a given restaurant menu
     */
    public function getProductsForMealSlotAndMenu(MealSlot $slot, RestaurantMenu $menu)
    {
        $productList = array();

        foreach ($slot->getProductCategories() as $category) {
            foreach ($this->productManager->getAvailableForCategoryAndMenu($category, $menu) as $product) {
                // if product is not disabled for this slot
                if (!in_array($product->getId(), $slot->getDisabledProducts())) {
                    // add price if custom price is defined
                    $price = in_array($product->getId(), array_keys($slot->getCustomPrices())) ? $slot->getCustomPrices()[$product->getId()] : null;
                    $productList[] = array('product' => $product, 'price' => $price);
                }
            }
        }

        return $productList;
    }

    /**
     * @param $serial
     *
     * @return array
     *               Unserialize the data
     */
    public function unserialize($serial)
    {
        $result = array();

        $json = unserialize($serial);

        if (isset($json['meal'])) {
            $result['meal'] = $this->em->getRepository('ClabRestaurantBundle:Meal')->find($json['meal']);
        }

        foreach ($json['slots'] as $data) {
            $product = null;
            if (isset($data['product']) && $data['product']) {
                $product = $this->em->getRepository('ClabRestaurantBundle:Product')->find($data['product']);
            }

            $optionsChoices = array();
            if (isset($data['options']) && is_array($data['options'])) {
                foreach ($data['options'] as $optionChoice) {
                    $optionsChoices[] = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->find($optionChoice);
                }
            }

            if (isset($data['slot']) && $data['slot']) {
                $slot = $this->em->getRepository('ClabRestaurantBundle:MealSlot')->find($data['slot']);

                if ($slot) {
                    $result['slots'][] = array(
                        'slot' => $slot,
                        'product' => isset($product) ? $product : null,
                        'price' => isset($data['price']) ? $data['price'] : 0,
                        'options' => $optionsChoices,
                    );
                }
            }
        }

        return $result;
    }

    /**
     * @param $meal
     *
     * @return string
     *                Serialize the data
     */
    public function serialize($meal)
    {
        $result = array('meal' => $meal['meal']->getId(), 'slots' => array());

        foreach ($meal['slots'] as $data) {
            $optionsId = array();
            if (isset($data['options']) && is_array($data['options'])) {
                foreach ($data['options'] as $optionChoice) {
                    $optionsId[] = $optionChoice->getId();
                }
            }

            $result['slots'][] = array(
                'slot' => $data['slot']->getId(),
                'product' => $data['product'] ? $data['product']->getId() : null,
                'price' => $data['price'] ? $data['price'] : 0,
                'options' => $optionsId,
            );
        }

        return serialize($result);
    }

    /**
     * @param $meal
     * @param int $orderType
     *
     * @return bool
     *              Add a meal to a cart
     */
    public function addToCart($meal, $orderType = 1)
    {
        $element = new CartElement();
        $element->setMeal($meal['meal']);
        $element->setQuantity(1);
        $element->setPrice($meal['meal']->getCurrentPrice($orderType));
        $element->setTax($meal['meal']->getTax());

        foreach ($meal['choices'] as $data) {
            $choice = $data['choice'];
            $childElement = new CartElement();
            $childElement->setProduct($choice);
            $childElement->setQuantity(1);
            $childElement->setPrice($data['slot']->getProductPrice($choice));
            //@todo
            $childElement->setTax($choice->getTax());
            $element->addChildren($childElement);

            foreach ($data['options'] as $optionChoice) {
                $choice = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->find($optionChoice);
                if ($choice) {
                    $childElement->addChoice($choice);
                    $childElement->setPrice($childElement->getPrice() + $choice->getPrice());
                }
            }
        }

        if ($element) {
            $this->em->persist($element);
            $this->em->flush();

            $cartManager = $this->container->get('app_shop.cart_manager');
            $cartManager->addMealToCart($element->getId(), $orderType);
        }

        return true;
    }

    public function getMealsCount($restaurant)
    {
        return intval($this->repository->getMealsCount($restaurant)['meals_count']);
    }
}
