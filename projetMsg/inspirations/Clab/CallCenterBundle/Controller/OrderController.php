<?php

namespace Clab\CallCenterBundle\Controller;

use Clab\BoardBundle\Entity\Client;
use Clab\BoardBundle\Entity\UserDataBase;
use Clab\RestaurantBundle\Entity\Product;
use Clab\ShopBundle\Entity\Loyalty;
use Clab\ShopBundle\Manager\OrderManager;
use Clab\StripeBundle\Form\Type\CardFormType;
use Clab\UserBundle\Entity\User;
use ElephantIO\Engine\SocketIO\Version1X;
use Exception;
use Proxies\__CG__\Clab\ShopBundle\Entity\OrderType;
use Stripe\Customer;
use Stripe\Error\Base;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\OrderDetail;
use Clab\LocationBundle\Entity\Address;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\VarDumper\VarDumper;

class OrderController extends Controller
{
    protected function isIframe(Request $request)
    {
        return strpos($request->getHost(), $this->getParameter('embedDomain')) !== false;
    }

    public function orderAction(Request $request)
    {
        $session = $this->get('session');
        $restaurantSlug = $session->get('restaurant');
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
            'slug' => $restaurantSlug
        ));
        if(!$restaurant)
        {
            $this->addFlash('Veuillez choisir une restaurant valide','error');
            return $this->redirectToRoute('clab_call_center_restaurant_list');
        }

        /**
         * @var $orderManager OrderManager
         */
        $orderManager = $this->get('app_shop.order_manager');
        $cartManager = $this->get('app_shop.cart_manager');
        $em = $this->getDoctrine()->getManager();
        $cart = $this->get('app_shop.cart_manager')->getCart($restaurant);
        $area = null;

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        $currentUser = $this->getDoctrine()->getEntityManager()->getRepository(User::class)->find($this->get('session')->get('customer'));
        $company = null;

        if (!$this->getUser()) {
            return $this->redirectToRoute('clab_call_center_homepage');
        }

        if (!$cart) {
            return $this->redirectToRoute('clab_call_center_order');
        }

        $timeOrderChosen = new \DateTime(sprintf("%s %s",$session->get('day'),$session->get('time')));

        $params = array('day'=> $timeOrderChosen, 'callcenter' => true);

        $hasOrderedInDelivery = count($em->getRepository(OrderDetail::class)->findBy(array("profile" => $currentUser, "orderType" => 3)));
        $params['hasOrderedInDelivery'] = $hasOrderedInDelivery;


        if ($cart->getOrderType() == 3) {
            $params['delivery'] = $session->get('address');
        } else {
            $session->remove($cart->getRestaurant()->getSlug() . '_delivery_address');
        }

        if($currentUser->getCompany() && $currentUser->getCompany()->isOnline()){
            $params['companyPaymentAvailable'] = true;
            $company = $currentUser->getCompany();
        }

        $form = $orderManager->getOrderForm($cart, $currentUser, $params);
        $cartManager->setFreeSauces($cart);

        $loyalties = $em->getRepository(Loyalty::class)->findAvailableForCart($cart, $currentUser);

        $sauceForm = $orderManager->getSauceForm($cart);

        if ($form->handleRequest($request)->isValid()) {
            $woodSticks = $form->get('woodSticks')->getData();
            $cart->setWoodSticks($woodSticks);

            $day = new \DateTime($session->get('day'));

            $order = $orderManager->computeOrderForm($form, array('day' => $day, 'company' => $company, 'isCallCenter' => true));

            if($order->getOnlinePayment() == true)
            {
                if(is_null($form->get('card')->getData()) || $form->get('card')->getData()==""){
                    $this->addFlash('notice','Veuillez selectionner un moyen de paiement');
                    return $this->redirectToRoute('clab_white_order_receipt', array('hash' => $order->getHash()));
                }
                try
                {
                    Stripe::setApiKey($this->getParameter('stripe_secret_key'));
                    $customer = Customer::retrieve($currentUser->getStripeCustomerId());
                    $cardId = $form->get('card')->getData();
                    $card = $customer->sources->retrieve($cardId);
                    $charge = $this->get('clab_stripe.customer.manager')->chargeCardForClickeat($currentUser,$order->getPrice()*100,$card);
                    $this->getDoctrine()->getManager()->flush();
                }
                catch(Base $e)
                {
                    $this->addFlash('notice',$e->getMessage());
                    return $this->redirectToRoute('clab_call_center_order_receipt', array('hash' => $order->getHash()));
                }
                if($charge == true)
                {
                    $orderManager->validateOrder($order, true);
                }
                else
                {
                    $this->addFlash('notice', 'Erreur lors du paiement');
                    return $this->redirectToRoute('clab_call_center_order_receipt', array('hash' => $order->getHash()));
                }

            }

            if ($form->get('phone')->getData() !== null) {
                $currentUser->setPhone($form->get('phone')->getData());
                $this->getDoctrine()->getManager()->flush();
            }
            if (!is_null($session->get($restaurant->getSlug().'_delivery_address'))) {
                $address = $session->get($restaurant->getSlug().'_delivery_address');
                if ($address->getStreet() != $form->get('address')->getData()) {
                    $coordinates = $this->get('app_location.location_manager')->getCoordinateFromAddress($form->get('address')->getData());
                    $lat = $coordinates['latitude'];
                    $long = $coordinates['longitude'];

                    $address->setStreet($form->get('address')->getData());
                    $address->setLatitude($lat);
                    $address->setLongitude($long);
                    $address->setDoor($form->get('addressComment')->getData());
                    $address->setDoorCode($form->get('entryCode')->getData());
                    $address->setComment($form->get('addressComment')->getData());
                }
                $currentUser->addAddress($address);
                $this->getDoctrine()->getManager()->flush();
            }

            try {
                $client = new \ElephantIO\Client(new Version1X('http://localhost:8081'));
                $client->initialize();
                $client->emit('emitPHP', ['order' => $order->getId(), 'restaurant' => $restaurant->getSlug()]);
                $client->close();
            } catch (\Exception $e) {
            }

            $userDatabase = $this->getDoctrine()->getEntityManager()->getRepository('ClabBoardBundle:UserDataBase')->findOneBy(array(
                'user' => $currentUser,
                'restaurant' => $restaurant
            ));
            if (is_null($userDatabase)) {
                $userDatabase = new UserDataBase();
                $userDatabase->setRestaurant($restaurant);
                $userDatabase->setUser($currentUser );
                $userDatabase->setIsDeleted(false);
                if (!is_null($currentUser->getBirthday())) {
                    $userDatabase->setBirthday($currentUser->getBirthday());
                }
            }
            $userDatabase->setEmail($currentUser ->getEmail());

            if (!is_null($currentUser->getFirstName())) {
                $userDatabase->setFirstName($currentUser->getFirstName());
            }
            if (!is_null($currentUser->getLastName())) {
                $userDatabase->setLastName($currentUser->getLastName());
            }
            if (!is_null($currentUser->getPhone())) {
                $currentUser->getPhone();
                $userDatabase->setPhone($currentUser->getPhone());
            }
            if (!is_null($currentUser->getHomeAddress())) {
                $userDatabase->setHomeAddress($currentUser->getHomeAddress());
            }
            if (!is_null($currentUser->getJobAddress())) {
                $userDatabase->setJobAddress($currentUser->getJobAddress());
            }
            if (!is_null($currentUser->getSubscribedNewsletter())) {
                $userDatabase->setSubscribedNewsletter($currentUser->getSubscribedNewsletter());
            }
            $this->getDoctrine()->getEntityManager()->persist($userDatabase);
            $this->getDoctrine()->getEntityManager()->flush();

            return $this->redirectToRoute('clab_call_center_order_receipt', array('hash' => $order->getHash()));
        }

        $parentSaltySoja = $em->getRepository(Product::class)->findOneBy(array('slug' => 'sauce-soja-salée'));
        $parentSugarySoja = $em->getRepository(Product::class)->findOneBy(array('slug' => 'sauce-soja-sucrée'));
        $parentGinger = $em->getRepository(Product::class)->findOneBy(array('slug' => 'gingembre'));
        $parentWasabi = $em->getRepository(Product::class)->findOneBy(array('slug' => 'wasabi'));

        $saltySoja = $em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentSaltySoja);
        $sugarySoja = $em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentSugarySoja);
        $wasabi = $em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentWasabi);
        $ginger = $em->getRepository(Product::class)->getForRestaurantAndParent($restaurant, $parentGinger);

        $getPrice = ($cart->getOrderType() == 3) ? "getDeliveryPrice" : "getPrice";

        $selectedLoyalties = [];

        foreach ($cart->getLoyalties() as $loyalty) {
            $selectedLoyalties[] = $loyalty->getId();
        }

        return $this->render('ClabCallCenterBundle:Order:order.html.twig', array(
            'form' => $form->createView(),
            'cart' => $cart,
            'area' => $area,
            'loyalties' => $loyalties,
            'selectedLoyalties' => $selectedLoyalties,
            'sauceForm' => $sauceForm->createView(),
            'freeSauces' => $cart->getFreeSauces(),
            'saltySojaPrice' => $parentSaltySoja->$getPrice(),
            'sugarySojaPrice' => $parentSugarySoja->$getPrice(),
            'wasabiPrice' => $parentWasabi->$getPrice(),
            'gingerPrice' => $parentGinger->$getPrice(),
            'customer' => $currentUser,
            'currentUser' => $currentUser,
            'restaurant' => $restaurant,
            'currentUser' => $currentUser,
            'hasOrderedInDelivery' => $hasOrderedInDelivery,
            'iframe' => $this->isIframe($request),
            'publishableKey' => $this->container->getParameter('stripe_publishable_key'),
        ));
    }

    /**
     * @ParamConverter("order", class="ClabShopBundle:OrderDetail")
     */
    public function orderReceiptAction(Request $request, OrderDetail $order)
    {
        $session = $this->get('session');

        $currentUser = $this->getDoctrine()->getEntityManager()->getRepository(User::class)->find($session->get('customer'));

        if ($order->getProfile() != $currentUser) {
            throw $this->createNotFoundException();
        }

        switch ((int) $order->getState()) {
            case OrderDetail::ORDER_STATE_CANCELLED:
                return $this->render('ClabCallCenterBundle:Order:orderCancelled.html.twig', array('order' => $order, 'iframe' => $this->isIframe($request)));
                break;
            default:
                $address = $this->get('app_restaurant.timesheet_manager')->getOrderLocation($order);
                $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $order->getRestaurant()->getSlug()));
                $this->get('app_restaurant.timesheet_manager')->getTodayPlanning($restaurant);

                return $this->render('ClabCallCenterBundle:Order:orderReceipt.html.twig', array(
                    'address' => $address,
                    'order' => $order,
                    'customer' => $currentUser,
                    'restaurant' => $order->getRestaurant(),
                    'cancelAvailable' => $this->get('app_shop.order_manager')->cancelAvailable($order),
                    'iframe' => $this->isIframe($request),
                ));
                break;
        }
    }


    /**
     * @ParamConverter("order", class="ClabShopBundle:OrderDetail")
     */
    public function switchPaymentAction(Request $request, OrderDetail $order)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $this->get('session');
        $customer = $this->getDoctrine()->getRepository(User::class)->find($session->get('customer'));

        if ($order->getProfile() != $customer) {
            throw $this->createNotFoundException();
        }

        if ($order->getState() >= OrderDetail::ORDER_STATE_VALIDATED) {
            $this->addFlash('notice', $this->get('translator')->trans('clickeat.error.order.not_found'));

            return $this->redirectToRoute('clab_white_user_profile');
        }

        if ($request->isMethod('post')) {
            $order->setOnlinePayment(false);
            $order->setIsPaid(false);
            $em->flush();

            $this->container->get('app_shop.order_manager')->validateOrder($order);
        }

        return $this->redirectToRoute('clab_white_order_receipt', array('hash' => $order->getHash()));
    }

    /**
     * @ParamConverter("order", class="ClabShopBundle:OrderDetail")
     */
    public function switchPaymentOnlineAction(Request $request, OrderDetail $order)
    {
        $em = $this->getDoctrine()->getManager();

        $session = $this->get('session');
        $customer = $this->getDoctrine()->getRepository(User::class)->find($session->get('customer'));

        if ($order->getProfile() != $customer) {
            throw $this->createNotFoundException();
        }

        if (!$order || $order->getState() >= OrderDetail::ORDER_STATE_VALIDATED) {
            $this->addFlash('notice', $this->get('translator')->trans('clickeat.error.order.not_found'));

            return $this->redirectToRoute('clab_white_user_profile');
        }
        if ($request->isMethod('post')) {
            $order->setState(OrderDetail::ORDER_STATE_WAITING_PAYMENT);
            $order->setOnlinePayment(1);

            $em->flush();
        }

        return $this->redirectToRoute('clab_white_order_receipt', array('hash' => $order->getHash()));
    }

    /**
     * @ParamConverter("order", class="ClabShopBundle:OrderDetail")
     */
    public function cancelAction(Request $request, OrderDetail $order)
    {
        $session = $this->get('session');
        $customer = $this->getDoctrine()->getRepository(User::class)->find($session->get('customer'));

        if ($order->getProfile() != $customer) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('post') && $this->get('app_shop.order_manager')->cancelAvailable($order)) {
            $this->get('app_shop.order_manager')->cancelOrder($order);

        }

        return $this->redirectToRoute('clab_white_order_receipt', array('hash' => $order->getHash()));
    }

    /**
     * @ParamConverter("user", class="ClabUserBundle:User", options={"id" = "userId"})
     */
    public function createCardAction(User $user, Request $request)
    {
        $customerManager = $this->container->get('clab_stripe.customer.manager');
        $form = $this->createForm(new CardFormType(),array());
        $form->handleRequest($request);
        if ($form->isValid()) {
            try {
                $customerManager->newCard($user);
                $cards = $customerManager->listCards($user);
              return new JsonResponse($cards,200);
            } catch (Base $e) {

                return new JsonResponse($e->getMessage(),400);
            }
        }
        return $this->render('ClabCallCenterBundle:Order:add-card.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @ParamConverter("user", class="ClabUserBundle:User")
     */
    public function listCardsAction(User $user)
    {
        try {
            $cards = $this->get('clab_stripe.customer.manager')->listCards($user);
            return new JsonResponse($cards,200);
        } catch (Base $e) {
            return new JsonResponse($e->getMessage(),400);
        }
    }

    /**
     * @ParamConverter("user", class="ClabUserBundle:User")
     */
    public function deleteCreditCardAction(User $user, $card)
    {
        try
        {
            $this->get('clab_stripe.customer.manager')->deleteCard($user, $card);
            return new JsonResponse('success',200);
        }
        catch(Base $e)
        {
            return new JsonResponse($e->getMessage(),400);
        }

    }
}