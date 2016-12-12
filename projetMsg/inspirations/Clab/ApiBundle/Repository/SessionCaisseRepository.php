<?php

namespace Clab\ApiBundle\Repository;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\EntityRepository;

class SessionCaisseRepository extends EntityRepository
{
     public function  findAllForRestaurant(Restaurant $restaurant, $start = null, $end = null) {

         $queryBuilder = $this
             ->createQueryBuilder('s')
             ->where('s.restaurant = :restaurant')
             ->setParameter('restaurant', $restaurant)
         ;

         if ($start) {
            $queryBuilder
                ->andWhere('DATE(s.dateStart) >= :start')
                ->setParameter('start', $start->format('Y-m-d'))
            ;
         }

         if ($end) {
             $queryBuilder
                 ->andWhere('DATE(s.dateEnd) <= :end')
                 ->setParameter('end', $end->format('Y-m-d'))
             ;
         }

         return $queryBuilder->getQuery()->getResult();
     }


}