<?php

namespace Clab\RestaurantBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Clab\RestaurantBundle\Entity\ProductCategory;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\ProductOption;
use Clab\RestaurantBundle\Event\ProductEvent;

class ProductManager
{
    protected $em;
    protected $repository;
    protected $restaurantMenuManager;

    /**
     * @param EntityManager $em
     *                          Constructor
     */
    public function __construct(EntityManager $em, RestaurantMenuManager $restaurantMenuManager)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('ClabRestaurantBundle:Product');
        $this->restaurantMenuManager = $restaurantMenuManager;
    }

    /**
     * @return Product
     *                 Create a new Product
     */
    public function create()
    {
        return new Product();
    }

    /**
     * @param RestaurantMenu $menu
     *
     * @return Product
     *                 Create a new Product for a menu
     */
    public function createForRestaurantMenu(RestaurantMenu $menu)
    {
        $product = new Product();
        $product->addRestaurantMenu($menu);

        return $product;
    }

    /**
     * @param Product $product
     *
     * @return bool
     *              Soft delete a product : keep it in the database
     */
    public function remove(Product $product)
    {
        $product->setIsOnline(false);
        $product->setIsDeleted(true);
        $product->setCategory(null);

        $this->em->flush();

        foreach ($product->getChildrens() as $children) {
            $this->remove($children);
        }

        return true;
    }

    /**
     * @param RestaurantMenu $menu
     *
     * @return array|\Clab\RestaurantBundle\Entity\Product[]
     */
    public function getForRestaurantMenu(RestaurantMenu $menu)
    {
        return $this->repository->findBy(array(
            'restaurantMenu' => $menu,
            'isDeleted' => false,
        ), array(
            'position' => 'asc',
        ));
    }

    public function getAvailableForRestaurant(Restaurant $restaurant)
    {
        return $this->repository->getAvailableForRestaurant($restaurant);
    }

    public function getAvailableForRestaurantMenu(RestaurantMenu $menu)
    {
        return $this->repository->getAvailableForRestaurantMenu($menu);
    }
    public function getAvailablePDJForRestaurantMenu(RestaurantMenu $menu)
    {
        return $this->repository->getAvailablePDJForRestaurantMenu($menu);
    }
    public function getAllPDJForRestaurantMenu(RestaurantMenu $menu)
    {
        return $this->repository->getAllPDJForRestaurantMenu($menu);
    }
    public function getAvailableForCategoryAndMenu(ProductCategory $category, RestaurantMenu $menu)
    {
        return $this->repository->getAvailableForCategoryAndMenu($category, $menu);
    }

    public function getForRestaurant(Restaurant $restaurant)
    {
        return $this->repository->getForRestaurant($restaurant);
    }

    public function getForChainStore(Client $chainStore)
    {
        return $this->repository->getForChainStore($chainStore);
    }

    public function getOneForRestaurant(Restaurant $restaurant, $slug)
    {
        return $this->repository->getOneForRestaurant($restaurant, $slug);
    }

    public function getOneForChainStore(Client $chainStore, $slug)
    {
        return $this->repository->getOneForChainStore($chainStore, $slug);
    }

    public function reorder(ArrayCollection $products)
    {
        foreach ($products as $key => $product) {
            $product->setPosition($key);
        }

        $this->em->flush();

        return true;
    }

    public function resetStock(ArrayCollection $products)
    {
        foreach ($products as $product) {
            $product->setStock($product->getDefaultStock());
        }

        $this->em->flush();

        return true;
    }

    public function addOptionToProduct(Product $product, ProductOption $option)
    {
        if (!$product->getOptions()->contains($option)) {
            $product->addOption($option);
            $option->addProduct($product);
            $this->em->flush();

            foreach ($product->getChildrens() as $children) {
                $restaurant = $children->getRestaurantMenus()->first()->getRestaurant();
                $childOption = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findOneBy(array('restaurant' => $restaurant, 'parent' => $option));

                $this->addOptionToProduct($children, $childOption);
            }
        }
    }

    public function removeOptionFromProduct(Product $product, ProductOption $option)
    {
        if ($product->getOptions()->contains($option)) {
            $product->removeOption($option);
            $option->removeProduct($product);
            $this->em->flush();

            foreach ($product->getChildrens() as $children) {
                $restaurant = $children->getRestaurantMenus()->first()->getRestaurant();
                $childOption = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findOneBy(array('restaurant' => $restaurant, 'parent' => $option));

                $this->removeOptionFromProduct($children, $childOption);
            }
        }
    }

    public function createdFromChainStore(ProductEvent $event)
    {
        $product = $event->getProduct();
        $menu = $product->getRestaurantMenus()->first();
        $chainStore = $menu->getChainStore();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $childMenu = $this->restaurantMenuManager->getByTypeForRestaurant($restaurant, $menu->getType());
            $childCategory = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array('restaurant' => $restaurant, 'parent' => $product->getCategory()));

            $child = $this->createForRestaurantMenu($childMenu);
            $child->setParent($product);
            $child->setName($product->getName());
            $child->setDescription($product->getDescription());
            $child->setPrice($product->getPrice());
            $child->setGallery($product->getGallery());
            $child->setTax($product->getTax());
            $child->setCategory($childCategory);
            $this->em->persist($child);
        }

        $this->em->flush();

        return true;
    }

    public function updatedFromChainStore(ProductEvent $event)
    {
        $product = $event->getProduct();
        $menu = $product->getRestaurantMenus()->first();
        $chainStore = $menu->getChainStore();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $childMenu = $this->restaurantMenuManager->getByTypeForRestaurant($restaurant, $menu->getType());
            $childCategory = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array('restaurant' => $restaurant, 'parent' => $product->getCategory()));
            $child = $this->repository->getChainStoreChildren($childMenu, $product);

            $child->setParent($product);
            $child->setName($product->getName());
            $child->setDescription($product->getDescription());
            $child->setPrice($product->getPrice());
            $child->setGallery($product->getGallery());
            $child->setTax($product->getTax());
            $child->setCategory($childCategory);
            $child->setPosition($product->getPosition());
        }

        $this->em->flush();

        return true;
    }

    public function updatedFromChainStoreFromProduct(Client $chainStore, $product)
    {
        $menu = $product->getRestaurantMenus()->first();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $childMenu = $this->restaurantMenuManager->getByTypeForRestaurant($restaurant, $menu->getType());
            $childCategory = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array('restaurant' => $restaurant, 'parent' => $product->getCategory()));
            $child = $this->repository->getChainStoreChildren($childMenu, $product);

            $child->setParent($product);
            $child->setName($product->getName());
            $child->setDescription($product->getDescription());
            $child->setPrice($product->getPrice());
            $child->setGallery($product->getGallery());
            $child->setTax($product->getTax());
            $child->setCategory($childCategory);
            $child->setPosition($product->getPosition());
        }

        $this->em->flush();

        return true;
    }
}
