<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Entity\Subscription;
use Clab\RestaurantBundle\Entity\Plan;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hook controller.
 *
 * @author Sacha Masson <sacha@click-eat.fr>
 */
class StripeController extends Controller
{
    public function cancelSubscriptionAction(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        $event = $content['type'];
        $data = $content['data']['object'];
        if ($event == 'customer.subscription.deleted') {
            $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
                'stripeCustomerId' => $data['customer'],
            ));
            $subscription = $this->getDoctrine()->getRepository('ClabBoardBundle:Subscription')->findOneBy(array(
                'restaurant' => $restaurant,
                'is_online' => true,
                'type' => 0,
            ));
            if (is_null($subscription)) {
                return new Response('Aucune subscription valide', 200, array('Content-Type' => 'text/html'));
            }
            $subscription->setIsOnline(false);
            $restaurant->setStatus(2040);
            $restaurant->setIsClickeat(false);
            $restaurant->setIsTtt(false);
            $this->getDoctrine()->getManager()->flush();
            try {
                $this->get('clab_board.mail_manager')->subscriptionCancelMail($restaurant);
            } catch (\ErrorException $e) {
                return new Response($e, 200, array('Content-Type' => 'text/html'));
            }
        }

        return new Response('ok', 200, array('Content-Type' => 'text/html'));
    }

    public function invoicePaymentSucceededAction(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        $event = $content['type'];
        $data = $content['data']['object'];
        if ($event == 'invoice.payment_succeeded') {
            $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
                'stripeCustomerId' => $data['customer'],
            ));
            try {
                $this->get('clab_board.mail_manager')->paymentSucceededMail($restaurant);
            } catch (\ErrorException $e) {
                return new Response($e, 200, array('Content-Type' => 'text/html'));
            }
        }

        return new Response('ok', 200, array('Content-Type' => 'text/html'));
    }

    public function paymentFailedAction(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        $event = $content['type'];
        $data = $content['data']['object'];
        if ($event == 'invoice.payment_failed') {
            $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
                'stripeCustomerId' => $data['customer'],
            ));
            try {
                $this->get('clab_board.mail_manager')->paymentFailedMail($restaurant);
            } catch (\ErrorException $e) {
                return new Response($e, 200, array('Content-Type' => 'text/html'));
            }
        }

        return new Response('ok', 200, array('Content-Type' => 'text/html'));
    }

    public function createPlanAction(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        $event = $content['type'];
        $data = $content['data']['object'];
        if ($event == 'plan.created') {
            $plan = new Plan();
            $plan->setIsOnline(true);
            $plan->setName($data['name']);
            $plan->setPrice($data['amount'] / 100);
            $plan->setStripePlanId($data['id']);
            $this->getDoctrine()->getManager()->persist($plan);
            $this->getDoctrine()->getManager()->flush();
        }

        return new Response('ok', 200, array('Content-Type' => 'text/html'));
    }
}
