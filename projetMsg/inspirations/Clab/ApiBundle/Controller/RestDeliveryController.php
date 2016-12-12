<?php

namespace Clab\ApiBundle\Controller;

use Clab\ApiBundle\Form\Type\Delivery\RestDeliveryManType;
use Clab\DeliveryBundle\Entity\DeliveryDay;
use Clab\DeliveryBundle\Form\Type\DeliveryManType;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\OrderDetail;
use Clab\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Clab\DeliveryBundle\Entity\Delivery;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clab\DeliveryBundle\Entity\DeliveryMan;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;


class RestDeliveryController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/list/{restaurantId}",
     *      description="Get deliveries by Restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id of the restaurant"}
     *      },
     *      parameters={
     *          {"name"="deliveryManId", "dataType"="integer", "required"=true, "description"="Identifier for delivery man"}
     *      },
     * )
     */
    public function listDeliveryAction($restaurantId, Request $request)
    {
        $deliveryManId = $request->get('deliveryManId');
        $criteria = array('restaurant' => $restaurantId);

        if ($deliveryManId) {
            $criteria['deliveryMan'] = $deliveryManId;
        }

        $delivery = $this->getDoctrine()->getRepository('ClabDeliveryBundle:Delivery')->getAllAvailable($criteria);
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($delivery, 'json', SerializationContext::create()->setGroups(array('pro')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/{id}",
     *      description="Get one delivery by id",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the delivery"}
     *      }
     * )
     *
     */
    public function getOneByIdAction($id)
    {

        $delivery = $this->getDoctrine()->getRepository('ClabDeliveryBundle:Delivery')->findBy(array('id'=>$id));
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($delivery, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource="/api/v1/delivery/{id}/deliveryMan/{deliveryManId}",
     *      description="Set a delivery Man to a delivery",
     *
     * requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the deliveryMan"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the delivery"}
     *      }
     * )
     * @ParamConverter("delivery", class="ClabDeliveryBundle:Delivery", options={"id" = "id"})
     * @ParamConverter("deliveryMan", class="ClabDeliveryBundle:DeliveryMan", options={"id" = "deliveryManId"})
     */
    public function setDeliveryManToDeliveryAction(Delivery $delivery, DeliveryMan $deliveryMan)
    {
        $delivery->setDeliveryMan($deliveryMan);
        $delivery->setState(Delivery::DELIVERY_STATE_IN_PROGRESS);
        $order = $delivery->getOrder();
        $manager = $this->getDoctrine()->getManager();

        if($order) {
            $order->setPreparationState(OrderDetail::ORDER_STATE_IN_DELIVERY);
            $manager->persist($order);
        }

        $manager->persist($delivery);
        $manager->flush();

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($delivery, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource="/api/v1/delivery/{id}/deliveryMan",
     *      description="remove delivery Men from a delivery",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the deliveryMan"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the delivery"}
     *      }
     * )
     * @ParamConverter("delivery", class="ClabDeliveryBundle:Delivery", options={"id" = "id"})
     */
    public function removeDeliveryManFromDeliveryAction(Delivery $delivery)
    {
        $delivery->setDeliveryMan(null);
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($delivery);
        $manager->flush();

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($delivery, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/state/{id}",
     *      description="Get sate of a delivery by id",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the delivery"}
     *      }
     * )
     */
    public function getStateAction($id)
    {

        $delivery = $this->getDoctrine()->getRepository('ClabDeliveryBundle:Delivery')->findOneBy(array('id'=>$id));
        if ($delivery) {
            $deliveryState = $delivery->getState();

            switch($deliveryState){
                case 0:
                    $state = 'DELIVERY_STATE_INITIAL';
                    break;
                case 10:
                    $state = 'DELIVERY_STATE_WAITING_DELIVERYMAN';
                    break;
                case 20:
                    $state = 'DELIVERY_STATE_IN_PROGRESS';
                    break;
                case 30:
                    $state = 'DELIVERY_STATE_DONE';
                    break;
                case 50:
                    $state = 'DELIVERY_STATE_CANCELLED';
                    break;
            }

            $response = [
                'code' => $deliveryState,
                'state' => $state,
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'delivery not found',
            ];
        }

        return new JsonResponse($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/state/{id}",
     *      description="Set sate of a delivery by id",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the delivery"}
     *      },
     *     parameters={
     *          {"name"="state", "dataType"="integer", "required"=true, "description"="State to set to the delivery"}
     *      },
     * )
     */
    public function setStateAction($id, Request $request)
    {
        $delivery = $this->getDoctrine()->getRepository('ClabDeliveryBundle:Delivery')->findOneBy(array('id'=>$id));
        $order = $delivery->getOrder();
        $newState = $request->get('state');

        if($delivery && $order)
        {
            $delivery->setState($newState);
            $order->setPreparationState($newState);
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($order);
            $manager->persist($delivery);
            $manager->flush();

            $response = 'success';
        }
        else {
            $response = [
                'success' => false,
                'message' => 'delivery not found',
            ];
        }

        return new JsonResponse($response);
    }

    /**
     * DeliveryMen section
     */


    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/deliveryMan/list/{restaurantId}",
     *      description="Get delivery man list by Restaurant",
     *      requirements={
     *          {"name"="RestaurantId", "dataType"="integer", "required"=true, "description"="Id of the restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function listDeliveryManAction($restaurant)
    {
        $delivery = $this->getDoctrine()->getRepository('ClabDeliveryBundle:DeliveryMan')->findBy(array('restaurant'=>$restaurant, 'isDeleted' => false));
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($delivery, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/deliveryMan/{id}",
     *      description="Get  delivery man by id",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the deliveryMan"}
     *      }
     * )
     * @ParamConverter("deliveryMan", class="ClabDeliveryBundle:DeliveryMan", options={"id" = "id"})
     */
    public function getDeliveryManAction(DeliveryMan $deliveryMan)
    {
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($deliveryMan, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/deliveryMan/me",
     *      description="Get  delivery man connected informations",
     * )
     */
    public function getDeliveryManMeAction()
    {
        $serializer = $this->get('serializer');
        $user = $this->getUser();
        $deliveryMan = $this->getDoctrine()->getRepository(DeliveryMan::class)->findOneByUser($user);

        $response = $serializer->serialize($deliveryMan, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/deliveryMan",
     *      description="create deliveryMan",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the deliveryMan"}
     *      },
     *     parameters={
     *          {"name"="isOnline", "dataType"="boolean", "required"=false, "description"="State to set to the deliveryMan"},
     *          {"name"="name", "dataType"="string", "required"=false, "description"="Name of the deliveryMan"},
     *          {"name"="phone", "dataType"="string", "required"=false, "description"="Phone of the deliveryMan"},
     *          {"name"="code", "dataType"="string", "required"=false, "description"="Code of the deliveryMan"},
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id of the restaurant"}
     *      },
     * )
     */
    public function createDeliveryManAction(Request $request)
    {
        $response = json_encode([
            'success' => false,
            'message' => 'erreur dans le formulaire',
        ]);

        $deliveryMan  = new DeliveryMan();

        $form = $this->createForm(new RestDeliveryManType(), $deliveryMan);

        $form->submit($request->request->all());

        $restaurantId = $request->get('restaurantId');

        if ($form->isValid() && $restaurantId) {
            $em = $this->getDoctrine()->getManager();

            $restaurant = $em->getRepository(Restaurant::class)->find($restaurantId);

            if ($restaurant) {

                $deliveryMan->setRestaurant($restaurant);

                $alreadyExist = $em->getRepository('ClabDeliveryBundle:DeliveryMan')->findOneBy(array(
                    'phone' => $deliveryMan->getPhone(),
                    'restaurant' => $deliveryMan->getRestaurant()
                ));

                $response = json_encode([
                    'success' => false,
                    'message' => 'un livreur avec le même téléphone existe pour ce restaurant.',
                ]);

                if (!$alreadyExist) {
                    if (!$deliveryMan->getCode()) {
                        $digits = 4;

                        $deliveryMan->setCode(str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT));
                    }

                    $user = new User();
                    $user->setUsername($deliveryMan->getName());
                    $user->setEmail($deliveryMan->getName().'@'.$deliveryMan->getPhone().'.com');
                    $user->addRole('ROLE_DELIVERYMAN');
                    $user->setPlainPassword($deliveryMan->getCode());
                    $user->setEnabled('true');

                    $deliveryMan->setUser($user);

                    $em->persist($user);
                    $em->persist($deliveryMan);
                    $em->flush();

                    $serializer = $this->get('serializer');
                    $response = $serializer->serialize($deliveryMan, 'json', SerializationContext::create()->setGroups(array('pro')));
                }
            }
        }

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/deliveryMan/{id}",
     *      description="edit deliveryMan by id",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the deliveryMan"}
     *      },
     *     parameters={
     *          {"name"="isOnline", "dataType"="boolean", "required"=false, "description"="State to set to the deliveryMan"},
     *          {"name"="name", "dataType"="string", "required"=false, "description"="Name of the deliveryMan"},
     *          {"name"="phone", "dataType"="string", "required"=false, "description"="Phone of the deliveryMan"},
     *          {"name"="code", "dataType"="string", "required"=false, "description"="Code of the deliveryMan"},
     *          {"name"="latitude", "dataType"="double", "required"=false, "description"="Latitude of the deliveryMan"},
     *          {"name"="longitude", "dataType"="double", "required"=false, "description"="Longitude of the deliveryMan"}
     *      },
     * )
     * @ParamConverter("deliveryMan", class="ClabDeliveryBundle:DeliveryMan", options={"id" = "id"})
     */
    public function editDeliveryManAction(Request $request, $deliveryMan)
    {
        $response = json_encode([
            'success' => false,
            'message' => 'deliveryMan not found',
        ]);

        if ($deliveryMan) {

            $form = $this->createForm(new RestDeliveryManType(), $deliveryMan);

            $form->submit($request->request->all(),false);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $alreadyExist = false;
                if ($request->request->get('phone') || $request->request->get('resturant')) {
                    $alreadyExist = $em->getRepository('ClabDeliveryBundle:DeliveryMan')->findOneBy(array(
                        'phone' => $deliveryMan->getPhone(),
                        'restaurant' => $deliveryMan->getRestaurant()
                    ));
                }

                $response = json_encode([
                    'success' => false,
                    'message' => 'un livreur avec le même téléphone existe pour ce restaurant.',
                ]);

                if (!$alreadyExist) {
                    $em->persist($deliveryMan);
                    $em->flush();

                    $serializer = $this->get('serializer');
                    $response = $serializer->serialize($deliveryMan, 'json', SerializationContext::create()->setGroups(array('public')));
                }
            }
        }

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/deliveryMan/{id}",
     *      description="edit deliveryMan by id",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id of the deliveryMan"}
     *      },
     *     parameters={
     *          {"name"="is_online", "dataType"="boolean", "required"=false, "description"="State to set to the deliveryMan"},
     *          {"name"="name", "dataType"="string", "required"=false, "description"="Name of the deliveryMan"},
     *          {"name"="phone", "dataType"="string", "required"=false, "description"="Phone of the deliveryMan"},
     *          {"name"="code", "dataType"="string", "required"=false, "description"="Code of the deliveryMan"}
     *      },
     * )
     * @ParamConverter("deliveryMan", class="ClabDeliveryBundle:DeliveryMan", options={"id" = "id"})
     */
    public function removeDeliveryManAction($deliveryMan)
    {
        $response = [
            'success' => false,
            'message' => 'deliveryMan not found',
        ];

        if ($deliveryMan) {
            $em = $this->getDoctrine()->getManager();

            $deliveryMan->setIsDeleted(true);
            $deliveryMan->setIsOnline(false);
            $user = $deliveryMan->getUser();
            $user->setEnabled(false);

            $em->persist($deliveryMan);
            $em->persist($user);
            $em->flush();

            $response = [
                'success' => true,
                'message' => 'livreur bien supprimé',
            ];
        }

        return new JsonResponse($response);
    }

    /**
     * @ApiDoc(
     *      section="Delivery",
     *      resource = "/api/v1/delivery/check",
     *      description="Check if delivery is available",
     *      parameters={
     *          {"name"="address", "dataType"="string", "required"=true, "description"="Address for delivery"}
     *      }
     * )
     */
    public function getCheckAction (Request $request) {
        $address = $request->query->get('address');

        if (!$address) {
            throw new Exception("You can't be homeless", 400);
        }

        //$restaurant = $this->getDoctrine()->getRepository(Restaurant::class)->find(9);
        $dayAsInt = date('N');

        $deliveryDays = $this->getDoctrine()->getRepository(DeliveryDay::class)->findBy(array(
            'weekDay' => $dayAsInt,
        ));

        foreach ($deliveryDays as $deliveryDay) {
            $geolocation = $this->get('clab_delivery.delivery_manager')->checkLocationApi($address, $deliveryDay);

            if ($geolocation) {
                return new JsonResponse(array('success' => true, 'lat' => $geolocation['lat'], 'lng' => $geolocation['lng'], 'restaurant' => $deliveryDay->getRestaurant()->getSlug()));
            }
        }

        return new JsonResponse(array('success' => false));
    }
}