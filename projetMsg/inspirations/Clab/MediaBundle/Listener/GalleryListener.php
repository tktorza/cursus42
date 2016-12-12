<?php

namespace Clab\MediaBundle\Listener;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Clab\MediaBundle\Entity\Gallery;
use Clab\MediaBundle\Entity\GalleryOwnerInterface;

class GalleryListener
{
    protected $cacheManager;
    protected $apiDomain;

    public function __construct($cacheManager, $apiDomain)
    {
        $this->cacheManager = $cacheManager;
        $this->apiDomain = $apiDomain;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        $default = $entityManager->getRepository('ClabMediaBundle:Image')->findOneBy(array(
               'name' => 'blank.png'
        ));

        if ($entity instanceof Restaurant) {
            $galleryMenu = new Gallery();
            $galleryMenu->setDirName(sha1(time().rand(10, 100)).'/');
            $galleryMenu->setDefault($default);
            $entity->setGalleryMenu($galleryMenu);
            $entityManager->persist($galleryMenu);

            $publicGallery = new Gallery();
            $publicGallery->setDirName(sha1(time().rand(10, 100)).'/');
            $publicGallery->setDefault($default);
            $entity->setGalleryPublic($publicGallery);
            $entityManager->persist($publicGallery);
        }

        if ($entity instanceof GalleryOwnerInterface) {
            if (null == $entity->getGallery()) {
                $gallery = new Gallery();
                $gallery->setDirName(sha1(time().rand(10, 100)).'/');
                $gallery->setDefault($default);

                $entity->setGallery($gallery);

                if (method_exists($entity, 'getParent') && method_exists($entity->getParent(), 'getGallery') && $entity->getParent()->getGallery()) {
                    $gallery->setParent($entity->getParent()->getGallery());

                    foreach ($entity->getParent()->getGallery()->getImages() as $image) {
                        $newImage = clone($image);
                        $newImage->setGallery($gallery);
                        $newImage->setParent($image);
                        $newImage->setCreated(date_create('now'));
                        $newImage->setUpdated(date_create('now'));

                        $entityManager->persist($newImage);
                    }
                }

                $entityManager->persist($gallery);
            }

            if (method_exists($entity, 'getPublicGallery') && null == $entity->getPublicGallery()) {
                $gallery = new Gallery();
                $gallery->setDirName(sha1(time().rand(10, 100)).'/');
                $gallery->setDefault($default);

                $entity->setPublicGallery($gallery);

                $entityManager->persist($gallery);
            }

            if (method_exists($entity, 'getGalleryBig') && null == $entity->getGalleryBig()) {
                $gallery = new Gallery();
                $gallery->setDirName(sha1(time().rand(10, 100)).'/');
                $gallery->setDefault($default);

                $entity->setGalleryBig($gallery);

                $entityManager->persist($gallery);
            }

            $entityManager->flush();
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($entity instanceof GalleryOwnerInterface) {
            $data = array(
                'cover' => null,
                'coverSmall' => null,
                'coverFull' => null,
            );

            if ($gallery = $entity->getGallery()) {
                $data['coverFull'] = 'http://'.$this->apiDomain.'/'.$gallery->getCover()->getWebPath();
                $data['coverSmall'] = $this->cacheManager->getBrowserPath($gallery->getCover()->getWebPath(), 'square_200');
                $data['cover'] = $this->cacheManager->getBrowserPath($gallery->getCover()->getWebPath(), 'square_400');

                $data['coverDefault'] = false;
                if (count($gallery->getImages()) == 0) {
                    $data['coverDefault'] = true;
                }
            }

            if (isset($data['coverFull']) && method_exists($entity, 'setCoverFull')) {
                $entity->setCoverFull($data['coverFull']);
            }
            if (isset($data['cover']) && method_exists($entity, 'setCover')) {
                $entity->setCover($data['cover']);
            }
            if (isset($data['coverSmall']) && method_exists($entity, 'setCoverSmall')) {
                $entity->setCoverSmall($data['coverSmall']);
            }
            if (isset($data['coverDefault']) && method_exists($entity, 'setCoverDefault')) {
                $entity->setCoverDefault($data['coverDefault']);
            }
        }

        if (method_exists($entity, 'getGalleryBig')) {
            $data = array(
                'cover' => null,
            );

            if ($gallery = $entity->getGalleryBig()) {
                $data['cover'] = 'http://'.$this->apiDomain.'/'.$gallery->getCover()->getWebPath();

                $data['coverDefault'] = false;
                if (count($gallery->getImages()) == 0) {
                    $data['coverDefault'] = true;
                }
            }

            if (isset($data['cover']) && method_exists($entity, 'setCoverBig')) {
                $entity->setCoverBig($data['cover']);
            }

            if (isset($data['coverDefault']) && method_exists($entity, 'setCoverBigDefault')) {
                $entity->setCoverBigDefault($data['coverDefault']);
            }
        }
    }
}
