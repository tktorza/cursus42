<?php

namespace Clab\ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\ShopBundle\Entity\OrderDetail;

class DashboardController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER")
     */
    public function dashboardAction()
    {
        $restaurant = $this->getRestaurant();

        if (!$restaurant) {
            return $this->redirectToRoute('manager_switch_restaurant');
        }

        $states = array(
            OrderDetail::ORDER_STATE_VALIDATED,
            OrderDetail::ORDER_STATE_READY,
            OrderDetail::ORDER_STATE_READY_PACKING,
            OrderDetail::ORDER_STATE_READY_PACKED,
            OrderDetail::ORDER_STATE_TERMINATED,
        );

        $orders = $this->getDoctrine()->getManager()->getRepository('ClabShopBundle:OrderDetail')
            ->findAllByRestaurant($restaurant->getId(), $states, 'today', null, null, null, 'asc', null,[0,1,2,4,5]);
        $states = array(
            OrderDetail::ORDER_STATE_TERMINATED,
            OrderDetail::ORDER_STATE_CANCELLED,
        );
        $results = array();
        $resultsClosed = array();
        $closedOrders = $this->getDoctrine()->getManager()->getRepository('ClabShopBundle:OrderDetail')
            ->findAllByRestaurant($restaurant->getId(), $states, 'today', null, null, null, 'asc', null,[OrderDetail::ORDER_STATE_SERVED]);

        foreach ($closedOrders as $key => $closedOrder) {
            $elements = $this->getDoctrine()->getRepository('ClabShopBundle:CartElement')->findBy(array('cart' => $closedOrder->getCart()));
            $resultsClosed[$key]['elements'] = $elements;
            $resultsClosed[$key]['order'] = $closedOrder;
        }

        foreach ($orders as $key => $order) {
            $elements = $this->getDoctrine()->getRepository('ClabShopBundle:CartElement')->findBy(array('cart' => $order->getCart()));
            $results[$key]['elements'] = $elements;
            $results[$key]['order'] = $order;
        }

        return $this->render('ClabManagerBundle:Dashboard2:dashboard.html.twig', array(
            'results' => $results,
            'resultsClosed' => $resultsClosed,
            'restaurant' => $this->getRestaurant(),
        ));
    }

    /**
     * @Secure(roles="ROLE_MANAGER")
     */
    public function orderDialogAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $order = $this->getDoctrine()->getManager()->getRepository('ClabShopBundle:OrderDetail')
            ->findOneBy(array('id' => $id, 'restaurant' => $this->getRestaurant()));

        if (!$order) {
            throw $this->createNotFoundException();
        } elseif ($order->getState() == OrderDetail::ORDER_STATE_VALIDATED) {
            $order->setState(OrderDetail::ORDER_STATE_READY);
            $em->flush();
        }

        if (strpos($order->getRestaurant()->getSlug(), 'subway') !== false || $order->getRestaurant()->getSlug() == 'le-grill') {
            return $this->render('ClabManagerBundle:Dashboard2:orderSubway.html.twig', array(
                'order' => $order,
            ));
        }

        return $this->render('ClabManagerBundle:Dashboard2:order.html.twig', array(
             'order' => $order,
        ));
    }

    /**
     * @Secure(roles="ROLE_MANAGER")
     */
    public function switchRestaurantAction($slug)
    {
        $user = $this->getUser();

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $restaurants = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Restaurant')
                ->findLightByStatus(0, 6999);
        } else {
            $restaurants = $user->getAllowedRestaurants();
        }

        $currentOrders = [];

        $states = array(
            OrderDetail::ORDER_STATE_VALIDATED,
            OrderDetail::ORDER_STATE_READY,
            OrderDetail::ORDER_STATE_READY_PACKING,
            OrderDetail::ORDER_STATE_READY_PACKED,
            OrderDetail::ORDER_STATE_TERMINATED,
        );

        foreach ($restaurants as $restaurant) {
            $nbOrders = $this->getDoctrine()->getRepository(OrderDetail::class)->findAllByRestaurant($restaurant['id'], $states, 'today', null, null, null, 'asc', null,[0,1,2,4,5], true);

            $currentOrders[$restaurant['id']] = $nbOrders[0]['nbOrders'];
        }

        if ($slug) {
            $restaurant = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Restaurant')
                ->findOneBy(array('slug' => $slug));

            if (!$restaurant || !$restaurant->hasManager($user)) {
                throw $this->createNotFoundException();
            }

            $this->get('session')->set('manager_restaurant', $restaurant->getSlug());

            return $this->redirectToRoute('manager_dashboard');
        }

        return $this->render('ClabManagerBundle:Dashboard2:switchRestaurant.html.twig', array(
            'restaurants' => $restaurants,
            'currentOrders' => $currentOrders
        ));
    }

    public function getRestaurant()
    {
        $session = $this->getRequest()->getSession();

        if ($slug = $this->get('session')->get('manager_restaurant')) {
            $restaurant = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Restaurant')
                ->findOneBy(array('slug' => $slug));

            if ($this->isGranted('ROLE_COMMERCIAL') || $restaurant->getManagers()->contains($this->getUser())) {
                return $restaurant;
            }
        }

        return;
    }
}
