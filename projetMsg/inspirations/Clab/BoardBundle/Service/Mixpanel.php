<?php

namespace Clab\BoardBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

class Mixpanel
{
    protected $container;
    protected $em;
    protected $router;
    protected $mp;

    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
        $this->mp = \Mixpanel::getInstance($this->container->getParameter('mixpanel_token_pro'));
    }

    public function track($event, array $properties, $proxy = null)
    {
        try {
            if($proxy) {
                $profile = $this->mp->identify($proxy->getId());
                $this->mp->people->set($proxy->getId(), array(
                    '$first_name' => $proxy->getName(),
                    '$email' => $proxy->getManagerEmail(),
                    '$phone' => $proxy->getManagerPhone(),
                    '$ip' => $this->container->get('request')->getClientIp()
                ));
            }

            $this->mp->register('Activity', $proxy->isMobile() ? 'Foodtruck' : 'Restaurant');

            $this->mp->track($event, $properties);
        } catch(\Exception $e) {}
    }
}