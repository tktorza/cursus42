<?php

namespace Clab\BoardBundle\Menu;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Knp\Menu\FactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\Restaurant;

class Menu extends Controller
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $context = $this->get('session')->get('board_menu_context');
        $contextPk = $this->get('session')->get('board_menu_context_pk');

        $menu = $factory->createItem('root');
        $menu->setCurrent($this->container->get('request')->getRequestUri());
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
            'slug' => $this->get('session')->get('board_menu_context_pk'), )
    );
        if ($context == 'restaurant') {
            $proxy = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
            $forcedAdd = 0;
            $forcedIsOnline = 0;
            if(!is_null($proxy->getClient()))
            {
                $forcedAdd = $proxy->getClient()->getForcedAdd();
                $forcedIsOnline = $proxy->getClient()->getForcedIsOnline();
            }
            $business = $menu->addChild('Restaurant', array(
                'route' => 'board_restaurant_profile',
                'routeParameters' => array('contextPk' => $contextPk),
            ));
            if($forcedAdd == 1)
            {
                $catalog = $menu->addChild('Catalogue', array(
                    'route' => 'board_catalog',
                    'routeParameters' => array('context' => $context, 'contextPk' => $contextPk),
                ));
            }
            else
            {
                $catalog = $menu->addChild('Catalogue', array(
                    'route' => 'board_category_library',
                    'routeParameters' => array('context' => $context, 'contextPk' => $contextPk),
                ));
            }

            $sales = $menu->addChild('Ventes', array(
                'route' => 'board_sales_reporting',
                'routeParameters' => array('contextPk' => $contextPk),
            ));

            $tools = $menu->addChild('Apps', array(
                    'route' => 'board_apps_library',
                    'routeParameters' => array('contextPk' => $contextPk),
            ));

        } elseif ($context == 'client') {
            $client = $menu->addChild('Enseigne', array(
                'route' => 'board_user_library',
                'routeParameters' => array('context' => $context, 'contextPk' => $contextPk),
            ));

            $catalog = $menu->addChild('Catalogue', array(
                'route' => 'board_category_library',
                'routeParameters' => array('context' => $context, 'contextPk' => $contextPk),
            ));
            $subscription = $menu->addChild('Abonnement', array(
                'route' => 'board_subscription_client_list',
                'routeParameters' => array('contextPk' => $contextPk),
            ));
           if($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN'))
           {
               $admin = $menu->addChild('Administration', array(
                   'route' => 'board_admin_library_restaurant',
                   'routeParameters' => array('contextPk' => $contextPk),
               ));
           }
        }

        return $menu;
    }
}
