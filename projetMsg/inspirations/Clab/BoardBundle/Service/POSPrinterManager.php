<?php

namespace Clab\BoardBundle\Service;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

class POSPrinterManager
{
    protected $container;
    protected $em;
    protected $router;
    protected $repository;

    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
        $this->repository = $this->em->getRepository('ClabBoardBundle:POSPrinter');
    }

    public function getForRestaurant(Restaurant $restaurant, $id)
    {
        $printer = $this->repository->find($id);

        foreach ($restaurant->getPosPrinters() as $posPrinter) {
            if($posPrinter == $printer) {
                return $printer;
            }
        }

        return null;
    }
}
