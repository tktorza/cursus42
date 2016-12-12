<?php

namespace Clab\ApiBundle\Controller;

use Clab\ApiBundle\Form\Type\ProductCategory\RestProductCategoryType;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\BoardBundle\Entity\Client;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\ProductCategory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestProductCategoryController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Product Category",
     *      description="Get product category",
     *      requirements={
     *          {"name"="categoryId", "dataType"="integer", "required"=true, "description"="Id category"}
     *      },
     *      parameters={
     *          {"name"="isCaisse", "dataType"="boolean", "required"="false", "description"="is request from caisse app"}
     *     },
     *      output="Clab\RestaurantBundle\Entity\ProductCategory"
     * )
     * @ParamConverter("category", class="ClabRestaurantBundle:ProductCategory", options={"id" = "categoryId"})
     */
    public function getAction(Request $request, ProductCategory $category)
    {
        $serializer = $this->get('serializer');

        if ($request->get('isCaisse')) {
            $group = 'pro';
        } else {
            $group = 'public';
        }

        $response = $serializer->serialize($category, 'json', SerializationContext::create()->setGroups(array($group)));

        return new Response($response);
    }

    /**
     *### Response format ###.
     *
     *     {
     *       "categories": [
     *         {
     *         "id": 4,
     *         "name": "Kebab",
     *         "slug": "kebab",
     *         "description":"kebabs",
     *         "cover":"url",
     *         "products_count":10
     *         },
     *         ...
     *       ],
     *       "meals_count":2
     *     }
     *
     * @ApiDoc(
     *      section="Product Category",
     *      description="Get product category list for a restaurant and meals count",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *     parameters={
     *     {"name"="light", "dataType"="boolean", "required"=false, "description"="wheter to return data in light format, valid values are 0, 1, true, false"}
     *     },
     *      output="Clab\RestaurantBundle\Entity\ProductCategory"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getForRestaurantAction(Request $request, Restaurant $restaurant)
    {
        if ($request->get('light') && boolval($request->get('light'))) {
            try {
                $categories = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductCategory')->getForRestaurantAsArray($restaurant);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            }
        } else {
            try {
                $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($restaurant);
                $mealsCount = $this->get('app_restaurant.meal_manager')->getMealsCount($restaurant);
            } catch (\Exception $e) {
                return new JsonResponse([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            }

            $categories = array('categories' => $categories, 'meals_count' => $mealsCount);
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($categories, 'json', SerializationContext::create()->setGroups(array('search')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Product Category",
     *      resource=true,
     *      description="Get product category for a chainstore",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="chainstore Id"},
     *      },
     *      output="Clab\RestaurantBundle\Entity\ProductCategory"
     * )
     * @ParamConverter("restaurant", class="ClabBoardBundle:Client", options={"id" = "id"})
     */
    public function getForChainStoreAction(Client $chainstore)
    {
        $categories = $this->get('app_restaurant.product_category_manager')->getForChainStore($chainstore);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($categories, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Product Category",
     *      resource=true,
     *      description="Create product category for restaurant",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\ProductCategory\RestProductCategoryType",
     *      output="Clab\RestaurantBundle\Entity\ProductCategory"
     * )
     */
    public function newForRestaurantAction(Restaurant $restaurant)
    {
        $category = $this->get('app_restaurant.product_category_manager')->createForRestaurant($restaurant);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($category, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Product Category",
     *      resource=true,
     *      description="Create product category for a chainstore",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="chainstore"}
     *      }
     * )
     */
    public function newForChainStoreAction(Client $chainstore)
    {
        $category = $this->get('app_restaurant.product_category_manager')->createForChainStore($chainstore);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($category, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Product Category",
     *      resource=true,
     *      description="Edit product category for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id category"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestProductCategoryType",
     *      output="Clab\RestaurantBundle\Entity\ProductCategory"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"idRestaurant" = "id"})
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "id"})
     */
    public function editAction(Restaurant $restaurant, ProductCategory $productCategory, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository('ClabRestaurantBundle:ProductCategory')->find($productCategory);
        $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($restaurant);

        $form = new RestProductCategoryType(array('categories' => $categories));
        $form = $this->createForm($form, $product);
        $form->submit($request->request->all(), false);

        if ($form->isValid()) {
            $em->persist($product);
            $em->flush();

            $serializer = $this->get('serializer');
            $response = $serializer->serialize($product, 'json');

            return new Response($response);
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);
    }

    /**
     * @ApiDoc(
     *      section="Product Category",
     *      resource=true,
     *      description="Delete product category",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id category"},
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function deleteAction(ProductCategory $category)
    {
        $this->get('app_restaurant.product_category_manager')->remove($category);

        return new JsonResponse('Deleted', 200);
    }
}
