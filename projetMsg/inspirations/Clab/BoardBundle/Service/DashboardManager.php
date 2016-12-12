<?php

namespace Clab\BoardBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
use Clab\ShopBundle\Entity\OrderType;

class DashboardManager
{
    protected $em;
    protected $container;
    protected $router;

    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
    }

    public function getTodo($restaurant)
    {
        $steps = array(
            'picture' => false,
            'preorder' => null,
            'delivery' => null,
            'legal' => false,
            'profile' => false,
            //'terms' => false,
        );

        if (count($restaurant->getGallery()->getImages()) > 0) {
            $steps['picture'] = true;
        }

        if ($restaurant->hasOrderType(OrderType::ORDERTYPE_PREORDER)) {
            $steps['preorder'] = false;

            foreach ($restaurant->getTimesheets() as $timesheet) {
                $steps['preorder'] = true;
            }
        }

        if ($restaurant->hasOrderType(OrderType::ORDERTYPE_DELIVERY)) {
            $steps['delivery'] = false;

            $deliveryPeriod = $this->container->get('clab_delivery.delivery_manager')->getCurrentDeliveryPeriod($restaurant);
            $deliveryDay = $this->em->getRepository('ClabDeliveryBundle:DeliverySchedule')->findBy(array('deliveryPeriod' => $deliveryPeriod));
            if (count($deliveryDay) > 0) {
                $steps['delivery'] = true;
            }

            if ($steps['delivery']) {
                if (count($restaurant->getPaymentMethods()) == 0) {
                    $steps['delivery'] = false;
                }
            }
        }

        if ($restaurant->hasOrderType(OrderType::ORDERTYPE_PREORDER) || $restaurant->hasOrderType(OrderType::ORDERTYPE_DELIVERY)) {
            $steps['product'] = false;

            foreach ($restaurant->getRestaurantMenus() as $menu) {
                foreach ($menu->getProducts() as $product) {
                    $steps['product'] = true;
                }
            }

            $steps['ordermail'] = false;

            if ($restaurant->getNotificationMails()) {
                $steps['ordermail'] = true;
            }
        }

        if ($restaurant->getLegalName() && $restaurant->getManagerName() && $restaurant->getLegalAddress() && $restaurant->getSiret()) {
            $steps['legal'] = true;
        }

        if ($restaurant->getEmail() && $restaurant->getPhone() && ($restaurant->isMobile() || $restaurant->getAddress())) {
            $steps['profile'] = true;
        }

        if ($restaurant->isMobile()) {
            $steps['foodtruck'] = false;
            $steps['foodtruckMail'] = false;

            foreach ($restaurant->getTimesheets() as $timesheet) {
                $steps['foodtruck'] = true;
            }

            if ($restaurant->getTttEventValidationMail()) {
                $steps['foodtruckMail'] = true;
            }
        }

        //deprecated
        $steps['invoice'] = null;

        $done = 0;
        $total = 0;
        foreach ($steps as $step) {
            if ($step !== null) {
                ++$total;

                if ($step) {
                    ++$done;
                }
            }
        }

        $percent = $total > 0 ? $done * 100 / $total : 0;
        $percent = round($percent);

        return array($steps, $percent);
    }

    public function getTodoList($restaurant)
    {
        list($todo, $percent) = $this->getTodo($restaurant);
        $steps = array();

        foreach ($todo as $step => $value) {
            if ($value == false) {
                switch ($step) {
                    case 'picture':
                        $steps[] = 'Ajoutez une photo à votre profil';
                        break;
                    case 'legal':
                        $steps[] = 'Complétez vos informations légales';
                        break;
                    case 'profile':
                        $steps[] = 'Complétez votre profil public';
                        break;
                    case 'product':
                        $steps[] = 'Ajoutez votre premier produit';
                        break;
                    case 'ordermail':
                        $steps[] = 'Ajoutez une adresse mail pour la réception des commandes';
                        break;
                    case 'foodtruck':
                        $steps[] = 'Complétez le planning de vos évènements';
                        break;
                    case 'foodtruckMail':
                        $steps[] = 'Ajoutez une adresse mail pour certifier votre emplacement';
                        break;
                    default:
                        break;
                }
            }
        }

        return array($steps, $percent);
    }
}
