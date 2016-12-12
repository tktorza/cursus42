<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\SocialBundle\Entity\SocialPost;
use Clab\BoardBundle\Form\Type\Social\SocialProfileType;
use Clab\BoardBundle\Form\Type\Social\SocialPostType;
use Facebook\FacebookRequestException;
use Symfony\Component\HttpFoundation\Response;

class SocialController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function postDashboardAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $socialManager = $this->get('clab.social_manager');
        $socialManager->initSocialProfile($this->get('board.helper')->getProxy());

        return $this->render('ClabBoardBundle:Social:postDashboard.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function configurationAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $application = $this->getDoctrine()->getRepository('ClabRestaurantBundle:App')->findOneBy(array(
            'slug' => 'post-automatique-de-votre-emplacement-du-jour-sur-facebook-et-twitter',
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

        $form = $this->createForm(new SocialProfileType(array('isMobile' => $this->get('board.helper')->getProxy()->isMobile())), $this->get('board.helper')->getProxy()->getSocialProfile());

        if ($form->handleRequest($request)->isValid()) {
            $em->flush();

            return $this->redirectToRoute('board_social_configuration', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Social:configuration.html.twig', $this->get('board.helper')->getParams());
    }

    public function pushtoFbAction($contextPk, Request $request)
    {
        $link = $request->get('link');
        $description = $request->get('description');
        $picture = $request->get('picture');
        $options = array();
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
        $page = $restaurant->getFacebookPage();
        if ($restaurant && $restaurant->isMobile()) {
            $homeLink = $this->generateUrl('ttt_profile', array('slug' => $restaurant->getSlug()));
        } else {
            $homeLink = $this->generateUrl('clickeat_store_profile', array('slug' => $restaurant->getSlug()));
        }

        if (is_null($page)) {
            return new JsonResponse([
                'success' => false,
                'data' => 'Veuillez lier votre compte facebook à la page myClickEat',
            ]);
        }

        if (is_null($picture)) {
            $type = 'no-picture';
        } else {
            $type = 'photo';
        }
        if ($link == 'yes') {
            $absoluteLink = 'http:'.$homeLink;
            $options['link'] = $absoluteLink;
        } else {
            unset($options['link']);
        }
        $options['message'] = $description;
        if (!is_null($picture)) {
            $options['picture'] = isset($picture) && $picture ? $picture : null;
            $options['caption'] = 'Click-eat.fr';
        }
        $response = $this->get('clab.social_manager')->pushFacebookPage($page, $options, $type);

        return new JsonResponse([
            'success' => true,
            'data' => $response,
        ]);
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function postAction($contextPk, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $post = new SocialPost();

        if ($type == 'message' && $this->getRequest()->get('tttNotificationCampaign')) {
            $post->setTitle('Nouveau ! Abonnez-vous à mes emplacements');
            $post->setMessage("Ne me ratez plus ! Soyez alerté par mail dès que je me situe à moins d'1km de vous ! Pour cela, rendez-vous sur mon profil Track The Truck, et abonnez-vous à mes emplacements en cliquant sur le bouton Track The Truck !");
        }

        try {
            $flow = $this->get('clab_board.form.flow.post');
            $flow->init($type, $this->get('board.helper')->getProxy());
            $flow->bind($post);
            $form = $flow->createForm();
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('notice', $e->getMessage());

            return $this->redirectToRoute('board_social_dashboard', array('contextPk' => $contextPk));
        }

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {
                $post->setRestaurant($this->get('board.helper')->getProxy());
                $em->persist($post);
                $em->flush();

                if ($form->has('add_link')) {
                    $socialManager = $this->get('clab.social_manager');
                    $socialManager->pushSocialPost(
                        $post,
                        $form->get('add_link')->getData(),
                        array(
                            'facebook' => $form->get('to_facebook')->getData(),
                            'twitter' => $form->get('to_twitter')->getData(),
                        )
                    );
                }

                $flow->reset();

                $this->get('session')->getFlashBag()->add('success', 'Votre message a bien été envoyé !');

                return $this->redirectToRoute('board_social_dashboard', array('contextPk' => $contextPk));
            }
        }

        $this->get('board.helper')->addParams(array(
            'post' => $post,
            'form' => $form->createView(),
            'flow' => $flow,
            'type' => $type,
        ));

        if ($post->getProduct()) {
            $entity = $post->getProduct();
        }
        if ($post->getMeal()) {
            $entity = $post->getMeal();
        }

        if (isset($entity) && $entity && $entity->getGallery()) {
            $this->get('board.helper')->addParam('cover', $entity->getGallery()->getCover());
        }

        $this->get('board.helper')->addAviary();

        return $this->render('ClabBoardBundle:Social:post.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function quickPostAction($contextPk, $backUrl = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $post = new SocialPost();

        $shareSocialNetworks = $this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'share_social_networks');
        $isOnline = $this->get('app_admin.subscription_manager')->isOnline($this->get('board.helper')->getProxy());
        $form = $this->createForm(new SocialPostType(array('share_social_networks' => $shareSocialNetworks, 'is_online' => $isOnline)), $post);

        if ($form->handleRequest($request)->isValid()) {
            $post->setRestaurant($this->get('board.helper')->getProxy());
            $em->persist($post);
            $em->flush();

            if ($form->has('add_link')) {
                $socialManager = $this->get('clab.social_manager');
                $socialManager->pushSocialPost(
                    $post,
                    $form->get('add_link')->getData(),
                    array(
                        'facebook' => $form->get('to_facebook')->getData(),
                        'twitter' => $form->get('to_twitter')->getData(),
                    )
                );
            }

            $this->get('session')->getFlashBag()->add('success', 'Votre message a bien été envoyé !');
        }

        return $this->redirectToRoute('board_dashboard', array('context' => 'restaurant', 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function configurationRemoveAction($contextPk, $type)
    {
        $em = $this->getDoctrine()->getManager();
        $context = $this->getRequest()->get('context') ? $this->getRequest()->get('context') : 'restaurant';
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($this->getRequest()->getMethod() == 'POST') {
            if ($type == 'facebook') {
                $socialProfile = $this->get('board.helper')->getProxy()->getSocialProfile();
                $socialProfile->setFacebookAccessToken(null);
                $socialProfile->setFacebookId(null);
                $socialProfile->setFacebookData(null);
                $this->get('board.helper')->getProxy()->setFacebookPage(null);

                foreach ($socialProfile->getFacebookPages() as $page) {
                    $page->remove();
                }
            } elseif ($type == 'twitter') {
                $socialProfile = $this->get('board.helper')->getProxy()->getSocialProfile();
                $socialProfile->setTwitterAccessToken(null);
                $socialProfile->setTwitterAccessSecret(null);
                $socialProfile->setTwitterId(null);
                $socialProfile->setTwitterData(null);
            }

            $em->flush();
        }

        if ($backUrl = $this->getRequest()->get('backUrl')) {
            return $this->redirect($backUrl);
        }

        return $this->redirectToRoute('board_settings', array('contextPk' => $contextPk, 'type' => 'social'));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function chooseFacebookPageAction($contextPk, $type = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $context = $this->getRequest()->get('context') ? $this->getRequest()->get('context') : 'restaurant';
        $this->get('board.helper')->initContext($context, $contextPk);

        $socialManager = $this->get('clab.social_manager');

        if ($type == 'multisite') {
            $site = $this->get('clab_multisite.multisite_manager')->getOrCreateForRestaurant($this->get('board.helper')->getProxy(), true);
        }

        try {
            $socialManager->initSocialProfile($this->get('board.helper')->getProxy(), true);
        } catch (FacebookRequestException $e) {
            return new Response(400, 'Votre connexion avec Facebook ne semble plus valide. Veuillez vous déconnecter et vous reconnecter.');
        }

        $form = $this->createFormBuilder()
            ->add('page', 'entity', array(
                'class' => 'Clab\SocialBundle\Entity\SocialFacebookPage',
                'label' => 'Votre page facebook',
                'required' => true,
                'expanded' => true,
                'choices' => $this->get('board.helper')->getProxy()->getSocialProfile()->getFacebookPages(),
                'data' => $this->get('board.helper')->getProxy()->getFacebookPage(),
            ))
        ->getForm();

        $backUrl = $this->getRequest()->get('backUrl');

        if ($form->handleRequest($request)->isValid()) {
            $currentPage = $form->get('page')->getData();

            $this->get('board.helper')->getProxy()->setFacebookPage($currentPage);

            $em->flush();

            if ($backUrl) {
                return $this->redirect($backUrl);
            }

            return $this->redirectToRoute('board_settings', array('contextPk' => $contextPk, 'type' => 'social'));
        }

        $this->get('board.helper')->addParam('form', $form->createView());
        $this->get('board.helper')->addParam('type', $type);
        $this->get('board.helper')->addParam('backUrl', $backUrl);
        $this->get('board.helper')->addParam('context', $context);

        return $this->render('ClabBoardBundle:Social:chooseFacebookPage.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function addPageTabAction($contextPk, $tab, $backUrl)
    {
        $context = $this->getRequest()->get('context') ? $this->getRequest()->get('context') : 'restaurant';
        $this->get('board.helper')->initContext($context, $contextPk);

        $success = $this->container->get('clab_board.facebook_manager')->addPageTab($this->get('board.helper')->getProxy()->getFacebookPage(), $tab);

        if ($success) {
            $this->get('session')->getFlashBag()->add('success', 'L\'onglet a bien été ajouté');
        } else {
            $this->get('session')->getFlashBag()->add('notice', 'Une erreur est survenue');
        }

        return $this->redirect(urldecode($backUrl));
    }
}
