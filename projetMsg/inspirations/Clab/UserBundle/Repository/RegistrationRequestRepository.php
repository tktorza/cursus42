<?php

namespace Clab\UserBundle\Repository;

use Doctrine\ORM\EntityRepository;

class RegistrationRequestRepository extends EntityRepository
{
    public function getAllForProxy($proxy)
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.is_online = 1')
            ->andWhere('r.is_deleted = 0');

        if($proxy instanceof \Clab\RestaurantBundle\Entity\Restaurant) {
            $qb->innerJoin('r.restaurants', 'rest', 'WITH', 'rest.id = :restaurantId')
                ->setParameter('restaurantId', $proxy->getId());
        } elseif ($proxy instanceof \Clab\BoardBundle\Entity\Client) {
            $qb->innerJoin('r.clients', 'c', 'WITH', 'c.id = :clientId')
                ->setParameter('clientId', $proxy->getId());
        } else {
            return null;
        }

        return $qb->getQuery()->getResult();
    }

    public function getForProxy($proxy, $id)
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.is_online = 1')
            ->andWhere('r.is_deleted = 0')
            ->andWhere('r.id = :id')
            ->setParameter('id', $id);

        if($proxy instanceof \Clab\RestaurantBundle\Entity\Restaurant) {
            $qb->innerJoin('r.restaurants', 'rest', 'WITH', 'rest.id = :restaurantId')
                ->setParameter('restaurantId', $proxy->getId());
        } elseif ($proxy instanceof \Clab\BoardBundle\Entity\Client) {
            $qb->innerJoin('r.clients', 'c', 'WITH', 'c.id = :clientId')
                ->setParameter('clientId', $proxy->getId());
        } else {
            return null;
        }

        return $qb->getQuery()->getSingleResult();
    }
}
