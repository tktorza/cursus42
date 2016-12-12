<?php

namespace Clab\LocationBundle\Service;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

use Clab\LocationBundle\Entity\Place;
use Clab\SocialBundle\Entity\SocialProfile;

class PlaceListener
{
    protected $container;

    public function __construct(ContainerInterface $container, $cacheManager, $apiDomain)
    {
        $this->container = $container;
        $this->cacheManager = $cacheManager;
        $this->apiDomain = $apiDomain;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if($entity instanceof Event && !$entity->getSocialProfile()) {
            $socialProfile = new SocialProfile();
            $socialProfile->setService('tttruck');
            $entity->setSocialProfile($socialProfile);
            $entityManager->persist($entity);
            $entityManager->flush();
        }

        if($entity instanceof Place && $entity->getGallery()) {

            if($entity->getProfilePicture()) {
                $profilePicture = $entity->getProfilePicture();
            } else {
                $profilePicture = $entity->getGallery()->getCover();
            }

            if($profilePicture && method_exists($entity, 'setProfilePicturePathFull')) {
                $entity->setProfilePicturePathFull('http://' . $this->apiDomain . '/' . $profilePicture->getWebPath());
            }
            if($profilePicture && method_exists($entity, 'setProfilePicturePath')) {
                $entity->setProfilePicturePath('http://' . $this->apiDomain . $this->cacheManager->getBrowserPath($profilePicture->getWebPath(), 'square_400'));
            }
            if($profilePicture && method_exists($entity, 'setProfilePicturePathSmall')) {
                $entity->setProfilePicturePathSmall('http://' . $this->apiDomain . $this->cacheManager->getBrowserPath($profilePicture->getWebPath(), 'square_200'));
            }

            if($entity->getCoverPicture()) {
                $coverPicture = $entity->getCoverPicture();
            } else {
                $coverPicture = $entity->getGallery()->getCover();
            }

            if($coverPicture && method_exists($entity, 'setCoverPicturePathFull')) {
                $entity->setCoverPicturePathFull('http://' . $this->apiDomain . '/' . $coverPicture->getWebPath());
            }
            if($coverPicture && method_exists($entity, 'setCoverPicturePath')) {
                $entity->setCoverPicturePath('http://' . $this->apiDomain . $this->cacheManager->getBrowserPath($coverPicture->getWebPath(), 'square_400'));
            }
            if($coverPicture && method_exists($entity, 'setCoverPicturePathSmall')) {
                $entity->setCoverPicturePathSmall('http://' . $this->apiDomain . $this->cacheManager->getBrowserPath($coverPicture->getWebPath(), 'square_200'));
            }
        }
    }
}
