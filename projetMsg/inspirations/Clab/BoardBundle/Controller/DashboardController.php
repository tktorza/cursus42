<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Form\Type\Store\RestaurantType;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\OrderType;
use Clab\SocialBundle\Entity\SocialPost;
use Clab\BoardBundle\Form\Type\Social\SocialPostType;
use Symfony\Component\HttpFoundation\Request;

class DashboardController extends Controller
{
    public function dashboardAction($context = null, $contextPk = null)
    {
        $em = $this->getDoctrine()->getManager();
        $params = array();

        $securityContext = $this->container->get('security.context');
        if (!$securityContext->isGranted('ROLE_MANAGER_2')) {
            return $this->redirectToRoute('clab_board_login');
        }

        // dashboard
        if ($context && $contextPk) {
            $this->get('board.helper')->initContext($context, $contextPk);

            if ($this->get('board.helper')->getProxy() instanceof Restaurant) {
            } else {
                return $this->redirectToRoute('board_client_reporting', array('contextPk' => $contextPk));
            }
            $reviews = $this->getDoctrine()->getRepository('ClabReviewBundle:Review')->findBy(array('restaurant' => $this->get('board.helper')->getProxy(),
                'isOnline' => true, 'isRead' => false, ), array('id' => 'desc'));
            $params['reviews'] = $reviews;
            $chainstore = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->find(1);
            $params['chainstore'] = $chainstore;

            return $this->render('ClabBoardBundle:Dashboard:dashboard.html.twig', array_merge($this->get('board.helper')->getParams(), $params));
        // redirect and stuff
        } else {
            $securityContext = $this->container->get('security.context');
            if ($securityContext->isGranted('ROLE_COMMERCIAL')) {
                $restaurants = $em->getRepository('ClabRestaurantBundle:Restaurant')->findAll();
                $clients = $em->getRepository('ClabBoardBundle:Client')->findAll();
            } elseif ($this->getUser()->hasRole('ROLE_MANAGER_2') || $this->getUser()->hasRole('ROLE_MANAGER')) {
                $restaurants = $this->getUser()->getAllowedRestaurants();
                $clients = $this->getUser()->getClients();

                if (count($restaurants) == 1) {
                    return $this->redirectToRoute('board_dashboard', array('context' => 'restaurant', 'contextPk' => $restaurants[0]->getSlug()));
                }
            } else {
                throw $this->createNotFoundException();
            }

            return $this->render('ClabBoardBundle:Dashboard:restaurants.html.twig', array_merge($this->get('board.helper')->getParams(), array(
                'restaurants' => $restaurants,
                'clients' => $clients,
            )));
        }
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function goOnlineAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $onboard = $this->get('app_admin.subscription_manager')->onboard($this->get('board.helper')->getProxy());

        if (!$onboard) {
            $this->addFlash('notice', 'Vous devez compléter la todo pour passer à la suivante');
        }

        return $this->redirectToRoute('board_dashboard', array('context' => 'restaurant', 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function featureAction($contextPk, $feature)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        return $this->render('ClabBoardBundle:Dashboard:features/feature-'.$feature.'.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function activateFeatureAction($contextPk, $feature)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $success = $this->get('app_admin.subscription_manager')->activateFeature($this->get('board.helper')->getProxy(), $feature);

        if ($success) {
            switch ($feature) {
                case 'preorder':
                    return $this->redirectToRoute('board_restaurant_planning', array('contextPk' => $contextPk));
                    break;
                case 'delivery':
                    return $this->redirectToRoute('board_delivery', array('contextPk' => $contextPk));
                    break;
                case 'discount':
                    return $this->redirectToRoute('board_discount_library', array('contextPk' => $contextPk));
                    break;
                case 'share':
                    return $this->redirectToRoute('board_social_dashboard', array('contextPk' => $contextPk));
                    break;
                case 'synch':
                    return $this->redirectToRoute('board_tools_synch', array('contextPk' => $contextPk));
                    break;
                case 'reporting':
                    return $this->redirectToRoute('board_sales_reporting', array('contextPk' => $contextPk));
                    break;
                case 'analytics':
                    return $this->redirectToRoute('board_sales_analytics', array('contextPk' => $contextPk));
                    break;
                case 'category':
                    return $this->redirectToRoute('board_category_library', array('context' => 'restaurant', 'contextPk' => $contextPk));
                    break;
                case 'product':
                    return $this->redirectToRoute('board_product_library', array('context' => 'restaurant', 'contextPk' => $contextPk));
                    break;
                case 'meal':
                    return $this->redirectToRoute('board_meal_library', array('context' => 'restaurant', 'contextPk' => $contextPk));
                    break;
                case 'iframe':
                    return $this->redirectToRoute('board_appstore_iframe', array('contextPk' => $contextPk));
                    break;
                case 'website':
                    return $this->redirectToRoute('board_appstore_website', array('contextPk' => $contextPk));
                    break;
                case 'app':
                    return $this->redirectToRoute('board_appstore_app', array('contextPk' => $contextPk));
                    break;
                case 'loyalty':
                    break;
            }
        }

        return $this->redirectToRoute('board_feature_showcase', array('contextPk' => $contextPk, 'feature' => $feature));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function desactivateFeatureAction($contextPk, $feature)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $success = $this->get('app_admin.subscription_manager')->desactivateFeature($this->get('board.helper')->getProxy(), $feature);
        }

        return $this->redirectToRoute('board_dashboard', array('context' => 'restaurant', 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function endTestAction($contextPk)
    {
        ;
        $this->get('board.helper')->initContext('restaurant', $contextPk, true);

        return $this->render('ClabBoardBundle:Dashboard:endTest.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'inbox' => false,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function contactAction()
    {
        return $this->render('ClabBoardBundle:Dashboard:contact.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function mailTestAction($type)
    {
        return $this->render('ClabBoardBundle:Mail:samples/'.$type.'.html.twig', $this->get('board.helper')->getParams());
    }

    public function addNewRestaurantAction($context = null, $contextPk = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $restaurant = new Restaurant();

        $form = $this->createForm(new RestaurantType(), $restaurant);
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                $client = $this->getDoctrine()->getRepository("ClabBoardBundle:Client")->findAll();
                $restaurant->setClient($client[0]);

                $menuClassic = $em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('chainStore' => $client[0] , 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT));
                $menuDelivery = $em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('chainStore' => $client[0] , 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY));

                $menuC = new RestaurantMenu();
                $menuC->setRestaurant($restaurant);
                $menuC->setIsOnline($menuClassic->getIsOnline());
                $menuC->setName($menuClassic->getName());
                $menuC->setType($menuClassic->getType());
                $em->persist($menuC);

                $menuD = new RestaurantMenu();
                $menuD->setRestaurant($restaurant);
                $menuD->setIsOnline($menuDelivery->getIsOnline());
                $menuD->setName($menuDelivery->getName());
                $menuD->setType($menuDelivery->getType());
                $em->persist($menuD);


                $categories = $em->getRepository('ClabRestaurantBundle:ProductCategory')->findBy(array('client' => $client[0]));
                $choices = $em->getRepository('ClabRestaurantBundle:OptionChoice')->findBy(array('client' => $client[0]));
                $options = $em->getRepository('ClabRestaurantBundle:ProductOption')->findBy(array('client' => $client[0]));
                $products = $em->getRepository('ClabRestaurantBundle:Product')->getForChainStore($client[0]);
                $slots = $em->getRepository('ClabRestaurantBundle:MealSlot')->findBy(array('client' => $client[0]));
                $meals = $em->getRepository('ClabRestaurantBundle:Meal')->getForChainStore($client[0]);


                $this->get('clab_board.restaurant_manager')->setCatalog($restaurant, $categories, $choices, $options, $menuC, $menuD, $products, $slots, $meals);

                $em->persist($restaurant);
                $em->flush();

                $this->get('session')->getFlashBag()->add('formSuccess', 'Le restaurant a bien été sauvegardé');

                return $this->redirectToRoute('board_dashboard', array('contextPk' => $contextPk));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        return $this->render('ClabBoardBundle:Store:add.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'form' => $form->createView(),
        )));
    }
}
