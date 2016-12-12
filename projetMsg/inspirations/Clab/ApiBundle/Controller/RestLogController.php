<?php

namespace Clab\ApiBundle\Controller;

use Clab\ApiBundle\Entity\Log;
use Clab\ApiBundle\Entity\SessionCaisse;
use Clab\ApiBundle\Form\Type\Log\RestSessionCaisseType;
use Clab\RestaurantBundle\Entity\Restaurant;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\VarDumper;


class RestLogController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Logs",
     *      description="Get logs for restaurant",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Cart"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getLogAction(Restaurant $restaurant)
    {
        $logs = $this->getDoctrine()->getRepository('ClabApiBundle:Log')->findBy(array(
            'restaurant' => $restaurant,
        ));

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($logs, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Logs",
     *      resource=true,
     *      description="Create a log for restaurant",
     *      requirements={
     *          {"name"="type", "dataType"="integer", "required"=true, "description"="Id of type"},
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id of restaurant"},
     *          {"name"="comment", "dataType"="string", "required"=true, "description"="full string"},
     *          {"name"="userId", "dataType"="integer", "required"=true, "description"="user ID"},
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestProductType",
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     */
    public function newAction(Request $request)
    {
        $log = new Log();
        $em = $this->getDoctrine()->getManager();
        $type = $request->get('type');
        $restaurantId = $request->get('restaurantId');
        $comment = $request->get('comment');
        $userId = $request->get('userId');
        if (!is_null($type)) {
            $log->setType($type);
        }
        if (!is_null($comment)) {
            $log->setComment($comment);
        }
        if (!is_null($restaurantId)) {
            $restaurant = $em->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
            $log->setRestaurant($restaurant);
        }
        if (!is_null($userId)) {
            $user = $em->getRepository('ClabUserBundle:User')->find($userId);
            $log->setUser($user);
        }

        $this->getDoctrine()->getManager()->persist($log);
        $this->getDoctrine()->getManager()->flush();
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($log, 'json');

        return new Response($response);
    }

    /**
     *
     * ### Response format ###
     *
     *     refunds of orders [{orderId: 1234, type: cash|cb, amount: 13.50 },...]
     *     inOuts for session ex: [{inOut: -25, date: 2016-09-09 20:30}]
     *
     * @ApiDoc(
     *      section="Logs",
     *      resource=true,
     *      description="Create a caisse session",
     *      requirements={
     *          {"name"="content", "dataType"="json", "required"=true, "description"="json content of session cf. parameters for details."}
     *      },
     *      parameters={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id of restaurant"},
     *          {"name"="device", "dataType"="string", "required"=true, "description"="device id of caisse"},
     *          {"name"="comment", "dataType"="string", "required"=false, "description"="full string"},
     *          {"name"="dateStart", "dataType"="date", "required"=false, "description"="start of session"},
     *          {"name"="dateEnd", "dataType"="date", "required"=false, "description"="start of session"},
     *          {"name"="cashFlowStart", "dataType"="float", "required"=false, "description"="cashflow start of session"},
     *          {"name"="cashFlowEndTheoric", "dataType"="float", "required"=false, "description"="cashflow theoric of session"},
     *          {"name"="cashFlowEnd", "dataType"="float", "required"=false, "description"="cashflow end of session"},
     *          {"name"="cashFlowDiff", "dataType"="float", "required"=false, "description"="cashflow diff of session"},
     *          {"name"="refund", "dataType"="array", "required"=false, "description"="refunds of orders"},
     *          {"name"="cash", "dataType"="float", "required"=false, "description"="amount of cash earned for session"},
     *          {"name"="check", "dataType"="float", "required"=false, "description"="amount of check earned for session"},
     *          {"name"="cb", "dataType"="float", "required"=false, "description"="amount of cb earned for session"},
     *          {"name"="restoTicket", "dataType"="float", "required"=false, "description"="amount of restoTicket earned for session"},
     *          {"name"="amex", "dataType"="float", "required"=false, "description"="amount of amex earned for session"},
     *          {"name"="productSwitch", "dataType"="float", "required"=false, "description"="product Switch for session"},
     *          {"name"="commercialGesture", "dataType"="float", "required"=false, "description"="commercialGesture for session"},
     *          {"name"="accidentalDebit", "dataType"="float", "required"=false, "description"="accidentalDebit for session"},
     *          {"name"="testError", "dataType"="float", "required"=false, "description"="testError earned for session"},
     *          {"name"="clientProblem", "dataType"="float", "required"=false, "description"="clientProblem for session"},
     *          {"name"="inOut", "dataType"="array", "required"=false, "description"="inOuts for session"},
     *          {"name"="commentary", "dataType"="float", "required"=false, "description"="commentary for session"},
     *          {"name"="orders", "dataType"="array", "required"=false, "description"="orders id of session"},
     *          {"name"="tva", "dataType"="array", "required"=false, "description"="taxes reported during session"}
     *      }
     * )
     */
    public function createSessionAction(Request $request)
    {
        $form = new RestSessionCaisseType();
        $sessionCaisse = new SessionCaisse();

        $form = $this->createForm($form, $sessionCaisse);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {

            if ($request->request->get('dateStart')) {
                $sessionCaisse->setDateStart(new \DateTime($request->request->get('dateStart')));
            }
            if ($request->request->get('dateEnd')) {
                $sessionCaisse->setDateEnd(new \DateTime($request->request->get('dateEnd')));
            }
            if ($request->request->get('inOut')) {
                $sessionCaisse->setInOut($request->request->get('inOut'));
            }
            if ($request->request->get('refund')) {
                $sessionCaisse->setRefund($request->request->get('refund'));
            }
            if ($request->request->get('orders')) {
                $sessionCaisse->setOrders($request->request->get('orders'));
            }
            if ($request->request->get('vat')) {
                $sessionCaisse->setTva($request->request->get('vat'));
            }

            $sessionCaisse->setUser($this->getUser());

            $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($form->get('restaurantID')->getData());
            $sessionCaisse->setRestaurant($restaurant);

            $em = $this->getDoctrine()->getManager();
            $em->persist($restaurant);
            $em->persist($sessionCaisse);
            $em->flush();

            $serializer = $this->get('serializer');
            $response = $serializer->serialize($sessionCaisse, 'json', SerializationContext::create()->setGroups(array('pro')));

            return new Response($response);
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);

    }
}
