<?php

namespace Clab\BoardBundle\Controller;

use Clab\StripeBundle\Form\Type\CardFormType;
use Stripe\Customer;
use Stripe\Plan;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\BoardBundle\Entity\Subscription;
use Clab\RestaurantBundle\Entity\Restaurant;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;

class UpgradeController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function upgradeAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $client = null;
        $restaurant = $this->get('board.helper')->getProxy();
        $cards = $this->get('clab_stripe.customer.manager')->listCards($restaurant)->__toArray()['data'];
        if (empty($cards)) {
            $this->addFlash('notice', "Merci d'ajouter une carte afin de procéder à l'upgrade de votre abonnement");

            return $this->redirectToRoute('board_subscription_dashboard', array('contextPk' => $contextPk));
        }
        $plans = $this->get('app_admin.subscription_manager')->planUpgradable($restaurant);
        if (empty($plans)) {
            $this->addFlash('notice', "Vous avez déjà l'abonnement le plus important");

            return $this->redirectToRoute('board_subscription_dashboard', array('contextPk' => $contextPk));
        }
        $plansStripe = array();
        foreach ($plans as $plan) {
            $plansStripe[] = $this->get('clab_stripe.plan.manager')->listPlan($plan)->__toArray();
        }

        return $this->render('ClabBoardBundle:Upgrade:plans.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'plans' => $plansStripe,
            'restaurant' => $restaurant,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER")
     */
    public function payUpgradeSubscriptionAction($contextPk, Request $request, $slug)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk, true);
        $client = null;
        $cards = null;
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
        $client = $this->get('clab_stripe.customer.manager')->retrieve($restaurant->getStripeCustomerId());
        $cardStripe = $this->get('clab_stripe.customer.manager')->listCards($restaurant);
        $cards = $cardStripe->__toArray(true)['data'];

        $customerManager = $this->container->get('clab_stripe.customer.manager');
        $plan = Plan::retrieve($slug);

        $formFactory = Forms::createFormFactory();
        $form = $formFactory->createBuilder(new CardFormType())
            ->getForm();
        $form->handleRequest();
        if ($form->isValid()) {
            $subscription = $this->getDoctrine()->getRepository('ClabBoardBundle:Subscription')->findOneBy(array(
                    'type' => 0,
                    'is_online' => true,
                    'restaurant' => $restaurant,
                ));
            $customerManager->upgrade($restaurant, $subscription->getStripeSubscriptionId(), $plan);
            $this->get('app_admin.subscription_manager')->changePlan($this->get('board.helper')->getProxy(), $plan);
            $this->container->get('session')->getFlashBag()->set('success', 'Abonnement acheté avec succès.');

            return $this->redirect($this->generateUrl('board_dashboard', array('context' => 'restaurant',
                'contextPk' => $contextPk, )));
        } else { // Show the credit card form
            return $this->render('ClabBoardBundle:Upgrade:stripe-payment.html.twig', array_merge($this->get('board.helper')->getParams(), array(
                'plan' => $plan,
                'form' => $form->createView(),
                'client' => $client,
                'restaurant' => $restaurant,
                'cards' => $cards,
                'publishableKey' => $this->container->getParameter('stripe_publishable_key'),
            )));
        }
    }

    /**
     * @Secure(roles="ROLE_MANAGER")
     */
    public function upgradeExistingCardAction($contextPk, $idCard, $idPlan)
    {
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $plan = Plan::retrieve($idPlan);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
        $customer = Customer::retrieve($restaurant->getStripeCustomerId());
        $card = $customer->sources->retrieve($idCard);
        $this->get('board.helper')->initContext('restaurant', $contextPk, true);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
        $customerManager = $this->container->get('clab_stripe.customer.manager');

        $subscription = $this->getDoctrine()->getRepository('ClabBoardBundle:Subscription')->findOneBy(array(
                'type' => 0,
                'is_online' => true,
                'restaurant' => $restaurant,
            ));

        $subscription = $customerManager->upgrade($restaurant, $subscription->getStripeSubscriptionId(), $plan);
        $subId = $subscription->__toArray()['id'];
        $this->get('app_admin.subscription_manager')->upgradePlan($this->get('board.helper')->getProxy(), $plan, $subId);
        $this->container->get('session')->getFlashBag()->set('success', 'Abonnement acheté avec succès.');

        $this->getDoctrine()->getManager()->flush();
        $this->container->get('session')->getFlashBag()->set('success', 'Abonnement acheté avec succès.');

        return $this->redirect($this->generateUrl('board_dashboard', array('context' => 'restaurant', 'contextPk' => $contextPk)));
    }
}
