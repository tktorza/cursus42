<?php

namespace Clab\RestaurantBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\Product;
use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Clab\RestaurantBundle\Entity\ProductCategory;

class ProductRepository extends EntityRepository
{
    public function getForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('product')
            ->andWhere('product.isDeleted = 0')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getUpdatedForRestaurant(Restaurant $restaurant, $updated)
    {
        $qb = $this->createQueryBuilder('product')
            ->where('product.isDeleted = 0')
            ->andWhere('product.updated > :date')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('date', $updated)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getCreatedForRestaurant(Restaurant $restaurant, $created)
    {
        $qb = $this->createQueryBuilder('product')
            ->where('product.isDeleted = 0')
            ->andWhere('product.created > :date')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('date', $created)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getForChainStore(Client $chainStore)
    {
        $qb = $this->createQueryBuilder('product')
            ->andWhere('product.isDeleted = 0')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->leftJoin('menus.chainStore', 'chainStore')
            ->andWhere('chainStore = :chainStore')
            ->setParameter('chainStore', $chainStore)
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getOneForRestaurant(Restaurant $restaurant, $slug)
    {
        $qb = $this->createQueryBuilder('product')
            ->andWhere('product.slug = :slug')
            ->andWhere('product.isDeleted = 0')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('slug', $slug)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getOneForChainStore(Client $chainStore, $slug)
    {
        $qb = $this->createQueryBuilder('product')
            ->andWhere('product.slug = :slug')
            ->andWhere('product.isDeleted = 0')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->leftJoin('menus.chainStore', 'chainStore')
            ->andWhere('chainStore = :chainStore')
            ->setParameter('slug', $slug)
            ->setParameter('chainStore', $chainStore)
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getAvailableForRestaurantMenu(RestaurantMenu $menu)
    {
        $qb = $this->createQueryBuilder('product')
            ->where('product.isOnline = 1')
            ->andWhere('product.isPDJ = 0')
            ->andWhere('product.isDeleted = 0')
            ->andWhere('(product.unlimitedStock = 1 OR product.stock > 0)')
            ->orWhere('product.isPDJ is NULL')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->andWhere('menus.id = :menuId')
            ->setParameter('menuId', $menu->getId())
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getAvailableForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('product')
            ->where('product.isOnline = 1')
            ->andWhere('product.isPDJ = 0')
            ->andWhere('product.isDeleted = 0')
            ->andWhere('(product.unlimitedStock = 1 OR product.stock > 0)')
            ->orWhere('product.isPDJ is NULL')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->andWhere('menus.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getAvailablePDJForRestaurantMenu(RestaurantMenu $menu)
    {
        $qb = $this->createQueryBuilder('product')
            ->where('product.isOnline = 1')
            ->andWhere('product.isPDJ = 1')
            ->andWhere('product.mealOnly = 0')
            ->andWhere('product.startDate <= :date_now')
            ->andWhere('product.endDate >= :date_now_end')
            ->andWhere('product.isDeleted = 0')
            ->andWhere('(product.unlimitedStock = 1 OR product.stock > 0)')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->andWhere('menus.id = :menuId')
            ->setParameter('menuId', $menu->getId())
            ->setParameter('date_now', new \DateTime('now'))
            ->setParameter('date_now_end', new \DateTime('-1 day'))
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getAllPDJForRestaurantMenu(RestaurantMenu $menu)
    {
        $qb = $this->createQueryBuilder('product')
            ->where('product.isOnline = 1')
            ->andWhere('product.isPDJ = 1')
            ->andWhere('product.isDeleted = 0')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->andWhere('menus.id = :menuId')
            ->setParameter('menuId', $menu->getId())
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getAvailableForCategoryAndMenu(ProductCategory $category, RestaurantMenu $menu)
    {
        $qb = $this->createQueryBuilder('product')
            ->where('product.isOnline = 1')
            ->andWhere('product.isDeleted = 0')
            ->andWhere('(product.unlimitedStock = 1 OR product.stock > 0)')
            ->andWhere('product.category = :category')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->andWhere('menus.id = :menuId')
            ->setParameter('category', $category)
            ->setParameter('menuId', $menu->getId())
            ->orderBy('product.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findOneAvailable(array $parameters = array())
    {
        $qb = $this->createQueryBuilder('product')
            ->where('product.isOnline = 1')
            ->andWhere('product.isDeleted = 0')
            ->andWhere('(product.unlimitedStock = 1 OR product.stock > 0)')
        ;

        foreach ($parameters as $key => $param) {
            $qb->andWhere('product.'.$key.' = :'.$key)
                ->setParameter($key, $param);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getChainStoreChildren(RestaurantMenu $menu, Product $product)
    {
        $qb = $this->createQueryBuilder('product')
            ->andWhere('product.isDeleted = 0')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->andWhere('menus.id = :menuId')
            ->setParameter('menuId', $menu->getId())
            ->andWhere('product.parent = :parent')
            ->setParameter('parent', $product);

        return $qb->getQuery()->getOneOrNullResult();
    }
    
    public function getForRestaurantAndParent(Restaurant $restaurant, $parent)
    {
        $qb = $this->createQueryBuilder('product')
            ->andWhere('product.parent = :parent')
            ->andWhere('product.isDeleted = 0')
            ->leftJoin('product.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('parent', $parent)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('product.position', 'asc')
        ;
        return $qb->getQuery()->getOneOrNullResult();
    }
}
