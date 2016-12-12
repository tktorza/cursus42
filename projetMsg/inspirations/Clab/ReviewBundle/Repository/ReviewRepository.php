<?php

namespace Clab\ReviewBundle\Repository;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\EntityRepository;

class ReviewRepository extends EntityRepository
{
    public function getLatest($limit = 5)
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.is_online = 1')
            ->andWhere('s.is_deleted = 0');

        if ($limit && is_int($limit)) {
            $qb->setMaxResults($limit);
        }

        $qb->orderBy('s.created', 'desc');

        return $qb->getQuery()->getResult();
    }

    public function findAllBetweenDate($start, $end)
    {
        $qb = $this->createQueryBuilder('review')
            ->select('review', 'profile')
            ->andWhere('review.created >= :start')
            ->andWhere('review.created <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->leftJoin('review.profile', 'profile')
            ->orderBy('review.id', 'desc');

        return $qb->getQuery()->getResult();
    }

    public function findAllScoreForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.score')
            ->where('r.restaurant = :restaurant')
            ->andWhere('r.isOnline = 1')
            ->setParameter('restaurant', $restaurant);

        $results = $qb->getQuery()->getResult();
        $total = 0;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $total = $total + $result['score'];
            }

            return ($total / count($results));
        }
    }

    public function findCleanScoreForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.hygieneScore')
            ->where('r.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant);

        $results = $qb->getQuery()->getResult();
        $total = 0;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $total = $total + $result['hygieneScore'];
            }

            return ($total / count($results));
        }
    }
    public function findCookScoreForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.cookScore')
            ->where('r.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant);

        $results = $qb->getQuery()->getResult();
        $total = 0;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $total = $total + $result['cookScore'];
            }

            return ($total / count($results));
        }
    }
    public function findPriceScoreForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.qualityScore')
            ->where('r.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant);

        $results = $qb->getQuery()->getResult();
        $total = 0;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $total = $total + $result['qualityScore'];
            }

            return ($total / count($results));
        }
    }
    public function findServiceScoreForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.serviceScore')
            ->where('r.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant);

        $results = $qb->getQuery()->getResult();
        $total = 0;
        if (count($results) > 0) {
            foreach ($results as $result) {
                $total = $total + $result['serviceScore'];
            }
        }
        return ($total / count($results));
    }

    public function findBestForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
        ;

        $results = $qb->getQuery()->getResult();
        $best = null;

        foreach ($results as $result) {
            if (is_null($best)) {
                $best = $result;
            }

            if ($result->getUpCount() > $best->getUpCount()) {
                $best = $result;
            }
        }

        return $best;
    }
}
