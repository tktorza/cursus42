<?php

namespace Clab\DeliveryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class DeliveryPeriodRepository extends EntityRepository
{
    public function getCurrentForRestaurant($restaurant)
    {
        $qb = $this->createQueryBuilder('dp')
            ->andWhere('dp.is_online = 1')
            ->andWhere('dp.is_deleted = 0')
            ->andWhere('dp.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant);
            //->andWhere('dp.start <= :today')
            //->andWhere('dp.end >= :today')
            //->setParameter('today', date_create('today'));

        $query = $qb->getQuery();
        $results = $query->getResult();

        if(count($results) > 0 && isset($results[0])) {
            return $results[0];
        }

        return null;
    }
}
