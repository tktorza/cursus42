<?php

namespace Clab\DeliveryBundle\Entity\Repository;

use Clab\ShopBundle\Entity\OrderDetail;
use Doctrine\ORM\EntityRepository;

class DeliveryRepository extends EntityRepository
{
    public function getAllForDeliveryMan($deliveryMan, $today = false)
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.is_online = 1')
            ->andWhere('d.is_deleted = 0')
            ->andWhere('d.deliveryMan = :deliveryMan')
            ->setParameter('deliveryMan', $deliveryMan)
            ->andWhere('d.state != 0')
            ->andWhere('d.state != 50');

        if($today) {
            $startTime = date_create('today');
            $startTime->modify('-1 minute');
            $endTime = date_create('today');
            $endTime->modify('+1 day');
            $endTime->modify('+1 hour');
            $qb->andWhere('d.start > :startTime')
                ->andWhere('d.end < :endTime')
                ->setParameter('startTime', $startTime)
                ->setParameter('endTime', $endTime);
        }

        return $qb->getQuery()->getResult();
    }

    public function getTodayActiveDeliveries($restaurant)
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.is_online = 1')
            ->andWhere('d.is_deleted = 0')
            ->andWhere('d.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('d.state != 50');

        $startTime = date_create('today');
        $startTime->modify('-1 minute');
        $endTime = date_create('today');
        $endTime->modify('+1 day');
        $qb->andWhere('d.start > :startTime')
            ->andWhere('d.end < :endTime')
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime);

        return $qb->getQuery()->getResult();
    }

    public function getAllAvailable($criteria)
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.is_online = 1')
            ->andWhere('d.is_deleted = 0')
            ->andWhere('d.restaurant = :restaurant')
            ->setParameter('restaurant', $criteria['restaurant'])
            ->leftJoin('d.order','o')
            ->andWhere('o.preparationState IN (:prepared)')
            ->setParameter('prepared', array(OrderDetail::ORDER_STATE_IN_PREPARATION, OrderDetail::ORDER_STATE_IN_DELIVERY))
        ;

        $startTime = date_create('today');
        $startTime->modify('-1 minute');
        $endTime = date_create('today');
        $endTime->modify('+1 day');
        $qb->andWhere('d.created > :startTime')
            ->andWhere('d.created < :endTime')
            ->setParameter('startTime', $startTime)
            ->setParameter('endTime', $endTime);

        if (isset($criteria['deliveryMan'])) {
            $qb
                ->orWhere('d.deliveryMan = :deliveryMan')
                ->setParameter('deliveryMan', $criteria['deliveryMan'])
            ;
        }

        return $qb->getQuery()->getResult();

    }
}
