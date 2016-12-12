<?php

namespace Clab\MultisiteBundle\Repository;

use Doctrine\ORM\EntityRepository;

class MultisiteRepository extends EntityRepository
{
    public function getByDomain($domain)
    {
        $qb = $this->createQueryBuilder('m')
            ->where('m.domain = :domain')
            ->andWhere('m.type = 1')
            ->setParameter('domain', $domain);

        $qb->setMaxResults(1);

        $query = $qb->getQuery();
        return $query->getSingleResult();
    }
}
