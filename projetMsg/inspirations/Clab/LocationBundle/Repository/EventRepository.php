<?php

namespace Clab\LocationBundle\Repository;

use Doctrine\ORM\EntityRepository;

class EventRepository extends EntityRepository
{
    public function getUpcomingForPlace($place)
    {
        $qb = $this->createQueryBuilder('event')
            ->select('event')
            ->innerJoin('event.place', 'place', 'WITH', 'place.id = :placeId')
            ->setParameter('placeId', $place->getId())
            ->where('event.is_deleted = false')
            ->andWhere('event.is_online = true');

        return $qb->getQuery()->getResult();
    }
}
