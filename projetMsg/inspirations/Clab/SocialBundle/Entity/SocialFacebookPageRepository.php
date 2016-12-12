<?php

namespace Clab\SocialBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SocialFacebookPageRepository extends EntityRepository
{
    public function getById($id)
    {
        $qb = $this->createQueryBuilder('page')
            ->select('page')
            ->where('page.facebook_id = :id')
            ->setParameter('id', $id);

        return $qb->getQuery()->getResult();
    }
}
