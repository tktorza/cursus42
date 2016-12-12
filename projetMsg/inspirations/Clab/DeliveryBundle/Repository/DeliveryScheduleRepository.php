<?php

namespace Clab\DeliveryBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class DeliveryScheduleRepository extends EntityRepository
{
    public function getDaySchedules($restaurant, $day = 'today')
    {
        if($day == 'today') {
            $day = date_create('today');
        }

        $qb = $this->createQueryBuilder('ds')
            ->andWhere('ds.is_online = 1')
            ->andWhere('ds.is_deleted = 0')
            ->innerJoin('ds.deliveryDay', 'dd', 'WITH', 'dd.day = :day')
            ->setParameter('day', $day);

        $query = $qb->getQuery();
        $results = $query->getResults();

        if(count($results)) {
            return $results;
        }

        $qb = $this->createQueryBuilder('ds')
            ->andWhere('ds.is_online = 1')
            ->andWhere('ds.is_deleted = 0')
            ->innerJoin('ds.deliveryDay', 'dd', 'WITH', 'dd.weekDay = :weekDay')
            ->setParameter('weekDay', $day->format('N'));

        $query = $qb->getQuery();
        $results = $query->getResults();

        return $results;
    }

    public function getGenericForWeekDay($restaurant, $weekDay)
    {
         $qb = $this->createQueryBuilder('s')
            ->andWhere('s.is_online = 1')
            ->andWhere('s.is_deleted = 0')
            ->andWhere('s.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('s.parent IS NULL');

        switch ($weekDay) {
            case 1:
                $qb->andWhere('s.monday = true');
                break;
            case 2:
                $qb->andWhere('s.tuesday = true');
                break;
            case 3:
                $qb->andWhere('s.wednesday = true');
                break;
            case 4:
                $qb->andWhere('s.thursday = true');
                break;
            case 5:
                $qb->andWhere('s.friday = true');
                break;
            case 6:
                $qb->andWhere('s.saturday = true');
                break;
            case 7:
                $qb->andWhere('s.sunday = true');
                break;
            default:
                return null;
                break;
        }

        $query = $qb->getQuery();
        $results = $query->getOneOrNullResult();

        return $results;
    }

    public function getRecurrentSchedulesForDay($restaurant, $day)
    {
         $qb = $this->createQueryBuilder('s')
            ->andWhere('s.is_online = 1')
            ->andWhere('s.is_deleted = 0')
            ->andWhere('s.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant);

        $weekDay = $day->format('N');

        switch ($weekDay) {
            case 1:
                $qb->andWhere('s.monday = true');
                break;
            case 2:
                $qb->andWhere('s.tuesday = true');
                break;
            case 3:
                $qb->andWhere('s.wednesday = true');
                break;
            case 4:
                $qb->andWhere('s.thursday = true');
                break;
            case 5:
                $qb->andWhere('s.friday = true');
                break;
            case 6:
                $qb->andWhere('s.saturday = true');
                break;
            case 7:
                $qb->andWhere('s.sunday = true');
                break;
            default:
                return null;
                break;
        }

        $qb->andWhere('s.customDays NOT LIKE :timestamp')
            ->setParameter('timestamp', '%' . $day->getTimestamp() . '%');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }

    public function getCustomForRestaurant($restaurant, $filterNew = false)
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.is_online = 1')
            ->andWhere('s.is_deleted = 0')
            ->andWhere('s.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('s.day IS NOT NULL');

        if($filterNew) {
            $qb->andWhere('s.day >= :today')
                ->setParameter('today', date_create('today'));
        }

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }

    public function getCustomSchedulesForDay($restaurant, $day)
    {
         $qb = $this->createQueryBuilder('s')
            ->andWhere('s.is_online = 1')
            ->andWhere('s.is_deleted = 0')
            ->andWhere('s.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('s.day = :day')
            ->setParameter('day', $day);

        $qb->orderBy('s.day', 'desc')
            ->addOrderBy('s.id', 'desc');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }
}
