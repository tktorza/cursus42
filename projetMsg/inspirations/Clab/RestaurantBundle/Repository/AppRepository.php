<?php

namespace Clab\RestaurantBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AppRepository extends EntityRepository
{
    public function findAllForFoodtruck()
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.platform = 10')
            ->orWhere('a.platform = 20');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }

    public function findAllForRestaurant()
    {
        $qb = $this->createQueryBuilder('a')
            ->where('a.platform = 0')
            ->orWhere('a.platform = 20');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }
}
