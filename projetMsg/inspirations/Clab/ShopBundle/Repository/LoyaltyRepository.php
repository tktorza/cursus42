<?php

namespace Clab\ShopBundle\Repository;

use Clab\ShopBundle\Entity\Cart;
use Clab\ShopBundle\Entity\OrderDetail;
use Clab\UserBundle\Entity\User;


/**
 * LoyaltyRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LoyaltyRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAvailableForCart(Cart $cart, User $user)
    {
        $now = new \DateTime();

        $qb = $this->createQueryBuilder('l')
            ->where('l.isUsed = false')
            ->andWhere('l.validUntil >= :now')
            ->andWhere('l.user = :user')
            ->andWhere('(l.orderType is null or l.orderType = :orderType)')
            ->andWhere('(l.minimumOrder <= :cartPrice)')
            ->setParameter('now', $now)
            ->setParameter('orderType', $cart->getOrderType())
            ->setParameter('user', $user)
            ->setParameter('cartPrice', $cart->getDiscountPrice())
        ;

        $qb
            ->addOrderBy('l.orderType','desc')
            ->addOrderBy('l.validUntil', 'asc')
        ;

        return $qb->getQuery()->getResult();
    }
}
