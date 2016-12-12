<?php

namespace Clab\UserBundle\Repository;

use Clab\UserBundle\Entity\User;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

use Clab\ShopBundle\Entity\OrderDetail;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\BoardBundle\Entity\Client;

class UserRepository extends EntityRepository
{

    public function findFavorite(User $user, array $options = null)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('u.favorites')
            ->where('u = :user')
            ->setParameter('user', $user)
        ;

        return  $results = $qb->getQuery()->getResult();
    }
    public function findFavoriteProducts(User $user)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('u.favoriteProducts')
            ->where('u = :user')
            ->setParameter('user', $user)
        ;

        return  $results = $qb->getQuery()->getResult();
    }
    public function findByRole($role, $proxy = null, $limit = 200, $offset = 0)
    {
        $qb = $this->createQueryBuilder('u');
        $qb->select('u')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%' . $role . '%');

        if($proxy) {
            if($proxy instanceof Restaurant) {
                $qb->leftJoin('u.restaurants', 'r')
                    ->andWhere('r.id = :restaurant')
                    ->setParameter('restaurant', $restaurant->getId());
            } elseif($proxy instanceof Client) {
                $qb->leftJoin('u.clients', 'c')
                    ->andWhere('c.id = :client')
                    ->setParameter('client', $client->getId());
            }
        }

        $qb
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->orderBy('u.email');
        ;

        return $qb->getQuery()->getResult();
    }

    public function findUsersHasDiscount()
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u')
            ->where('u.discounts != :empty')
            ->setParameter('empty', "N;")
            ->getQuery()
            ->getResult();

        return $qb;
    }

    public function findAllBetweenDate($start, $end)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u', 'g')
            ->andWhere('u.created >= :start')
            ->andWhere('u.created <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('u.id', 'desc');

        return $qb->getQuery()->getResult();
    }

    public function getCustomersForRestaurant($restaurant)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u', 'o', 'm', 'f', 'd', 'b', 'ha', 'ja')
            ->innerJoin('u.orders', 'o')
            ->leftJoin('o.delivery', 'd')
            ->leftJoin('u.homeAddress', 'ha')
            ->leftJoin('u.jobAddress', 'ja')
            ->where('o.state = :state')
            ->andWhere('o.restaurant = :restaurant')
            ->setParameter('state', \Clab\ShopBundle\Entity\OrderDetail::ORDER_STATE_TERMINATED)
            ->setParameter('restaurant', $restaurant)
            ->leftJoin('o.multisite', 'm')
            ->leftJoin('o.facebookPage', 'f');

        return $qb->getQuery()->getResult();
    }

    public function getCustomerForRestaurant($restaurant, $id)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('u', 'o', 'm', 'f', 'd')
            ->innerJoin('u.orders', 'o')
            ->where('o.state = :state')
            ->leftJoin('o.delivery', 'd')
            ->andWhere('o.restaurant = :restaurant')
            ->andWhere('u.id = :id')
            ->setParameter('state', \Clab\ShopBundle\Entity\OrderDetail::ORDER_STATE_TERMINATED)
            ->setParameter('restaurant', $restaurant)
            ->setParameter('id', $id)
            ->leftJoin('o.multisite', 'm')
            ->leftJoin('o.facebookPage', 'f');

        return $qb->getQuery()->getSingleResult();
    }

    public function findForTTTNotificationsBookmarks($restaurant, $type, $address, $distance)
    {
        $qb = $this->createQueryBuilder('user')
            ->where('user.tttEventNotificationsBookmarks = 1');

        if($type == 'job') {
            $qb->select('user', 'GEO_DISTANCE(:latitude, :longitude, jobAddress.latitude, jobAddress.longitude) AS distance')
                ->innerJoin('user.jobAddress', 'jobAddress');
        } else {
            $qb->select('user', 'GEO_DISTANCE(:latitude, :longitude, homeAddress.latitude, homeAddress.longitude) AS distance')
                ->innerJoin('user.homeAddress', 'homeAddress');
        }

        $qb->having('distance < :distance')
            ->setParameter('distance', $distance)
            ->setParameter('latitude', $address->getLatitude())
            ->setParameter('longitude', $address->getLongitude())
            ->andWhere('user.favorites LIKE :restaurantSlug')
            ->setParameter('restaurantSlug', $restaurant->getSlug());

        return $qb->getQuery()->getResult();
    }

    public function findForTTTNotifications($type, $address, $distance)
    {
        $qb = $this->createQueryBuilder('user')
            ->where('user.tttEventNotifications = 1');

        if($type == 'job') {
            $qb->select('user', 'GEO_DISTANCE(:latitude, :longitude, jobAddress.latitude, jobAddress.longitude) AS distance')
                ->innerJoin('user.jobAddress', 'jobAddress');
        } else {
            $qb->select('user', 'GEO_DISTANCE(:latitude, :longitude, homeAddress.latitude, homeAddress.longitude) AS distance')
                ->innerJoin('user.homeAddress', 'homeAddress');
        }

        $qb->having('distance < :distance')
            ->setParameter('distance', $distance)
            ->setParameter('latitude', $address->getLatitude())
            ->setParameter('longitude', $address->getLongitude());

        return $qb->getQuery()->getResult();
    }


    public function findFacebookOrEmail($facebookId, $email)
    {
        $qb = $this->createQueryBuilder('u');
        $exp = $qb->expr();
        $matchFacebook = $exp->eq('u.facebookId', ':facebookId');
        $matchEmail = $exp->eq('u.email', ':email');
        $matchUserExist = $exp->orX();
        $matchUserExist->addMultiple(array($matchEmail, $matchFacebook));

        $qb
            ->where($matchUserExist)
            ->setParameter('facebookId', $facebookId)
            ->setParameter('email', $email)
        ;

        return  $results = $qb->getQuery()->getOneOrNullResult();
    }

    public function findUserByPhone($phones)
    {
        $qb = $this->createQueryBuilder('u')
            ->where('u.phone IN (:phones)')
            ->setParameter('phones', $phones);

        return $results = $qb->getQuery()->getOneOrNullResult();
    }

    public function countUsers($role)
    {
        $qb = $this->createQueryBuilder('u')
            ->select('COUNT(u)')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%' . $role . '%');

        return $results = $qb->getQuery()->getOneOrNullResult();
    }
}
