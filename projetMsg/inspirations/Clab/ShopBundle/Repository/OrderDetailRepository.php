<?php

namespace Clab\ShopBundle\Repository;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\EntityRepository;
use Clab\ShopBundle\Entity\OrderDetail;
use Symfony\Component\Validator\Constraints\DateTime;
class OrderDetailRepository extends EntityRepository
{
    public function getForRestaurant($restaurant, array $parameters = array())
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant);

        if (isset($parameters['day']) && $parameters['day'] instanceof \DateTime) {
            $qb->andWhere('DAY( o.time ) = DAY( :day )');
            $qb->setParameter('day', $parameters['day']);
        } else {
            if (isset($parameters['start']) && $parameters['start'] instanceof \DateTime) {
                $qb->andWhere('o.time >= :start');
                $qb->setParameter('start', $parameters['start']);
            }

            if (isset($parameters['end']) && $parameters['start'] instanceof \DateTime) {
                $qb->andWhere('o.time <= :end');
                $qb->setParameter('end', $parameters['end']);
            }
        }

        if (isset($parameters['states']) && is_array($parameters['states'])) {
            $qb->andWhere('o.state IN (:states)');
            $qb->setParameter('states', $parameters['states']);
        }

        $qb->orderBy('o.time', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function findAllByRestaurant($restaurant, $states = null, $day = null, $start = null, $end = null, $limit = null, $order = 'asc', $search = null, $preparationStates = null, $count = false)
    {
        $em = $this->getEntityManager();
        $orderRepository = $em->getRepository('ClabShopBundle:OrderDetail');

        if ($states == null) {
            $states = array(0, 100, 200, 300, 400);
        }

        if ($preparationStates == null) {
            $states = array(0, 1, 2, 3, 4, 5);
        }

        $qb = $orderRepository->createQueryBuilder('o');

        if ($count) {
            $qb->select('COUNT(DISTINCT(o.id)) as nbOrders');
        }

        $qb
            ->leftJoin('o.cart', 'c')
            ->where('c.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('o.state IN (:states)')
            ->setParameter('states', $states)
            ->andWhere('o.preparationState IN (:preparationStates)')
            ->setParameter('preparationStates', $preparationStates);

        if (!is_null($day)) {
            if ($day = 'today') {
                $start = date_create('today');
                $end = date_create('tomorrow');
            } elseif ($day = 'yesterday') {
                $start = date_create('yesterday');
                $end = date_create('today');
            } else {
                $start = date_create('today');
                $end = date_create('tomorrow');
            }

            $qb->andWhere('o.time >= :start')
                ->andWhere('o.time <= :end')
                ->setParameter('start', $start)
                ->setParameter('end', $end);
        }

        if ($limit && is_int($limit)) {
            $qb->setMaxResults($limit);
        }

        //$qb->leftJoin('o.schedule', 's')
        //	->orderBy('s.start', $order);

        if ($start) {
            $qb->andWhere('o.time > :start')
                ->setParameter('start', $start);
        }

        if ($end) {
            $qb->andWhere('o.time < :end')
                ->setParameter('end', $end);
        }

        if ($search) {
            $search = '%'.$search.'%';

            $qb->leftJoin('o.profile', 'p')
                ->andWhere('(p.first_name LIKE :string OR p.last_name LIKE :string OR o.reference LIKE :string)')
                ->setParameter('string', $search)
                ->setParameter('string', $search)
                ->setParameter('string', $search);
        }

        $qb->orderBy('o.time', 'asc');

        $query = $qb->getQuery();
        $results = $query->getResult();

        return $results;
    }

    public function findAllByRestaurantBetweenDate($restaurant, $start, $end)
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('o.time >= :start')
            ->andWhere('o.time <= :end')
            ->andWhere('o.state = 4')
            ->setParameter('start', $start->format('Y-m-d'))
            ->setParameter('end', $end->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    public function findAllClosedBetweenDate($start, $end)
    {
        $qb = $this->createQueryBuilder('o')
            ->andWhere('o.time >= :start')
            ->andWhere('o.time <= :end')
            ->andWhere('o.state = 4')
            ->setParameter('start', $start->format('Y-m-d'))
            ->setParameter('end', $end->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    public function findAllBetweenDate($start, $end, $restaurant = null, array $parameters = array())
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o', 'profile', 'delivery')
            ->andWhere('o.time >= :start')
            ->andWhere('o.time <= :end')
            ->setParameter('start', $start->modify('-1 day'))
            ->setParameter('end', $end->modify('+1 day'))
            ->leftJoin('o.profile', 'profile')
            ->leftJoin('o.delivery', 'delivery')
            ->orderBy('o.id', 'desc');

        if ($restaurant) {
            $qb->andWhere('o.restaurant = :restaurant')
                ->setParameter('restaurant', $restaurant);
        }

        if (isset($parameters['closed']) && $parameters['closed']) {
            $qb->andWhere('o.state = 4');
        }

        return $qb->getQuery()->getResult();
    }

    public function getUserCurrentOrders($user)
    {
        $em = $this->getEntityManager();
        $orderRepository = $em->getRepository('ClabShopBundle:OrderDetail');

        $states = array(1, 2, 3);

        $qb = $orderRepository->createQueryBuilder('o')
            ->leftJoin('o.profile', 'p')
            ->where('p.id = :profile')
            ->andWhere('o.state IN (:states)')
            ->setParameter('states', $states)
            ->setParameter('profile', $user->getId());

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function getCountForProfile($profile, $options = null)
    {
        $em = $this->getEntityManager();

        $qb = $this->createQueryBuilder('o')
            ->select('count(o.id)')
            ->leftJoin('o.profile', 'p')
            ->leftJoin('o.cart', 'c')
            ->where('p.id = :profile')
            ->andWhere('o.state = :stateClosed ')
            ->setParameter('profile', $profile)
            ->setParameter('stateClosed', OrderDetail::ORDER_STATE_TERMINATED);

        if (isset($options['restaurant']) && $options['restaurant'] instanceof \Clab\RestaurantBundle\Entity\Restaurant) {
            $qb->andWhere('c.restaurant = :restaurant')
                ->setParameter('restaurant', $options['restaurant']);
        }

        $count = $qb->getQuery()->getSingleScalarResult();

        return $count;
    }

    public function findByUserForRestaurantQuery($user, $restaurant)
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.restaurant = :restaurant')
            ->setParameter('restaurant', $restaurant)
            ->andWhere('o.profile = :profile')
            ->setParameter('profile', $user);

        $qb->orderBy('o.id', 'desc');

        $query = $qb->getQuery();

        return $query;
    }

    public function findByUserForRestaurant($user, $restaurant)
    {
        $results = $this->findByUserForRestaurantQuery($user, $restaurant)->getResult();

        return $results;
    }

    public function findForSlots($restaurant, $day)
    {
        $qb = $this->createQueryBuilder('o')
            ->select('o.time', 'o.price')
            ->where('o.is_test = false')
            ->andWhere('o.state >= :startState')
            ->andWhere('o.state <= :endState')
            ->andWhere('o.time = :day')
            ->andWhere('o.restaurant = :restaurant')
            ->setParameter('startState', OrderDetail::ORDER_STATE_WAITING_PAYMENT)
            ->setParameter('endState', OrderDetail::ORDER_STATE_CANCELLED)
            ->setParameter('restaurant', $restaurant)
            ->setParameter('day', $day);

        return $qb->getQuery()->getResult();
    }

    public function findTestNotCancelled()
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.is_test = true')
            ->andWhere('o.state != :state')
            ->setParameter('state', OrderDetail::ORDER_STATE_CANCELLED);

        return $qb->getQuery()->getResult();
    }

    public function getAccountingByVAT(Restaurant $restaurant, \DateTime $date, $isCaisse = false)
    {
        $qb = $this->createQueryBuilder('o');
        if (!$isCaisse) {
            $qb
                ->select('sum(o.tva55) as tva55, sum(o.tva10) as tva10,sum(o.tva20) as tva20, o.price, ot.id')
                ->join('o.orderType','ot')
                ->groupBy('ot.id')
            ;
        } else {
            $qb->select('sum(o.tva55) as tva55, sum(o.tva10) as tva10,sum(o.tva20) as tva20, o.price, 4 as id');
        }

        $qb
            ->where('o.restaurant =:restaurant')
            ->andWhere('DATE(o.time) = :date')
            ->andWhere('o.state = 400')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('date', $date->format('Y-m-d'))
        ;

        return $qb->getQuery()->getResult();
    }

    public function findInProgress($day)
    {
        /* if orders are late change $day by previous day maybe or one hour ago*/
        $qb = $this->createQueryBuilder('o')
            ->where('o.time >= :day')
            ->setParameter('day', $day);

        return $qb->getQuery()->getResult();
    }

    public function findInProgressRestaurant($day)
    {
       $qb = $this->createQueryBuilder('o')
            ->where('o.time >= :day')
            //->andWhere('o.id < 6')
            ->setParameter('day', $day)
            ->orderBy('o.restaurant');

        return $qb->getQuery()->getResult();
    }

    public function findInProgressDate($day)
    {
        $qb = $this->createQueryBuilder('o')
            ->where('o.time >= :day')
            ->andWhere('o.id < 6')
            ->setParameter('day', $day)
            ->orderBy('o.time');

        return $qb->getQuery()->getResult();
    }

    public function findInProgressType($day)
    {
        /*$qb = $this->createQueryBuilder('o')
            ->leftJoin('o.orderType', 'orderType')
            ->where('o.time >= :day')
            ->andWhere('o.id < 6')
            ->setParameter('day', $day)
            ->orderBy('orderType.id');

        return $qb->getQuery()->getResult();*/
    }

    public function getAccountingByPaymentType(Restaurant $restaurant, \DateTime $date)
    {
        $qb = $this->createQueryBuilder('o');

        $qb
            ->where('o.restaurant =:restaurant')
            ->andWhere('DATE(o.time) = :date')
            ->andWhere('o.state = 400')
            ->setParameter('restaurant', $restaurant)
            ->setParameter('date', $date->format('Y-m-d'))
        ;

        return $qb->getQuery()->getResult();
    }
}
