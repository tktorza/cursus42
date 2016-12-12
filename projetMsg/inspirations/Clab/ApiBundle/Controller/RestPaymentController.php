<?php

namespace Clab\ApiBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Manager\CartManager;
use Clab\ShopBundle\Manager\OrderManager;
use Clab\StripeBundle\Form\Type\CardFormType;
use Clab\StripeBundle\Manager\CustomerManager;
use Clab\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class RestPaymentController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Payments",
     *      description="Get payment methods for cart",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Restaurant identifier"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getPaymentMethodsAction(Restaurant $restaurant)
    {
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');
        $cart = $cartManager->getCart($restaurant);

        /**
         * @var $orderManager OrderManager
         */
        $orderManager = $this->get('app_shop.order_manager');
        $paymentChoices = $orderManager->getPaymentMethodsForCart($cart, $this->getUser());

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($paymentChoices, 'json');

        return new Response($response);
    }

    /**
     * ### Returns a cards collection ###
     *      [
     *          {
     *              "id": "card_18OjdFJnWjwZO2KVUIe1OAmt",
     *              "object": "card",
     *              "exp_month": 12,
     *              "exp_year": 2016,
     *              "fingerprint": "KtQ3mm7l9ZfqoKr7",
     *              "funding": "credit",
     *              "last4": "4242",
     *              "metadata": [],
     *              "name": null,
     *              ...
     *          },
     *          {
     *              "id": "card_18OlKNJnWjwZO2KVvSDt6Lmo",
     *              "object": "card",
     *              "exp_month": 12,
     *              "exp_year": 2016,
     *              "fingerprint": "KtQ3mm7l9ZfqoKr7",
     *              "funding": "credit",
     *              "last4": "4242",
     *              "metadata": [],
     *              "name": null,
     *              ...
     *          }
     *      ]
     *
     * @ApiDoc(
     *      section="Payments",
     *      description="Get cards for payment",
     *      output="Card Object"
     * )
     */
    public function getCardsAction()
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();
        /**
         * @var $customerManager CustomerManager
         */
        $customerManager = $this->container->get('clab_stripe.customer.manager');

        if ($user->getStripeCustomerId()) {
            $cards = $customerManager->listCards($user);
        } else {
            $customerManager->create(null, $user->getEmail(), $user);
            $cards = $customerManager->listCards($user);
        }

        return new JsonResponse($cards['data']);
    }

    /**
     * ### Return a card object (id is the tokenId to use next time to pay) ###
     *      {
     *          "id": "card_18OlKNJnWjwZO2KVvSDt6Lmo",
     *              "object": "card",
     *              "exp_month": 12,
     *              "exp_year": 2016,
     *              "fingerprint": "KtQ3mm7l9ZfqoKr7",
     *              "funding": "credit",
     *              "last4": "4242",
     *              "metadata": [],
     *              "name": null,
     *              ...
     *      }
     *
     * @ApiDoc(
     *      section="Payments",
     *      description="Add new card to customer",
     *      parameters={
     *          {"name"="card", "dataType"="integer", "required"=true, "description"="Number on card"},
     *          {"name"="cvc", "dataType"="integer", "required"=true, "description"="CVC"},
     *          {"name"="month", "dataType"="integer", "required"=true, "description"="01 to 12"},
     *          {"name"="year", "dataType"="integer", "required"=true, "description"="Valid year"}
     *      }
     * )
     */
    public function postCardsAction(Request $request)
    {
        /**
         * @var $user User
         */
        $user = $this->getUser();
        /**
         * @var $customerManager CustomerManager
         */
        $customerManager = $this->container->get('clab_stripe.customer.manager');
        /**
         * @var $formFactory FormFactory
         */
        $formFactory = $this->get('form.factory');

        $data = array(
            'token' => $user->getStripeCustomerId(),
            'name' => trim($user->getFullName()) ?: $user->getEmail()
        );

        $form = $formFactory
            ->createNamedBuilder(null, new CardFormType(), null, array('csrf_protection' => false))
            ->getForm()
        ;
        $form->handleRequest($request);

        if (!$form->isValid()) {
            return $this->get('api.rest_manager')->getFormErrorResponse($form);
        }

        $cardData = array_merge($form->getData(), $data);
        $card = $customerManager->addCard($cardData);

        return new JsonResponse($card);
    }

    /**
     * ### Create an order from the cart and pay with a card ###
     *
     * @ApiDoc(
     *      section="Payments",
     *      description="Pay with a card from Stripe",
     *      parameters={
     *          {"name"="cardToken", "dataType"="string", "required"=true, "description"="Token of card from list"},
     *          {"name"="comment", "dataType"="string", "required"=false, "description"="Comment of order"},
     *          {"name"="phone", "dataType"="int", "required"=true, "description"="Phone of user"},
     *          {"name"="time", "dataType"="string", "required"=true, "description"="Time to get order"}
     *      }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function postPaymentAction(Restaurant $restaurant, Request $request)
    {
        $cardToken = $request->get('cardToken');

        $parameters = array(
            'comment' => $request->get('comment'),
            'phone' => $request->get('phone'),
            'time' => $request->get('time'),
            'source' => 'api'
        );

        if (!$cardToken) {
            return new JsonResponse(array(
                'success' => false,
                'message' => 'You need to have a valid cardToken'
            ), Response::HTTP_BAD_REQUEST);
        }

        if (!$parameters['time']) {
            return new JsonResponse(array(
                'success' => false,
                'message' => 'You need to have a time for order'
            ), Response::HTTP_BAD_REQUEST);
        }

        if (!$parameters['phone']) {
            return new JsonResponse(array(
                'success' => false,
                'message' => 'You need to have a phone for order'
            ), Response::HTTP_BAD_REQUEST);
        }

        /**
         * @var $user User
         */
        $user = $this->getUser();
        /**
         * @var $customerManager CustomerManager
         */
        $customerManager = $this->container->get('clab_stripe.customer.manager');
        /**
         * @var $orderManager OrderManager
         */
        $orderManager = $this->get('app_shop.order_manager');
        /**
         * @var $cartManager CartManager
         */
        $cartManager = $this->get('app_shop.cart_manager');

        $cart = $cartManager->getCart($restaurant);
        $card = $customerManager->getCard($user, $cardToken);

        $order = $orderManager->createOrderFromCart($cart, $user, $parameters);
        $orderDetails = $orderManager->initOrder($order);

        $customerManager->chargeCardForClickeat($user, $orderDetails->getPrice() * 100, $card);

        $orderDetails = $orderManager->validateOrder($orderDetails);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($orderDetails, 'json');

        return new Response($response);
    }
}