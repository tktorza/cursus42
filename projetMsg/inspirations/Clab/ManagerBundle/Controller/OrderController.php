<?php

namespace Clab\ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\ShopBundle\Entity\OrderDetail;

class OrderController extends Controller
{
    /**
     * @Secure(roles="ROLE_SELLER")
     */
    public function updateOrderAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->container->get('security.context')->getToken()->getUser();

        $restaurant = $this->getRestaurant();

        if (!$restaurant) {
            return $this->redirect($this->get('router')->generate('manager_switch_restaurant'));
        }

        $id = $request->request->get('order');

        $order = $em->getRepository('ClabShopBundle:OrderDetail')->find($id);

        if (null !== $order && $order->getCart()->getRestaurant() == $restaurant && $request->isMethod('POST')) {
            $this->container->get('app_shop.order_manager')->closeOrder($order);

            return new RedirectResponse($this->container->get('router')->generate('manager_dashboard'));
        }

        throw $this->createNotFoundException();
    }

    public function updatePreparationStateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $order = $em->getRepository('ClabShopBundle:OrderDetail')->find($id);

        if ($order && $request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $state = $request->request->get('preparationstate');
            if(!is_null($state)) {
                $order->setPreparationState($state);

                if($state == OrderDetail::ORDER_STATE_SERVED) {
                    $order->setState(OrderDetail::ORDER_STATE_TERMINATED);
                    $order->setIsPaid(true);
                }

                $em->flush();

                return new Response('success',200);
            }
            return new Response('error',400);
        }

        return new Response('error',400);

    }

    public function mailValidationAction($token)
    {
        $order = $this->getDoctrine()->getManager()->getRepository('ClabShopBundle:OrderDetail')->findOneBy(array('hash' => $token));

        if (!$order || $order->getState() !== OrderDetail::ORDER_STATE_VALIDATED) {
            throw $this->createNotFoundException();
        }

        $this->container->get('app_shop.order_manager')->closeOrder($order);

        return new Response(200, 'Commande validée');
    }

    public function getRestaurant()
    {
        $session = $this->getRequest()->getSession();

        if ($session->get('manager_restaurant')) {
            $slug = $session->get('manager_restaurant');
        } else {
            return;
        }

        $em = $this->getDoctrine()->getManager();

        $restaurant = $em->getRepository('ClabRestaurantBundle:Restaurant')
            ->findOneBy(array('slug' => $slug));

        $user = $this->get('security.context')->getToken()->getUser();

        if (!$restaurant->hasManager($user)) {
            throw $this->createNotFoundException();
        }

        return $restaurant;
    }
}
