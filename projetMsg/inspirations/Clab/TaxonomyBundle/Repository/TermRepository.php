<?php

namespace Clab\TaxonomyBundle\Repository;

use Clab\TaxonomyBundle\Entity\Vocabulary;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;

class TermRepository extends EntityRepository
{
    public function getAllByVocabulary(Vocabulary $vocabulary)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.is_online = 1')
            ->andWhere('t.is_deleted = 0');

        $qb->innerJoin('t.vocabularies', 'v', 'WITH', 'v.id = :vocabulary')
            ->setParameter('vocabulary', $vocabulary->getId());

        $qb->orderBy('t.name', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function getAllByVocabularyId($id)
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.is_online = 1')
            ->andWhere('t.is_deleted = 0');

        $qb->innerJoin('t.vocabularies', 'v', 'WITH', 'v.id = :vocabulary')
            ->setParameter('vocabulary', $id);

        $qb->orderBy('t.name', 'asc');

        return $qb->getQuery()->getResult();
    }

    public function getAllWithVocabulary($asArray = false, $categories)
    {
        $qb = $this->createQueryBuilder('t')
            ->select('t.id, t.name, t.slug, t.iconOnName, t.iconOffName, v.name as cat_name')
            ->leftJoin('t.vocabularies', 'v')
            ->where('t.is_online = 1')
            ->andWhere('t.is_deleted = 0');
        if(count($categories) > 0) {
            $qb
                ->andWhere('REGEXP(v.slug, :categories) = 1')
                ->setParameter('categories',implode('|',$categories))
            ;
        }


        return $qb->getQuery()->getResult( ($asArray ? AbstractQuery::HYDRATE_ARRAY : AbstractQuery::HYDRATE_OBJECT) );
    }
}
