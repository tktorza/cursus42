<?php

namespace Clab\BoardBundle\Controller;

use Clab\StripeBundle\Form\Type\CardFormType;
use Stripe\Customer;
use Stripe\Error\Base;
use Stripe\Plan;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\BoardBundle\Entity\Subscription;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\BoardBundle\Entity\SubscriptionInvoice;
use Clab\BoardBundle\Form\Type\Subscription\SettingsLegalType;
use Clab\BoardBundle\Form\Type\Subscription\SettingsMailType;
use Clab\BoardBundle\Form\Type\Subscription\SettingsDocsType;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;

class SubscriptionController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function termsAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $subscriptionManager = $this->get('app_admin.subscription_manager');
        $terms = $subscriptionManager->initSubscriptionTerms($this->get('board.helper')->getProxy());
        $subscription = $subscriptionManager->getCurrentSubscription($this->get('board.helper')->getProxy());

        $this->get('board.helper')->addParam('terms', $terms);
        $this->get('board.helper')->addParam('subscription', $subscription);

        return $this->render('ClabBoardBundle:Subscription:terms.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function sendTermsAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $subscriptionManager = $this->get('app_admin.subscription_manager');
        $terms = $subscriptionManager->initSubscriptionTerms($this->get('board.helper')->getProxy());
        $this->get('board.helper')->addParam('terms', $terms);

        if ($request->isMethod('POST') && $email = $this->getRequest()->request->get('email')) {
            $mailer = $this->get('mailer');
            $message = \Swift_Message::newInstance()
                ->setSubject('myClickeat - Conditions Générales d\'Utilisation et de Vente de la plateforme')
                ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
                ->setTo($email)
                ->setBody($this->renderView('ClabBoardBundle:Subscription:contracts/'.$terms->getContractVersion().'.html.twig', array('terms' => $terms)), 'text/html');

            $mailer->send($message);

            $this->get('session')->getFlashBag()->add('success', 'Les conditions ont bien été envoyée à votre mail '.$email);
        }

        return $this->redirect($this->getRequest()->request->get('backUrl'));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function invoiceAction($contextPk, Request $request)
    {
        $invoiceId = $request->get('invoice');
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $invoice = $this->get('clab_stripe.customer.manager')->retrieveOneInvoice($invoiceId);
        $this->get('board.helper')->addParam('invoice', $invoice->__toArray(true));
        $this->get('board.helper')->addParam('restaurant', $this->get('board.helper')->getProxy());
        $invoiceDetails = $this->get('clab_stripe.customer.manager')->retrieveInvoiceDetails($invoiceId);
        $this->get('board.helper')->addParam('invoiceDetails', $invoiceDetails->__toArray(true)['data']);

        return $this->render('ClabBoardBundle:Subscription:invoice.html.twig', $this->get('board.helper')->getParams());
    }
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function dashboardAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $subscriptionManager = $this->get('app_admin.subscription_manager');
        $currentSubscription = $this->getDoctrine()->getRepository('ClabBoardBundle:Subscription')->findOneBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
            'type' => 0,
            'is_online' => true,
        ));
        $this->get('board.helper')->addParam('currentSubscription', $currentSubscription);
        $this->get('board.helper')->addParam('nextSubscriptions', $subscriptionManager->getNextSubscriptions($this->get('board.helper')->getProxy()));

        $terms = $subscriptionManager->initSubscriptionTerms($this->get('board.helper')->getProxy());
        $this->get('board.helper')->addParam('terms', $terms);
        $this->get('board.helper')->addParams(array('restaurant' => $contextPk));
        $cards = $this->get('clab_stripe.customer.manager')->listCards($this->get('board.helper')->getProxy());
        $customerManager = $this->container->get('clab_stripe.customer.manager');
        $formFactory = Forms::createFormFactory();
        $form = $formFactory->createBuilder(new CardFormType())->getForm();

        $form->handleRequest();
        if ($form->isValid()) {
            try {
                $customerManager->createCard($this->get('board.helper')->getProxy());
                $this->container->get('session')->getFlashBag()->set('success', 'Carte ajoutée.');

                return $this->redirect($this->generateUrl('board_subscription_dashboard', array('context' => 'restaurant', 'contextPk' => $contextPk)));
            } catch (\ErrorException $e) {
                $this->container->get('session')->getFlashBag()->set('error', $e->getMessage());

                return $this->redirect($this->generateUrl('board_subscription_dashboard', array('context' => 'restaurant', 'contextPk' => $contextPk)));
            }
        }
        if ($this->get('board.helper')->getProxy()->getStatus() < Restaurant::STORE_STATUS_WAITING) {
            return $this->render('ClabBoardBundle:Subscription:dashboardTest.html.twig', $this->get('board.helper')->getParams());
        }
        $invoices = $this->get('clab_stripe.customer.manager')->retrieveInvoicesFromCustomer($this->get('board.helper')->getProxy()->getStripeCustomerId());

        $this->get('board.helper')->addParams(array('publishableKey' => $this->container->getParameter('stripe_publishable_key')));
        $this->get('board.helper')->addParams(array('cards' => $cards->__toArray(true)['data']));
        $this->get('board.helper')->addParams(array('form' => $form->createView()));
        $this->get('board.helper')->addParams(array('invoices' => $invoices->__toArray(true)['data']));

        return $this->render('ClabBoardBundle:Subscription:dashboard.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deleteCreditCardAction($contextPk, $card)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
        $this->get('clab_stripe.customer.manager')->deleteCard($restaurant, $card);
        $this->container->get('session')->getFlashBag()->set('success', 'Carte effacée.');
        $referer = $this->getRequest()->headers->get('referer');

        return $this->redirect($referer);
    }
    /**
     * @Secure(roles="ROLE_MANAGER")
     */
    public function orderStatementAction($contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $orderStatement = $em->getRepository('ClabBoardBundle:OrderStatement')->findOneBy(array('id' => $id, 'restaurant' => $this->get('board.helper')->getProxy()));

        $this->get('board.helper')->addParam('orderStatement', $orderStatement);

        $orderStatement->getBalance();
        $em->flush();

        return $this->render('ClabBoardBundle:Subscription:orderStatement.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function subscriptionInvoiceAction($contextPk, $id)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $em = $this->getDoctrine()->getManager();

        $subscriptionInvoice = $em->getRepository('ClabBoardBundle:SubscriptionInvoice')->findOneBy(array('id' => $id, 'restaurant' => $this->get('board.helper')->getProxy()));

        if (!$subscriptionInvoice) {
            throw $this->createNotFoundException();
        }

        if ($subscriptionInvoice->getRestaurant()) {
            $this->get('board.helper')->addParam('restaurant', $subscriptionInvoice->getRestaurant());
        } elseif ($this->get('board.helper')->getProxy()->getStartInvoice() == $subscriptionInvoice) {
            $this->get('board.helper')->addParam('restaurant', $this->get('board.helper')->getProxy());
        } else {
            throw $this->createNotFoundException();
        }

        $this->get('board.helper')->addParam('invoice', $subscriptionInvoice);

        return $this->render('ClabBoardBundle:Subscription:subscriptionInvoice.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function subscriptionInvoicePaymentAction($contextPk, $id)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $em = $this->getDoctrine()->getManager();

        $subscriptionInvoice = $em->getRepository('ClabBoardBundle:SubscriptionInvoice')->find($id);

        if (!$subscriptionInvoice) {
            throw $this->createNotFoundException();
        }

        if ($subscriptionInvoice->getRestaurant() && $subscriptionInvoice->getRestaurant()->isAllowed($this->getUser())) {
            $this->get('board.helper')->addParam('restaurant', $subscriptionInvoice->getRestaurant());
        } elseif ($this->get('board.helper')->getProxy()->getStartInvoice() == $subscriptionInvoice) {
            $this->get('board.helper')->addParam('restaurant', $this->get('board.helper')->getProxy());
        } else {
            throw $this->createNotFoundException();
        }

        $this->get('board.helper')->addParam('invoice', $subscriptionInvoice);


        return $this->render('ClabBoardBundle:Subscription:subscriptionInvoice.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function amendmentViewAction($contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $amendment = $this->get('board.helper')->getAllowedOrDeny('ClabBoardBundle:SubscriptionAmendment', $id);

        if (!$amendment->isSigned()) {
            $form = $this->createFormBuilder()
                ->add('validation', 'checkbox', array('label' => 'J\'accepte les conditions', 'required' => true))
                ->getForm();

            if ($form->handleRequest($request)->isValid()) {
                $amendment->setIsSigned(true);
                $amendment->setLastSign(date_create('now'));
                $em->flush();

                return $this->redirectToRoute('board_subscription_dashboard', array('contextPk' => $contextPk));
            }

            $this->get('board.helper')->addParam('form', $form->createView());
        }

        $this->get('board.helper')->addParam('amendment', $amendment);

        return $this->render('ClabBoardBundle:Subscription:amendmentView.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function activateAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $this->get('app_admin.subscription_manager')->activate($this->get('board.helper')->getProxy());

        return $this->redirectToRoute('board_subscription_dashboard', array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function settingsAction($contextPk, $type, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        switch ($type) {
            case 'legal':
                $form = $this->createForm(new SettingsLegalType(), $this->get('board.helper')->getProxy());
                break;
            case 'docs':
                $form = $this->createForm(new SettingsDocsType(), $this->get('board.helper')->getProxy());
                break;
            case 'mails':
                $form = $this->createForm(new SettingsMailType($this->get('board.helper')->getProxy()->isMobile()), $this->get('board.helper')->getProxy());
                break;
            case 'social':
                if ($page = $this->get('board.helper')->getProxy()->getFacebookPage()) {
                    $facebookManager = $this->get('clab_board.facebook_manager');

                    if ($this->get('board.helper')->getProxy()->isMobile()) {
                        $fbMenu = $facebookManager->checkPageTab($page, 'ttt_menu');
                        $fbPlanning = $facebookManager->checkPageTab($page, 'ttt_planning');

                        $this->get('board.helper')->addParams(array(
                            'fbMenu' => $fbMenu,
                            'fbPlanning' => $fbPlanning,
                        ));
                    }

                    $fbOrder = $facebookManager->checkPageTab($page, 'iframe');
                    $this->get('board.helper')->addParam('fbOrder', $fbOrder);
                }
                break;
            default:
                return $this->redirectToRoute('board_settings', array('contextPk' => $contextPk, 'type' => 'legal'));
                break;
        }

        if (isset($form) && $form->handleRequest($request)->isValid()) {
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Les informations ont bien été sauvegardées');

            return $this->redirectToRoute('board_settings', array('contextPk' => $contextPk, 'type' => $type));
        }

        if (isset($form)) {
            $this->get('board.helper')->addParams(array(
                'form' => $form->createView(),
                'type' => $type,
            ));
        }

        if ($trigger = $this->getRequest()->get('trigger')) {
            $this->get('board.helper')->addParam('trigger', $trigger);
        }

        return $this->render('ClabBoardBundle:Subscription:settings-'.$type.'.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function settingsOrdermailAction($contextPk, $backUrl = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $form = $this->createForm(new SettingsMailType($this->get('board.helper')->getProxy()->isMobile()), $this->get('board.helper')->getProxy());

        if ($form->handleRequest($request)->isValid()) {
            $em->flush();

            if ($backUrl) {
                return $this->redirect(urldecode($backUrl));
            }

            $this->get('session')->getFlashBag()->add('success', 'Les informations ont bien été sauvegardées');

            return $this->redirectToRoute('board_settings', array('contextPk' => $contextPk, 'type' => 'mails'));
        }

        $this->get('board.helper')->addParams(array(
            'form' => $form->createView(),
            'backUrl' => urldecode($backUrl),
        ));

        return $this->render('ClabBoardBundle:Subscription:settings-ordermail.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function subscriptionListAction($contextPk)
    {
        $this->get('board.helper')->initContext('client', $contextPk);
        $client = $this->get('board.helper')->getProxy();
        $restos = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findBy(array(
            'client' => $this->get('board.helper')->getProxy()
        ));
        $restaurants = array();
        foreach($restos as $key => $resto)
        {
            $restaurants[$key]['restaurant'] = $resto;
            if($resto->getStripeCustomerId() !== null)
            {
                $restaurants[$key]['invoices'] = $this->get('clab_stripe.customer.manager')->retrieveInvoicesFromCustomer
                ($resto->getStripeCustomerId())->__toArray()['data'];
            }

        }
        $plan = $this->getDoctrine()->getRepository("ClabRestaurantBundle:Plan")->find(1);
        $this->get('board.helper')->addParam('client', $client);
        $this->get('board.helper')->addParam('restaurants', $restaurants);
        $this->get('board.helper')->addParam('plan', $plan);
        return $this->render('ClabBoardBundle:Subscription:client-library.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function paySubscriptionAction($contextPk, Request $request, $slug)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk, true);
        $client = null;
        $cards = null;
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
        if(is_null($restaurant->getEmailPayment()))
        {
            $this->addFlash('notice','Veuillez remplir un email de notification de paiement avant de procéder au
                paiement');
            return $this->redirect($this->generateUrl('board_settings',array('contextPk' => $contextPk,'type' => 'mails')));
        }
        if (!is_null($restaurant->getStripeCustomerId())) {
            $client = $this->get('clab_stripe.customer.manager')->retrieve($restaurant->getStripeCustomerId());
            $cardStripe = $this->get('clab_stripe.customer.manager')->listCards($restaurant);
            $cards = $cardStripe->__toArray(true)['data'];
        }

        $customerManager = $this->container->get('clab_stripe.customer.manager');
        $plan = Plan::retrieve($slug);

        $formFactory = Forms::createFormFactory();
        $form = $formFactory->createBuilder(new CardFormType())
            ->getForm();
        $form->handleRequest();

        if ($form->isValid()) {

            try {
                $customer = $customerManager->process($plan, $restaurant, $restaurant->getEmailPayment());
                $restaurant->setStripeCustomerId($customer->__toArray()['customer']);
                $this->getDoctrine()->getManager()->flush();
                $this->get('app_admin.subscription_manager')->activate($this->get('board.helper')->getProxy(), $plan);

                $this->container->get('session')->getFlashBag()->set('success', 'Abonnement acheté avec succès.');
            } catch (\ErrorException $e) {
                $this->container->get('session')->getFlashBag()->set('error', $e->getMessage());

                return $this->redirect($this->generateUrl('board_dashboard', array('context' => 'restaurant',
                    'contextPk' => $contextPk, )));
            }

            return $this->redirect($this->generateUrl('board_dashboard', array('context' => 'restaurant',
                'contextPk' => $contextPk, )));
        } else { // Show the credit card form

            return $this->render('ClabBoardBundle:Subscription:stripe-payment.html.twig', array_merge($this->get('board.helper')->getParams(), array(
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
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function paySubscriptionForChainStoreAction($contextPk, Request $request, $slug)
    {
        $this->get('board.helper')->initContext('client', $contextPk, true);
        $clientStripe = null;
        $cards = null;
        $client = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->findOneBy(array('slug' => $contextPk));
        $restaurants = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findBy(array(
            'client' => $client
        ));
        $planInhouse = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Plan')->find(1);
        $this->get('board.helper')->addParam('planInhouse', $planInhouse);
        if(is_null($client->getEmailPayment()))
        {
            $this->addFlash('notice','Veuillez remplir un email de notification de paiement avant de procéder au
                paiement');
            return $this->redirect($this->generateUrl('board_settings',array('contextPk' => $contextPk,'type' => 'mails')));
        }
        if (!is_null($client->getStripeCustomerId())) {
            $clientStripe = $this->get('clab_stripe.customer.manager')->retrieve($client->getStripeCustomerId());
            $cardStripe = Customer::retrieve($client->getStripeCustomerId())->sources->all(array('object' => 'card'));
            $cards = $cardStripe->__toArray(true)['data'];
        }

        $customerManager = $this->container->get('clab_stripe.customer.manager');
        $plan = Plan::retrieve($slug);

        $formFactory = Forms::createFormFactory();
        $form = $formFactory->createBuilder(new CardFormType())
            ->getForm();
        $form->handleRequest();

        if ($form->isValid()) {

            try {
                foreach($restaurants as $restaurant)
                {
                    $customer = $customerManager->process($plan, $restaurant, $restaurant->getEmailPayment());
                    $restaurant->setStripeCustomerId($customer->__toArray()['customer']);
                    $this->getDoctrine()->getManager()->flush();
                    $this->get('app_admin.subscription_manager')->activate($this->get('board.helper')->getProxy(), $plan);
                }
                $this->container->get('session')->getFlashBag()->set('success', 'Abonnement acheté avec succès.');

            } catch (\ErrorException $e) {
                $this->container->get('session')->getFlashBag()->set('error', $e->getMessage());

                return $this->redirect($this->generateUrl('board_dashboard', array('context' => 'client',
                    'contextPk' => $contextPk, )));
            }

            return $this->redirect($this->generateUrl('board_dashboard', array('context' => 'client',
                'contextPk' => $contextPk, )));
        } else { // Show the credit card form

            return $this->render('ClabBoardBundle:Subscription:stripe-payment-client.html.twig', array_merge($this->get('board.helper')->getParams(), array(
                'plan' => $plan,
                'form' => $form->createView(),
                'client' => $client,
                'cards' => $cards,
                'publishableKey' => $this->container->getParameter('stripe_publishable_key'),
            )));
        }
    }

    /**
     * @Secure(roles="ROLE_MANAGER")
     */
    public function payWithExistingCardAction($contextPk, $idCard, $idPlan)
    {
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $plan = Plan::retrieve($idPlan);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
        $customer = Customer::retrieve($restaurant->getStripeCustomerId());
        $card = $customer->sources->retrieve($idCard);
        $this->get('board.helper')->initContext('restaurant', $contextPk, true);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
        try {
            $this->get('app_admin.subscription_manager')->activate($this->get('board.helper')->getProxy(), $plan);
            $stripeSubscription = $this->get('clab_stripe.customer.manager')->chargeCard($restaurant, $card, $plan);
        } catch (Base $e) {
            $this->container->get('session')->getFlashBag()->set('error', $e->getMessage());

            return $this->redirect($this->generateUrl('board_dashboard', array('context' => 'restaurant', 'contextPk' => $contextPk)));
        }
        $restaurant->setStatus(Restaurant::STORE_STATUS_ACTIVE);
        $this->getDoctrine()->getManager()->flush();
        $this->container->get('session')->getFlashBag()->set('success', 'Abonnement acheté avec succès.');

        return $this->redirect($this->generateUrl('board_dashboard', array('context' => 'restaurant', 'contextPk' => $contextPk)));
    }

}
