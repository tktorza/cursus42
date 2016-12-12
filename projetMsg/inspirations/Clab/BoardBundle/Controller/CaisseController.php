<?php

namespace Clab\BoardBundle\Controller;

use Clab\ApiBundle\Entity\SessionCaisse;
use Clab\BoardBundle\Form\Type\Caisse\CaisseSettingsType;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\OrderDetailCaisse;
use Clab\TaxonomyBundle\Service\Manager;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\VarDumper;

class CaisseController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($contextPk, $context, Request $request)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $start = $request->get('start');
        $end = $request->get('end');

        if ($start) {
            $start = new \DateTime($start);
        } else {
            $start = date_create('now');
            $start->modify('-1 month');
        }

        if ($end) {
            $end = new \DateTime($end);
        } else {
            $end = date_create('now');
        }

        if( $context == 'restaurant') {
            $sessions = $this->get('api.session_manager')->getAllCaisseBeetweenDates($this->get('board.helper')->getProxy(), $start, $end);
        } else {
            if ($request->get('restaurant')) {
                $restaurant = $this->getDoctrine()->getRepository(Restaurant::class)->find($request->get('restaurant'));

                if ($restaurant) {
                    $sessions = $this->get('api.session_manager')->getAllCaisseBeetweenDates($restaurant, $start, $end);
                }
            } else {
                $sessions = new ArrayCollection();

                foreach ($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                    $s = $this->get('api.session_manager')->getAllCaisseBeetweenDates($restaurant, $start, $end);
                    if($s) {
                        $sessions->add($s);
                    }
                }
            }
        }

        return $this->render('ClabBoardBundle:Caisse:library.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'sessions' => $sessions,
            'start' => $start,
            'end' => $end
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction($context, $contextPk, Request $request)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $start = $request->get('start');
        $end = $request->get('end');

        if ($start) {
            $start = new \DateTime($start);
        } else {
            $start = date_create('now');
            $start->modify('-1 month');
        }

        if ($end) {
            $end = new \DateTime($end);
        } else {
            $end = date_create('now');
        }

        if( $context == 'restaurant') {
            $sessions = $this->get('api.session_manager')->getAllCaisseBeetweenDates($this->get('board.helper')->getProxy(), $start, $end);
        } else {
            $sessions = new ArrayCollection();
            foreach ($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                $s = $this->get('api.session_manager')->getAllCaisseBeetweenDates($restaurant, $start, $end);
                if($s) {
                    $sessions->add($s);
                }
            }
        }

        if ($request->isXmlHttpRequest()) {
            $serializer = $this->get('serializer');

            $response = $serializer->serialize($sessions, 'json', SerializationContext::create()->setGroups(array('app')));

            return new Response($response);
        }


        return $this->render('ClabBoardBundle:Caisse:libraryList.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'sessions' => $sessions,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction(Request $request, $context, $contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $session = $em->getRepository(SessionCaisse::class)->find($id);

        $orders = [];

        if ($session->getOrders()){
            foreach($session->getOrders() as $orderId) {
                $o = $em->getRepository(OrderDetailCaisse::class)->find($orderId);
                $orders[] = $o;
            }
        }

        return $this->render('ClabBoardBundle:Caisse:edit.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'session' => $session,
            'orders' => $orders
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function caisseSettingsAction(Request $request, $context, $contextPk)
    {
        if ($context == 'client') {
            return $this->redirectToRoute('board_caisse_session_library', array('context' => $context, 'contextPk' => $contextPk));
        }

        $this->get('board.helper')->initContext($context, $contextPk);
        $restaurant = $this->get('board.helper')->getProxy();

        $products = $this->get('app_restaurant.product_manager')->getForRestaurant($restaurant);

        $formatedProducts = [];

        foreach ($products as $product) {
            $formatedProducts[$product->getId()] = $product->getName();
        }

        $form = $this->createForm(new CaisseSettingsType(array('products' => $formatedProducts)), $restaurant);

        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                $restaurant->setCaisseTags(array_values($restaurant->getCaisseTags()));
                $restaurant->setCaisseDiscountsLabels(array_values($restaurant->getCaisseDiscountsLabels()));
                $restaurant->setCaissePrinterLabels(array_values($restaurant->getCaissePrinterLabels()));

                foreach ($form->get('caissePrinterLabels')->getData() as $printerData) {
                    foreach ($products as $product) {
                        if (in_array($product->getId(), $printerData['products'])) {
                            $product->addPrinter($printerData['printerId']);
                        } else {
                            $product->removePrinter($printerData['printerId']);
                        }
                    }
                }

                $this->getDoctrine()->getManager()->flush();
            }
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Caisse:caisseSettings.html.twig', $this->get('board.helper')->getParams());
    }
}
