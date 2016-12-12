<?php

namespace Clab\BoardBundle\Entity;

use Doctrine\ORM\EntityRepository;

use Clab\BoardBundle\Entity\OrderStatement;

class OrderStatementRepository extends EntityRepository
{
    public function findAllByRestaurantBetweenDate($restaurant, $startDate, $endDate)
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('o.endDate >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('o.endDate <= :endDate')
            ->setParameter('endDate', $endDate);

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }

    public function findAllBetweenDate($start, $end, array $parameters = array())
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o', 'restaurant', 'orders', 'delivery')
            ->andWhere('o.endDate >= :startDate')
            ->setParameter('startDate', $start)
            ->andWhere('o.endDate <= :endDate')
            ->setParameter('endDate', $end)
            ->leftJoin('o.restaurant', 'restaurant')
            ->innerJoin('o.orders', 'orders')
            ->leftJoin('orders.delivery', 'delivery')
            ->orderBy('o.id', 'asc');

        if(isset($parameters['restaurant']) && $parameters['restaurant']) {
            $qb->andWhere('o.restaurant = :restaurant')
                ->setParameter('restaurant', $parameters['restaurant']);
        }

        return $qb->getQuery()->getResult();
    }

    public function findAllOpened()
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o', 'restaurant', 'orders', 'delivery')
            ->where('o.status != :status')
            ->setParameter('status', OrderStatement::ORDERSTATEMENT_STATUS_CLOSED)
            ->leftJoin('o.restaurant', 'restaurant')
            ->innerJoin('o.orders', 'orders')
            ->leftJoin('orders.delivery', 'delivery')
            ->orderBy('o.id', 'asc');

        return $qb->getQuery()->getResult();
    }
}
