<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Entity\Restaurant;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Product;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestOptionController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Options",
     *      description="Get option category list for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\ProductOption"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function listAction(Restaurant $restaurant)
    {
        $options = $this->get('app_restaurant.product_option_manager')->getForRestaurant($restaurant);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($options, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      description="Get option category list for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\ProductOption"
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "id"})
     */
    public function listForProductAction(Product $product)
    {
        $options = $this->get('app_restaurant.product_option_manager')->getAvailableForProduct($product);
        $iterator = $options->getIterator();

        while ($iterator->valid()) {
            $option = $iterator->current();
            if (count($option->getChoices()) <= 0) {
                $options->removeElement($option);
            }
            $iterator->next();
        }

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($options, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource=true,
     *      description="Create option",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id option"}
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function createAction(Restaurant $restaurant)
    {
        $option = $this->get('app_restaurant.product_option_manager')->createForRestaurant($restaurant);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($option, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource=true,
     *      description="remove option from product",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id option"}
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "id"})
     *
     * @param Product $product
     *
     * @return JsonResponse
     */
    public function removeAction(Product $product)
    {
        $option = $this->get('app_restaurant.product_option_manager')->remove($product);

        return new JsonResponse($option, 200);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource=true,
     *      description="remove choice from option",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id option"}
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("choice", class="ClabRestaurantBundle:OptionChoice", options={"id" = "id"})
     */
    public function removeChoiceAction(OptionChoice $choice)
    {
        $choice = $this->get('app_restaurant.product_option_manager')->removeChoice($choice);

        return new JsonResponse($choice, 200);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource=true,
     *      description="edit choice from option",
     *      requirements={
     *          {"name"="choiceId", "dataType"="integer", "required"=true, "description"="Id choice"},
     *          {"name"="is_online", "dataType"="integer", "required"=true, "description"="Is online 1 or 0"}
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("choice", class="ClabRestaurantBundle:OptionChoice", options={"id" = "id"})
     *
     * @param OptionChoice $choice
     * @param Request $request
     *
     * @return Response
     */
    public function patchChoiceAction(Request $request, OptionChoice $choice)
    {
        $em = $this->getDoctrine()->getManager();

        $choice = $em->getRepository('ClabRestaurantBundle:OptionChoice')->find($choice);

        $isOnline = $request->get('is_online');
        $choice->setIsOnline($isOnline);
        $em->persist($choice);
        $em->flush();

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($choice, 'json');

        return new Response($response);
    }
}
