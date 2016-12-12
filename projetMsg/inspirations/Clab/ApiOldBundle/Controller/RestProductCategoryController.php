<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\ApiOldBundle\Form\Type\ProductCategory\RestProductCategoryType;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\BoardBundle\Entity\Client;
use FOS\RestBundle\Controller\FOSRestController;
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
     *      section="Catégories de produit",
     *      description="Get product category list for a restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\ProductCategory"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getForRestaurantAction(Restaurant $restaurant)
    {
        try {
            $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($restaurant);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($categories, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Catégories de produit",
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

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($categories, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Catégories de produit",
     *      resource=true,
     *      description="Create product category for restaurant",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\ProductCategory\RestProductCategoryType",
     *      output="Clab\RestaurantBundle\Entity\ProductCategory"
     * )
     */
    public function newForRestaurantAction(Restaurant $restaurant)
    {
        $category = $this->get('app_restaurant.product_category_manager')->createForRestaurant($restaurant);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($category, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Catégories de produit",
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

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($category, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Catégories de produit",
     *      resource=true,
     *      description="Edit product category for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id category"}
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestProductCategoryType",
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

            $serializer = $this->container->get('serializer');
            $response = $serializer->serialize($product, 'json');

            return new Response($response);
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);
    }

    /**
     * @ApiDoc(
     *      section="Catégories de produit",
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
