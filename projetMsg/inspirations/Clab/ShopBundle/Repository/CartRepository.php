<?php

namespace Clab\ShopBundle\Repository;

use Doctrine\ORM\EntityRepository;

class CartRepository extends EntityRepository
{
    public function load($id)
    {
        $qb = $this->createQueryBuilder('cart')
            ->select('cart', 'elements', 'childrens', 'product', 'meal', 'product2', 'meal2', 'choices', 'productTax', 'productTax2', 'mealTax', 'mealTax2', 'restaurant')
            ->where('cart.id = :id')
            ->setParameter('id', $id);

        $qb
            ->leftJoin('cart.elements', 'elements')
            ->leftJoin('elements.product', 'product')
            ->leftJoin('elements.meal', 'meal')
            ->leftJoin('product.tax', 'productTax')
            ->leftJoin('meal.tax', 'mealTax')
            ->leftJoin('elements.childrens', 'childrens')
            ->leftJoin('childrens.product', 'product2')
            ->leftJoin('childrens.meal', 'meal2')
            ->leftJoin('product2.tax', 'productTax2')
            ->leftJoin('meal2.tax', 'mealTax2')
            ->leftJoin('elements.choices', 'choices')
            ->leftJoin('cart.restaurant', 'restaurant')
        ;

        return $qb->getQuery()->getSingleResult();
    }
}
