<?php

namespace Clab\RestaurantBundle\Manager;

use Clab\RestaurantBundle\Repository\ProductCategoryRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\ProductCategory;
use Clab\RestaurantBundle\Event\ProductCategoryEvent;

class ProductCategoryManager
{
    protected $em;
    /**
     * @var $repository ProductCategoryRepository
     */
    protected $repository;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('ClabRestaurantBundle:ProductCategory');
    }

    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return ProductCategory
     *                         Create a product category for a given restaurant
     */
    public function createForRestaurant(Restaurant $restaurant)
    {
        $category = new ProductCategory();
        $category->setRestaurant($restaurant);

        return $category;
    }

    /**
     * @param Client $chainStore
     *
     * @return ProductCategory
     *                         Create a product category for a given chainstore
     */
    public function createForChainStore(Client $chainStore)
    {
        $category = new ProductCategory();
        $category->setClient($chainStore);

        return $category;
    }

    /**
     * @param ProductCategory $category
     *
     * @return bool
     *              Soft delete a product category and keep it in the database
     */
    public function remove(ProductCategory $category)
    {
        $category->setIsOnline(false);
        $category->setIsDeleted(true);

        foreach ($category->getProducts() as $product) {
            $product->setCategory(null);
            $product->setIsOnline(0);
        }

        $this->em->flush();

        foreach ($category->getChildrens() as $childrenCategory) {
            $this->remove($childrenCategory);
        }

        return true;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return array|\Clab\RestaurantBundle\Entity\ProductCategory[]
     *                                                               Get product category for a given restaurant
     */
    public function getForRestaurant(Restaurant $restaurant)
    {
        return $this->repository->findBy(array(
            'restaurant' => $restaurant,
            'isDeleted' => false,
        ), array(
            'position' => 'asc',
        ));
    }

    /**
     * @param Client $chainStore
     *
     * @return array|\Clab\RestaurantBundle\Entity\ProductCategory[]
     *                                                               Get product category for a given chainstore
     */
    public function getForChainStore(Client $chainStore)
    {
        return $this->repository->findBy(array(
            'client' => $chainStore,
            'isDeleted' => false,
        ), array(
            'position' => 'asc',
        ));
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return array
     *               Get only available (not soft deleted) category for a given restaurant
     */
    public function getAvailableForRestaurant(Restaurant $restaurant)
    {
        return $this->repository->getAvailableForRestaurant($restaurant);
    }

    /**
     * @param ArrayCollection $categories
     *
     * @return bool
     *              Reorder in the backoffice the position of the categories
     */
    public function reorder(ArrayCollection $categories)
    {
        foreach ($categories as $key => $category) {
            $category->setPosition($key);
        }

        $this->em->flush();

        return true;
    }

    /**
     * @param ProductCategoryEvent $event
     *
     * @return bool
     *              Create for children (restaurant) of a chainstore every categories
     */
    public function createdFromChainStore(ProductCategoryEvent $event)
    {
        $category = $event->getProductCategory();
        $chainStore = $category->getClient();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $child = $this->createForRestaurant($restaurant);
            $child->setParent($category);
            $child->setName($category->getName());
            $child->setType($category->getType());
            $this->em->persist($child);
        }

        $this->em->flush();

        return true;
    }

    /**
     * @param ProductCategoryEvent $event
     *
     * @return bool
     *              Update for children (restaurant) of a chainstore every categories
     */
    public function updatedFromChainStore(ProductCategoryEvent $event)
    {
        $category = $event->getProductCategory();
        $chainStore = $category->getClient();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $child = $this->repository->findOneBy(array('restaurant' => $restaurant, 'parent' => $category));

            if ($child) {
                $child->setName($category->getName());
                $child->setType($category->getType());
                $child->setPosition($category->getPosition());
                $this->em->flush();
            }
        }

        return true;
    }

    public function findProducts(Client $chainStore, $productCategory)
    {
        $productsCategory = [$productCategory];
        $productsResultCategory = array();
        foreach ($chainStore->getRestaurants() as $restaurant) {
            $productsResultCategory[] = $this->repository->findOneBy(array('restaurant' => $restaurant, 'parent' => $productCategory));
        }
        $result = array_merge($productsCategory, $productsResultCategory);

        return $result;
    }
}
