<?php

namespace Clab\RestaurantBundle\Repository;

use Clab\BoardBundle\Entity\Client;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\RestaurantMenu;

class MealRepository extends EntityRepository
{
    public function getForRestaurant(Restaurant $restaurant, $params = array())
    {
        $qb = $this->createQueryBuilder('meal')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('meal.position', 'asc')
        ;

        if (isset($params['hasSlots'])) {
            $qb
                ->leftJoin('meal.slots', 'slot')
                ->having('count(slot.id) > 0')
                ->groupBy('meal.id')
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function getForRestaurantAsArray(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('meal')
            ->select('meal.id, meal.name, meal.slug, meal.description, meal.price, meal.position')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('meal.position', 'asc');

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function getMealDetails(Meal $meal)
    {
        $qb = $this->createQueryBuilder('meal')
            ->select('meal.id, meal.name, meal.slug, meal.description, meal.price, meal.position')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('meal.position', 'asc');

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function getUpdatedForRestaurant(Restaurant $restaurant, $updated)
    {
        $qb = $this->createQueryBuilder('meal')
            ->where('meal.isDeleted = 0')
            ->andWhere('meal.updated > :updated')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('updated', $updated)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('meal.position', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function getCreatedForRestaurant(Restaurant $restaurant, $created)
    {
        $qb = $this->createQueryBuilder('meal')
            ->where('meal.isDeleted = 0')
            ->andWhere('meal.created > :created')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('created', $created)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('meal.position', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function getForChainStore(Client $chainStore)
    {
        $qb = $this->createQueryBuilder('meal')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->leftJoin('menus.chainStore', 'chainStore')
            ->andWhere('chainStore = :chainStore')
            ->setParameter('chainStore', $chainStore)
            ->orderBy('meal.position', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function getOneForRestaurant(Restaurant $restaurant, $slug)
    {
        $qb = $this->createQueryBuilder('meal')
            ->andWhere('meal.slug = :slug')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('slug', $slug)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('meal.position', 'asc');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getOneForChainStore(Client $chainStore, $slug)
    {
        $qb = $this->createQueryBuilder('meal')
            ->andWhere('meal.slug = :slug')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->leftJoin('menus.chainStore', 'chainStore')
            ->andWhere('chainStore = :chainStore')
            ->setParameter('slug', $slug)
            ->setParameter('chainStore', $chainStore)
            ->orderBy('meal.position', 'asc');

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getAvailableForRestaurantMenu(RestaurantMenu $menu)
    {
        $qb = $this->createQueryBuilder('meal')
            ->where('meal.isOnline = 1')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->andWhere('menus.id = :menu')
            ->leftJoin('meal.slots', 'slots')
            ->andWhere('meal.slots IS NOT EMPTY')
            ->andWhere('slots.productCategories IS NOT EMPTY')
            ->setParameter('menu', $menu)
            ->orderBy('meal.position', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function getAvailableForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('meal')
            ->where('meal.isOnline = 1')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->andWhere('menus.restaurant = :restaurant')
            ->leftJoin('meal.slots', 'slots')
            ->andWhere('meal.slots IS NOT EMPTY')
            ->andWhere('slots.productCategories IS NOT EMPTY')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('meal.position', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function findOneAvailable(array $parameters = array())
    {
        $qb = $this->createQueryBuilder('meal')
            ->where('meal.isOnline = 1')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.slots', 'slots')
            ->andWhere('slots.productCategories IS NOT EMPTY');

        foreach ($parameters as $key => $param) {
            $qb->andWhere('meal.' . $key . ' = :' . $key)
                ->setParameter($key, $param);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getChainStoreChildren(RestaurantMenu $menu, Meal $meal)
    {
        $qb = $this->createQueryBuilder('meal')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->andWhere('menus.id = :menuId')
            ->setParameter('menuId', $menu->getId())
            ->andWhere('meal.parent = :parent')
            ->setParameter('parent', $meal);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getForRestaurantAndParent(Restaurant $restaurant, Meal $meal)
    {
        $qb = $this->createQueryBuilder('meal')
            ->andWhere('meal.isDeleted = 0')
            ->andWhere('meal.parent = :parent')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->leftJoin('menus.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('parent', $meal)
            ->orderBy('meal.position', 'asc');
        return $qb->getQuery()->getOneOrNullResult();
    }

    public function getMealsCount($restaurant)
    {
        $qb = $this->createQueryBuilder('meal')
            ->select('COUNT(DISTINCT(meal)) as meals_count')
            ->where('meal.isOnline = 1')
            ->andWhere('meal.isDeleted = 0')
            ->leftJoin('meal.restaurantMenus', 'menus')
            ->andWhere('menus.restaurant = :restaurant')
            ->leftJoin('meal.slots', 'slots')
            ->andWhere('meal.slots IS NOT EMPTY')
            ->andWhere('slots.productCategories IS NOT EMPTY')
            ->setParameter('restaurant', $restaurant)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
