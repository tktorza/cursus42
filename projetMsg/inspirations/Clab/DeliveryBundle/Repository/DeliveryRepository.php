<?php

namespace Clab\DeliveryBundle\Entity\Repository;

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
}
