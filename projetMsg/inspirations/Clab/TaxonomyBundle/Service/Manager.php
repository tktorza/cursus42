<?php

namespace Clab\TaxonomyBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Clab\TaxonomyBundle\Entity\Vocabulary;

class Manager
{
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getTermsByVocabulary($vocabulary = null)
    {
        if($vocabulary) {
            $vocabulary = $this->em->getRepository('ClabTaxonomyBundle:Vocabulary')
                ->findOneBy(array('slug' => $vocabulary, 'is_online' => true, 'is_deleted' => false));
        }

        if(!$vocabulary) {
            $vocabulary = new Vocabulary();
        }

        $terms = $this->em->getRepository('ClabTaxonomyBundle:Term')->getAllByVocabulary($vocabulary);
        return $terms;
    }

    public function getAllAvailable()
    {
        $terms = $this->em->getRepository('ClabTaxonomyBundle:Term')
            ->findBy(array('is_online' => true, 'is_deleted' => false), array('name' => 'asc'));

        return $terms;
    }

    public function getAllWithVocabulary($asArray = false, $categories = array())
    {
        $terms = $this->em->getRepository('ClabTaxonomyBundle:Term')->getAllWithVocabulary($asArray, $categories);

        return $terms;
    }
}