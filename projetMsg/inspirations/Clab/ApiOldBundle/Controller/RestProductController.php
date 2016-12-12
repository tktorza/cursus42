<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Product;
use Clab\ApiOldBundle\Form\Type\Product\RestProductType;
use Symfony\Component\HttpFoundation\Response;

class RestProductController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Products",
     *      description="Get taxes list",
     * )
     */
    public function taxesAction()
    {
        $taxes = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Tax')->findBy(array('is_online' => true));

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($taxes, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      description="Get product list for restaurant menu",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     * @ParamConverter("menu", class="ClabRestaurantBundle:RestaurantMenu", options={"id" = "id"})
     */
    public function listAction(RestaurantMenu $menu)
    {
        $products = $this->get('app_restaurant.product_manager')->getForRestaurantMenu($menu);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($products, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      description="Get product list for restaurant menu",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function getForRestaurantAction(Restaurant $restaurant)
    {
        $products = $this->get('app_restaurant.product_manager')->getForRestaurant($restaurant);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($products, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource=true,
     *      description="Get product for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id produit"}
     *      },
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     * @ParamConverter("menu", class="ClabRestaurantBundle:RestaurantMenu", options={"id" = "id"})
     */
    public function listAvailable(RestaurantMenu $menu)
    {
        $products = $this->get('app_restaurant.product_manager')->getAvailableForRestaurantMenu($menu);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($products, 'json');

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
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($product, 'json');

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
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "id"})
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
            $gallery = $product->getGallery();
            
            $gallery->setCover($image);
            $product->setCoverFull( 'http://'.$this->getParameter('apiDomain').'/'.$image->getWebPath());
            $product->setCoverSmall('http://'.$this->getParameter('apiDomain').'/'.$image->getWebPath());
            $product->setCover('http://'.$this->getParameter('apiDomain').'/'.$image->getWebPath());

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
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($product, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource=true,
     *      description="Edit product for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id produit"}
     *      },
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestProductType",
     *      output="Clab\RestaurantBundle\Entity\Product"
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "idRestaurant"})
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "id"})
     */
    public function editAction(Restaurant $restaurant, Product $product, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $product = $em->getRepository('ClabRestaurantBundle:Product')->find($product);
        $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($restaurant);

        $form = new RestProductType(array('categories' => $categories));
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
     *      section="Products",
     *      resource=true,
     *      description="Delete product for restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id produit"}
     *      },
     * )
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"id" = "id"})
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
     *      resource=true,
     *      description="Reset product stock to default value",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      },
     * )
     */
    public function resetStockAction($products)
    {
        $this->get('app_restaurant.product_manager')->resetStock($products);

        return new JsonResponse([
            'success' => true,
            'data' => 'Success', // Your data here
        ]);
    }

    /**
     * @ApiDoc(
     *      section="Products",
     *      resource=true,
     *      description="Edit options choices",
     *      requirements={
     *          {"name"="optionId", "dataType"="integer", "required"=true, "description"="Id Option"}
     *      },
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
                $this->getDoctrine()->getManager()->flush();
            }
        }
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($option, 'json');

        return new Response($response);
    }
}
