<?php

namespace Clab\ApiBundle\Repository;

use Doctrine\ORM\EntityRepository;

class LicenceRepository extends EntityRepository
{
    /**
     * Check is a licence is available for a restaurant
     *
     * @param $licence
     * @param $restaurant
     * @return array
     */
    public function licenceIsAvailable($licence, $restaurant) {
        $queryBuilder = $this->createQueryBuilder('l');
        $expressionBuilder = $queryBuilder->expr();

        $licenceEqual = $expressionBuilder->eq('l.licence', ':licence');
        $restaurantEqual = $expressionBuilder->eq('l.restaurant', ':restaurant');
        $isNotAttributed = $expressionBuilder->isNull('l.serial');

        $queryBuilder
            ->where($licenceEqual)
            ->andWhere($restaurantEqual)
            ->andWhere($isNotAttributed)
            ->setMaxResults(1)
        ;

        $queryBuilder
            ->setParameter('licence', $licence)
            ->setParameter('restaurant', $restaurant)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Find one attributed licence
     *
     * @param $licence
     * @param $restaurant
     * @param $serial
     * @return array
     */
    public function getOne($licence, $restaurant, $serial) {
        $queryBuilder = $this->createQueryBuilder('l');
        $expressionBuilder = $queryBuilder->expr();

        $licenceEqual = $expressionBuilder->eq('l.licence', ':licence');
        $restaurantEqual = $expressionBuilder->eq('l.restaurant', ':restaurant');
        $serialEqual = $expressionBuilder->eq('l.serial', ':serial');

        $queryBuilder
            ->where($licenceEqual)
            ->andWhere($restaurantEqual)
            ->andWhere($serialEqual)
        ;

        $queryBuilder
            ->setParameter('licence', $licence)
            ->setParameter('restaurant', $restaurant)
            ->setParameter('serial', $serial)
            ->setMaxResults(1)
        ;

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}