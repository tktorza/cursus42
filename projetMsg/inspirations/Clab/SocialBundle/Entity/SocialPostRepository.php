<?php

namespace Clab\SocialBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SocialPostRepository extends EntityRepository
{
    public function getForRestaurant($restaurant, $page = 1, $itemPerPage = 10)
    {
         $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.restaurant', 'r')
            ->where('r = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('s.is_online = 1')
            ->andWhere('s.is_deleted = 0');

        $qb->orderBy('s.created', 'desc');

        $qb->setFirstResult(($page - 1) * $itemPerPage)
            ->setMaxResults($itemPerPage);

        return $qb->getQuery()->getResult();
    }

    public function getLatestByRestaurant($restaurant, $limit = 5)
    {
         $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.restaurant', 'r')
            ->where('r = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('s.is_online = 1')
            ->andWhere('s.is_deleted = 0');

        if($limit && is_int($limit)) {
            $qb->setMaxResults($limit);
        }

        $qb->orderBy('s.created', 'desc');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }

    public function getLatest($limit = 5, array $parameters = array())
    {
        $qb = $this->createQueryBuilder('s')
            ->andWhere('s.is_online = 1')
            ->andWhere('s.is_deleted = 0');

        if(isset($parameters['service']) && $parameters['service'] == 'ttt') {
            $qb->leftJoin('s.restaurant', 'r')
            ->where('r.isTtt = 1');
        }

        if($limit && is_int($limit)) {
            $qb->setMaxResults($limit);
        }

        $qb->orderBy('s.created', 'desc');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }
}
