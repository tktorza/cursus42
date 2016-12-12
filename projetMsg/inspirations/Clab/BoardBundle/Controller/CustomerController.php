<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function listAction($contextPk)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $users = $em->getRepository('ClabUserBundle:User')->getCustomersForRestaurant($this->get('board.helper')->getProxy());

        $customers = array();
        foreach ($users as $user) {
            $customers[$user->getId()] = array('user' => $user, 'source' => array());
            foreach ($user->getOrders() as $order) {
                if ($order->getFacebookPage() && !in_array('Facebook', $customers[$user->getId()]['source'])) {
                    $customers[$user->getId()]['source'][] = 'Facebook';
                } elseif ($order->getMultisite() && !in_array('Marque blanche', $customers[$user->getId()]['source'])) {
                    $customers[$user->getId()]['source'][] = 'Marque blanche';
                } elseif (!in_array('Clickeat', $customers[$user->getId()]['source'])) {
                    $customers[$user->getId()]['source'][] = 'Clickeat';
                }
            }
        }

        return $this->render('ClabBoardBundle:Customer:list.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'customers', $customers,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function viewAction($contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $user = $em->getRepository('ClabUserBundle:User')->getCustomerForRestaurant($this->get('board.helper')->getProxy(), $id);

        $sources = array();
        foreach ($user->getOrders() as $order) {
            if ($order->getFacebookPage() && !in_array('Facebook', $sources)) {
                $sources[] = 'Facebook';
            } elseif ($order->getMultisite() && !in_array('Marque blanche', $sources)) {
                $sources[] = 'Marque blanche';
            } elseif (!in_array('Clickeat', $sources)) {
                $sources[] = 'Clickeat';
            }
        }

        $form = $this->createFormBuilder()
            ->add('message', 'textarea', array(
                'label' => 'Message',
                'required' => true,
            ))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            if ($message = $form->get('message')->getData()) {
                $this->get('clickeat.mail_manager')->restaurantToCustomerMessage($this->get('board.helper')->getProxy(), $user, $message);
            }

            return new Response('ok', 200);
        } elseif ($request->getMethod() == 'POST') {
            return new Response('ko', 400);
        }

        return $this->render('ClabBoardBundle:Customer:view.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'form' => $form->createView(),
            'user' => $user,
            'sources' => $sources,
        )));
    }
}
