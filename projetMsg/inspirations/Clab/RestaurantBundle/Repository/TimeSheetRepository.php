<?php

namespace Clab\RestaurantBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\TimeSheet;

class TimeSheetRepository extends EntityRepository
{
    public function getDayEvents(Restaurant $restaurant, \Datetime $day)
    {
        $qbEvent = $this->createQueryBuilder('t')
            ->select('t', 'address')
            ->where('t.restaurant = :restaurant')
            ->andWhere('t.type IN (:types)')
            ->andWhere('t.startDate <= :day OR t.startDate IS NULL')
            ->andWhere('t.endDate >= :day OR t.endDate IS NULL')
            ->leftJoin('t.address', 'address')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('types', array(TimeSheet::TIMESHEET_TYPE_EVENT, TimeSheet::TIMESHEET_TYPE_CLOSED))
            ->setParameter('day', $day);

        $events = $qbEvent->getQuery()->getResult();
        $day->format('N');
        if ($day->format('N') == 1)
        {
            $likeDay = "MONDAY";
        }
        if ($day->format('N') == 2)
        {
            $likeDay = "TUESDAY";
        }
        if ($day->format('N') == 3)
        {
            $likeDay = "WEDNESDAY";
        }
        if ($day->format('N') == 4)
        {
            $likeDay = "THURSDAY";
        }
        if ($day->format('N') == 5)
        {
            $likeDay = "FRIDAY";
        }
        if ($day->format('N') == 6)
        {
            $likeDay = "SATURDAY";
        }
        if ($day->format('N') == 7)
        {
            $likeDay = "SUNDAY";
        }
        $qbRec = $this->createQueryBuilder('t')
            ->where('t.restaurant = :restaurant')
            ->andWhere('t.type = :type')
            ->andWhere('t.startDate <= :day OR t.startDate IS NULL')
            ->andWhere('t.endDate >= :day OR t.endDate IS NULL')
            ->andWhere('t.days LIKE :weekDay')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('type', TimeSheet::TIMESHEET_TYPE_CLASSIC)
            ->setParameter('day', $day)
            ->setParameter('weekDay', '%'.$likeDay.'%');

        $recEvents = $qbRec->getQuery()->getResult();
        return array_merge($events, $recEvents);
    }

    public function findTimesheetsForDates(Restaurant $restaurant, \Datetime $start, \Datetime $end)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.restaurant = :restaurant')
            ->andWhere('t.startDate <= :end OR t.startDate IS NULL') // pas encore commencé
            ->andWhere('t.endDate >= :start OR t.endDate IS NULL') // déjà fini
            ->setParameter('restaurant', $restaurant)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('t.startDate', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function getUpcomingEvent($restaurant)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('t.type = :type')
            ->setParameter('type', TimeSheet::TIMESHEET_TYPE_EVENT)
            ->andWhere('t.endDate >= :day')
            ->setParameter('day', date_create('today'))
            ->orderBy('t.startDate', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function getUpcomingClose($restaurant)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('t.type = :type')
            ->setParameter('type', TimeSheet::TIMESHEET_TYPE_CLOSED)
            ->andWhere('t.endDate >= :day')
            ->setParameter('day', date_create('today'))
            ->orderBy('t.startDate', 'asc');

        return $qb->getQuery()->getResult();
    }
}
