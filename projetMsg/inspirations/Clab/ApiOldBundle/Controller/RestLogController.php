<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\ApiOldBundle\Entity\Log;
use Clab\ApiOldBundle\Entity\SessionCaisse;
use Clab\RestaurantBundle\Entity\Restaurant;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $serializer = $this->container->get('serializer');
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
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestProductType",
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
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($log, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Logs",
     *      resource=true,
     *      description="Create a caisse session",
     *      requirements={
     *          {"name"="type", "dataType"="integer", "required"=true, "description"="Id of type"},
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id of restaurant"},
     *          {"name"="comment", "dataType"="string", "required"=true, "description"="full string"},
     *          {"name"="userId", "dataType"="integer", "required"=true, "description"="user ID"},
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestProductType",
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     */
    public function newSessionAction(Request $request)
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
        $sessionSent = json_decode($content, true);
        $sessionIH = new SessionCaisse();
        $dateStart = $sessionSent['dateStart'];
        $dateStartFormatted = new \DateTime($dateStart);
        $dateEnd = $sessionSent['dateEnd'];
        $dateEndFormatted = new \DateTime($dateEnd);
        $cashFlowStart = $sessionSent['cashFlowStart'];
        $cashFlowEndTheoric = $sessionSent['cashFlowEndTheoric'];
        $cashFlowEnd = $sessionSent['cashFlowEnd'];
        $cashFlowDiff = $sessionSent['cashFlowDiff'];
        $cashRefund = $sessionSent['cashRefund'];
        $restaurantId = $sessionSent['restaurantID'];
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
        $sessionIH->setDateEnd($dateEndFormatted);
        $sessionIH->setDateStart($dateStartFormatted);
        $sessionIH->setCashFlowStart($cashFlowStart);
        $sessionIH->setCashFlowEndTheoric($cashFlowEndTheoric);
        $sessionIH->setCashFlowEnd($cashFlowEnd);
        $sessionIH->setCashFlowDiff($cashFlowDiff);
        $sessionIH->setCashRefund($cashRefund);
        $sessionIH->setRestaurant($restaurant);

        $this->getDoctrine()->getManager()->persist($sessionIH);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'session added',
            'orderId' => $sessionIH->getId(),
        ]);
    }
}
