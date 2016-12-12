<?php

namespace Clab\WhiteBundle\Controller;

use Clab\BoardBundle\Entity\Client;
use Clab\BoardBundle\Entity\UserDataBase;
use Clab\DeliveryBundle\Entity\DeliveryDay;
use Clab\RestaurantBundle\Entity\Product;
use Clab\ShopBundle\Entity\Loyalty;
use Clab\ShopBundle\Entity\OrderType;
use Clab\StripeBundle\Form\Type\CardFormType;
use Clab\UserBundle\Entity\User;
use ElephantIO\Engine\SocketIO\Version1X;
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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Validator\Constraints\DateTime;

class OrderController extends Controller
{

    public function chainStoreAction()
    {
        $clientSlug = $this->getParameter('client_name');
        $client = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->findOneBy(array(
            'slug' => $clientSlug,
        ));

        $user = $this->getUser();

        $session = $this->get('session');
        $cartLink = $session->has('current-order') ? $session->get('current-order') : $this->generateUrl('clab_white_homepage');

        $session->clear();
        $session->set('current-order', $cartLink);

        return $this->render('ClabWhiteBundle:Order:chainStore.html.twig', array(
            'client' => $client,
            'user'  => $user,
            'cartLink' => $cartLink
        ));
    }

    public function serpAction(Request $request)
    {
        $clientSlug = $this->getParameter('client_name');
        $client = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->findOneBy(array(
            'slug' => $clientSlug,
        ));

        $address = $request->get('address');

        $street = $request->get('street');
        $city = $request->get('city');
        $zip = $request->get('zip');

        $isDelivery = $request->get('isDelivery');

        $options =array();

        $options['type'] = $isDelivery ? 'delivery' : 'preorder';

        if (!$address) {
           return $this->redirectToRoute('clab_white_homepage');
        }

        $coordinates = $this->get('app_location.location_manager')->getCoordinateFromAddress($address);

        $planning = array();

        if (!$isDelivery) {
            $restaurants = $this->getDoctrine()->getRepository(Restaurant::class)->findNearbyPaginatedWithTag($coordinates['latitude'],
                $coordinates['longitude'], null, $options);
        } else {
            $deliveryDays = $this->getDoctrine()->getRepository(DeliveryDay::class)->getAvailableForDay(new \DateTime());
            $selectedRestaurant = null;
            $selectedArea = null;
            $filterResto = [];
            $distanceMin = INF;

            foreach ($deliveryDays as $deliveryDay) {
                $response = $this->get('clab_delivery.delivery_manager')->checkLocationApi($address, $deliveryDay);
                if($response) {
                    $restaurant = $deliveryDay->getRestaurant();
                    if(! in_array($restaurant, $filterResto)) {
                        $distance = $this->get('clab_delivery.delivery_manager')->haversine($coordinates['latitude'],  $coordinates['longitude'], $restaurant->getAddress()->getLatitude(), $restaurant->getAddress()->getLongitude());
                        if($distance < $distanceMin) {
                            $selectedRestaurant = $restaurant;
                            $selectedArea = $response['area'];
                        }

                        $filterResto[] = $restaurant;
                    }
                }
            }

            if($selectedRestaurant && $selectedArea) {
                $this->get('session')->set('areaDelivery', $selectedArea->getSlug());
                return $this->redirectToRoute('clab_white_home',
                    array(
                        'slug' => $selectedRestaurant->getSlug(),
                        'isDelivery' => true,
                        'address' => $address,
                        'street' => $street,
                        'city' => $city,
                        'zip' => $zip,
                        'lat' => $coordinates['latitude'],
                        'lng' => $coordinates['longitude']
                        )
                    )
                ;
            } else {
                $this->get('session')->getFlashBag()->add('notice','Aucun restaurant ne propose de livraison pour l\'adresse indiquée');

                return $this->redirectToRoute('clab_white_homepage');
            }
        }

        foreach ($restaurants as $restaurantData) {
            $restaurant = $restaurantData[0];
            $planning[$restaurant->getSlug()] = $this->get('app_restaurant.timesheet_manager')->getDayPlanning($restaurant, new \DateTime());
        }

        $session = $this->get('session');
        $cartLink = $session->has('current-order') ? $session->get('current-order') : $this->generateUrl('clab_white_homepage');

        return $this->render('ClabWhiteBundle:Order:serp.html.twig', array(
            'restaurants' => $restaurants,
            'client' => $client,
            'planning' => $planning,
            'isDelivery' => $isDelivery,
            'address' => $address,
            'street' => $street,
            'city' => $city,
            'zip' => $zip,
            'lat' => $coordinates['latitude'],
            'lng' => $coordinates['longitude'],
            'cartLink' => $cartLink
        ));
    }

    public function isIframe(Request $request)
    {
        return strpos($request->getHost(), $this->getParameter('embedDomain')) !== false;
    }

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function homeAction(Request $request, Restaurant $restaurant)
    {
        $planning = $this->get('app_restaurant.timesheet_manager')->getDayPlanning($restaurant, new \DateTime());

        if($restaurant->getStatus() != 3000 || !$restaurant->getIsOpen()) {
            $this->addFlash('notice', 'Le restaurant séléctionné n\'est pas ouvert à la commande en ligne');
            return $this->redirectToRoute('clab_white_homepage');
        }

        $isDelivery = $request->query->get('isDelivery');
        $lat = $request->query->get('lat');
        $lng = $request->query->get('lng');
        $addressText = $request->query->get('address');

        $street = $request->get('street');
        $city = $request->get('city');
        $zip = $request->get('zip');

        $session = $this->get('session');

        $session->set('current-order', $request->getUri());

        $menu = $this->get('clab.restaurant_menu_manager')->getDefaultMenuForRestaurant($restaurant);

        if ($isDelivery) {
            $address = new Address();

            $address->setStreet($street ? $street : $addressText);
            $address->setCity($city ? $city : '');
            $address->setZip($zip ? $zip : '');
            $address->setLatitude($lat);
            $address->setLongitude($lng);

            $session->set(sprintf('%s_delivery_address', $restaurant->getSlug()), $address);
            $session->set(sprintf('%s_delivery_address_text', $restaurant->getSlug()), $addressText);
            $menu = $this->get('clab.restaurant_menu_manager')->getDeliveryMenuForRestaurant($restaurant);
        }

        $session->set('current_restaurant', $restaurant->getSlug());

        $categories = $this->get('app_restaurant.product_category_manager')->getAvailableForRestaurant($restaurant);
        $products = $this->get('app_restaurant.product_manager')->getAvailableForRestaurantMenu($menu);
        $pdjs = $this->get('app_restaurant.product_manager')->getAvailablePDJForRestaurantMenu($menu);
        $products = array_merge($products, $pdjs);
        $options = $this->get('app_restaurant.product_option_manager')->getAvailableForProducts($products);
        $meals = $this->get('app_restaurant.meal_manager')->getAvailableForRestaurantMenu($menu);
        $cart = $this->get('app_shop.cart_manager')->getCart($restaurant);
        $planning = $this->get('app_restaurant.timesheet_manager')->getWeekDayPlanning($restaurant);
        $isOpen = $this->get('app_restaurant.timesheet_manager')->isRestaurantOpenForOrder($restaurant);
        $discounts = $this->getDoctrine()->getRepository('ClabShopBundle:Discount')->findAllAvailable($restaurant->getId());
        $types = array();
        $slots = $this->get('app_shop.order_manager')->getSlotsForCart($cart);

        foreach ($categories as $category) {
            $types[] = $category->getType();
        }

        if ($isDelivery) {
            $cart->setOrderType(OrderType::ORDERTYPE_DELIVERY);
        }

        $favorites = array();

        if($this->getUser()) {
            $favoriteProducts = $this->getUser()->getFavoriteProducts();
            if($favoriteProducts) {
                foreach ($favoriteProducts as $product) {
                    $favorites[] = $product->getId();
                }
            }
        }

        $area = null;
        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        $params = array(
            'restaurant' => $restaurant,
            'categories' => $categories,
            'products' => $products,
            'pdjs' => $pdjs,
            'options' => $options,
            'meals' => $meals,
            'meal' => $meals[0],
            'cart' => $cart,
            'area' => $area,
            'planning' => $planning,
            'isOpen' => $isOpen,
            'types' => array_unique($types),
            'discounts' => $discounts,
            'slots' => $slots,
            'favorites' => $favorites
        );

        return $this->render('ClabWhiteBundle:Order:home.html.twig', $params);
    }

    public function orderAction(Request $request, Restaurant $restaurant, $day = null)
    {
        $session = $this->get('session');

        $orderManager = $this->get('app_shop.order_manager');
        $cartManager = $this->get('app_shop.cart_manager');
        $cart = $cartManager->getCart($restaurant);
        $suggestion = null;

        if (!$session->has('suggestion')){
            $additionalSales = $this->get('app_shop.suggestion_manager')->chooseCartCategory($cart);
            $suggestion = [];

            foreach ($additionalSales as $s) {
                if (!in_array($s, $suggestion)) {
                    $suggestion[] = $s;
                }
            }

            $session->set('suggestion', true);
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('clab_white_user_login', array('next' => $this->generateUrl('clab_white_order_recap', array('slug' => $restaurant->getSlug()), true)));
        }

        if (!$cart) {
            return $this->redirectToRoute('clab_white_home', array('slug' => $restaurant->getSlug()));
        }

        if (!$day) {
            $dayDate = new \DateTime();
        } else {
            $dayDate = new \DateTime($day);
            $dayDate->setTime(0,0,0);
        }
        $params = array('day' => $dayDate);

        $hasOrderedInDelivery = count($em->getRepository(OrderDetail::class)->findBy(array("profile" => $user, "orderType" => 3)));
        $params['hasOrderedInDelivery'] = $hasOrderedInDelivery;

        if ($this->getUser()->getRestoflashToken() && $cart->getRestaurant()->getRestoflashId()) {
            $params['restoflash'] = true;
        }

        if ($session->get(sprintf('%s_delivery_address', $restaurant->getSlug()))) {
            $params['delivery'] = $session->get(sprintf('%s_delivery_address_text', $restaurant->getSlug()));
            $params['fullDeliveryDetails'] = true;
            $address = $session->get(sprintf('%s_delivery_address', $restaurant->getSlug()));

            $lat = $address->getLatitude();
            $lng = $address->getLongitude();

            $cartLink = $this->generateUrl('clab_white_order_home', array('slug' => $restaurant->getSlug(), 'isDelivery' => true, 'address' => $params['delivery'], 'lat' => $lat, 'lng' => $lng ));

        } else {
            $cartLink = $this->generateUrl('clab_white_order_home', array('slug' => $restaurant->getSlug()));
        }

        if ($cart->getOrderType() == 3) {
            $params['address'] = $this->getUser()->getHomeAddress();
        }

        try {
            $cartManager->setFreeSauces($cart);
            $form = $orderManager->getOrderForm($cart, $this->getUser(), $params);

            $loyalties = $em->getRepository(Loyalty::class)->findAvailableForCart($cart, $this->getUser());

            $sauceForm = $orderManager->getSauceForm($cart);

        } catch (\Exception $e) {
            $this->addFlash('notice', $e->getMessage());

            return $this->redirectToRoute('clab_white_order_home', array('slug' => $restaurant->getSlug()));
        }
        try {
            $cards = $this->get('clab_stripe.customer.manager')->listCards($this->getUser());

            
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
            
        } catch (Base $e) {
            $cards = null;
        }

        if ($form->handleRequest($request)->isValid()) {
            $woodSticks = $form->get('woodSticks')->getData();
            $cart->setWoodSticks($woodSticks);

            if ($cart->getOrderType() == 3){
                $homeAddress = $user->getHomeAddress() ? $user->getHomeAddress() : new Address();
                $homeAddress->setDoorCode($form->get('entryCode')->getData() ? $form->get('entryCode')->getData() : null);
                $homeAddress->setDoor($form->get('appartmentNumber')->getData() ? $form->get('appartmentNumber')->getData() : null);
                $homeAddress->setComment($form->get('addressComment')->getData() ? $form->get('addressComment')->getData() : null);
                $homeAddress->setBuilding($form->get('building')->getData() ? $form->get('building')->getData() : null);
                $homeAddress->setStaircase($form->get('staircase')->getData() ? $form->get('staircase')->getData() : null);
                $homeAddress->setFloor($form->get('floor')->getData() ? $form->get('floor')->getData() : null);
                $homeAddress->setElevator($form->get('lift')->getData() ? $form->get('lift')->getData() : null);
                $homeAddress->setIntercom($form->get('intercom')->getData() ? $form->get('intercom')->getData() : null);
                $homeAddress->setSecondDoorCode($form->get('entryCode2')->getData() ? $form->get('entryCode2')->getData() : null);

                if(!$user->getHomeAddress()) {
                    $homeAddress->setStreet($form->get('address')->getData() ? $form->get('address')->getData() : null);
                    $homeAddress->setZip($form->get('zip')->getData() ? $form->get('zip')->getData() : null);
                    $homeAddress->setCity($form->get('city')->getData() ? $form->get('city')->getData() : null);
                    $homeAddress->setComment($form->get('addressComment')->getData() ? $form->get('addressComment')->getData() : null);

                    $user->addAddress($homeAddress);
                    $user->setHomeAddress($homeAddress);
                    $em->persist($homeAddress);
                }

                $session->set($cart->getRestaurant()->getSlug().'_delivery_address',$homeAddress);
            }

           // try{
                if ($day) {
                    $order = $orderManager->computeOrderForm($form, array('day' => new \DateTime($day)));
                } else {
                    $order = $orderManager->computeOrderForm($form);
                }
            /*}catch(\Exception $e) {
                $session->getFlashBag()->add('notice', $e->getMessage());
                return $this->redirectToRoute('clab_white_order_recap', array('slug' => $restaurant->getSlug()));
            }*/

            if ($form->get('phone')->getData() !== null) {
                $this->getUser()->setPhone($form->get('phone')->getData());
                $em->flush();
            }
            
            if($order->getOnlinePayment() == true)
            {
                if(is_null($form->get('card')->getData()) || $form->get('card')->getData()==""){
                    $this->addFlash('notice','Veuillez selectionner un moyen de paiement');
                    return $this->redirectToRoute('clab_white_order_recap', array('slug' => $restaurant->getSlug()));
                }
                try
                {
                    Stripe::setApiKey($this->getParameter('stripe_secret_key'));
                    $customer = Customer::retrieve($this->getUser()->getStripeCustomerId());
                    $cardId = $form->get('card')->getData();
                    $card = $customer->sources->retrieve($cardId);
                    $charge = $this->get('clab_stripe.customer.manager')->chargeCardForClickeat($this->getUser(),$order->getPrice()*100,$card);
                    $em->flush();
                }
                catch(Base $e)
                {
                    $session->getFlashBag()->add('notice', $e->getMessage());
                    return $this->redirectToRoute('clab_white_order_recap', array('slug' => $restaurant->getSlug()));
                }
                if($charge == true)
                {
                    $orderManager->validateOrder($order);
                    $order->setIsPaid(true);
                    $order->setState(OrderDetail::ORDER_STATE_TERMINATED);
                    $em->flush();
                    $this
                        ->get('app_shop.loyalty_manager')
                        ->refreshLoyalties($order->getProfile())
                        ->generateLoyaltyFromOrder($order)
                    ;
                }
                else
                {
                    $this->addFlash('notice', 'Erreur lors du paiement');
                    return $this->redirectToRoute('clab_white_order_receipt', array('hash' => $order->getHash()));
                }

            }

            try {
                $client = new \ElephantIO\Client(new Version1X('http://localhost:8081'));
                $client->initialize();
                $client->emit('emitPHP', ['order' => $order->getId(), 'restaurant' => $restaurant->getSlug()]);
                $client->close();
            } catch (\Exception $e) {
            }
            $userDatabase = $em->getRepository('ClabBoardBundle:UserDataBase')->findOneBy(array(
                'user' => $this->getUser(),
                'restaurant' => $restaurant
            ));
            $user = $this->getUser();
            if (is_null($userDatabase)) {
                $userDatabase = new UserDataBase();
                $userDatabase->setRestaurant($restaurant);
                $userDatabase->setUser($user);
                if (!is_null($user->getBirthday())) {
                    $userDatabase->setBirthday($user->getBirthday());
                }
            }
            $userDatabase->setEmail($user->getEmail());

            if (!is_null($user->getFirstName())) {
                $userDatabase->setFirstName($user->getFirstName());
            }
            if (!is_null($user->getLastName())) {
                $userDatabase->setLastName($user->getLastName());
            }
            if (!is_null($user->getPhone())) {
                $user->getPhone();
                $userDatabase->setPhone($user->getPhone());
            }
            if (!is_null($user->getHomeAddress())) {
                $userDatabase->setHomeAddress($user->getHomeAddress());
            }
            if (!is_null($user->getJobAddress())) {
                $userDatabase->setJobAddress($user->getJobAddress());
            }
            if (!is_null($user->getSubscribedNewsletter())) {
                $userDatabase->setSubscribedNewsletter($user->getSubscribedNewsletter());
            }
            $em->persist($userDatabase);
            $em->flush();

            return $this->redirectToRoute('clab_white_order_receipt', array('hash' => $order->getHash()));
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

        return $this->render('ClabWhiteBundle:Order:order.html.twig', array(
            'form' => $form->createView(),
            'day' => $day,
            'freeSauces' => $cart->getFreeSauces(),
            'loyalties' => $loyalties,
            'sauceForm' => $sauceForm->createView(),
            'saltySojaPrice' => $parentSaltySoja->$getPrice(),
            'sugarySojaPrice' => $parentSugarySoja->$getPrice(),
            'wasabiPrice' => $parentWasabi->$getPrice(),
            'gingerPrice' => $parentGinger->$getPrice(),
            'cart' => $cart,
            'countMeals' => $cartManager->countMeals($cart),
            'cartLink' => $cartLink,
            'cards' => $cards ? array_values($cards) : array(),
            'restaurant' => $restaurant,
            'iframe' => $this->isIframe($request),
            'publishableKey' => $this->container->getParameter('stripe_publishable_key'),
            'hasOrderedInDelivery' => $hasOrderedInDelivery,
            'suggestion' => $suggestion,
        ));
    }


    public function orderReceiptAction($hash, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $order =  $em->getRepository(OrderDetail::class)->findOneBy(array('hash' => $hash));
        
        if ($order->getProfile() != $this->getUser()) {
            throw $this->createNotFoundException();
        }

        switch ((int) $order->getState()) {
            case OrderDetail::ORDER_STATE_CANCELLED:
                return $this->render('ClabWhiteBundle:Order:orderCancelled.html.twig', array('order' => $order, 'iframe' => $this->isIframe($request)));
                break;
            case OrderDetail::ORDER_STATE_WAITING_PAYMENT:
                $this->addFlash('error','Erreur paiement.');
                $this->redirect('clab_white_order_recap', array('slug' => $order->getRestaurant()->getSlug()));
                break;
            default:
                $address = $this->get('app_restaurant.timesheet_manager')->getOrderLocation($order);
                $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $order->getRestaurant()->getSlug()));
                $this->get('app_restaurant.timesheet_manager')->getTodayPlanning($restaurant);

                $this->get('session')->remove(sprintf('%s_delivery_address', $restaurant->getSlug()));
                $this->get('session')->remove(sprintf('%s_delivery_address_text', $restaurant->getSlug()));

                return $this->render('ClabWhiteBundle:Order:orderReceipt.html.twig', array(
                    'address' => $address,
                    'order' => $order,
                    'restaurant' => $order->getRestaurant(),
                    'cancelAvailable' => $this->get('app_shop.order_manager')->cancelAvailable($order),
                    'iframe' => $this->isIframe($request),
                ));
                break;
        }
    }

    public function orderFollowAction(Request $request, $hash)
    {
        if ($request->isXmlHttpRequest()) {
            $order = $this->getDoctrine()->getRepository(OrderDetail::class)->findOneBy(array('hash'=>$hash));

            $response = array();

            $response['state'] = $order->getPreparationState();
            if ($order->getDelivery() && $response['state'] == 2 && $order->getDelivery()->getDeliveryMan()) {
                $response['deliveryMan'] = [
                    'lat' => $order->getDelivery()->getDeliveryMan()->getLatitude(),
                    'lng' => $order->getDelivery()->getDeliveryMan()->getLongitude()
                ];
            }
            return new JsonResponse($response);
        }

        return $this->redirectToRoute('clab_white_order_receipt', array('hash' => $hash));
    }

    /**
     * @ParamConverter("order", class="ClabShopBundle:OrderDetail")
     */
    public function orderPaymentRestoflashCheckAction(Request $request, OrderDetail $order)
    {
        $em = $this->getDoctrine()->getManager();

        if ($order->getProfile() != $this->getUser()) {
            throw $this->createNotFoundException();
        }

        if ($order->getState() !== OrderDetail::ORDER_STATE_WAITING_PAYMENT_RESTOFLASH) {
            return $this->redirectToRoute('clab_white_order_receipt', array('hash' => $order->getHash()));
        }

        $restoflash = $this->get('clab_restoflash.restoflash_web_payment');
        $transaction = $restoflash->checkTransaction($order->getRestoflashTransaction());

        if ($transaction->isValidated()) {
            $order->setIsPaid(true);
            $em->flush();

            $this->get('app_shop.order_manager')->validateOrder($order);
        }

        return $this->redirectToRoute('clab_white_order_receipt', array('hash' => $order->getHash()));
    }

    /**
     * @ParamConverter("order", class="ClabShopBundle:OrderDetail")
     */
    public function switchPaymentAction(Request $request, OrderDetail $order)
    {
        $em = $this->getDoctrine()->getManager();

        if ($order->getProfile() != $this->getUser()) {
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

        if ($order->getProfile() != $this->getUser()) {
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
        if ($order->getProfile() != $this->getUser()) {
            throw $this->createNotFoundException();
        }

        if ($request->isMethod('post') && $this->get('app_shop.order_manager')->cancelAvailable($order)) {
            $this->get('app_shop.order_manager')->cancelOrder($order);

        }

        return $this->redirectToRoute('clab_white_order_receipt', array('hash' => $order->getHash()));
    }

    /**
     * @ParamConverter("user", class="ClabUserBundle:User")
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
        return $this->render('ClabWhiteBundle:Order:add-card.html.twig', array(
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

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function checkOrderDateAction(Request $request, Restaurant $restaurant, $date)
    {
        $day = new \DateTime($date);

        $planning = $this->get('app_restaurant.timesheet_manager')->getDayPlanning($restaurant, $day);

        if (!count($planning)) {
            $this->get('session')->getFlashBag()->add('notice', 'Ce restaurant ne sera pas ouvert à la date choisie');
        }

        return $this->redirectToRoute('clab_white_recap', array('slug' => $restaurant->getSlug(), 'day' => count($planning) ? $date :  null ));

    }
}
