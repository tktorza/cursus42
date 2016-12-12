<?php

namespace Clab\RestaurantBundle\Repository;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\EntityRepository;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\OptionChoice;

class ProductOptionRepository extends EntityRepository
{
    public function getAvailableForProduct(Product $product)
    {
        $qb = $this->createQueryBuilder('option')
            ->where('option.isOnline = 1')
            ->andWhere('option.isDeleted = 0')
            ->leftJoin('option.products', 'products')
            ->andWhere('products = :product')
            ->leftJoin('option.choices', 'choices')
            ->andWhere('choices.isOnline = 1')
            ->andWhere('choices.isDeleted = 0')
            ->andWhere('option.choices IS NOT EMPTY')
            ->setParameter('product', $product)
            ->orderBy('option.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getUpdatedForRestaurant(Restaurant $restaurant, $updated)
    {
        $qb = $this->createQueryBuilder('option')
            ->where('option.isOnline = 1')
            ->andWhere('option.updated > :updated')
            ->andWhere('option.isDeleted = 0')
            ->leftJoin('option.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->leftJoin('option.choices', 'choices')
            ->andWhere('choices.isOnline = 1')
            ->andWhere('choices.isDeleted = 0')
            ->andWhere('option.choices IS NOT EMPTY')
            ->setParameter('updated', $updated)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('option.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getCreatedForRestaurant(Restaurant $restaurant, $created)
    {
        $qb = $this->createQueryBuilder('option')
            ->where('option.isOnline = 1')
            ->andWhere('option.created > :created')
            ->andWhere('option.isDeleted = 0')
            ->leftJoin('option.restaurant', 'restaurant')
            ->andWhere('restaurant = :restaurant')
            ->leftJoin('option.choices', 'choices')
            ->andWhere('choices.isOnline = 1')
            ->andWhere('choices.isDeleted = 0')
            ->andWhere('option.choices IS NOT EMPTY')
            ->setParameter('created', $created)
            ->setParameter('restaurant', $restaurant)
            ->orderBy('option.position', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }

    public function getOptionsForChoice(OptionChoice $choice)
    {
        $qb = $this->createQueryBuilder('option')
            ->leftJoin('option.choices', 'choices')
            ->leftJoin('choices.parent', 'parent')
            ->andWhere('parent = :parent')
            ->setParameter('parent', $choice)
        ;

        return $qb->getQuery()->getResult();
    }
}
