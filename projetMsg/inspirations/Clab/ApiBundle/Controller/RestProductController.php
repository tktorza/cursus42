<?php

namespace Clab\ApiBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Product;
use Clab\ApiBundle\Form\Type\Product\RestProductType;
use Symfony\Component\HttpFoundation\Response;

class RestProductController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Products",
     *      resource="/api/v1/products/taxes",
     *      description="Get taxes list",
     * )
     */
    public function taxesAction()
    {
        $taxes = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Tax')->findBy(array('is_online' => true));

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($taxes, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource="/api/v1/products/menu/{menuId}",
     *      description="Get product list for restaurant menu",
     *      requirements={
     *          {"name"="menuId", "dataType"="integer", "required"=true, "description"="Id menu"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     * @ParamConverter("menu", class="ClabRestaurantBundle:RestaurantMenu", options={"id" = "menuId"})
     */
    public function listAction(RestaurantMenu $menu)
    {
        $products = $this->get('app_restaurant.product_manager')->getForRestaurantMenu($menu);

        foreach ($products as $key => $product) {
            if (!$product->getCategory()) {
                unset($products[$key]);
            }
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($products, 'json', SerializationContext::create()->setGroups(array('search')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource="/api/v1/products/restaurant/{restaurantId}",
     *      description="Get product list for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getForRestaurantAction(Restaurant $restaurant)
    {
        $products = $this->get('app_restaurant.product_manager')->getForRestaurant($restaurant);

        foreach ($products as $key => $product) {
            if (!$product->getCategory()) {
                unset($products[$key]);
            }
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($products, 'json', SerializationContext::create()->setGroups(array('search')));

        return new Response($response);
    }

    /**
     * Unused
     * @ParamConverter("menu", class="ClabRestaurantBundle:RestaurantMenu", options={"id" = "menuId"})
     */
    public function listAvailable(RestaurantMenu $menu)
    {
        $products = $this->get('app_restaurant.product_manager')->getAvailableForRestaurantMenu($menu);

        foreach ($products as $key => $product) {
            if (!$product->getCategory()) {
                unset($products[$key]);
            }
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($products, 'json', SerializationContext::create()->setGroups(array('public')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource="/api/v1/products/create",
     *      description="Create product for restaurant",
     *      requirements={
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestProductType",
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     */
    public function newAction(Request $request)
    {
        $product = new Product();
        $em = $this->getDoctrine()->getManager();
        $isOnline = $request->get('isOnline');
        $name = $request->get('name');
        $price = $request->get('price');
        $vat = $request->get('vat');
        $description = $request->get('description');
        $category = $request->get('category');
        $options = $request->get('options');
        $menus = $request->get('menus');
        $file = $request->get('file');
        $isPDJ = $request->get('isPDJ');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $imageManager = $this->get('app_media.image_manager');

        if (!is_null($isPDJ)) {
            $product->setIsPDJ($isPDJ);
        }
        if (!is_null($isOnline)) {
            $product->setIsOnline($isOnline);
        }
        if (!is_null($startDate)) {
            $product->setStartDate($startDate);
        }
        if (!is_null($endDate)) {
            $product->setEndDate($endDate);
        }
        if (!is_null($name)) {
            $product->setName($name);
        }
        if (!is_null($price)) {
            $product->setPrice($price);
        }
        if (!is_null($vat)) {
            $optionvat = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Tax')->find($vat);
            $product->setTax($optionvat);
        }
        if (!is_null($description)) {
            $product->setDescription($description);
        }
        if (!is_null($category)) {
            $optioncategory = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductCategory')->find($category);
            $product->setCategory($optioncategory);
        }

        if (!is_null($menus)) {
            foreach ($menus as $menu) {
                $product->addRestaurantMenu($this->getDoctrine()->getRepository('ClabRestaurantBundle:RestaurantMenu')->find($menu));
            }
        }
        $this->getDoctrine()->getManager()->persist($product);
        $this->getDoctrine()->getManager()->flush();
        if (!is_null($options)) {
            foreach ($options as $option) {
                $objectOption = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductOption')->find($option);
                $objectOption->addProduct($product);
                $product->addOption($objectOption);
            }
        }
        $this->getDoctrine()->getManager()->flush();
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($product, 'json');

        return new Response($response);
    }

    /**
     * Unused
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "productId"})
     */
    public function editPostAction(Product $product, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $isOnline = $request->get('isOnline');
        $name = $request->get('name');
        $price = $request->get('price');
        $vat = $request->get('vat');
        $description = $request->get('description');
        $category = $request->get('category');
        $options = $request->get('options');
        $menus = $request->get('menus');

        $isPDJ = $request->get('isPDJ');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        if (!is_null($isOnline)) {
            $product->setIsOnline($isOnline);
        }
        $imageManager = $this->get('app_media.image_manager');

        list($success, $image) = $imageManager->upload('product', $product->getId(), null);

        if ($success) {
            $product->getGallery()->setCover($image);
            $product->setCover($image);
            $em->flush();
        }

        if (!is_null($isPDJ)) {
            $product->setIsPDJ($isPDJ);
        }
        if (!is_null($startDate)) {
            $product->setStartDate($startDate);
        }
        if (!is_null($endDate)) {
            $product->setEndDate($endDate);
        }
        if (!is_null($name)) {
            $product->setName($name);
        }
        if (!is_null($price)) {
            $product->setPrice($price);
        }
        if (!is_null($vat)) {
            $optionvat = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Tax')->find($vat);
            $product->setTax($optionvat);
        }
        if (!is_null($description)) {
            $product->setDescription($description);
        }
        if (!is_null($category)) {
            $optioncategory = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductCategory')->find($category);
            $product->setCategory($optioncategory);
        }

        if (!is_null($menus)) {
            foreach ($menus as $menu) {
                $product->getRestaurantMenus()->clear();
                $product->addRestaurantMenu($this->getDoctrine()->getRepository('ClabRestaurantBundle:RestaurantMenu')->find($menu));
            }
        }
        $this->getDoctrine()->getManager()->flush();
        if (!is_null($options)) {
            foreach ($options as $option) {
                $objectOption = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductOption')->find($option);
                $product->getOptions()->clear();
                $objectOption->getProducts()->clear();
                $objectOption->addProduct($product);
                $product->addOption($objectOption);
            }
        }
        $this->getDoctrine()->getManager()->flush();
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($product, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource="/api/v1/products/{productId}",
     *      description="Edit product for restaurant",
     *      requirements={
     *          {"name"="productId", "dataType"="integer", "required"=true, "description"="Id produit"}
     *      },
     *      input="Clab\ApiBundle\Form\Type\Product\RestProductType",
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "productId"})
     */
    public function editAction(Product $product, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($product->getRestaurant());

        $form = new RestProductType(array('categories' => $categories));
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
     *      section="Products",
     *      resource="/api/v1/products/{productId}",
     *      description="Delete product for restaurant",
     *      requirements={
     *          {"name"="productId", "dataType"="integer", "required"=true, "description"="Id produit"}
     *      },
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "productId"})
     */
    public function deleteAction(Product $product)
    {
        $this->get('app_restaurant.product_manager')->remove($product);

        return new JsonResponse([
            'success' => true,
            'data' => 'Success', // Your data here
        ]);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource="/api/v1/products/{productId}",
     *      description="Reset product stock to default value",
     *      requirements={
     *          {"name"="productId", "dataType"="integer", "required"=true, "description"="Id produit"}
     *      },
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "productId"})
     */
    public function resetStockAction(Product $product)
    {
        $this->get('app_restaurant.product_manager')->resetStock($product);

        return new JsonResponse([
            'success' => true,
            'data' => 'Success', // Your data here
        ]);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource="/api/v1/products/{productId}",
     *      description="get one Product",
     *      requirements={
     *          {"name"="productId", "dataType"="integer", "required"=true, "description"="Id produit"}
     *      },
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "productId"})
     */
    public function getAction(Product $product)
    {
        $serializer = $this->get('serializer');
        $response = $serializer->serialize($product, 'json');

        return new Response($response);
    }
}
