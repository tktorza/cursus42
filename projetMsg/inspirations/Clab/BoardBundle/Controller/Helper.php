<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Clab\BoardBundle\Exception\SubscriptionException;
use Clab\RestaurantBundle\Entity\Restaurant;

class Helper extends Controller
{
    protected $params = array();
    protected $proxy = null;
    protected $parent = null;
    protected $context = null;
    protected $contextPk = null;
    protected $subscriptionManager;

    protected $onboardCustomRoutes = array(
        'board_onboard_custom_infos', 'board_onboard_custom_catalog', 'board_onboard_custom_timesheets', 'board_onboard_custom_planning', 'board_onboard_custom_order',
        'board_onboard_custom_legal', 'board_onboard_custom_subscription', 'board_onboard_custom_terms', 'board_onboard_custom_social', 'board_onboard_custom_final',
        'board_onboard_custom_ready',
        'board_gallery_get', 'board_gallery_upload', 'board_gallery_delete', 'board_gallery_cover',
        'board_social_configuration_remove', 'board_social_configuration_choose_page',
        'board_subscription_terms', 'board_subscription_terms_send',
    );

    protected $onboardLaunchRoutes = array(
        'board_onboarding_plans', 'board_onboarding_infos', 'board_onboarding_terms',
        'board_onboarding_billing', 'board_onboarding_buckleup', 'board_onboarding_activate',
    );

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function initContext($context, $contextPk, $forceAllow = false)
    {
        $em = $this->getDoctrine()->getManager();
        $this->subscriptionManager = $this->container->get('app_admin.subscription_manager');

        if ($context == 'admin') {
            if (!$this->isGranted('ROLE_ADMIN')) {
                throw $this->createNotFoundException();
            }
            $proxy = null;
        } elseif ($context == 'restaurant') {

            $proxy = $em->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
            $forcedAdd = 0;
            $forcedIsOnline = 0;
            if(!is_null($proxy->getClient()))
            {
                $forcedAdd = $proxy->getClient()->getForcedAdd();
                $forcedIsOnline = $proxy->getClient()->getForcedIsOnline();
            }
            $this->get('board.helper')->addParam('forcedIsOnline', $forcedIsOnline);
            $this->get('board.helper')->addParam('forcedAdd', $forcedAdd);
            $plan = $em->getRepository('ClabRestaurantBundle:Plan')->find(1);
            $this->get('board.helper')->addParam('planInhouse', $plan);
            if (!$proxy) {
                throw $this->createNotFoundException();
            }

            if (!$this->isGranted('ROLE_COMMERCIAL') && !$proxy->getManagers()->contains($this->getUser())) {
                $this->get('session')->getFlashBag()->add('notice', 'Vous n\'avez pas accès à ce compte. Veuillez vous connecter avec un compte manager');
                throw new SubscriptionException($this->generateUrl('clab_board_login', array('next' => $this->generateUrl('board_dashboard', array('context' => 'restaurant', 'contextPk' => $contextPk)))));
            }

            $this->get('session')->set('board_menu_context', 'restaurant');
            $this->get('session')->set('board_menu_context_pk', $contextPk);

            if (!$forceAllow) {
                if ($proxy->getStatus() < Restaurant::STORE_STATUS_ACTIVE) {
                    if ($proxy->getStatus() < Restaurant::STORE_STATUS_TEST && !$this->isGranted('ROLE_COMMERCIAL')) {
                        $route = $this->getRequest()->get('_route');

                        if (!in_array($route, $this->onboardCustomRoutes)) {
                            throw new SubscriptionException($this->generateUrl('board_onboard_custom_infos', array('slug' => $contextPk)));
                        }
                    } elseif ($proxy->getStatus() >= Restaurant::STORE_STATUS_WAITING) {
                        $route = $this->getRequest()->get('_route');

                        if (!in_array($route, $this->onboardLaunchRoutes)) {
                            throw new SubscriptionException($this->generateUrl('board_onboarding_plans', array('contextPk' => $contextPk)));
                        }
                    }
                }
            }

            if ($proxy->getClient() && $proxy->getClient()->getForcedPricing()) {
                $this->get('board.helper')->addParam('forcedPricing', true);
            } else {
                $this->get('board.helper')->addParam('forcedPricing', false);
            }
        } elseif ($context == 'client') {
            $proxy = $em->getRepository('ClabBoardBundle:Client')->findOneBy(array('slug' => $contextPk));

            if (!$proxy) {
                throw $this->createNotFoundException();
            }

            if (!$this->isGranted('ROLE_COMMERCIAL') && !$proxy->getManagers()->contains($this->getUser())) {
                $this->get('session')->getFlashBag()->add('notice', 'Vous n\'avez pas accès à ce compte. Veuillez vous connecter avec un compte manager');
                throw new SubscriptionException($this->generateUrl('clab_board_login', array('next' => $this->generateUrl('board_dashboard', array('context' => 'restaurant', 'contextPk' => $contextPk)))));
            }

            $this->get('session')->set('board_menu_context', 'client');
            $this->get('session')->set('board_menu_context_pk', $contextPk);
        } else {
            throw $this->createNotFoundException();
        }

        $this->setProxy($proxy);
        if ($this->context == 'restaurant') {
            $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
                'slug' => $contextPk,
            ));
            $this->get('board.helper')->addParam('restaurant', $restaurant);
        }
        $this->get('board.helper')->addParam('context', $context);
        $this->get('board.helper')->addParam('contextPk', $contextPk);

        $this->get('board.helper')->addParam('subscriptionManager', $this->subscriptionManager);

        $this->initParams();

        $params = $this->getParams();
    }

    public function initParams()
    {
        $this->get('board.helper')->addParam('ajax', false);
        if ($this->getRequest()->isXmlHttpRequest()) {
            $this->get('board.helper')->addParam('ajax', true);
        }

        if ($this->get('board.helper')->getProxy()) {
            $this->get('board.helper')->addParam('proxy', $this->get('board.helper')->getProxy());
        }
    }

    public function addAviary()
    {
        $this->get('board.helper')->addParams(array(
            'aviary_key' => $this->getParameter('aviary_key'),
            'aviary_secret' => $this->getParameter('aviary_secret'),
        ));
    }

    public function getProxy()
    {
        return $this->proxy;
    }

    public function setProxy($proxy)
    {
        $this->proxy = $proxy;

        return $this->proxy;
    }

    public function getParam($name)
    {
        if (isset($this->params[$name])) {
            return $this->params[$name];
        }

        return;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function addParam($key, $value)
    {
        $this->params[$key] = $value;
    }

    public function addParams($params)
    {
        foreach ($params as $key => $value) {
            $this->addParam($key, $value);
        }
    }
}
