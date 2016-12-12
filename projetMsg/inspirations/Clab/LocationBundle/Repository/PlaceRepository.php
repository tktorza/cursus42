<?php

namespace Clab\LocationBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PlaceRepository extends EntityRepository
{
    public function findNearBy($address, $distance = 0.1)
    {
        $qb = $this->createQueryBuilder('place')
            ->select('place', 'GEO_DISTANCE(:latitude, :longitude, address.latitude, address.longitude) AS distance')
            ->having('distance <= :distance')
            ->leftJoin('place.address', 'address')
            ->setParameter('latitude', $address->getLatitude())
            ->setParameter('longitude', $address->getLongitude())
            ->setParameter('distance', $distance)
            ->where('place.is_deleted = false')
            ->andWhere('place.is_online = true')
            ->orderBy('distance');

        return $qb->getQuery()->getResult();
    }
}
