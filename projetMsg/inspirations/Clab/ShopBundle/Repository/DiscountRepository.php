<?php

namespace Clab\ShopBundle\Repository;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

class DiscountRepository extends EntityRepository
{
    public function findAllAvailable($restaurant, array $parameters = array())
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d')
            ->where('d.isOnline = 1')
            ->andWhere('d.isDeleted = 0')
            ->andWhere('d.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('d.percent', 'desc');

        if(isset($parameters['multisite']) && $parameters['multisite']) {
            $qb->andWhere('d.isMultisite = 1');
        }

        return $qb->getQuery()->getResult();
    }

    public function findBestAvailable($restaurant, array $parameters = array())
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d')
            ->where('d.isOnline = 1')
            ->andWhere('d.isDeleted = 0')
            ->andWhere('d.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->orderBy('d.percent', 'desc');

        if(isset($parameters['multisite']) && $parameters['multisite']) {
            $qb->andWhere('d.isMultisite = 1');
        }

        return $qb->getQuery()->setMaxResults(1)->getSingleResult();
    }

    public function getByLocation($latitude, $longitude, $distanceMin = 0, $distanceMax = 5)
    {
        $qb = $this->createQueryBuilder('discount')
            ->select('discount', 'GEO_DISTANCE(:latitude, :longitude, discount.latitude, discount.longitude) AS distance')
            ->having('distance >= :distanceMin AND distance < :distanceMax')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('distanceMin', $distanceMin)
            ->setParameter('distanceMax', $distanceMax)
            ->where('discount.isOnline = true')
            ->andWhere('discount.isDeleted = false')
            ->orderBy('distance');

        return $qb->getQuery()->getResult();
    }

    public function getActualDiscountAsArray(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('discount')
            ->select('discount.id, discount.name, discount.slug, discount.percent, discount.type')
            ->leftJoin('discount.restaurant','restaurant')
            ->where('restaurant = :restaurant')
            ->setParameter('restaurant',$restaurant)
            ->orderBy('discount.type', 'desc')
            ->addOrderBy('discount.percent', 'desc')
        ;

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
    }
}
