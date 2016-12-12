<?php

namespace Clab\BoardBundle\Controller;

use Clab\RestaurantBundle\Entity\App;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AppsController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
        if ($restaurant->isMobile() == true) {
            $apps = $this->getDoctrine()->getRepository('ClabRestaurantBundle:App')->findAllForFoodtruck();
        } else {
            $apps = $this->getDoctrine()->getRepository('ClabRestaurantBundle:App')->findAllForRestaurant();
        }
        $appsRestaurant = $restaurant->getApps()->toArray();
        $currentSubscription = $this->getDoctrine()->getRepository('ClabBoardBundle:Subscription')->findOneBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
            'is_online' => true,
            'type' => 0,
        ));
        if (!is_null($currentSubscription)) {
            $plan = $currentSubscription->getPlan();
        } else {
            $plan = array();
        }

        $appsFinal = array();
        foreach ($apps as $key => $app) {
            if (in_array($plan, $app->getPlans()->toArray())) {
                $appsFinal[$key]['inPlan'] = true;
            } else {
                $appsFinal[$key]['inPlan'] = false;
            }

            if (in_array($app, $appsRestaurant)) {
                $appsFinal[$key]['already_purchased'] = true;
                $appsFinal[$key]['app'] = $app;
            } else {
                $appsFinal[$key]['already_purchased'] = false;
                $appsFinal[$key]['app'] = $app;
            }
        }

        return $this->render('ClabBoardBundle:Apps:library.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'apps' => $appsFinal,
            'restaurant' => $restaurant,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function myLibraryAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
            'slug' => $contextPk,
        ));
        $apps = $restaurant->getApps()->toArray();

        return $this->render('ClabBoardBundle:Apps:my-library.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'apps' => $apps,
            'restaurant' => $restaurant,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     * @ParamConverter("app", class="ClabRestaurantBundle:App")
     */
    public function removeAppAction($contextPk, App $app)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
            'slug' => $contextPk,
        ));

        $restaurant->removeApp($app);

        $em->flush();
        $apps = $restaurant->getApps()->toArray();

        return $this->render('ClabBoardBundle:Apps:my-library.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'apps' => $apps,
            'restaurant' => $restaurant,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     * @ParamConverter("app", class="ClabRestaurantBundle:App")
     */
    public function addAppAction($contextPk, App $app)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
            'slug' => $contextPk,
        ));

        $restaurant->addApp($app);

        $em->flush();
        $apps = $restaurant->getApps()->toArray();

        return $this->render('ClabBoardBundle:Apps:my-library.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'apps' => $apps,
            'restaurant' => $restaurant,
        )));
    }
}
