<?php

namespace Clab\RestaurantBundle\Repository;

use Doctrine\ORM\EntityRepository;

class TimesheetValidationRepository extends EntityRepository
{
    protected $lunchEnd = '18:00:00';

    public function getCountForRestaurant($restaurant, $startDate)
    {
        $qb = $this->createQueryBuilder('v')
            ->select('count(distinct v.id)')
            ->where('v.restaurant = :restaurant')
            ->andWhere('v.created >= :startDate')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('startDate', $startDate);

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    public function findForLunch($date)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.date = :date')
            ->andWhere('v.start < :lunchEnd')
            ->andWhere('v.isPrivate != 1')
            ->setParameter('date', $date)
            ->setParameter('lunchEnd', $this->lunchEnd);

        return $qb->getQuery()->getResult();
    }

    public function findForDiner($date)
    {
        $qb = $this->createQueryBuilder('v')
            ->where('v.date = :date')
            ->andWhere('v.start >= :lunchEnd')
            ->andWhere('v.isPrivate != 1')
            ->setParameter('date', $date)
            ->setParameter('lunchEnd', $this->lunchEnd);

        return $qb->getQuery()->getResult();
    }
}
