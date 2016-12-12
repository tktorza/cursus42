<?php

namespace Clab\RestaurantBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PlanRepository extends EntityRepository
{
    public function findTTTPlan()
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.stripePlanId LIKE :word')
        ->setParameter('word', 'ttt%');
        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }
    public function findClickEatPlan()
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.stripePlanId LIKE :word')
            ->setParameter('word', 'clickeat%');
        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }
}
