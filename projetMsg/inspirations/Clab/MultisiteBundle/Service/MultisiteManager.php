<?php

namespace Clab\MultisiteBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Clab\MultisiteBundle\Entity\Multisite;

class MultisiteManager
{
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public function getForRestaurant($restaurant, $embed = false)
    {
        if ($embed) {
            $site = $this->em->getRepository('ClabMultisiteBundle:Multisite')
                ->findOneBy(array('is_deleted' => false, 'restaurant' => $restaurant, 'type' => Multisite::MULTISITE_TYPE_EMBED));
        } else {
            $site = $this->em->getRepository('ClabMultisiteBundle:Multisite')
                ->findOneBy(array('is_deleted' => false, 'restaurant' => $restaurant, 'type' => Multisite::MULTISITE_TYPE_CLASSIC));
        }

        return $site;
    }

    public function getForClient($client)
    {
        $site = $this->em->getRepository('ClabMultisiteBundle:Multisite')
            ->findOneBy(array('is_deleted' => false, 'client' => $client));

        return $site;
    }

    public function getByDomain($domain)
    {
        try {
            $site = $this->em->getRepository('ClabMultisiteBundle:Multisite')
                ->getByDomain($domain);
        } catch (\Exception $e) {
            return;
        }

        return $site;
    }

    public function getByFacebookPage($page, $embed = false)
    {
        if ($embed) {
            $site = $this->em->getRepository('ClabMultisiteBundle:Multisite')
                ->findOneBy(array('facebookPage' => $page, 'type' => Multisite::MULTISITE_TYPE_EMBED));
        } else {
            $site = $this->em->getRepository('ClabMultisiteBundle:Multisite')
                ->findOneBy(array('facebookPage' => $page, 'type' => Multisite::MULTISITE_TYPE_CLASSIC));
        }

        return $site;
    }

    public function getOrCreateForRestaurant($restaurant, $embed = false)
    {
        $site = $this->getForRestaurant($restaurant, $embed);

        if (!$site) {
            $site = new Multisite();
            $site->setRestaurant($restaurant);
            $site->setIsOnline(false);

            if ($embed) {
                $site->setType(Multisite::MULTISITE_TYPE_EMBED);
                $site->setDomain('order.'.$restaurant->getSlug().'.'.$this->container->getParameter('byclickeatdomain'));
            } else {
                $site->setDomain($restaurant->getSlug().'.'.$this->container->getParameter('byclickeatdomain'));
            }

            $this->em->persist($site);
            $this->em->flush();
        }

        return $site;
    }

    public function getOrCreateForClient($client)
    {
        $site = $this->getForClient($client);

        if (!$site) {
            $site = new Multisite();
            $site->setClient($client);
            $site->setIsOnline(true);
            $site->setDomain($client->getSlug().'.byclickeat.fr');
            $this->em->persist($site);
            $this->em->flush();
        }

        return $site;
    }

    public function getUrlForRestaurant($restaurant, $embed = false)
    {
        if($embed) {
            return 'http://' . $this->container->getParameter('embeddomain') . '/redirect?restaurant=' . $restaurant->getSlug();
        }
    }
}
