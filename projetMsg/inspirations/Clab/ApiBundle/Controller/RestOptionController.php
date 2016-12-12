<?php

namespace Clab\ApiBundle\Controller;

use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Entity\ProductOption;
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
     *      resource="/api/v1/options/product/{productId}",
     *      description="Get options for given product",
     *      requirements={
     *          {"name"="productId", "dataType"="integer", "required"=true, "description"="Id product"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\ProductOption"
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "productId"})
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

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($options, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource="/api/v1/options/restaurant/{restaurantId}",
     *      description="Create option for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function createAction(Restaurant $restaurant)
    {
        $option = $this->get('app_restaurant.product_option_manager')->createForRestaurant($restaurant);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($option, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource="/api/v1/options/{optionId}",
     *      description="delete option",
     *      requirements={
     *          {"name"="optionId", "dataType"="integer", "required"=true, "description"="Option id"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("option", class="ClabRestaurantBundle:ProductOption", options={"id" = "optionId"})
     *
     * @param ProductOption $option
     *
     * @return JsonResponse
     */
    public function removeAction(ProductOption $option)
    {
        $option = $this->get('app_restaurant.product_option_manager')->remove($option);

        return new JsonResponse($option, 200);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource="/api/v1/options/choice/{choiceId}",
     *      description="remove choice from option",
     *      requirements={
     *          {"name"="choiceId", "dataType"="integer", "required"=true, "description"="Id choice"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("choice", class="ClabRestaurantBundle:OptionChoice", options={"id" = "choiceId"})
     */
    public function removeChoiceAction(OptionChoice $choice)
    {
        $choice = $this->get('app_restaurant.product_option_manager')->removeChoice($choice);

        return new JsonResponse($choice, 200);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource="/api/v1/options/choice/{choiceId}",
     *      description="edit choice option online status",
     *      requirements={
     *          {"name"="choiceId", "dataType"="integer", "required"=true, "description"="Id choice"},
     *          {"name"="is_online", "dataType"="integer", "required"=true, "description"="Is online 1 or 0"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("choice", class="ClabRestaurantBundle:OptionChoice", options={"id" = "choiceId"})
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

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($choice, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource=true,
     *      description="Edit options choices",
     *      requirements={
     *          {"name"="optionId", "dataType"="integer", "required"=true, "description"="Id Option"}
     *      }
     * )
     */
    public function editOptionChoiceAction(Request $request)
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
        $orderSent = json_decode($content, true);
        $option = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductOption')->find(key($orderSent));

        foreach ($orderSent as $choice) {
            foreach ($choice as $c) {
                $realChoice = $this->getDoctrine()->getRepository('ClabRestaurantBundle:OptionChoice')->find($c['id']);
                $realChoice->setPrice($c['price']);
            }
        }
        $this->getDoctrine()->getManager()->flush();
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($option, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource="/api/v1/options/restaurant/{restaurantId}/choice/create",
     *      description="Create choice for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function createChoiceAction(Restaurant $restaurant){
        $choice = $this->get('app_restaurant.product_option_manager')->createChoiceForRestaurant($restaurant);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($choice, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource="/api/v1/options/{optionId}",
     *      description="get one Option",
     *      requirements={
     *          {"name"="optionId", "dataType"="integer", "required"=true, "description"="Id Option"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("option", class="ClabRestaurantBundle:ProductOption", options={"id" = "optionId"})
     */
    public function getAction(Request $request, ProductOption $option){
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($option, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Options",
     *      resource="/api/v1/options/choice/{choiceId}",
     *      description="get one option choice",
     *      requirements={
     *          {"name"="choiceId", "dataType"="integer", "required"=true, "description"="Id choice"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestOptionType",
     * )
     * @ParamConverter("choice", class="ClabRestaurantBundle:OptionChoice", options={"id" = "choiceId"})
     */
    public function getChoiceAction(OptionChoice $choice){
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($choice, 'json');

        return new Response($response);
    }
}
