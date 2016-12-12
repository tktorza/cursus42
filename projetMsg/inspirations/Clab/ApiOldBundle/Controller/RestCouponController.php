<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\Coupon;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestCouponController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Discount",
     *      resource=true,
     *      description="List of discount by restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function listAction(Restaurant $restaurant)
    {
        $coupons = $this->getDoctrine()->getRepository('ClabShopBundle:Coupon')->findBy(array(
         'restaurant' => $restaurant,
     ));

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($coupons, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource=true,
     *      description="Create product for restaurant",
     *      requirements={
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestProductType",
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function newAction(Request $request, Restaurant $restaurant)
    {
        $coupon = new Coupon();
        $isOnline = $request->get('isOnline');
        $name = $request->get('name');
        $platform = $request->get('platform');
        $percent = $request->get('percent');
        $amount = $request->get('amount');
        $startDay = $request->get('startDay');
        $endDay = $request->get('endDay');
        $unlimited = $request->get('unlimited');
        $quantity = $request->get('quantity');
        if (!is_null($isOnline)) {
            $coupon->setIsOnline($isOnline);
        }

        if (!is_null($name)) {
            $coupon->setName($name);
        }
        if (!is_null($startDay)) {
            $coupon->setStartDay($startDay);
        }
        if (!is_null($endDay)) {
            $coupon->setEndDay($endDay);
        }
        if (!is_null($platform)) {
            $coupon->setPlatform($platform);
        }
        if (!is_null($amount)) {
            $coupon->setAmount($amount);
        }
        if (!is_null($percent)) {
            $coupon->setPercent($percent);
        }
        if (!is_null($unlimited)) {
            $coupon->setUnlimited($unlimited);
        }
        if (!is_null($quantity)) {
            $coupon->setQuantity($quantity);
        }

        $coupon->setRestaurant($restaurant);
        $this->getDoctrine()->getManager()->persist($coupon);
        $this->getDoctrine()->getManager()->flush();
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($coupon, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource=true,
     *      description="Create product for restaurant",
     *      requirements={
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestProductType",
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     * @ParamConverter("coupon", class="ClabQhopBundle:Coupon", options={"id" = "id"})
     */
    public function editAction(Request $request, Coupon $coupon)
    {
        $isOnline = $request->get('isOnline');
        $name = $request->get('name');
        $platform = $request->get('platform');
        $percent = $request->get('percent');
        $amount = $request->get('amount');
        $startDay = $request->get('startDay');
        $endDay = $request->get('endDay');
        $unlimited = $request->get('unlimited');
        $quantity = $request->get('quantity');
        if (!is_null($isOnline)) {
            $coupon->setIsOnline($isOnline);
        }

        if (!is_null($name)) {
            $coupon->setName($name);
        }
        if (!is_null($startDay)) {
            $coupon->setStartDay($startDay);
        }
        if (!is_null($endDay)) {
            $coupon->setEndDay($endDay);
        }
        if (!is_null($platform)) {
            $coupon->setPlatform($platform);
        }
        if (!is_null($amount)) {
            $coupon->setAmount($amount);
        }
        if (!is_null($percent)) {
            $coupon->setPercent($percent);
        }
        if (!is_null($unlimited)) {
            $coupon->setUnlimited($unlimited);
        }
        if (!is_null($quantity)) {
            $coupon->setQuantity($quantity);
        }

        $this->getDoctrine()->getManager()->flush();
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($coupon, 'json');

        return new Response($response);
    }
}
