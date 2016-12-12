<?php

namespace Clab\ApiBundle\Controller;

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
     * ### Returns a collection of Discounts for a Restaurant ###
     *
     *      [
     *          {
     *          "id": 27,
     *          "name": "MERCICOSI",
     *          "percent": 10,
     *          "verbose": "- 10%",
     *          "manager_verbose": "Coupon de - 10% (offert par Clickeat)"
     *          }
     *      ]
     *
     * @ApiDoc(
     *      section="Coupons",
     *      resource=true,
     *      description="List of discount by restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Restaurant identifier"}
     *      },
     *      output="Clab\ShopBundle\Entity\Coupon"
     * )
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getCouponsAction(Restaurant $restaurant)
    {
        $coupons = $this->getDoctrine()->getRepository('ClabShopBundle:Coupon')->findBy(array(
            'restaurant' => $restaurant,
        ));

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($coupons, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource=true,
     *      description="Create coupon for restaurant",
     *      requirements={
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestProductType",
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
        $serializer = $this->get('serializer');
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
     *      input="Clab\ApiBundle\Form\Type\Product\RestProductType",
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
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($coupon, 'json');

        return new Response($response);
    }
}
