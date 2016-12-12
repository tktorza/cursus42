<?php

namespace Clab\ApiBundle\Repository;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\EntityRepository;

class SessionRepository extends EntityRepository
{
     public function  findAllForRestaurant(Restaurant $restaurant) {

         $tokens = [];

         foreach ($restaurant->getTokenDevices() as $device) {
             $tokens[] = $device->getToken();
         }

         $queryBuilder = $this
             ->createQueryBuilder('s')
             ->where('s.token IN (:tokens)')
             ->andWhere('s.isActive = true')
             ->setParameter('tokens', $tokens)
         ;

         return $queryBuilder->getQuery()->getResult();
     }
}