<?php

namespace Clab\ApiBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\Cart;
use Clab\ShopBundle\Entity\CartElement;
use Clab\ShopBundle\Entity\Loyalty;
use Clab\ShopBundle\Entity\OrderDetailCaisse;
use Clab\ShopBundle\Entity\OrderType;
use Clab\ShopBundle\Entity\Payment;
use Clab\ShopBundle\Manager\OrderManager;
use Clab\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Clab\ShopBundle\Entity\OrderDetail;

class RestOrderController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Orders",
     *      description="Order list for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      parameters={
     *          {"name"="start", "dataType"="string", "required"=false, "description"="Start format YYYY-mm-dd"},
     *          {"name"="end", "dataType"="string", "required"=false, "description"="End format YYYY-mm-dd"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getForRestaurantAction(Restaurant $restaurant, Request $request)
    {
        $parameters = $request->query->all();

        if (isset($parameters['start']) && $parameters['start']) {
            $parameters['start'] = date_create_from_format('Y-m-d', $parameters['start']);
        }

        if (isset($parameters['end']) && $parameters['end']) {
            $parameters['end'] = date_create_from_format('Y-m-d', $parameters['end'])->modify('+1 day');
        } else if(!isset($parameters['end'])) {
            $parameters['day'] = new \DateTime();
        }

        $orders = $this->get('app_shop.order_manager')->getForRestaurant($restaurant, $parameters);

        foreach ($orders as $order) {
            foreach ($order->getCart()->getElements() as $element ) {
                if ($element->getProduct()) {
                    $element
                        ->getProduct()
                        ->setCategory(null);
                }
            }
            $order->setRestaurant(null);
            if ($order->getDelivery()) {
                $order
                    ->getDelivery()
                    ->setRestaurant(null);
            }

        }
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($orders, 'json', SerializationContext::create()->setGroups(array('pro')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      description="Order list beetween dates",
     *      parameters={
     *          {"name"="start", "dataType"="string", "required"=false, "description"="Start format YYYY-mm-dd"},
     *          {"name"="end", "dataType"="string", "required"=false, "description"="End format YYYY-mm-dd"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     */
    public function getBeetweenDatesAction(Request $request)
    {
        $parameters = $request->query->all();

        if (isset($parameters['start']) && $parameters['start']) {
            $parameters['start'] = date_create_from_format('Y-m-d', $parameters['start']);
        }

        if (isset($parameters['end']) && $parameters['end']) {
            $parameters['end'] = date_create_from_format('Y-m-d', $parameters['end']);
        }

        if (isset($parameters['closed']) && $parameters['closed']) {
            $orders = $this->getDoctrine()->getRepository('ClabShopBundle:OrderDetail')->findAllBetweenDate($parameters['start'], $parameters['end'], array('closed' => true));
        } else {
            $orders = $this->getDoctrine()->getRepository('ClabShopBundle:OrderDetail')->findAllBetweenDate($parameters['start'], $parameters['end']);
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($orders, 'json');

        return new Response($response);
    }

    /**
     * * Get available meals for given restaurant Id.
     *
     * ### Response format ###
     *
     *		[
     *		  {
     *		    "id": 1,
     *		    "hash": "7c2dbea12e884b4f1dc2c5a060bd1413bd481792",
     *		    "is_paid": true,
     *		    "time": {
     *		      "date": "2016-02-12 12:30:00.000000",
     *		      "timezone_type": 3,
     *		      "timezone": "Europe/Berlin"
     *		    },
     *		    "state": 200,
     *		    "profile": {
     *		      "email": "test@hotmail.fr",
     *		      "first_name": "Hélène",
     *		      "last_name": "S",
     *		      "cover": "/images/blankuser.png",
     *		      "phone": "0666666666"
     *		    },
     *		    "order_type": "preorder",
     *		    "comment": null,
     *		    "price": 9.27,
     *		    "tva55": 0,
     *		    "tva7": 0,
     *		    "tva10": 0.93,
     *		    "tva20": 0,
     *		    "discount": {
     *		      "id": 133,
     *		      "name": "Offre découverte : -10% sur votre commande Clickeat !",
     *		      "percent": 10,
     *		      "type": 0
     *		    },
     *		    "elements": {
     *		      "60baba08b114b6906429c02d1c0160929319adcf": {
     *		        "quantity": 1,
     *		        "price": 0,
     *		        "tva": 10,
     *		        "product": "Evian 50cl"
     *		      },
     *		      "05252c9bbf331abf394176bdfeb8f8f28351900e": {
     *		        "quantity": 1,
     *		        "price": 0,
     *		        "tva": 10,
     *		        "product": "Salade de fruits frais"
     *		      },
     *		      "3dec0a6e06a36c8de0d7c38b31b406e0b6fb70e0": {
     *		        "quantity": 1,
     *		        "price": 0,
     *		        "tva": 10,
     *		        "product": "Le Dumber",
     *		        "choices": {
     *		          "Pain": {
     *		            "30915": {
     *		              "value": "Nature",
     *		              "price": 4
     *		            }
     *		          },
     *		          "Sauce": {
     *		            "30987": {
     *		              "value": "Moutarde au miel artisanale",
     *		              "price": 0
     *		            }
     *		          }
     *		        }
     *		      },
     *		      "b2662521cac27f4ebeaa12934fdb75baeb264698": {
     *		        "quantity": 1,
     *		        "price": 10.2,
     *		        "tva": 10,
     *		        "meal": "Le Dumber + Dessert ou Chips + Boisson",
     *		        "supplement": 0
     *		      }
     *		    },
     *		    ...
     *		  },
     *		  ...
     *		]
     *
     * @ApiDoc(
     *      section="Orders",
     *      description="Order list for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      parameters={
     *          {"name"="start", "dataType"="string", "required"=false, "description"="Start format YYYY-mm-dd"},
     *          {"name"="end", "dataType"="string", "required"=false, "description"="End format YYYY-mm-dd"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getTicketForRestaurantAction(Restaurant $restaurant, Request $request)
    {
        $parameters = $request->query->all();

        if (isset($parameters['start']) && $parameters['start']) {
            $parameters['start'] = date_create_from_format('Y-m-d', $parameters['start']);
        }

        if (isset($parameters['end']) && $parameters['end']) {
            $parameters['end'] = date_create_from_format('Y-m-d', $parameters['end']);
        }
        $results = array();
        try {
            $orders = $this->get('app_shop.order_manager')->getForRestaurant($restaurant, $parameters);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        if (count($orders) <= 0) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Aucune commande',
            ]);
        }

        foreach ($orders as $key => $order) {
            $results[$key]['id'] = $order->getId();
            $results[$key]['hash'] = $order->getHash();
            $results[$key]['is_paid'] = $order->getIsPaid();
            $results[$key]['time'] = $order->getTime();
            $results[$key]['state'] = $order->getState();
            $results[$key]['profile']['email'] = $order->getProfile()->getEmail();
            $results[$key]['profile']['first_name'] = $order->getProfile()->getFirstName();
            $results[$key]['profile']['last_name'] = $order->getProfile()->getLastName();
            $results[$key]['profile']['cover'] = $order->getProfile()->getCover();
            $results[$key]['profile']['phone'] = $order->getProfile()->getPhone();
            $results[$key]['order_type'] = $order->getOrderType()->getSlug();
            $results[$key]['comment'] = $order->getComment();
            $results[$key]['price'] = $order->getPrice();
            $results[$key]['tva55'] = $order->getTva55();
            $results[$key]['tva7'] = $order->getTva7();
            $results[$key]['tva10'] = $order->getTva10();
            $results[$key]['tva20'] = $order->getTva20();

            if (!is_null($order->getCart()->getDiscount())) {
                $results[$key]['discount']['id'] = $order->getCart()->getDiscount()->getId();
                $results[$key]['discount']['name'] = $order->getCart()->getDiscount()->getName();
                $results[$key]['discount']['percent'] = $order->getCart()->getDiscount()->getPercent();
                $results[$key]['discount']['type'] = $order->getCart()->getDiscount()->getType();
            }
            foreach ($order->getCart()->getElements() as $element) {
                $results[$key]['elements'][$element->getHash()]['quantity'] = $element->getQuantity();
                $results[$key]['elements'][$element->getHash()]['price'] = $element->getPrice();
                $results[$key]['elements'][$element->getHash()]['tva'] = $element->getTax()->getValue();
                if (!is_null($element->getProduct())) {
                    $results[$key]['elements'][$element->getHash()]['product'] = $element->getProduct()->getName();
                }
                if (!is_null($element->getMeal())) {
                    $results[$key]['elements'][$element->getHash()]['meal'] = $element->getMeal()->getName();
                    $supplement = 0;
                    foreach ($element->getMeal()->getChildrens() as $children) {
                        $supplement += $children->getPrice() * $children->getQuantity();
                    }
                    $results[$key]['elements'][$element->getHash()]['supplement'] = $supplement;
                }
                if (!is_null($element->getParent())) {
                    $results[$key]['elements'][$element->getHash()]['parent']['name'] = $element->getParent()->getMeal()->getName();
                    $results[$key]['elements'][$element->getHash()]['parent']['hash'] = $element->getParent()
                        ->getHash();
                }

                foreach ($element->getChoices() as $choice) {
                    $results[$key]['elements'][$element->getHash()]['choices'][$choice->getOption()->getName()][$choice->getId()] = array('value' => $choice->getValue(), 'price' => $choice->getPrice());
                }
            }
        }

        $response = json_encode($results);

        return new Response($response);
    }
    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="Get order",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     */
    public function getAction($id)
    {
        list($roles, $order) = $this->get('app_shop.order_manager')->getOrder($id, $this->get('api.session_manager')->getUser());

        if (!$order) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Vous n\'avez pas accès à cette commande');
        }

        $groups = array();
        if ($roles['manager']) {
            $groups = array('pro');
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($groups, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="Get order cart",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\Cart"
     * )
     * @ParamConverter("order", class="ClabRestaurantBundle:OrderDetail", options={"id" = "id"})
     */
    public function getCartAction(OrderDetail $order)
    {
        $cart = $order->getCart();

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($cart, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="Get order cart",
     *      requirements={
     *          {"name"="OrderId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("order", class="ClabShopBundle:OrderDetail", options={"id" = "id"})
     */
    public function changeOrderStateAction(OrderDetail $order, Request $request)
    {
        $state = $request->get('state');
        $order->setState($state);
        $this->getDoctrine()->getManager()->persist($order);
        $this->getDoctrine()->getManager()->flush();

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($order, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="Patch order",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        list($roles, $order) = $this->get('app_shop.order_manager')->getOrder($id, $this->get('api.session_manager')->getUser());

        if (!$order) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Vous n\'avez pas accès à cette commande');
        }

        $currentState = $order->getState();
        $states = OrderDetail::getStateArray();

        $availableStates = array();
        if ($roles['owner'] && $this->get('app_shop.order_manager')->cancelAvailable($order)) {
            $availableStates[OrderDetail::ORDER_STATE_CANCELLED] = OrderDetail::ORDER_STATE_CANCELLED;
        }
        if ($roles['manager']) {
            $availableStates[(string) OrderDetail::ORDER_STATE_READY] = OrderDetail::ORDER_STATE_READY;
            $availableStates[(string) OrderDetail::ORDER_STATE_READY_PACKING] = OrderDetail::ORDER_STATE_READY_PACKING;
            $availableStates[(string) OrderDetail::ORDER_STATE_READY_PACKED] = OrderDetail::ORDER_STATE_READY_PACKED;
            $availableStates[(string) OrderDetail::ORDER_STATE_TERMINATED] = OrderDetail::ORDER_STATE_TERMINATED;
        }

        $form = $this->get('form.factory')->createNamedBuilder('', 'form', null, array('csrf_protection' => false))
            ->add('state', 'choice', array(
                'choices' => $availableStates,
                'constraints' => array(new GreaterThan(array('value' => $currentState))),
            ))
        ->getForm();
        $form->submit($this->getRequest());

        if ($form->isValid()) {
            $state = $form->get('state')->getData();

            if ($state && $state > $currentState) {
                $order->setState($state);
                $em->flush();
                switch ($state) {
                    case OrderDetail::ORDER_STATE_READY;
                        break;
                    case OrderDetail::ORDER_STATE_READY_PACKING;
                        break;
                    case OrderDetail::ORDER_STATE_READY_PACKED;
                        break;
                    case OrderDetail::ORDER_STATE_TERMINATED;
                        $this->get('api.session_manager')->getSession()->addOrder($order);
                        $em->flush();

                        $this->get('app_shop.order_manager')->closeOrder($order);
                        break;
                    default:
                        break;
                }
            }

            $em->flush();

            return new JsonResponse($order);
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="cancel order",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("order", class="ClabRestaurantBundle:OrderDetail", options={"id" = "id"})
     */
    public function removeAction(OrderDetail $order)
    {
        $this->get('app_shop.order_manager')->cancelOrder($order);

        return new JsonResponse('Deleted ok', 200);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="close order",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("order", class="ClabRestaurantBundle:OrderDetail", options={"id" = "id"})
     */
    public function closeOrderAction(OrderDetail $order)
    {
        $this->get('app_shop.order_manager')->closeOrder($order);

        return new JsonResponse('closed ok', 200);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="validate order",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("order", class="ClabRestaurantBundle:OrderDetail", options={"id" = "id"})
     */
    public function validateOrderAction(OrderDetail $order)
    {
        $this->get('app_shop.order_manager')->validateOrder($order);

        return new JsonResponse('validate ok', 200);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="Create order from cart",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Cart identifier"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("cart", class="ClabShopBundle:Cart", options={"id" = "id"})
     */
    public function postOrderCartAction(Cart $cart)
    {
        /**
         * @var $orderManager OrderManager
         */
        $orderManager = $this->get('app_shop.order_manager');
        $user = $this->getUser();
        $order = $orderManager->createOrderFromCart($cart, $user);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($order, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="get time slot for a cart",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("cart", class="ClabRestaurantBundle:Cart", options={"id" = "id"})
     */
    public function getSlotsFromCartAction(Cart $cart)
    {
        $slots = $this->get('app_shop.order_manager')->getSlotsForCart($cart);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($slots, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="get payment method for a cart",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("cart", class="ClabRestaurantBundle:Cart", options={"idCart" = "id"})
     * @ParamConverter("cart", class="ClabUserBundle:User", options={"id" = "id"})
     */
    public function getPaymentMethodsForCartAction(Cart $cart, User $user)
    {
        $paymentChoices = $this->get('app_shop.order_manager')->getPaymentMethodsForCart($cart, $user);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($paymentChoices, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="initialise the order",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("order", class="ClabRestaurantBundle:OrderDetail", options={"id" = "id"})
     */
    public function initOrderAction(OrderDetail $order)
    {
        $this->get('app_shop.order_manager')->initOrder($order);

        return new JsonResponse('ok', 200);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="initialise the order",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("order", class="ClabRestaurantBundle:OrderDetail", options={"id" = "id"})
     */
    public function getPayzenRequestAction(OrderDetail $order)
    {
        $payzenRequest = $this->get('app_shop.order_manager')->getPayzenRequest($order);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($payzenRequest, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="save order from caisse",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     */
    public function saveCaisseOrderAction(Request $request)
    {
        $content = $request->getContent();

        if (empty($content)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'no JSON data',
            ]);
        }

        if (0 !== strpos($request->headers->get('Content-Type'), 'application/json')) {
            return new JsonResponse([
                'success' => false,
                'message' => 'malformed JSON data',
            ]);
        }

        $orderSent = json_decode($content, true);

        $logger = $this->get('logger');

        $logger->error('order:'.$content);

        $order = new OrderDetailCaisse();
        $cart = new Cart();
        $cart->setRestaurant($this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($orderSent['restaurantID']));
        $this->getDoctrine()->getManager()->persist($cart);
        $time = $orderSent['time'];
        $timeFormatted = new \DateTime($time);
        foreach ($orderSent['menus'] as $meal) {
            $element = new CartElement();
            $element->setMeal($this->getDoctrine()->getRepository('ClabRestaurantBundle:Meal')->find($meal['mealID']));
            $element->setQuantity(1);
            $element->setPrice($meal['price']);

            foreach ($meal['selection'] as $slotChoice) {
                $choices = $slotChoice['choices'];

                $childElement = new CartElement();
                $childElement->setProduct($this->getDoctrine()->getRepository('ClabRestaurantBundle:Product')->find($slotChoice['productID']));
                $childElement->setQuantity(1);
                $childElement->setPrice($slotChoice['price']);
                $childElement->setTax($this->getDoctrine()->getRepository('ClabRestaurantBundle:Tax')->find($slotChoice['vat']['vatId']));
                if (!empty($choices)) {
                    foreach ($choices as $choice) {
                        $childElement->addChoice($this->getDoctrine()->getRepository('ClabRestaurantBundle:OptionChoice')->find($choice['choiceID']));
                        $childElement->setPrice($childElement->getPrice() + $choice['price']);
                    }
                }
                $this->getDoctrine()->getManager()->persist($childElement);
                $element->addChildren($childElement);
            }
            $element->setCart($cart);
            $this->getDoctrine()->getManager()->persist($element);
            $cart->addElement($element);
        }

        if (isset($orderSent['user'])) {
            $user = $this->getDoctrine()->getRepository(User::class)->find($orderSent['user']);

            if ($user) {
                $order->setProfile($user);

                if (isset($orderSent['loyalties'])) {
                    foreach ($orderSent['loyalties'] as $loyalties) {
                        $loyalty = $this->getDoctrine()->getRepository(Loyalty::class)->find($loyalties);
                        if($loyalty) {
                            $cart->addLoyalty($loyalty);
                            $loyalty->setIsUsed(true);
                            $loyalty->setCart($cart);
                            $this->getDoctrine()->getManager()->persist($loyalty);
                        }
                    }
                }
            }
        }

        if ($orderSent['covers']) {
            $order->setCovers($orderSent['covers']);
        }

        foreach ($orderSent['products'] as $product) {
            $element = new CartElement();
            $element->setProduct($this->getDoctrine()->getRepository('ClabRestaurantBundle:Product')->find($product['productID']));
            $element->setQuantity($product['quantity']);
            $element->setPrice($product['price']);
            $element->setTax($this->getDoctrine()->getRepository('ClabRestaurantBundle:Tax')->find($product['vat']['vatId']));

            foreach ($product['choices'] as $choice) {
                $element->addChoice($this->getDoctrine()->getRepository('ClabRestaurantBundle:OptionChoice')->find($choice['choiceID']));
                $element->setPrice($element->getPrice() + $choice['price']);
            }
            $element->setCart($cart);
            $this->getDoctrine()->getManager()->persist($element);
            $cart->addElement($element);
        }
        $order->setCart($cart);
        $order->setIsPaid($orderSent['isPaid']);
        $order->setTime($timeFormatted);
        $order->setOrderType($orderSent['orderType']);
        $order->setReference($orderSent['ref']);
        $order->setState($orderSent['state']);
        $order->setPrice($orderSent['amount']);

        foreach ($orderSent['vats'] as $vat) {
        }

        if (array_key_exists('comment', $orderSent)) {
            $order->setComment($orderSent['comment']);
        }
        if (array_key_exists('userID', $orderSent['client'])) {
            $order->setProfile($this->getDoctrine()->getRepository('ClabUserBundle:User')->find(($orderSent['client']['userID'])));
        }
        $payments = [];
        foreach ($orderSent['payments'] as $payment) {
            $method = $this->getDoctrine()->getRepository('ClabShopBundle:PaymentMethod')->findOneBy(array(
                'code' => $payment['mode'],
            ));
            if (!is_null($method)) {
                $p = new Payment();
                $p->setAmount($payment['amount']);
                $p->setIsCanceled(false);
                $p->addPaymentMethod($method);
                $payments[] = $p;
                $this->getDoctrine()->getManager()->persist($p);
            }
        }
        $order->setPayments($payments);

        $order->setRestaurant($this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($orderSent['restaurantID']));
        $order->setPin($this->getDoctrine()->getRepository('ClabUserBundle:Pincode')->find($orderSent['waiterID']));
        if(isset($orderSent['vats'])) {
            foreach ($orderSent['vats'] as $key => $vat) {
                if ($key == 'TVA 10%') {
                    $order->setTva10($vat);
                }
                if ($key == 'TVA 5,5%') {
                    $order->setTva55($vat);
                }
                if ($key == 'TVA 7%') {
                    $order->setTva7($vat);
                }
                if ($key == 'TVA 20%') {
                    $order->setTva20($vat);
                }
            }
        }

        $this->getDoctrine()->getManager()->persist($order);
        $this->getDoctrine()->getManager()->flush();

        if ($order->getProfile()) {
            $this
                ->get('app_shop.loyalty_manager')
                ->refreshLoyalties($order->getProfile())
                ->generateLoyaltyFromOrder($order)
            ;
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'order added',
            'orderId' => $order->getId(),
        ]);
    }

    /**
     * @ApiDoc(
     *      section="Orders",
     *      resource=true,
     *      description="Edit state order caisse",
     *      requirements={
     *          {"name"="OrderId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="state", "dataType"="integer", "required"=true, "description"="state of order"},
     *          {"name"="preparation-state", "dataType"="integer", "required"=true, "preparation state of order"}
     *      },
     *      output="Clab\ShopBundle\Entity\OrderDetail"
     * )
     * @ParamConverter("order", class="ClabShopBundle:OrderDetailCaisse", options={"id" = "id"})
     */
    public function changeOrderCaisseStateAction(OrderDetailCaisse $order, Request $request)
    {
        $state = $request->get('state');
        $preparationState = $request->get('preparation-state');
        $cancelComment = $request->get('cancelComment');
        $order->setState($state);
        $order->setPreparationState($preparationState);
        $order->setCancelComment($cancelComment);
        $this->getDoctrine()->getManager()->persist($order);
        $this->getDoctrine()->getManager()->flush();

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($order, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource="/api/v1/ordertypes",
     *      description="get order types"
     * )
     */
    public function getOrderTypesAction() {

        $orderTypes = $this->getDoctrine()->getRepository(OrderType::class)->findAll();

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($orderTypes, 'json', SerializationContext::create()->setGroups(array('pro')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource="/api/v1/me/orders",
     *      description="get user orders"
     * )
     */
    public function getMyOrdersAction() {
        $orders = $this->getDoctrine()->getRepository(OrderDetail::class)
            ->findBy(array('profile'=>$this->getUser()) ,array('created' => 'DESC'),10);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($orders, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }
}
