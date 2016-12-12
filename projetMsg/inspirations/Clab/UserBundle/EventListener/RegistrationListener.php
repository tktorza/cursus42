<?php

namespace Clab\UserBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use Clab\UserBundle\Event\AuthenticationEvent;
use Clab\UserBundle\Event\RegistrationEvent;
use Clab\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class RegistrationListener implements EventSubscriberInterface
{
    protected $em;
    protected $container;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_COMPLETED => 'createEvent',
            //FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        );
    }

    public function createEvent(FilterUserResponseEvent $event)
    {
        $registrationEvent = new RegistrationEvent($event->getUser());
        $this->registrationCompleted($registrationEvent);
    }

    public function registrationCompleted(RegistrationEvent $event)
    {
        $request = $this->container->get('request');
        $session = $request->getSession();
        $user = $event->getUser();

        $host = $request->getHost();

        if ($iframeSource = $session->get('iframe_restaurant')) {
            $user->setSource($iframeSource);
        }

        if ($session->get('facebook_connect_source')) {
            $source = $session->get('facebook_connect_source');
            $facebookPageId = $session->get('facebook_connect_source_fb_page');

            $user->setSource($source);

            if ($facebookPageId) {
                $facebookPage = $this->em->getRepository('ClabSocialBundle:SocialFacebookPage')->findOneBy(array('id' => $facebookPageId, 'is_online' => true));

                if ($facebookPage) {
                    $user->setSourceFacebookPage($facebookPage);
                }
            }
        }

        $source = strtolower($user->getSource());

        $user->addRole('ROLE_MEMBER');

        // check registration request with some roles
        $this->container->get('app_user.registration_request_manager')->checkRequestsForUser($user);

        $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
            $user,
            null,
            'main',
            $user->getRoles()
        );
        $this->container->get('security.context')->setToken($token);

        // faire un refresh du user a l'aide du user manager
        $userManager = $this->container->get('fos_user.user_manager');

        $mailManager = $this->container->get('clab_core.mail_manager');
        $userManager->refreshUser($user);

        // send mail if from web
        if ($source == 'ttt') {
            $mailManager = $this->container->get('clab_ttt.mail_manager');
            $mailManager->registerMail($user);
        } elseif ($source == 'clickeat') {
            $mailManager->registerMail($user);

            // check sponsor
            if ($session->get('registration_sponsor')) {
                $sponsor = $this->em->getRepository('ClabUserBundle:User')->find($session->get('registration_sponsor'));

                if ($sponsor) {
                    $user->setSponsor($sponsor);
                }
            }
        } else {
            $restaurant = $this->em->getRepository('ClabRestaurantBundle:Restaurant')
                ->findOneBy(array('slug' => $source));

            if ($restaurant) {
                $mailManager = $this->container->get('clab_multisite.mail_manager');
                $mailManager->registerMail($user, $restaurant);
            }
        }

        if ($session->get('cart')) {
            $sessionCart = $session->get('cart');
            $cart = $this->em->getRepository('ClabShopBundle:Cart')->find($sessionCart['id']);

            if ($cart && is_object($cart)) {
                $cart->setProfile($user);
            }
        }

        if ($this->container->get('app_shop.loyalty_manager')) {
            $this->container->get('app_shop.loyalty_manager')->generateFirstLoyalties($user);
        }

        $this->em->flush();

        if ($user->getNewsletterClickeat()) {
            try {
                $this->container->get('app_people.mailchimp_manager')->subscribeToList($user, 'clickeat');
            } catch (\Exception $e) {
            }
        }

        if ($user->getNewsletterTTT()) {
            try {
                $this->container->get('app_people.mailchimp_manager')->subscribeToList($user, 'ttt');
            } catch (\Exception $e) {
            }
        }

        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch('app_user.registration_clear', new RegistrationEvent($user));

        $dispatcher = $this->container->get('event_dispatcher');
        $dispatcher->dispatch('app_user.authentication.success', new AuthenticationEvent($request, $user));
    }

    public function onRegistrationSuccess(FormEvent $event, Request $request)
    {
        if (!($request->isXmlHttpRequest())) {
            $referer_url = $request->headers->get('referer');
            $request = $this->container->get('request');
            $host = $request->getHost();
            $source = 'clickeat';

            if ($request->get('_target_path')) {
                $event->setResponse(new RedirectResponse($request->get('_target_path')));
            } elseif (strpos($host, 'pro') !== false) {
                $url = $this->container->get('router')->generate('board_dashboard');
                $event->setResponse(new RedirectResponse($url));

                try {
                    $session = $request->getSession();
                    if ($session->get('pro_register_redirect')) {
                        $event->setResponse(new RedirectResponse($session->get('pro_register_redirect')));
                        $session->remove('pro_register_redirect');
                    }
                } catch (\Exception $e) {
                }
            } elseif (strpos($host, 'ttt') !== false) {
                $url = $this->container->get('router')->generate('ttt_user_profile', array('registration' => 'done'));
                $event->setResponse(new RedirectResponse($url));
            } elseif ($referer_url && strpos($host, 'click-eat') !== false) {
                $event->setResponse(new RedirectResponse($referer_url));
            } else {
                $url = $this->container->get('router')->generate('clickeat_user_confirm_phone');
                $event->setResponse(new RedirectResponse($url));
            }
        }
    }
}
