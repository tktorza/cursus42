<?php

namespace Clab\MediaBundle\Service;

use Clab\MediaBundle\Entity\Gallery;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraints\Image as ImageConstraint;
use Symfony\Component\Validator\Constraints\NotNull;
use Clab\MediaBundle\Entity\GalleryOwnerInterface;
use Clab\MediaBundle\Entity\Image;
use Symfony\Component\VarDumper\VarDumper;

class ImageManager
{
    protected $em;
    protected $container;
    protected $request;
    private $defaultImage;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
        $this->request = $this->container->get('request');

        $this->defaultImage = $this->em
            ->getRepository(Image::class)
            ->find(88888888)
        ;
    }

    public function getAbsoluteUrl(GalleryOwnerInterface $entity)
    {
        $cacheManager = $this->container->get('liip_imagine.cache.manager');
        $cover = $entity->getGallery()->getCover();
        $host = $this->container->get('request')->getHost();
        if ($host == 'pro.click-eat.fr') {
            $host = 'click-eat.fr';
        }

        return 'http://'.$host.$cacheManager->getBrowserPath($cover->getWebPath(), 'square_400');
    }

    public function getUploadForm()
    {
        $builder = $this->container->get('form.factory')->createNamedBuilder('', 'form', null, array('csrf_protection' => false, 'allow_extra_fields' => true));

        $form = $builder
            ->add('image', 'file', array(
                'required' => true,
                'constraints' => array(
                    new ImageConstraint(), new NotNull()
                ),
            ))
            ->getForm()
        ;

        return $form;
    }

    public function getGalleryForEntity($entityType, $entityId)
    {
        switch ($entityType) {
            case 'restaurant':
                $entity = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->find($entityId);
                break;
            case 'category':
                $entity = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->find($entityId);
                break;
            case 'product':
                $entity = $this->em->getRepository('ClabRestaurantBundle:Product')->find($entityId);
                break;
            case 'meal':
                $entity = $this->em->getRepository('ClabRestaurantBundle:Meal')->find($entityId);
                break;
            case 'choice':
                $entity = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->find($entityId);
                break;
            case 'place':
                $entity = $this->em->getRepository('ClabLocationBundle:Place')->find($entityId);
                break;
            case 'event':
                $entity = $this->em->getRepository('ClabLocationBundle:Event')->find($entityId);
                break;
            case 'album':
                $entity = $this->em->getRepository('ClabMediaBundle:Album')->find($entityId);
                break;
            case 'client':
                $entity = $this->em->getRepository('ClabBoardBundle:Client')->find($entityId);
                break;
            default:
                $entity = null;
                break;
        }

        return $entity;
    }

    public function getGallery($entityType, $entityId, $user, $type = null, $forceAllow = false)
    {
        $entity = $this->getGalleryForEntity($entityType, $entityId);
        $gallery = $this->getGalleryType($entity, $type);

        return array(true, $gallery, $entity);
    }

    public function getPublicGallery($entityType, $entityId, $user)
    {
        $entity = $this->getGalleryForEntity($entityType, $entityId);

        if (!$entity) {
            return array(false, 'Not found');
        }

        return array(true, $entity->getPublicGallery());
    }

    public function upload($entityType, $entityId, $user, $type = null)
    {
        $entity = $this->getGalleryForEntity($entityType, $entityId);

        if (!$entity) {
            return array(false, 'Not found');
        }

        $form = $this->getUploadForm();
        $form->submit($this->request, 'POST');
        $gallery = $this->getGalleryType($entity, $type);

        if ($form->isValid() && $file = $form->get('image')->getData()) {
            $name = md5(time().$file->getClientOriginalName()).'.'.$file->guessClientExtension();


            $dir = $gallery->getDirName().$name;

            $image = new Image();
            $image->setFile($file);
            $image->setPath($dir);
            $image->setName($name);
            $image->setGallery($gallery);
            $image->setIsGeneric($gallery->getIsGeneric());

            if ($type == 'public' && $user && $user) {
                $image->setProfile($user);
            }

            try {
                list($width, $height) = getimagesize($file);
            } catch (\Exception $e) {
            }

            $image->upload();
            $this->resize($image, $width, $height);
            $this->em->persist($image);

            foreach ($gallery->getChildrens() as $children) {
                $newImage = clone($image);
                $newImage->setGallery($children);
                $newImage->setParent($image);
                $this->em->persist($newImage);

                foreach ($children->getChildrens() as $greatChildren) {
                    $newImage2 = clone($newImage);
                    $newImage2->setGallery($greatChildren);
                    $newImage2->setParent($newImage);

                    $this->em->persist($newImage2);
                }
            }

            $this->em->flush();

            return array(true, $image);
        } else {
            return array(false, $form);
        }
    }

    public function uploadGeneric($gallery, $file)
    {
        $name = md5(time().$file->getClientOriginalName()).'.'.$file->guessClientExtension();
        $dir = $gallery->getDirName().$name;

        $image = new Image();
        $image->setFile($file);
        $image->setPath($dir);
        $image->setName($name);
        $image->setGallery($gallery);
        $image->setIsGeneric($gallery->getIsGeneric());

        try {
            list($width, $height) = getimagesize($file);
        } catch (\Exception $e) {
        }

        $image->upload();

        $this->resize($image, $width, $height);

        $this->em->persist($image);
        $this->em->flush();

        return $image;
    }

    public function resize($image, $width, $height)
    {
        $width = 0;
        $height = 0;

        if ($width >= $height && $width > 1200) {
            $filter = 'blogal_widen';
        } elseif ($height > $width && $height > 1200) {
            $filter = 'global_heighten';
        }

        if (isset($filter)) {
            $dataManager = $this->container->get('liip_imagine.data.manager');
            $filterManager = $this->container->get('liip_imagine.filter.manager');
            $imageFile = $dataManager->find($filter, $image->getWebPath());
            $response = $filterManager->get($this->request, $filter, $imageFile, $image->getWebPath());
            $thumb = $response->getContent();

            $f = fopen($image->getAbsolutePath(), 'w');
            fwrite($f, $thumb);
            fclose($f);
        }
    }

    public function replace($entityType, $entityId, $user, $media, $replace, $type = null)
    {
        $entity = $this->getGalleryForEntity($entityType, $entityId);

        if (!$entity) {
            return array(false, 'Not found');
        }

        $gallery = $this->getGalleryType($entity, $type);

        $replaceImage = $this->em->getRepository('ClabMediaBundle:Image')
            ->findOneBy(array('id' => $replace, 'gallery' => $gallery));

        $name = $media->getClientOriginalName();
        $dir = $gallery->getDirName().$name;

        $image = new Image();
        $image->setFile($media);
        $image->setPath($dir);
        $image->setName($name);
        $image->setGallery($gallery);
        $image->setIsGeneric($gallery->getIsGeneric());

        $image->upload();

        $this->em->persist($image);

        foreach ($gallery->getChildrens() as $children) {
            $newImage = clone($image);
            $newImage->setGallery($children);
            $newImage->setParent($image);
            $this->em->persist($newImage);

            foreach ($children->getChildrens() as $greatChildren) {
                $newImage2 = clone($newImage);
                $newImage2->setGallery($greatChildren);
                $newImage2->setParent($newImage);
                $this->em->persist($newImage2);
            }
        }

        if ($replaceImage->isPromoted()) {
            $image->setIsPromoted(true);
        } else {
            $image->setIsPromoted(false);
        }

        foreach ($replaceImage->getChildrens() as $children) {
            foreach ($children->getChildrens() as $greatChildren) {
                $this->em->remove($greatChildren);
            }
            $this->em->remove($children);
        }
        $this->em->remove($replaceImage);

        $this->em->flush();

        return array(true, $image);
    }

    public function remove($entityType, $entityId, $imageId, $user, $type = null)
    {
        $entity = $this->getGalleryForEntity($entityType, $entityId);

        if (!$entity) {
            return array(false, 'Not found');
        }

        $gallery = $this->getGalleryType($entity, $type);

        foreach ($gallery->getImages() as $image) {
            if ($image->getId() == $imageId) {
                foreach ($image->getChildrens() as $children) {
                    foreach ($children->getChildrens() as $greatChildren) {
                        $this->em->remove($greatChildren);
                    }
                    $this->em->remove($children);
                }
                $this->em->remove($image);
                $this->em->flush();

                return array(true, $gallery);
            }
        }

        return array(false, $gallery);
    }

    public function removeGeneric($image)
    {
        $this->em->remove($image);
        $this->em->flush();
    }

    public function setCover($entityType, $entityId, $imageId, $user, $type = null)
    {
        $entity = $this->getGalleryForEntity($entityType, $entityId);

        if (!$entity) {
            return array(false, 'Not found');
        }

        $gallery = $this->getGalleryType($entity, $type);

        foreach ($gallery->getImages() as $image) {
            if ($image->getId() == $imageId) {
                $gallery->setCover($image);
                $this->em->flush();

                return array(true, $gallery);
            }
        }

        return array(false, 'Not found');
    }

    private function getGalleryType($entity, $type)
    {
        switch ($type) {
            case 'big':
                $gallery = $entity->getGalleryBig();
                break;
            case 'public':
                $gallery = $entity->getPublicGallery();
                break;
            case 'menu':
                $gallery = $entity->getGalleryMenu();
                break;
            default:
                $gallery = $entity->getGallery();
        }

        return $gallery ?: $this->createGallery($entity, $type);
    }

    public function createGallery($entity, $type) {
        $gallery = new Gallery();
        $gallery->setDirName($this->generateName($entity->getSlug(), '/'));
        $gallery->setDefault($this->defaultImage);
        $this->em->persist($gallery);

        $method = sprintf('setGallery%s', ucfirst($type));
        $entity->$method($gallery);
        $this->em->persist($entity);

        $this->em->flush();

        return $gallery;
    }

    private function generateName($slug = 'image', $suffix = null) {
        return substr(uniqid($slug), 0, 20) . $suffix;
    }
}
