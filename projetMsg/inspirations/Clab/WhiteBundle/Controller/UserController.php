<?php

namespace Clab\WhiteBundle\Controller;

use Clab\RestaurantBundle\Entity\Product;
use Clab\WhiteBundle\Form\Type\RegisterType;
use Clab\WhiteBundle\Form\Type\UserType;
use Clab\WhiteBundle\Form\Type\UserAddressType;
use Clab\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Stripe\Error\Base;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\Security\Core\Security;


class UserController extends BaseController
{
    public function loginAction(Request $request, $isModal = null)
    {
        $session = $request->getSession();

        if (class_exists('\Symfony\Component\Security\Core\Security')) {
            $authErrorKey = Security::AUTHENTICATION_ERROR;
            $lastUsernameKey = Security::LAST_USERNAME;
        } else {
            // BC for SF < 2.6
            $authErrorKey = SecurityContextInterface::AUTHENTICATION_ERROR;
            $lastUsernameKey = SecurityContextInterface::LAST_USERNAME;
        }

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
        } elseif (null !== $session && $session->has($authErrorKey)) {
            $error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }

        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        if ($this->has('security.csrf.token_manager')) {
            $csrfToken = $this->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
        } else {
            // BC for SF < 2.4
            $csrfToken = $this->has('form.csrf_provider')
                ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
                : null;
        }
        if ($isModal == true) {
            return $this->render('ClabWhiteBundle:User:login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
            ));
        } else {

            return $this->redirectToRoute('clab_white_homepage');

            /*$userManager = $this->container->get('fos_user.user_manager');
            $dispatcher = $this->container->get('event_dispatcher');

            $user = $userManager->createUser();
            $user->setEnabled(true);
            $form = $this->createForm(new RegisterType(), $user);
            if ($form->isValid()) {

                    $array = array('success' => true, 'cover' => 'images/blank.png');
                    $response = new Response(json_encode($array));
                    $userManager->updateUser($user);
                    $event = new FormEvent($form, $request);
                    $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
                    $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
                    $this->getDoctrine()->getManager()->flush();
                    $this->get('clab_core.mail_manager')->registerMail($user);


                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $userManager->updateUser($user);

                $response = $this->redirectToRoute('clab_white_homepage');

                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $response;
            }

            return $this->render('ClabWhiteBundle:User:login-simple.html.twig', array(
                'last_username' => $lastUsername,
                'form' => $form->createView(),
                'error' => $error,
                'csrf_token' => $csrfToken,
            ));
            */
        }
    }

    public function registerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $params = array();
        if ($next = $request->get('next')) {
            $params = array('next', $next);
        }

        $userManager = $this->container->get('fos_user.user_manager');
        $dispatcher = $this->container->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(new RegisterType(), $user);
        $form->handleRequest($request);
        if (!$form->isValid() && $request->isXmlHttpRequest()) {
            $array = array('success' => false, 'message' => 'Email déjà utilisé ou invalide');
            $response = new Response(json_encode($array));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        }
        if ($form->isValid()) {
            if ($request->isXmlHttpRequest()) {
                $array = array('success' => true, 'cover' => 'images/blank.png');
                $response = new Response(json_encode($array));
                $response->headers->set('Content-Type', 'application/json');
                $userManager->updateUser($user);
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
                $this->getDoctrine()->getManager()->flush();
                $this->get('clab_core.mail_manager')->registerMail($user);

                return $response;
            }
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $userManager->updateUser($user);

                $response = $this->redirectToRoute('clab_white_homepage');

            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        $params['form'] = $form->createView();

        return $this->render('ClabWhiteBundle:User:register.html.twig', $params);
    }

    public function profileAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }

        $session = $this->get('session');
        $slug = $session->get('iframe_restaurant');

        $cards = $this->get('clab_stripe.customer.manager')->listCards($this->getUser());

        $cartLink = $session->has('current-order') ? $session->get('current-order') : $this->generateUrl('clab_white_homepage');

        if ($session->get('current_restaurant')) {
            if ($session->get(sprintf('%s_delivery_address_text', $session->get('current_restaurant')))) {
                $params['delivery'] = $session->get(sprintf('%s_delivery_address_text',
                    $session->get('current_restaurant')));
                $params['fullDeliveryDetails'] = true;
                $address = $session->get(sprintf('%s_delivery_address', $session->get('current_restaurant')));

                $lat = $address->getLatitude();
                $lng = $address->getLongitude();
            } else {
                $cartLink = $this->generateUrl('clab_white_order_home', array('slug' => $session->get('current_restaurant')));
            }
        }

        if ($cards !== false) {
            $cards = $cards->__toArray(true)['data'];
            $countries = array(
                'AT',
                'US',
                'BE',
                'BG',
                'HR',
                'CY',
                'CZ',
                'DK',
                'EE',
                'FI',
                'FR',
                'DE',
                'GB',
                'EL',
                'HU',
                'IE',
                'IT',
                'LV',
                'LT',
                'LU',
                'MT',
                'NL',
                'NO',
                'PL',
                'PT',
                'RO',
                'SK',
                'SI',
                'ES',
                'SE',
                'CH'
            );
            foreach ($cards as $key => $card) {
                if (!in_array($card['country'], $countries) || $card['name'] == 'CALL_CENTER_CARD') {
                    unset($cards[$key]);
                }
            }
        } else {
            $cards = null;
        }

        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new UserType(), $this->getUser());

        if ($form->handleRequest($request)->isValid()) {

            $this->get('fos_user.user_manager')->updateUser($this->getUser());
            $em->flush();

            return $this->redirectToRoute('clab_white_user_profile');
        }

        $params = array(
                'form' => $form->createView(),
                'user' => $this->getUser(),
                'cards'=> $cards,
                'cartLink' => $cartLink,
                'publishableKey' => $this->container->getParameter('stripe_publishable_key')
            );

        return $this->render('ClabWhiteBundle:User:profile.html.twig', $params);
    }

    public function favoriteAction() {
        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }

        $favorites = $this->getUser()->getFavoriteProducts();

        $params = array(
            'user' => $this->getUser(),
            'products'=>$favorites
        );

        return $this->render('ClabWhiteBundle:User:favorites.html.twig', $params);
    }

    public function userAddressAction() {
        $session = $this->get('session');
        $slug = $session->get('iframe_restaurant');
        
        $user = $this->getUser();

        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }

        $addresses = $user->getAddresses();
        $homeAddress = $user->getHomeAddress();

        $cartLink = $session->has('current-order') ? $session->get('current-order') : $this->generateUrl('clab_white_homepage');

        return $this->render('ClabWhiteBundle:User:userAddress.html.twig', array(
            'user' => $user,
            'addresses' => $addresses,
            'homeAddress'=> $homeAddress,
            'cartLink' => $cartLink
        ));
    }

    public function deleteAddressAction($id) {
        $repository =  $this->getDoctrine()->getManager()->getRepository('Clab\LocationBundle\Entity\Address');
        $address = $repository->findOneBy(array('id' => $id));

        if (!$address) {
            throw $this->createNotFoundException('No address found');
        }

        $em = $this->getDoctrine()->getEntityManager();
        $em->remove($address);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'l\'addresse a bien été supprimmée');

        return $this->redirectToRoute('clab_white_user_address');
    }
    
    public function addAddressAction(Request $request) {
        $session = $this->get('session');
        $slug = $session->get('iframe_restaurant');
        $user = $this->getUser();

        $em = $this->getDoctrine()->getManager();

        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }

        $form = $this->createForm(new UserAddressType());

        if ($form->handleRequest($request)->isValid()) {

           $address = $form->getData();
           $address->setUser($user);

           $em->persist($user);
           $em->persist($address);

           $em->flush();

           $this->get('session')->getFlashBag()->add('success', 'Les adresses ont bien été sauvegardées');

            return $this->redirectToRoute('clab_white_user_address');
        }

        $cartLink = $session->has('current-order') ? $session->get('current-order') : $this->generateUrl('clab_white_homepage');

        return $this->render('ClabWhiteBundle:User:addAddress.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
            'cartLink' => $cartLink
        ));
    }

    public function editAddressAction($id, Request $request)
    {
        $user = $this->getUser();

        $repository =  $this->getDoctrine()->getManager()->getRepository('Clab\LocationBundle\Entity\Address');
        $address = $repository->findOneBy(array('id' => $id));
        
        $session = $this->get('session');
        $slug = $session->get('iframe_restaurant');

        $em = $this->getDoctrine()->getManager();

        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }

        $form = $this->createForm(new UserAddressType(), $address);

        if ($form->handleRequest($request)->isValid()) {

            $address = $form->getData();
            $address->setUser($user);

            $em->persist($user);
            $em->persist($address);

            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Les adresses ont bien été sauvegardées');

            return $this->redirectToRoute('clab_white_user_address');
        }

        return $this->render('ClabWhiteBundle:User:editAddress.html.twig', array(
            'form' => $form->createView(),
            'user' => $user,
            'address'=>$address
        ));
    }

    public function setHomeAddressAction($id) {
        $user = $this->getUser();

        $repository =  $this->getDoctrine()->getManager()->getRepository('Clab\LocationBundle\Entity\Address');
        $address = $repository->findOneBy(array('id' => $id));

        $session = $this->get('session');
        $slug = $session->get('iframe_restaurant');

        $em = $this->getDoctrine()->getManager();

        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }

        $user->setHomeAddress($address);

        $em->persist($user);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Votre adresse"'.$address->getName().'"est désormais votre adresse par défault');

        return $this->redirectToRoute('clab_white_user_address');
    }

    public function removeHomeAddressAction() {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();

        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }

        $user->setHomeAddress(null);

        $em->persist($user);
        $em->flush();

        $this->get('session')->getFlashBag()->add('success', 'Adresse par défault supprimée');

        return $this->redirectToRoute('clab_white_user_address');
    }


    public function orderHistoryAction(Request $request)
    {


        $client = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->findOneBy(array(
            'slug' => 'matsuri',
        ));
        $restaurants = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findBy(array(
            'client' => $client,
        ));
        $session = $this->get('session');
        $cartLink = $session->has('current-order') ? $session->get('current-order') : $this->generateUrl('clab_white_homepage');

        return $this->render('ClabWhiteBundle:User:order-history.html.twig', array(
            'orders' => $this->getUser()->getOrders(),
            'cartLink' => $cartLink
        ));
    }

    public function reductionsAction(Request $request) {
        $user = $this->getUser();
        $loyalties = $user->getLoyalties();
        $discounts = $user->getDiscounts();
        $session = $this->get('session');
        $cartLink = $session->has('current-order') ? $session->get('current-order') : $this->generateUrl('clab_white_homepage');


        return $this->render('ClabWhiteBundle:User:reductions.html.twig', array(
            'user' => $user,
            'loyalties'=>$loyalties,
            'discounts'=>$discounts,
            'cartLink' => $cartLink
        ));
    }

    public function logoutFacebookAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }
        $user = $this->getUser();
        $user->setFacebookAccessToken(null);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', 'Votre compte n\'est plus lié au site');
        $referer = $request->headers->get('referer');

        return $this->redirect($referer);
    }

    public function paymentAction(Request $request)
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }
        try {
            $cards = $this->get('clab_stripe.customer.manager')->listCards($this->getUser());
            if(!is_null($cards)) {
                $cards = $cards->__toArray(true)['data'];
            }
        } catch (Base $e) {
            $cards = null;
        }

        $session = $this->get('session');
        $cartLink = $session->has('current-order') ? $session->get('current-order') : $this->generateUrl('clab_white_homepage');

        return $this->render('ClabWhiteBundle:User:payment.html.twig', array(
            'user' => $this->getUser(),
            'cards' => $cards,
            'publishableKey' => $this->getParameter('stripe_publishable_key'),
            'cartLink' => $cartLink
        ));
    }

    /**
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"repository_method" = "findOneAvailable"})
     */
    public function updateFavoriteProductAction(Product $product, $add, $origin = null) {
        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_white_user_login');
        }

        $user = $this->getUser();

        if($add == 1) {
            $user->addFavoriteProduct($product);
        } else if ($add == 0) {
            $user->removeFavoriteProduct($product);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new Response('true');
    }
    
}
