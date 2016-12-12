<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\TokenDevice;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use RMS\PushNotificationsBundle\Message\iOSMessage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RestPushController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Push Notification IOS",
     *      resource=true,
     *      description="Push toa specific device a specific message",
     *      requirements={
     *          {"name"="message", "dataType"="string", "required"=true, "description"="Message of the push"},
     *          {"name"="token", "dataType"="string", "required"=true, "description"="push token"},
     *      },
     * )
     */
    public function pushAction(Request $request)
    {
        $messagePush = $request->get('message');
        $token = $request->get('token');
        $message = new iOSMessage();
        $message->setMessage($messagePush);
        $message->setDeviceIdentifier($token);

        $this->container->get('rms_push_notifications')->send($message);

        return new JsonResponse([
            'success' => true,
            'data' => 'sent',
        ]);
    }

    /**
     * @ApiDoc(
     *      section="Push Notifications to restaurant",
     *      resource=true,
     *      description="Push toa specific device a specific message",
     *      requirements={
     *          {"name"="message", "dataType"="string", "required"=true, "description"="Message of the push"},
     *          {"name"="restaurant", "dataType"="integer", "required"=true, "description"="id of the restaurant"},
     *      },
     * )
     */
    public function pushToRestaurantDevicesAction(Request $request)
    {
        $messagePush = $request->get('message');
        $restaurantId = $request->get('restaurant');
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
        $devices = $restaurant->getTokenDevices();
        if ($devices !== null) {
            foreach ($devices as $device) {
                if ($device->getType() == 10) {
                    $message = new iOSMessage();
                    $message->setMessage($messagePush);
                    $message->setDeviceIdentifier($device->getToken());
                    $this->container->get('rms_push_notifications')->send($message);
                }
            }
        }

        return new JsonResponse([
            'success' => true,
            'data' => 'sent',
        ]);
    }

    /**
     * @ApiDoc(
     *      section="Push Notification",
     *      resource=true,
     *      description="Add a new device to the database",
     *      requirements={
     *          {"name"="restaurant", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="token", "dataType"="integer", "required"=true, "description"="Token"},
     *          {"name"="os", "dataType"="string", "required"=false, "description"="version or OS of the device"},
     *          {"name"="device", "dataType"="string", "required"=false, "description"="Device (name or sku)"},
     *          {"name"="type", "dataType"="integer", "required"=false, "description"="10 for IOS, 20 for Android, 30 for windows phone"}
     *      },
     * )
     */
    public function registerDeviceAction(Request $request)
    {
        $restaurantId = $request->get('restaurant');
        $tokenId = $request->get('token');
        $os = $request->get('os');
        $device = $request->get('device');
        $type = $request->get('type');
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
        $tokens = array();
        if (!empty($restaurant->getTokenDevices())) {
            foreach ($restaurant->getTokenDevices() as $deviceToken) {
                $tokens[] = $deviceToken->getToken();
            }
            if (in_array($tokenId, $tokens)) {
                return new JsonResponse([
                   'success' => false,
                   'data' => 'Token already in database',
               ]);
            }
        }

        $token = new TokenDevice();
        $token->setDevice($device);
        $token->setOs($os);
        $token->setType($type);
        $token->setToken($tokenId);
        $this->getDoctrine()->getManager()->persist($token);
        $restaurant->addTokenDevice($token);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
            'data' => 'Device added',
        ]);
    }
}
