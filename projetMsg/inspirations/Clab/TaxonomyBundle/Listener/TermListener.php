<?php

namespace Clab\TaxonomyBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Clab\TaxonomyBundle\Entity\Term;

class TermListener
{
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if($entity instanceof Term) {
            //$entity->setName(ucfirst(strtolower($entity->getName())));
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if($entity instanceof Term) {
            //$entity->setName(ucfirst(strtolower($entity->getName())));
        }
    }
}