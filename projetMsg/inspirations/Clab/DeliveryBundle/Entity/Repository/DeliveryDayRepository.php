<?php

namespace Clab\DeliveryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class DeliveryDayRepository extends EntityRepository
{
    public function getDayPlanning($restaurant, $day)
    {
        $weekDay = $day->format('N');

        $qb = $this->createQueryBuilder('dd')
            ->select('dd', 'dm', 'ds')
            ->andWhere('dd.is_online = 1')
            ->andWhere('dd.is_deleted = 0')
            ->andWhere('dd.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('dd.day = :day OR (dd.weekDay = :weekDay AND dd.cancelDays NOT LIKE :timestamp)')
            ->setParameter('day', $day->format('Y-m-d'))
            ->setParameter('weekDay', $weekDay)
            ->setParameter('timestamp', '%' . $day->getTimestamp() . '%')
            ->leftJoin('dd.deliveryMen', 'dm')
            ->leftJoin('dd.deliverySchedule', 'ds');

        $query = $qb->getQuery();
        $result = $query->getResult();

        return $result;
    }

    public function getAvailableForDay($day) {
        $weekDay = $day->format('N');

        $qb = $this->createQueryBuilder('dd')
            ->where('dd.is_online = 1')
            ->andWhere('dd.is_deleted = 0')
            ->andWhere('dd.day = :day OR (dd.weekDay = :weekDay AND dd.cancelDays NOT LIKE :timestamp)')
            ->setParameter('day', $day->format('Y-m-d'))
            ->setParameter('weekDay', $weekDay)
            ->setParameter('timestamp', '%' . $day->getTimestamp() . '%');

        return $qb->getQuery()->getResult();
    }
}
