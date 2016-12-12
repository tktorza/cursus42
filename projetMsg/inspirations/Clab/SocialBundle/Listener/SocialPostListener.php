<?php

namespace Clab\SocialBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Clab\SocialBundle\Entity\SocialPost;

class SocialPostListener
{
    protected $vichHelper;
    protected $apiDomain;

    public function __construct($vichHelper, $apiDomain)
    {
        $this->vichHelper = $vichHelper;
        $this->apiDomain = $apiDomain;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof SocialPost) {
            if($entity->getImage()) {
                $path = $this->vichHelper->asset($entity, 'image');
                $entity->setCover('http://' . $this->apiDomain . $path);
            } elseif ($entity->getProxy() && method_exists($entity->getProxy(), 'getCover')) {
                $entity->setCover($entity->getProxy()->getCover());
            } elseif ($entity->getRestaurant()) {
                $entity->setCover($entity->getRestaurant()->getCover());
            }
        }
    }
}