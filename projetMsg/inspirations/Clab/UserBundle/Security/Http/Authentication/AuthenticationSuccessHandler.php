<?php
namespace Clab\UserBundle\Security\Http\Authentication;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;

use Clab\UserBundle\Event\AuthenticationEvent;

class AuthenticationSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    protected $container;
    protected $em;

    public function __construct(HttpUtils $httpUtils, ContainerInterface $container, array $options = array())
    {
        $this->httpUtils   = $httpUtils;

        $this->options = array_merge(array(
            'always_use_default_target_path' => false,
            'default_target_path'            => '/',
            'login_path'                     => '/login',
            'target_path_parameter'          => '_target_path',
            'use_referer'                    => false,
        ), $options);

        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        $user = $token->getUser();

        $event = new AuthenticationEvent($request, $user);
        $this->handleAuthenticationSuccess($event);

        return parent::onAuthenticationSuccess($request, $token);
    }

    public function handleAuthenticationSuccess(AuthenticationEvent $event)
    {
        $request = $event->getRequest();
        $user = $event->getUser();

        $tttWellDone = false;

        if($request->getSession()->get('tttNotification')) {
            $user->setTttEventNotifications(true);
            $request->getSession()->set('tttNotification', null);
            $this->em->flush();
            $tttWellDone = true;
        }

        if($request->getSession()->get('tttNotificationBookmarks')) {
            $user->setTttEventNotificationsBookmarks(true);
            $request->getSession()->set('tttNotificationBookmarks', null);
            $this->em->flush();
            $tttWellDone = true;
        }

        if($address = $request->getSession()->get('tttHomeAddress')) {
            if($user->getHomeAddress() && !$user->getHomeAddress()->isEmpty()) {
                $request->getSession()->getFlashBag()->add('launchModal', $this->container->get('router')->generate('ttt_notification_address_conflict'));
            } else {
                $address = $this->container->get('app_location.location_manager')->transformAddress($address);
                $user->setHomeAddress($address);
                $this->em->persist($address);
                $request->getSession()->set('tttHomeAddress', null);
                $this->em->flush();
                $tttWellDone = true;
            }
        }

        if($address = $request->getSession()->get('tttJobAddress')) {
            if($user->getJobAddress() && !$user->getJobAddress()->isEmpty()) {
                $request->getSession()->getFlashBag()->add('launchModal', $this->container->get('router')->generate('ttt_notification_address_conflict'));
            } else {
                $address = $this->container->get('app_location.location_manager')->transformAddress($address);
                $user->setJobAddress($address);
                $this->em->persist($address);
                $request->getSession()->set('tttJobAddress', null);
                $this->em->flush();
                $tttWellDone = true;
            }
        }

        $userManager = $this->container->get('app_user.user_manager');
        $bookmarks = unserialize($request->getSession()->get('restaurantBookmarks'));
        if(is_array($bookmarks)) {
            foreach ($bookmarks as $bookmarkId) {
                $restaurant = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->find($bookmarkId);
                if($restaurant) {
                    $userManager->addFavorite($user, $restaurant);
                    $request->getSession()->getFlashBag()->add('launchModal', $this->container->get('router')->generate('ttt_notification_confirmation', array('type' => 'bookmark')));
                }
            }

            $this->em->flush();
            $request->getSession()->set('restaurantBookmarks', null);
        }

        if($tttWellDone) {
            $request->getSession()->getFlashBag()->add('launchModal', $this->container->get('router')->generate('ttt_notification_confirmation'));
        }
    }
}
