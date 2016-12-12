<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Form\Type\Appstore\PrintType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\BoardBundle\Form\Type\Appstore\WebsiteType;
use Clab\BoardBundle\Form\Type\Appstore\IframeType;
use Clab\BoardBundle\Form\Type\Appstore\AppType;
use Clab\BoardBundle\Form\Type\Appstore\AppDealType;
use Clab\BoardBundle\Form\Type\Appstore\SettingsType;
use Symfony\Component\HttpFoundation\Request;

class AppstoreController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function websiteAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $application = $this->getDoctrine()->getRepository('ClabRestaurantBundle:App')->findOneBy(array(
            'slug' => 'site-internet',
        ));
        $subscription = $this->getDoctrine()->getRepository('ClabBoardBundle:Subscription')->findOneBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
            'is_online' => true,
            'type' => 0,
        ));
        $appsInSub = $application->getPlans();
        $restaurantApp = $this->get('board.helper')->getProxy()->getApps();
        if (!in_array($subscription->getPlan(), $appsInSub->toArray()) && !in_array($application, $restaurantApp->toArray())) {
            $this->get('board.helper')->addParam('application', $application);

            return $this->render('ClabBoardBundle:Apps:empty-screen.html.twig', $this->get('board.helper')->getParams());
        }
        if (!$this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'website')) {
            return $this->redirectToRoute('board_feature_showcase', array('feature' => 'website', 'contextPk' => $contextPk));
        }

        $site = $this->get('clab_multisite.multisite_manager')
            ->getOrCreateForRestaurant($this->get('board.helper')->getProxy());

        $form = $this->createForm(new WebsiteType(array('images' => $this->get('board.helper')->getProxy()->getGallery()->getImages())), $site);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($site);
            $em->flush();

            return $this->redirectToRoute('board_appstore_website', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParams(array(
            'site' => $site,
            'form' => $form->createView(),
        ));

        return $this->render('ClabBoardBundle:Appstore:website.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function iframeAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $application = $this->getDoctrine()->getRepository('ClabRestaurantBundle:App')->findOneBy(array(
            'slug' => 'commande-en-ligne-sur-votre-site',
        ));
        $subscription = $this->getDoctrine()->getRepository('ClabBoardBundle:Subscription')->findOneBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
            'is_online' => true,
            'type' => 0,
        ));
        $appsInSub = $application->getPlans();
        $restaurantApp = $this->get('board.helper')->getProxy()->getApps();
        if (!in_array($subscription->getPlan(), $appsInSub->toArray()) && !in_array($application, $restaurantApp->toArray())) {
            $this->get('board.helper')->addParam('application', $application);

            return $this->render('ClabBoardBundle:Apps:empty-screen.html.twig', $this->get('board.helper')->getParams());
        }
        $socialManager = $this->get('clab.social_manager');
        $socialManager->initSocialProfile($this->get('board.helper')->getProxy());

        if (!$this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'iframe')) {
            return $this->redirectToRoute('board_feature_showcase', array('feature' => 'iframe', 'contextPk' => $contextPk));
        }

        $multisiteManager = $this->get('clab_multisite.multisite_manager');
        $site = $multisiteManager->getOrCreateForRestaurant($this->get('board.helper')->getProxy(), true);

        $form = $this->createForm(new IframeType(), $site);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($site);
            $em->flush();

            return $this->redirectToRoute('board_appstore_iframe', array('contextPk' => $contextPk));
        }

        $params = array(
            'site' => $site,
            'form' => $form->createView(),
            'assetUrl' => $this->getParameter('domain'),
            'url' => $multisiteManager->getUrlForRestaurant($this->get('board.helper')->getProxy(), true),
        );

        if ($page = $this->get('board.helper')->getProxy()->getFacebookPage()) {
            $fbTab = $this->get('clab_board.facebook_manager')->checkPageTab($page, 'iframe');
            $params = array(
               'site' => $site,
               'form' => $form->createView(),
               'assetUrl' => $this->getParameter('domain'),
               'url' => $multisiteManager->getUrlForRestaurant($this->get('board.helper')->getProxy(), true),
               'fbTab' => $fbTab,
           );
        }

        $this->get('board.helper')->addParams($params);

        return $this->render('ClabBoardBundle:Appstore:iframe.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function appAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if (!$this->get('board.helper')->subscriptionManager->hasAccess($this->get('board.helper')->getProxy(), 'app')) {
            return $this->redirectToRoute('board_appstore_app_deal', array('contextPk' => $contextPk));
        }

        $app = $this->get('clab_multisite.multiapp_manager')
            ->getOrCreateForRestaurant($this->get('board.helper')->getProxy());

        $form = $this->createForm(new AppType(), $app);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($app);

            $storage = $this->container->get('vich_uploader.storage');
            $storage->upload($app);

            $em->flush();

            return $this->redirectToRoute('board_appstore_app', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParams(array(
            'application' => $app,
            'form' => $form->createView(),
        ));

        return $this->render('ClabBoardBundle:Appstore:app.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function appDealAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($this->get('board.helper')->subscriptionManager->hasAccess($this->get('board.helper')->getProxy(), 'app')) {
            return $this->redirectToRoute('board_appstore_app', array('contextPk' => $contextPk));
        }

        $form = $this->createForm(new AppDealType(), $this->get('board.helper')->getProxy()->getDeal());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->flush();

            if ($this->get('board.helper')->getProxy()->getDeal()->getInterestedInApp()) {
                $this->container->get('clab_board.mail_manager')->adminAppNotification($this->get('board.helper')->getProxy());
                $this->get('session')->getFlashBag('success', 'Votre mail a bien été envoyé');
            }

            return $this->redirectToRoute('board_appstore_app_deal', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParams(array(
            'form', $form->createView(),
            'interested',   $this->get('board.helper')->getProxy()->getDeal()->getInterestedInApp(),
        ));

        return $this->render('ClabBoardBundle:Appstore:appDeal.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function widgetAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        if ($this->get('board.helper')->getProxy()->getIsMobile() == false) {
            throw $this->createNotFoundException();
        }
        $application = $this->getDoctrine()->getRepository('ClabRestaurantBundle:App')->findOneBy(array(
            'slug' => 'menu-et-planning-sur-votre-site',
        ));
        $subscription = $this->getDoctrine()->getRepository('ClabBoardBundle:Subscription')->findOneBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
            'is_online' => true,
            'type' => 0,
        ));
        $appsInSub = $application->getPlans();
        $restaurantApp = $this->get('board.helper')->getProxy()->getApps();
        if (!in_array($subscription->getPlan(), $appsInSub->toArray()) && !in_array($application, $restaurantApp->toArray())) {
            $this->get('board.helper')->addParam('application', $application);

            return $this->render('ClabBoardBundle:Apps:empty-screen.html.twig', $this->get('board.helper')->getParams());
        }
        $this->get('board.helper')->addParam('widgetDomain', $this->getParameter('widgetDomain'));

        return $this->render('ClabBoardBundle:Appstore:synch.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function settingsAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $form = $this->createForm(new SettingsType(), $this->get('board.helper')->getProxy());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('board_appstore_settings', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());
        $this->get('board.helper')->addParam('restaurant', $this->get('board.helper')->getProxy());

        return $this->render('ClabBoardBundle:Appstore:settings.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function printAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $form = $this->createForm(new PrintType(), $this->get('board.helper')->getProxy());

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('board_appstore_print', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Appstore:print.html.twig', $this->get('board.helper')->getParams());
    }
}
