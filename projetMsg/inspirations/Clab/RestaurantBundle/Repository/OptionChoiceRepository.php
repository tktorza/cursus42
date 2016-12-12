<?php

namespace Clab\RestaurantBundle\Repository;

use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\EntityRepository;

class OptionChoiceRepository extends EntityRepository
{
    public function findByOptionChoice(OptionChoice $choice)
    {
        $qb = $this->createQueryBuilder('o')
        ->select('o,g')
        ->leftJoin('o.gallery', 'g')
        ->where('o.id = :id')
        ->andWhere('g.id = :idGallery')
        ->setParameter('id', $choice->getId())
        ->setParameter('idGallery', $choice->getGallery()->getId());
        $query = $qb->getQuery();
        $results = $query->getSingleResult();

        return $results;
    }


    public function findAllForRestaurant(Restaurant $restaurant)
    {
        $qb = $this->createQueryBuilder('o')
            ->leftJoin('o.parent','p')
            ->where('o.restaurant = :restaurant')
            ->andWhere('o.isDeleted = false')
            ->andWhere('p.restaurant is null')
            ->andWhere('p.parent is null')
            ->orderBy('o.position','asc')
            ->setParameter('restaurant',$restaurant);
        $query = $qb->getQuery();
        $results = $query->getResult();
        return $results;
    }

}
