<?php

namespace Clab\RestaurantBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Clab\RestaurantBundle\Entity\Restaurant;

class ProductCategoryRepository extends EntityRepository
{
    public function getAvailableForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('category')
            ->where('category.isOnline = 1')
            ->andWhere('category.isDeleted = 0')
            ->andWhere('category.restaurant = :restaurant')
            ->leftJoin('category.products', 'products')
            ->andWhere('products.isOnline = 1')
            ->andWhere('products.isDeleted = 0')
            ->andWhere('(products.unlimitedStock = 1 OR products.stock > 0)')
            ->andWhere('category.products IS NOT EMPTY')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('category.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getForRestaurantAsArray(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('category')
            ->select('category.id, category.name, category.slug, category.type, category.description, category.position')
            ->addSelect('COUNT(products) as count_products')
            ->where('category.isOnline = 1')
            ->andWhere('category.isDeleted = 0')
            ->andWhere('category.restaurant = :restaurant')
            ->leftJoin('category.products', 'products')
            ->andWhere('products.isOnline = 1')
            ->andWhere('products.isDeleted = 0')
            ->andWhere('(products.unlimitedStock = 1 OR products.stock > 0)')
            ->andWhere('category.products IS NOT EMPTY')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('category.position', 'asc')
            ->groupBy('category.id')
        ;

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function getUpdatedForRestaurant(Restaurant $restaurant, $updated)
    {
        $qb = $this->createQueryBuilder('category')
            ->where('category.isOnline = 1')
            ->andWhere('category.updated > :updated')
            ->andWhere('category.isDeleted = 0')
            ->andWhere('category.restaurant = :restaurant')
            ->leftJoin('category.products', 'products')
            ->andWhere('products.isOnline = 1')
            ->andWhere('products.isDeleted = 0')
            ->andWhere('(products.unlimitedStock = 1 OR products.stock > 0)')
            ->andWhere('category.products IS NOT EMPTY')
            ->setParameter('updated', $updated)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('category.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getCreatedForRestaurant(Restaurant $restaurant, $created)
    {
        $qb = $this->createQueryBuilder('category')
            ->where('category.isOnline = 1')
            ->andWhere('category.created > :created')
            ->andWhere('category.isDeleted = 0')
            ->andWhere('category.restaurant = :restaurant')
            ->leftJoin('category.products', 'products')
            ->andWhere('products.isOnline = 1')
            ->andWhere('products.isDeleted = 0')
            ->andWhere('(products.unlimitedStock = 1 OR products.stock > 0)')
            ->andWhere('category.products IS NOT EMPTY')
            ->setParameter('created', $created)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('category.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }
}
