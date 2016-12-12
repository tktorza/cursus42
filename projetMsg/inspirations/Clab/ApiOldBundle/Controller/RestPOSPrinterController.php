<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\BoardBundle\Entity\POSPrinter;
use Clab\ApiOldBundle\Form\Type\POSPrinter\RestPOSPrinterType;
use Symfony\Component\HttpFoundation\JsonResponse;

class RestPOSPrinterController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="POS Printers",
     *      description="Get printers list for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\BoardBundle\Entity\POSPrinter"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function listAction(Restaurant $restaurant)
    {
        $printers = $restaurant->getPosPrinters();

        return new JsonResponse($printers);
    }

    /**
     * @ApiDoc(
     *      section="POS Printers",
     *      resource=true,
     *      description="Get printer for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id printer"}
     *      },
     *      output="Clab\BoardBundle\Entity\POSPrinter"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"idRestaurant" = "id"})
     */
    public function getAction(Restaurant $restaurant, $id)
    {
        $printer = $this->get('clab.pos_printer_manager')->getForRestaurant($restaurant, $id);

        if (!$printer) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Vous n\'avez pas accès à cette
            imprimante');
        }

        return new JsonResponse($printer);
    }

    /**
     * @ApiDoc(
     *      section="POS Printers",
     *      resource=true,
     *      description="Create printer for restaurant",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\POSPrinter\RestPOSPrinterType",
     *      output="Clab\BoardBundle\Entity\POSPrinter"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function newAction(Restaurant $restaurant)
    {
        $printer = new POSPrinter();
        $restaurant->addPosPrinter($printer);
        $result = $this->get('api.rest_manager')->processForm($printer, new RestPOSPrinterType(), array('restaurantId' => $restaurant));

        return new JsonResponse($result);
    }

    /**
     * @ApiDoc(
     *      section="POS Printers",
     *      resource=true,
     *      description="Edit printer for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id printer"}
     *      },
     *      input="Clab\BoardBundle\Entity\POSPrinter",
     *      output="Clab\BoardBundle\Entity\POSPrinter"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"idRestaurant" = "id"})
     */
    public function editAction(Restaurant $restaurant, $id)
    {
        $printer = $this->get('clab.pos_printer_manager')->getForRestaurant($restaurant, $id);

        if (!$printer) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Vous n\'avez pas accès à cette
            imprimante');
        }
        $result = $this->get('api.rest_manager')->processForm($printer, new RestPOSPrinterType());

        return new JsonResponse($result);
    }

    /**
     * @ApiDoc(
     *      section="POS Printers",
     *      resource=true,
     *      description="Delete printer for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id printer"}
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"idRestaurant" = "id"})
     */
    public function deleteAction(Restaurant $restaurant, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $printer = $this->get('clab.pos_printer_manager')->getForRestaurant($restaurant, $id);

        if (!$printer) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Vous n\'avez pas accès à cette
            imprimante');
        }

        $em->remove($printer);
        $em->flush();

        return new JsonResponse('Deleted', 200);
    }
}
