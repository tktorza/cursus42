<?php

namespace Clab\ShopBundle\Manager;

use Clab\ApiBundle\Manager\PushManager;
use Clab\BoardBundle\Service\SubscriptionManager;
use Clab\CallCenterBundle\Form\Type\User\EditType;
use Clab\ShopBundle\Entity\Loyalty;
use Clab\WhiteBundle\Manager\MailManager;
use Clab\DeliveryBundle\Entity\DeliveryCart;
use Clab\DeliveryBundle\Service\DeliveryManager;
use Clab\LocationBundle\Entity\Address;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Manager\TimeSheetManager;
use Clab\MultisiteBundle\Service\MultisiteManager;
use Clab\ShopBundle\Entity\CartElement;
use Clab\ShopBundle\Entity\PaymentMethod;
use Clab\UserBundle\Entity\User;
use Clab\UserBundle\Service\UserManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Monolog\Logger;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\Cart;
use Clab\ShopBundle\Entity\OrderDetail;
use Clab\ShopBundle\Entity\OrderType;
use Clab\DeliveryBundle\Entity\Delivery;
use Clab\ShopBundle\Exception\DeliveryCartException;

class OrderManager
{
    protected $em;
    protected $router;
    protected $formFactory;
    protected $session;
    protected $request;
    protected $translator;
    protected $logger;
    protected $userManager;
    protected $cartManager;
    protected $timesheetManager;
    protected $deliveryManager;
    protected $subscriptionManager;
    protected $clickeatMailManager;
    protected $repository;
    protected $multisite = false;
    protected $mailManager;

    /**
     * @param EntityManager                          $em
     * @param Router                                 $router
     * @param FormFactoryInterface                   $formFactory
     * @param SessionInterface                       $session
     * @param TranslatorInterface                    $translator
     * @param Logger                                 $logger
     * @param UserManager                            $userManager
     * @param \Clab\WhiteBundle\Manager\MailManager $mailManager
     * @param PushManager                            $pushManager
     * @param CartManager                            $cartManager
     * @param TimeSheetManager                       $timesheetManager
     * @param DeliveryManager                        $deliveryManager
     * @param SubscriptionManager                    $subscriptionManager
     * @param MailManager                            $mailManager
     * @param Mixpanel                               $mixpanel
     *                                                                            Constructor
     */
    public function __construct(EntityManager $em, Router $router, FormFactoryInterface $formFactory, SessionInterface $session, Request $request, TranslatorInterface $translator, Logger $logger, UserManager $userManager, CartManager $cartManager, TimeSheetManager $timesheetManager, DeliveryManager $deliveryManager, SubscriptionManager $subscriptionManager, \Clab\WhiteBundle\Manager\MailManager $mailManager)
    {
        $this->em = $em;
        $this->router = $router;
        $this->formFactory = $formFactory;
        $this->session = $session;
        $this->request = $request;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->userManager = $userManager;
        $this->cartManager = $cartManager;
        $this->timesheetManager = $timesheetManager;
        $this->deliveryManager = $deliveryManager;
        $this->subscriptionManager = $subscriptionManager;
        $this->mailManager = $mailManager;
        $this->repository = $this->em->getRepository('ClabShopBundle:OrderDetail');
    }

    /**
     * @param Restaurant $restaurant
     * @param array      $parameters
     *
     * @return array
     *               Get Orders for a restaurant
     */
    public function getForRestaurant(Restaurant $restaurant, array $parameters = array())
    {
        if (!isset($parameters['start']) || !$parameters['start']) {
            $parameters['start'] = date_create('today');
        }

        if (!isset($parameters['end']) || !$parameters['end']) {
            $parameters['end'] = date_create('tomorrow');
        }

        $parameters['states'] = array(
            OrderDetail::ORDER_STATE_VALIDATED,
            OrderDetail::ORDER_STATE_INITIAL,
            OrderDetail::ORDER_STATE_READY,
            OrderDetail::ORDER_STATE_READY_PACKING,
            OrderDetail::ORDER_STATE_READY_PACKED,
            OrderDetail::ORDER_STATE_TERMINATED,
            OrderDetail::ORDER_STATE_CANCELLED,
        );

        return $this->repository->getForRestaurant($restaurant, $parameters);
    }

    /**
     * @param $id
     * @param User $user
     *
     * @return array
     *               Return one order and a state
     */
    public function getOrder($id, User $user)
    {
        $order = $this->repository->find($id);
        $roles = array('manager' => false, 'owner' => false);

        if (!$order) {
            return array($roles, null);
        }

        if ($order->getProfile() == $user) {
            $roles['owner'] = true;
        }

        $states = array(
            OrderDetail::ORDER_STATE_VALIDATED,
            OrderDetail::ORDER_STATE_READY,
            OrderDetail::ORDER_STATE_READY_PACKING,
            OrderDetail::ORDER_STATE_READY_PACKED,
            OrderDetail::ORDER_STATE_TERMINATED,
        );

        if (in_array($order->getState(), $states) && (in_array($order->getRestaurant(), $user->getAllowedRestaurants()->toArray()) || $this->userManager->isUserGranted($user, 'ROLE_ADMIN'))) {
            $roles['manager'] = true;
        }

        if ($roles['manager'] || $roles['owner']) {
            return array($roles, $order);
        }

        return array($roles, null);
    }

    /**
     * @param $hash
     *
     * @return Order $order
     *               Return one order
     */
    public function getOrderByHash($hash)
    {
        $order = $this->repository->findOneBy(array('hash'=>$hash));

        return $order;
    }

    /**
     * @param Cart  $cart
     * @param array $parameters
     *
     * @return OrderDetail
     *                     Create an Order
     */
    public function createOrderFromCart(Cart $cart, User $user, array $parameters = array())
    {
        $type = $cart->getOrderType();
        $orderType = $this->em->getRepository('ClabShopBundle:OrderType')->find($type);

        $order = new OrderDetail();
        $order->setCart($cart);
        $order->setRestaurant($cart->getRestaurant());
        $order->setState(OrderDetail::ORDER_STATE_WAITING_PAYMENT);
        $order->setProfile($user);
        $order->setOrderType($orderType);

        if ($type == OrderType::ORDERTYPE_DELIVERY) {
            $delivery = $this->deliveryManager->createDelivery($order);
            $deliveryAddress = $parameters['address'];
            $delivery->setAddress($deliveryAddress);
            $delivery->setComment($this->session->get('delivery_comment'));
            $order->setDelivery($delivery);
        }

        return $order;
    }

    /**
     * @param Cart  $cart
     * @param array $parameters
     *
     * @return array
     *
     * @throws DeliveryCartException
     * @throws \Exception
     *                               Get time slot available for cart
     */
    public function getSlotsForCart(Cart $cart, array $parameters = array())
    {
        $now = new \DateTime('now');
        $restaurantID = $cart->getRestaurant();
        $restaurant = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantID->getId());

        if ($cart->getOrderType() == OrderType::ORDERTYPE_DELIVERY) {

            if (!isset($parameters['address'])) {
                return new \Exception($this->translator->trans('clickeat.error.order.missing_delivery_address'));
            } else {
                $address = $parameters['address'];
            }
            if (is_string($address)) {
                $address = unserialize($address);
            }
            if ($address->getLatitude() == null || $address->getLongitude() == null) {
                if (!is_null($address->getStreet()) && !is_null($address->getCity())) {
                    $location = $address->getStreet().$address->getZip().$address->getCity();
                    $coordonates = $this->locationManager->getCoordinateFromAddress($location);
                    $address->setLatitude($coordonates['latitude']);
                    $address->setLongitude($coordonates['longitude']);
                }
            }
            if(isset($parameters['day'])) {
                $slots = $this->deliveryManager->getSlotsForDay($restaurant, array('cart' => $cart, 'latitude' => $address->getLatitude(), 'longitude' => $address->getLongitude(), 'day' => $parameters['day']));
            } else {
                $slots = $this->deliveryManager->getSlotsForDay($restaurant, array('cart' => $cart, 'latitude' => $address->getLatitude(), 'longitude' => $address->getLongitude()));
            }

            if (empty($slots)) {
                return new \Exception($this->translator->trans('clickeat.error.order.delivery_address_no_slot'));
            }

            $prettySlots = array();

            foreach ($slots as $key => $slot) {
                $prettySlots[$key] = array('slot' => $slot['end'], 'slotLength'=>$slot['slotLength']);
            }
            $slots = $prettySlots;

        } else {
            $restaurant = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->find($cart->getRestaurant()->getId());

            $slots = $this->timesheetManager->getPreorderSlots($restaurant, $cart, $parameters);
        }

        return $slots;
    }

    /**
     * @param Cart  $cart
     * @param array $parameters
     *
     * @return array
     *               Get available payment method for cart
     */
    public function getPaymentMethodsForCart(Cart $cart, User $user, array $parameters = array())
    {
        $paymentChoices = array();
        $idResto = $cart->getRestaurant()->getId();
        $paymentMethods = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->find($idResto)->getPaymentMethods();
        foreach ($paymentMethods->toArray() as $method) {
            if ($method->getIsOnline() && !isset($paymentChoices[1])) {
                $paymentChoices[1] = 'Paiement par carte bancaire';
            }
                // paiement sur place uniquement iframe
                    if ($cart->getOrderType() == 3) {
                        $paymentChoices[0] = 'Paiement à la livraison';
                    } else {
                        $paymentChoices[0] = 'Paiement sur place';
                    }
                    if (isset($parameters['companyPaymentAvailable'])) {
                       $paymentChoices[2]  = 'Paiement en compte';
                    }
        }
        if (isset($parameters['restoflash']) && $parameters['restoflash']) {
            $token = $user->getRestoflashToken();
            $this->restoFlashTokenManager->getInfo($token);

            if ($token->getCredit() >= $cart->getTotalPrice() && $token->getMaxTransaction() >= $cart->getTotalPrice()) {
                $paymentChoices[2] = 'Restoflash';
            }
        }

        return $paymentChoices;
    }

    /**
     * @param Cart  $cart
     * @param array $parameters
     *
     * @return \Symfony\Component\Form\Form
     *
     * @throws DeliveryCartException
     * @throws \Exception
     *                               Create the form of the order
     */
    public function getOrderForm(Cart $cart, User $user, array $parameters = array())
    {
        if (!$cart) {
            throw new \Exception($this->translator->trans('clickeat.error.order.empty_cart'));
        }

        $host = $this->request->getHost();
        $deliveryAddress = $this->session->get($cart->getRestaurant()->getSlug() . '_delivery_address');

        if ($deliveryAddress) {
            $parameters['address'] = $deliveryAddress;
        }

        $restaurantId = $cart->getRestaurant()->getId();
        $restaurant = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);

        $slots = $this->getSlotsForCart($cart, $parameters);
        $paymentChoices = $this->getPaymentMethodsForCart($cart, $user, $parameters);
        $type = $cart->getOrderType();

        if (empty($slots)) {
            throw new \Exception('Le restaurant ne prend pas de commandes actuellement');
        }

        $minPanier = 5;

        $area = null;

        if( $this->session->get('areaDelivery')) {
            $area = $this->em->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $this->session->get('areaDelivery'),
            ));
        }

        $minPanier = ($cart->getOrderType() == 3 && $area && $area->getMinPanier()) ? $area->getMinPanier() : 5;

        if ($cart->getTotalPrice() < $minPanier && !isset($parameters['callcenter'])) {
            throw new \Exception('Minimum de '.$minPanier.' euros de commande non atteint');
        }
        if (empty($paymentChoices)) {
            if (strpos($host, 'click-eat.') !== false) {
                $cb = $this->em->getRepository('ClabShopBundle:PaymentMethod')->find(1);
                $paymentChoices = $restaurant->addPaymentMethod($cb);
                $this->em->flush();
            } else {
                throw new \Exception('Pas de moyens de paiements disponible dans ce restaurant');
            }
        }

        $now = isset($parameters['day']) && $parameters['day'] instanceof \DateTime ? $parameters['day'] :new \DateTime();
        $now->modify('+ ' . $restaurant->getOrderDelay() . ' minute');

        if ($type == 3) {
            $now->modify('+ ' . current($slots)['slotLength'] . ' min');
        }

        $order = $this->createOrderFromCart($cart, $user, $parameters);
        $formatedSlots = array();

        foreach ($slots as $key => $slot) {

            if ($slot instanceof \DateTime) {
                $slot = $slot->format('H:i');
            } else {
                if ($type == 3) {
                    $slot = $slot['slot']->format('H:i');
                }
            }

            if ($slot > $now->format('H:i')) {
                $formatedSlots[$slot] = $slot;
            }
        }

        $woodSticksValues = range(0, intval($cart->getBasePrice() / 13) + 1);
        $woodSticksChoices = array_combine($woodSticksValues, $woodSticksValues);

        $cart->setWoodSticks(round($cart->getBasePrice() / 26));

        $formBuilder = $this->formFactory->createBuilder('form', $order)
            ->setMethod('POST')
            ->add('online_payment', 'choice', array(
                'choices' => $paymentChoices,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
                'data' => array_keys($paymentChoices)[0],
            ))
            ->add('slots', 'choice', array(
                'choices' => $formatedSlots,
                'label' => $type == OrderType::ORDERTYPE_PREORDER ? $this->translator->trans('clickeat.order.slot.choose_takeaway') : $this->translator->trans('clickeat.order.slot.choose_delivery'),
                'required' => true,
                'mapped' => false,
                'data' => $parameters['day']->format('H:i')

            ))
            ->add('woodSticks', 'choice', array(
                'choices' => $woodSticksChoices,
                'required' => false,
                'mapped' => false,
                'data' => $cart->getWoodSticks(),
                'label' => 'Nombre de baguettes',
            ))
            ->add('cash', 'hidden', array(
                'label' => 'Espèces',
                'required' => false,
                'mapped' => false,
                'data' => $order->getOnSitePayments()['cash']
            ))
            ->add('ticketResto', 'hidden', array(
                'label' => 'Titres Restaurant',
                'required' => false,
                'mapped' => false,
                'data' => $order->getOnSitePayments()['ticketResto']
            ));
        if (!$deliveryAddress || $parameters['hasOrderedInDelivery']) {
            $formBuilder->add('cbOnSite', 'hidden', array(
                'label' => 'Paiement par chèque à la livraison',
                'required' => false,
                'mapped' => false,
                'data' => $order->getOnSitePayments()['cbOnSite']
            ));
        }

    //Phone
    if (!is_null($user->getPhone())) {
        $formBuilder->add('phone', null, array(
        'required' => true,
        'data' => $user->getPhone(),
        'mapped' => false,
    ));
    } else {
        $formBuilder->add('phone', null, array(
            'required' => true,
            'mapped' => false,
        ));
    }

    $formBuilder->add('profile',new EditType(true));

    if ($deliveryAddress  || isset($parameters['address'])) {
        $formBuilder->add('entryCode', null, array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getDoorCode()));
        $formBuilder->add('appartmentNumber', null, array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getDoor()));
        $formBuilder->add('address', null, array('mapped' => false, 'required' => true, 'data' => $parameters['address']->getStreet()));
        $formBuilder->add('zip', null, array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getZip()));
        $formBuilder->add('city', null, array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getCity()));
        $formBuilder->add('addressComment', 'textarea', array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getComment()));

        if (isset($parameters['fullDeliveryDetails'])) {
                $formBuilder->add('building', null, array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getBuilding()));
                $formBuilder->add('staircase', null, array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getStaircase()));
                $formBuilder->add('floor', null, array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getFloor()));
                $formBuilder->add('lift', null, array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getElevator()));
                $formBuilder->add('intercom', null, array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getIntercom()));
                $formBuilder->add('entryCode2', null, array('mapped' => false, 'required' => false, 'data' => $parameters['address']->getSecondDoorCode()));
            }
        }

        $formBuilder->add('card','hidden',array('mapped' => false, 'required' => true));

        if (isset($parameters['orderType'])) {
            $formBuilder->add('type','hidden',array('required'=>false, 'data'=> $parameters['orderType']));
        }

        return $formBuilder->getForm();
    }

    public function getSauceForm(Cart $cart)
    {

        $formBuilder = $this->formFactory->createBuilder('form', $cart);

        $formBuilder
            ->add('saltySoja', 'hidden', array('required' => false))
            ->add('sugarySoja', 'hidden', array('required' => false))
            ->add('wasabi', 'hidden', array('required' => false))
            ->add('ginger', 'hidden', array('required' => false))
            ->add('freeSaltySoja', 'hidden', array('required' => false))
            ->add('freeSugarySoja', 'hidden', array('required' => false))
            ->add('freeWasabi', 'hidden', array('required' => false))
            ->add('freeGinger', 'hidden', array('required' => false));

        return $formBuilder->getForm();
    }

    /**
     * @param FormInterface $form
     * @param array         $parameters
     *
     * @throws DeliveryCartException
     * @throws \Exception
     *                               Process the form of the order
     */
    public function computeOrderForm(FormInterface $form, array $parameters = array())
    {
        $order = $form->getData();
        $cart = $order->getCart();

        $deliveryAddress = $this->session->get($cart->getRestaurant()->getSlug().'_delivery_address');

        if ($deliveryAddress) {
            $parameters['address'] = $deliveryAddress;
        }

        if ($order->getDelivery()) {
            $delivery = $order->getDelivery();
        } else {
            $order->addOnSitePayment('cash', is_numeric($form->get('cash')->getData()) ? $form->get('cash')->getData() : 0.);
            $order->addOnSitePayment('cbOnSite', is_numeric($form->get('cbOnSite')->getData()) ? $form->get('cbOnSite')->getData() : 0.);
            $order->addOnSitePayment('ticketResto', is_numeric($form->get('ticketResto')->getData()) ? $form->get('ticketResto')->getData() : 0.);
        }

        if ($order->getOnlinePayment() == 0) {
            $order->setState(OrderDetail::ORDER_STATE_VALIDATED);

            if ($order->getDelivery()) {
                $order->getDelivery()->setState(Delivery::DELIVERY_STATE_WAITING_DELIVERYMAN);
            }
        }
        //2 is for for company payments
        if ( 2 == $order->getOnlinePayment() && isset($parameters['company']) && $parameters['company']){
            /**
             *var ClabBoardBundle/Entity/Company $company
             */
            $company = $parameters['company'];
            $order->setCompany($company);
            $order->setState(OrderDetail::ORDER_STATE_VALIDATED);

            if ($order->getDelivery()) {
                $order->getDelivery()->setState(Delivery::DELIVERY_STATE_WAITING_DELIVERYMAN);
            }
            $order->setOnlinePayment(false);
        }

        if ($order->getOrderType()->getId() == OrderType::ORDERTYPE_DELIVERY) {
            $time = $form['slots']->getData();
            $time = explode(':', $time);
            if (isset($parameters['day'])) {
                $start = $parameters['day'];
            } else {
                $start = new \DateTime('now');
            }

            $start->setTime($time[0], $time[1]);
            $end = clone $start;
            $order->setTime($end);

            if (isset($delivery)) {
                $delivery->setStart($start);
                $delivery->setEnd($end);
                $pickupAddress = $this->timesheetManager->getOrderLocation($order);
                $delivery->setPickupAddress($pickupAddress);
            } else {
                $order->setTime(date_create('now'));
            }
        } else {
            $timeFromForm = $form['slots']->getData();

            $time = explode(':', $timeFromForm);

            if (isset($parameters['day'])) {
                $date = $parameters['day'];
            } else {
                $date = new \DateTime('now');
            }

            $date->setTime($time[0], $time[1]);
            $order->setTime($date);
        }

        return  $this->initOrder($order, $parameters);
    }

    /**
     * @param OrderDetail $order
     * @param array       $parameters
     *
     * @return OrderDetail
     *                     Create the basic stuff for an order
     */
    public function initOrder(OrderDetail $order, array $parameters = array())
    {
        $cart = $order->getCart();
        $restaurant = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->find($order->getCart()->getRestaurant()->getId());
        $tva = array();
        $tva['10'] = 0;
        $tva['5.5'] = 0;
        $tva['20'] = 0;
        $tva['7'] = 0;
        $this->updateStock($cart);

        if ($cart->getCoupon() && !$cart->getCoupon()->getUnlimited()) {
            $quantity = $cart->getCoupon()->getQuantity();
            $cart->getCoupon()->setQuantity($quantity - 1);
        }

        if (isset($parameters['isCallCenter'])) {
            $order->setSource('CDA');
        }

        $order->setPrice($cart->getTotalPrice());


        if ($this->session->get('facebook_connect_source')) {
            $source = $this->session->get('facebook_connect_source');
            $facebookPageId = $this->session->get('facebook_connect_source_fb_page');
            if ($facebookPageId) {
                $facebookPage = $this->em->getRepository('ClabSocialBundle:SocialFacebookPage')->findOneBy(array('id' => $facebookPageId, 'is_online' => true));

                if ($facebookPage) {
                    $order->setFacebookPage($facebookPage);
                }
            }
        }

        if ($cart->getDiscount()) {
            $discount = $this->em->getRepository('ClabShopBundle:Discount')->find($cart->getDiscount()->getId());
        } else {
            $discount = null;
        }

        $time = new \DateTime('now');
        $realCart = new Cart();
        if (!is_null($cart->getCoupon())) {
            $coupon = $this->em->getRepository('ClabShopBundle:Coupon')->find($cart->getCoupon()->getId());
            $realCart->setCoupon($coupon);
        }
        $deliveryAddress = isset($parameters['address']) ? $parameters['address'] : null;

        $realCart->setCreated($cart->getCreated());
        $realCart->setUpdated($cart->getCreated());
        $realCart->setRestaurant($restaurant);

        $realCart->setDiscount($discount);
        $realCart->setWoodSticks($cart->getWoodSticks());

        foreach ($cart->getLoyalties() as $loyalty) {
            $l = $this->em->getRepository(Loyalty::class)->find($loyalty->getId());
            $realCart->addLoyalty($l);
        }

        $this->em->persist($realCart);
        $this->em->flush($realCart);


        foreach ($cart->getElements() as $element) {
            if ($element->getProduct() !== null) {
               $tax = $this->em->getRepository('ClabRestaurantBundle:Tax')->find($element->getTax()->getId());
                $parentElement = new CartElement();
                $parentElement->setQuantity($element->getQuantity());
                $parentElement->setPrice($element->getPrice());
                $parentElement->setCart($realCart);
                $parentElement->setParent($element->getParent());
                $parentElement->setSale($element->getSale());
                $parentElement->setTax($tax);

                $productTmp = $this->em->getRepository('ClabRestaurantBundle:Product')->find($element->getProduct()
                    ->getId());

                $parentElement->setProduct($productTmp);
                foreach ($element->getChildrens() as $child) {
                    $parentElement->addChildren($child);
                }
                foreach ($element->getChoices() as $option) {
                    $realOption = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->find($option->getId());
                    $parentElement->addChoice($realOption);
                }
                $taxAsString = (string) $parentElement->getTax()->getValue();
                $priceProductSupp = $parentElement->getPrice();
                //Calcul TVA PRODUIT
                if (!is_null($realCart->getCoupon())) {
                    if ($realCart->getCoupon()->getPercent() == null) {
                        if ((!is_null($realCart->getDiscount()) && ($realCart->getDiscount()->getType() == 0 || $realCart->getDiscount()->getType() == 100))) {
                            $tva[$taxAsString] = $tva[$taxAsString] + ($priceProductSupp - (($priceProductSupp * $realCart->getCoupon()->getAmount())/ ($order->getCart()->getDiscountPrice())));
                        } else {
                            $tva[$taxAsString] = $tva[$taxAsString] + ($priceProductSupp - (($priceProductSupp * $realCart->getCoupon()->getAmount())/ ($cart->getTotalPrice() + $realCart->getCoupon()->getAmount())));
                        }
                    } else {
                        if ((!is_null($realCart->getDiscount()) && ($realCart->getDiscount()->getType() == 0 || $realCart->getDiscount()->getType() == 100))) {
                            $tva[$taxAsString] = $tva[$taxAsString]  + (($priceProductSupp - (($priceProductSupp * ($realCart->getCoupon()->getPercent() / 100))) - ($priceProductSupp * ($realCart->getDiscount()->getPercent() / 100))));
                        } else {
                            $tva[$taxAsString] = $tva[$taxAsString] + (($priceProductSupp - ($priceProductSupp * ($realCart->getCoupon()->getPercent() / 100))));
                        }
                    }
                } else {
                    if ((!is_null($realCart->getDiscount()) && ($realCart->getDiscount()->getType() == 0 || $realCart->getDiscount()->getType() == 100))) {
                        $tva[$taxAsString] = $tva[$taxAsString] + (($priceProductSupp * (1 - ($realCart->getDiscount()->getPercent() / 100))));
                    } else {
                        $tva[$taxAsString] = $tva[$taxAsString] + ($priceProductSupp);
                    }
                }

                $realCart->addElement($parentElement);
                $this->em->persist($parentElement);
                $this->em->flush($parentElement);
            }
            if ($element->getMeal() !== null) {
                $totalSupp = 0;
                $totalProduct = 0;
                $tax = $this->em->getRepository('ClabRestaurantBundle:Tax')->find($element->getTax()->getId());
                $parentElement = new CartElement();
                $parentElement->setQuantity($element->getQuantity());
                $parentElement->setPrice($element->getPrice());
                $parentElement->setCart($realCart);
                $parentElement->setParent($element->getParent());
                $parentElement->setSale($element->getSale());
                $parentElement->setTax($tax);
                $mealTmp = $this->em->getRepository('ClabRestaurantBundle:Meal')->find($element->getMeal()->getId());
                $parentElement->setMeal($mealTmp);

                if ($element->getChildrens() !== null) {
                    foreach ($element->getChildrens() as $child) {
                        $cleanProduct = $this->em->getRepository('ClabRestaurantBundle:Product')->find($child->getProduct()->getId());
                        $totalSupp = $totalSupp + $child->getPrice();
                        $totalProduct = $totalProduct + $cleanProduct->getPrice() + $child->getPrice();
                    }
                    foreach ($element->getChildrens() as $child) {
                        $newChildElement = new CartElement();
                        $cleanProduct = $this->em->getRepository('ClabRestaurantBundle:Product')->find($child->getProduct()->getId());
                        $taxChild = $this->em->getRepository('ClabRestaurantBundle:Tax')->find($child->getTax()->getId());
                        $newChildElement->setProduct($cleanProduct);
                        $newChildElement->setQuantity($child->getQuantity());
                        $newChildElement->setPrice($child->getPrice());
                        $newChildElement->setCart($realCart);
                        $newChildElement->setSale($child->getSale());
                        $newChildElement->setTax($taxChild);
                        $newChildElement->setParent($parentElement);

                        $parentElement->addChildren($newChildElement);

                        foreach ($child->getChildrens() as $child2) {
                            $newChildElement->addChildren($child2);
                        }
                        foreach ($child->getChoices() as $option) {
                            $realOption2 = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->find($option->getId());
                            $newChildElement->addChoice($realOption2);
                        }
                        if (is_null($realCart->getCoupon())) {
                            if (!is_null($realCart->getDiscount()) && ($realCart->getDiscount()->getType() == 0 || $realCart->getDiscount()->getType() == 100)) {
                                $priceRatio = ((($cleanProduct->getPrice() + $newChildElement->getPrice()) * (1 - $realCart->getDiscount()->getPercent() / 100)) * ($mealTmp->getPrice() + $totalSupp)) / $totalProduct;
                            } else {
                                $priceRatio = ((($cleanProduct->getPrice() + $newChildElement->getPrice())) * ($mealTmp->getPrice() + $totalSupp)) / $totalProduct;
                            }
                        } else {
                            if ($realCart->getCoupon()->getPercent() == null) {
                                if (!is_null($realCart->getDiscount()) && ($realCart->getDiscount()->getType() == 0 || $realCart->getDiscount()->getType() == 100)) {
                                    $totalMealDiscounted = ($mealTmp->getPrice() + $totalSupp) - ($cart->getDiscount()->getPercent() / 100);
                                    $ratioCoupon = ($totalMealDiscounted * $realCart->getCoupon()->getAmount()) / ($order->getCart()->getDiscountPrice());
                                    $priceRatio = (($totalMealDiscounted - $ratioCoupon) * ($cleanProduct->getPrice() + $newChildElement->getPrice())) / $totalProduct;
                                } else {
                                    $totalMealDiscounted = ($mealTmp->getPrice() + $totalSupp);
                                    $ratioCoupon = ($totalMealDiscounted * $realCart->getCoupon()->getAmount()) / ($order->getCart()->getBasePrice());
                                    $priceRatio = (($totalMealDiscounted - $ratioCoupon) * ($cleanProduct->getPrice() + $newChildElement->getPrice())) / $totalProduct;
                                }
                            } else {
                                if (!is_null($realCart->getDiscount()) && ($realCart->getDiscount()->getType() == 0 || $realCart->getDiscount()->getType() == 100)) {
                                    $priceRatio = ((($cleanProduct->getPrice() + $newChildElement->getPrice())  * ($mealTmp->getPrice() + $totalSupp) * (1 - $realCart->getDiscount()->getPercent() / 100) * (1 - $realCart->getCoupon()->getPercent() / 100)) / $totalProduct);
                                } else {
                                    $priceRatio = ((($cleanProduct->getPrice() + $newChildElement->getPrice())  * ($mealTmp->getPrice() + $totalSupp) * (1 - $realCart->getCoupon()->getPercent() / 100)) / $totalProduct);
                                }
                            }
                        }

                        //Calcul TVA MEAL
                        $taxAsString = (string) $taxChild->getValue();
                        $tva[$taxAsString] = $tva[$taxAsString] + $priceRatio;
                        $realCart->addElement($newChildElement);

                        $this->em->persist($newChildElement);
                        $this->em->flush($newChildElement);
                    }
                }
                $realCart->addElement($parentElement);
                $this->em->persist($parentElement);
                $this->em->flush($parentElement);
            }
        }
        $ht['5.5'] = $tva['5.5'];
        $ht['10'] = $tva['10'];
        $ht['20'] = $tva['20'];
        $ht['7'] = $tva['7'];

        $tva['5.5'] = $tva['5.5']-$tva['5.5']/1.055;
        $tva['7'] = $tva['7']-$tva['7']/1.07;
        $tva['10'] = $tva['10']-$tva['10']/1.10;
        $tva['20'] = $tva['20']-$tva['20']/1.20;


        $orderTmp = new OrderDetail();
        $orderTmp->setRestaurant($restaurant);
        $delivery = false;
        if ($deliveryAddress) {
            $delivery = new Delivery();
            $delivery->setAddress($deliveryAddress);
            $delivery->setRestaurant($restaurant);
            $delivery->setOrder($orderTmp);
            $day = date('l');
            $dayAsInt = date('N', strtotime($day));
            $deliveryDays = $this->em->getRepository('ClabDeliveryBundle:DeliveryDay')->findBy(array(
                'restaurant' => $restaurant,
                'weekDay' => $dayAsInt,
            ));
            $delivrable = false;
            foreach ($deliveryDays as $deliveryDay) {
                $addressForm = $this->deliveryManager->checkLocation($delivery, $deliveryDays[0]);
                if ($addressForm['success'] != false) {
                    $delivrable = true;
                    break;
                } else {
                    continue;
                }
            }
            if ($delivrable == false) {
                throw new \Exception("Ce restaurant ne livre pas à l'adresse indiquée");
            }
            $this->em->persist($delivery);
        }

        $orderTmp->setPrice($order->getPrice());
        $orderTmp->setState($order->getState());
        $orderTmp->setOnlinePayment($order->getOnlinePayment());
        $orderTmp->setFacebookPage($order->getFacebookPage());
        $orderTmp->setProfile($order->getProfile());
        $orderTmp->setComment($order->getComment());
        $orderTmp->setOrderType($order->getOrderType());
        $orderTmp->setOrderStatement($order->getOrderStatement());
        $orderTmp->setRestoflashTransaction($order->getRestoflashTransaction());
        $orderTmp->setDelivery($order->getDelivery());
        $orderTmp->setIsPaid($order->getIsPaid());
        $orderTmp->setIsTest($order->getIsTest());
        $orderTmp->setState($order->getState());
        $orderTmp->setHash(sha1(time().$order->getPrice().$restaurant->getId()));
        $orderTmp->setTime($order->getTime());
        $orderTmp->setSource($order->getSource());
        $orderTmp->setCompany($order->getCompany());

        if($order->getCompany()){
            $company = $order->getCompany();
            $company
                ->addOrder($orderTmp)
                ->updateBalance($orderTmp);
            $this->em->persist($company);
        }
        if (array_key_exists('5.5', $tva)) {
            $orderTmp->setTva55(round($tva['5.5'],2));
        } else {
            $orderTmp->setTva55(0);
        }
        if (array_key_exists('7', $tva)) {
            $orderTmp->setTva7(round($tva['7'],2));
        } else {
            $orderTmp->setTva7(0);
        }
        if (array_key_exists('20', $tva)) {
            $orderTmp->setTva20(round($tva['20'],2));
        } else {
            $orderTmp->setTva20(0);
        }
        if (array_key_exists('10', $tva)) {
            $orderTmp->setTva10(round($tva['10'],2));
        } else {
            $orderTmp->setTva10(0);
        }
        $orderTmp->setCart($realCart);
        if ($delivery !== false) {
            $orderTmp->setDelivery($delivery);
        }

        $orderTmp->setOnSitePayments($order->getOnSitePayments());

        $this->em->persist($orderTmp);

        $this->em->flush();
        if ($orderTmp->getState() == OrderDetail::ORDER_STATE_VALIDATED) {
            $this->validateOrder($orderTmp);
        }

        return $orderTmp;
    }

    /**
     * @param Cart $cart
     *
     *
     * @throws \Exception
     *                    Check if product is still in stock
     */
    public function checkStock(Cart $cart)
    {
        foreach ($cart->getElements() as $element) {
            if ($element->getProduct() && !$element->getProduct()->getUnlimitedStock()) {
                $stock = $element->getProduct()->getStock();

                if ($stock < $element->getQuantity()) {
                    if ($stock == 0) {
                        $this->em->remove($element);
                        $this->em->flush();
                        throw new \Exception('Le stock de '.$element->getProduct()->getName().' est épuisé, veuillez revoir votre panier');
                    } else {
                        $element->setQuantity($stock);
                        $this->em->flush();
                        throw new \Exception('Le stock de '.$element->getProduct()->getName().' a été modifié, veuillez revoir votre panier');
                    }
                }
            } elseif ($element->getMeal()) {
                foreach ($element->getChildrens() as $children) {
                    $stock = $children->getProduct()->getStock();
                    if ($stock < 1 && !$children->getProduct()->getUnlimitedStock()) {
                        $this->em->remove($element, true);
                        $this->em->flush();
                        throw new \Exception('Le stock de '.$children->getProduct()->getName().' est épuisé, veuillez revoir votre panier');
                    }
                }
            }
        }
    }

    /**
     * @param Cart $cart
     *                   Update the stock automatically
     */
    public function updateStock(Cart $cart)
    {
        foreach ($cart->getElements() as $element) {
            if ($element->getProduct() && !$element->getProduct()->getUnlimitedStock()) {
                $element->getProduct()->setStock($element->getProduct()->getStock() - $element->getQuantity());
            } elseif ($element->getMeal()) {
                foreach ($element->getChildrens() as $children) {
                    if (!$children->getProduct()->getUnlimitedStock()) {
                        $children->getProduct()->setStock($children->getProduct()->getStock() - $children->getQuantity());
                    }
                }
            }
        }
    }

    /**
     * @param Cart $cart
     *
     * @throws \Exception
     *                    Check if sale is still available
     */
    public function checkSale(Cart $cart)
    {
        foreach ($cart->getElements() as $element) {
            if ($element->getSale() !== null) {
                if ($element->getProduct()->getCurrentSale() == null) {
                    $element->setPrice($element->getProduct()->getCurrentPrice());
                    $element->setSale(null);
                    $this->em->flush();
                    throw new \Exception('La promotion a expirée, veuillez revoir votre panier');
                } elseif ($element->getProduct()->getCurrentSale()->getId() != $element->getSale()->getId()) {
                    $element->setPrice($element->getProduct()->getCurrentPrice());
                    $element->setSale($element->getProduct()->getCurrentSale());
                    $this->em->flush();
                    throw new \Exception('La promotion a été modifiée, veuillez revoir votre panier');
                }
            }
        }
    }

    /**
     * @param Cart $cart
     *
     * @throws \Exception
     *                    Check if coupon is still available
     */
    public function checkCoupon(Cart $cart)
    {
        if ($cart->getCoupon()) {
            $availability = $cart->getCoupon()->isAvailableForCart($cart);

            if (!$availability['response']) {
                $cart->setCoupon(null);
                $this->em->flush();
                throw new \Exception('Le coupon n\'est plus disponible, veuillez revoir votre panier');
            }
        }
    }

    /**
     * @param OrderDetail $order
     *
     * @return OrderDetail
     *                     A restaurant accept an order
     */
    public function validateOrder(OrderDetail $order, $callCenter = false)
    {
        $order->setState(OrderDetail::ORDER_STATE_VALIDATED);

        foreach($order->getCart()->getLoyalties() as $loyalty) {
            $loyalty->setCart($order->getCart());
            $loyalty->setIsUsed(true);
        }

        $this->cartManager->emptyCart($order->getRestaurant());
        $this->em->flush();
        // customer confirmation mail
        if(!$callCenter) {
            try {
                $this->mailManager->confirmOrder($order->getRestaurant(), $order);
            } catch (\Exception $e) {
                $this->logger->error('Bug order confirmation mail : '.$e->getMessage());
            }
        }

        // admin + manager notification mail
        try {
            $this->mailManager->mailNotification($order);
        } catch (\Exception $e) {
            $this->logger->error('Bug order notification mail : '.$e->getMessage());
        }

        return $order;
    }

    /**
     * @param OrderDetail $order
     *
     * @return OrderDetail
     *                     Close Order when it's done
     */
    public function closeOrder(OrderDetail $order)
    {
        $order->setState(OrderDetail::ORDER_STATE_TERMINATED);

        if ($order->getDelivery()) {
            $order->getDelivery()->setState(Delivery::DELIVERY_STATE_DONE);
        }

        $this->em->flush();

        return $order;
    }

    public function cancelAvailable(OrderDetail $order)
    {
        $now = date_create('now');
        $now->modify('+1 hour');
        $today = date_create('today');

        if ($now < $order->getTime()) {
            return true;
        }

        return false;
    }

    public function cancelOrder(OrderDetail $order)
    {
        $sendMail = $order->getState() >= OrderDetail::ORDER_STATE_VALIDATED && !$order->isTest();
        $order->setState(OrderDetail::ORDER_STATE_CANCELLED);

        if ($order->getDelivery()) {
            $order->getDelivery()->setState(Delivery::DELIVERY_STATE_CANCELLED);
        }

        $this->em->flush();

        return $order;
    }

    public function formatPrice($price)
    {
        $price = round($price, 2);
        $price = str_replace('.', '', $price);
        $price = str_replace(',', '', $price);

        return $price;
    }

    public function cancelOldOrders()
    {
        $now = date_create('now');
        $start = clone $now;
        $start->modify('-2 days');
        $end = clone $now;
        $orders = $this->repository->findAllBetweenDate($start, $end);
        $count = 0;
        foreach ($orders as $order) {
            if ($order->getState() == OrderDetail::ORDER_STATE_WAITING_PAYMENT || $order->getState() == OrderDetail::ORDER_STATE_WAITING_PAYMENT_RESTOFLASH) {
                $this->cancelOrder($order);
                ++$count;
            } elseif ($order->getState() >= OrderDetail::ORDER_STATE_VALIDATED && $order->getState() < OrderDetail::ORDER_STATE_TERMINATED) {
                $this->closeOrder($order);
                ++$count;
            }
        }

        return $count;
    }
}
