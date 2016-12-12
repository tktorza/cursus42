<?php

namespace Clab\ApiBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;

class RestLicenceController extends FOSRestController
{
    /**
     * Create a licence
     *
     * @ApiDoc(
     *   section="Licence",
     *   resource = "/api/v1/licences/{restaurantId}",
     *   description = "Return the overall User List",
     *   parameters={
     *       {"name"="licence", "dataType"="integer", "required"=true, "description"="licence number"}
     *   }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function postLicenceAction(Request $request, Restaurant $restaurant)
    {
        $licence = $request->request->get('licence');
        $licenceManager = $this->get('api.manager.licence');

        $response = $licenceManager
            ->setRestaurant($restaurant)
            ->setLicenceNumber($licence)
            ->createLicence()
        ;

        return new JsonResponse($response);
    }

    /**
     * Apply to a licence
     *
     * @ApiDoc(
     *   section="Licence",
     *   resource = "/api/v1/licences/{restaurantId}/apply",
     *   description = "Return the overall User List",
     *   parameters={
     *       {"name"="licenceNumber", "dataType"="integer", "required"=true, "description"="licence number"}
     *   }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function postLicenceApplyAction(Request $request, Restaurant $restaurant)
    {
        $licence = $request->request->get('licenceNumber');
        $licenceManager = $this->get('api.manager.licence');

        $response = $licenceManager
            ->setRestaurant($restaurant)
            ->setLicenceNumber($licence)
            ->applyToLicence()
        ;

        return new JsonResponse($response);
    }

    /**
     * Reset a licence
     *
     * @ApiDoc(
     *   section="Licence",
     *   resource = "/api/v1/licences/{restaurantId}/{licenceNumber}/{serial}",
     *   description = "Return the overall User List",
     *   parameters={
     *       {"name"="licenceNumber", "dataType"="integer", "required"=true, "description"="licence number"},
     *       {"name"="serial", "dataType"="string", "required"=true, "description"="serial number"}
     *   }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function deleteLicenceAction(Request $request, Restaurant $restaurant, $licenceNumber, $serial)
    {
        $licenceManager = $this->get('api.manager.licence');

        $response = $licenceManager
            ->setRestaurant($restaurant)
            ->setLicenceNumber($licenceNumber)
            ->setSerial($serial)
            ->resetLicence()
        ;

        return new JsonResponse($response);
    }


    /**
     * Ping to a licence
     *
     * @ApiDoc(
     *   section="Licence",
     *   resource = "/api/v1/licences/{restaurantId}/{licenceNumber}/{serial}",
     *   description = "Return the overall User List",
     *   parameters={
     *       {"name"="licenceNumber", "dataType"="integer", "required"=true, "description"="licence number"},
     *       {"name"="serial", "dataType"="integer", "required"=true, "description"="serial number"}
     *   }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getLicenceAction(Request $request, Restaurant $restaurant, $licenceNumber, $serial)
    {
        $licenceManager = $this->get('api.manager.licence');
        $subscriptionManager = $this->get('app_admin.subscription_manager');

        if (!$subscriptionManager->isValid($restaurant)) {
            $response = array('success' => false, 'message' => 'Licence has expired');
        } else {
            $response = $licenceManager
                ->setRestaurant($restaurant)
                ->setLicenceNumber($licenceNumber)
                ->setSerial($serial)
                ->pingLicence()
            ;
        }

        return new JsonResponse($response);
    }
}