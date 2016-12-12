<?php

namespace Clab\UserBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Clab\UserBundle\Entity\User;

class ProfileListener
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    public function preUpdate(LifecycleEventArgs $args)
    {

    }
    public function postLoad(LifecycleEventArgs $args)
    {
        $profile = $args->getEntity();

        if($profile instanceof User) {
            if($profile->getImageFile()) {
                $profile->setCover($profile->getImageFile()->getPath());
            } else {
                $profile->setCover('/images/blankuser.png');
            }

        }
    }
}
