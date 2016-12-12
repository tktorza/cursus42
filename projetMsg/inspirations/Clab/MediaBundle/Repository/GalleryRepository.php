<?php

namespace Clab\MediaBundle\Repository;

use Doctrine\ORM\EntityRepository;

class GalleryRepository extends EntityRepository
{
    public function getChildrens()
    {
        $qb = $this->createQueryBuilder('g')
            ->where('g.parent IS NOT NULL');

        return $qb->getQuery()->getResult();
    }
}
