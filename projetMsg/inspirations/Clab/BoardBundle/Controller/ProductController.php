<?php

namespace Clab\BoardBundle\Controller;

use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\RestaurantBundle\Entity\Product;
use Clab\BoardBundle\Form\Type\Product\ProductType;
use Clab\BoardBundle\Form\Type\Catalog\CatalogType;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction(Request $request, $context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant' && !$this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'product')) {
            return $this->redirectToRoute('board_feature_showcase', array('feature' => 'product', 'contextPk' => $contextPk));
        }

        if ($context == 'restaurant') {
            $products = $this->get('app_restaurant.product_manager')->getForRestaurant($this->get('board.helper')->getProxy());
            $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $products = $this->get('app_restaurant.product_manager')->getForChainStore($this->get('board.helper')->getProxy());
            $categories = $this->get('app_restaurant.product_category_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }

        if ($request->get('product')) {
            $product = $request->get('product');
        }

        return $this->render('ClabBoardBundle:Product:library.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'categories' => $categories,
            'products' => $products,
            'product' => isset($product) ? $product : null,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $products = $this->get('app_restaurant.product_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $products = $this->get('app_restaurant.product_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }

        return $this->render('ClabBoardBundle:Product:libraryList.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'products' => $products,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction(Request $request, $context, $contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $productManager = $this->get('app_restaurant.product_manager');
        $restaurant = null;
        $associatedRestaurants = array();
        $associatedProductsData = array();
        $hasCaisse = null;
        $hasDelivery = true;
        $application = $this->getDoctrine()->getRepository('ClabRestaurantBundle:App')->findOneBy(array(
            'slug' => 'livraison',
        ));

        if ($slug) {
            if ($context == 'restaurant') {
                $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
            } else {
                $restaurant = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->findOneBy(array('slug' => $contextPk));
            }
        }

        $hasCaisse = $restaurant ? $restaurant->getHasCaisse() : false;

        if ($slug) {
            if ($context == 'restaurant') {
                $product = $productManager->getOneForRestaurant($this->get('board.helper')->getProxy(), $slug);
            } else {
                $product = $productManager->getOneForChainStore($this->get('board.helper')->getProxy(), $slug);

                $childProducts = $em->getRepository('ClabRestaurantBundle:Product')->findBy(array('parent' => $product));

                foreach ($childProducts as $children) {
                    if ($children->isOnline()) {
                        $associatedRestaurants[] = $children->getRestaurant();
                        $associatedProductsData[$children->getRestaurant()->getSlug()] = $children;
                    }
                }
            }
        } else {
            $product = $productManager->create();
        }

        $form = $this->createForm(new ProductType($this->get('board.helper')->getProxy(), $hasDelivery), $product);

        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                if ($form->has('category')) {
                    $categoryManager = $this->get('app_restaurant.product_category_manager');
                    $categoryId = $form->get('category')->getData();

                    if (is_numeric($categoryId)) {
                        if ($context == 'restaurant') {
                            $category = $categoryManager->getRepository()->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'id' => $categoryId));
                        } else {
                            $category = $categoryManager->getRepository()->findOneBy(array('client' => $this->get('board.helper')->getProxy(), 'id' => $categoryId));
                        }
                    } elseif ($categoryId) {
                        if ($context == 'restaurant') {
                            $category = $categoryManager->createForRestaurant($this->get('board.helper')->getProxy());
                        } else {
                            $category = $categoryManager->createForChainStore($this->get('board.helper')->getProxy());
                        }

                        $category->setName($categoryId);
                        $category->setProduct($product);
                        $em->persist($category);
                    } else {
                        $category = null;
                    }
                    $product->setCategory($category);
                }

                if ($form->has('allergies')) {
                    $allergies = $form->get('allergies')->getData();
                    $product->addExtraFields('allergies',$allergies);
                }
                if ($form->has('condiments')) {
                    $condiments = $form->get('condiments')->getData();
                    $product->addExtraFields('condiments',$condiments);
                }
                if ($form->has('regime')) {
                    $regime = $form->get('regime')->getData();
                    $product->addExtraFields('regime',$regime);
                }
                if ($form->has('nbpieces')) {
                    $nbpieces = $form->get('nbpieces')->getData();
                    $product->addExtraFields('nbpieces',$nbpieces);
                }
                if ($form->has('calories')) {
                    $calories = $form->get('calories')->getData();
                    $product->addExtraFields('calories',$calories);
                }

                $em->persist($product);

                $this->get('session')->getFlashBag()->add('formSuccess', 'Le produit a bien été sauvegardé');

                if ($context == 'client') {
                    foreach ($associatedRestaurants as $associatedRestaurant) {
                        $price = $request->request->get('price-'.$associatedRestaurant->getSlug());
                        $price = str_replace(',', '.', $price);
                        if (is_numeric($price) and $price >= 0) {
                            $childMenuClassic = $em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('restaurant' => $associatedRestaurant, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT));
                            $childMenuDelivery = $em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('restaurant' => $associatedRestaurant, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY));

                            $child=$this->get('clab_board.restaurant_manager')->synchroOneProduct($associatedRestaurant,$childMenuClassic,$childMenuDelivery,$product);

                            $child->setPrice($price);
                            $child->setIsPDJ($form->get('isPDJ')->getData());
                            $child->setStartDate($form->get('startDate')->getData());
                            $child->setEndDate($form->get('endDate')->getData());
                            $child->setMealOnly($product->isMealOnly());
                            $em->persist($child);
                            $em->flush();

                            $this->get('clab_board.restaurant_manager')->synchroOptions($associatedRestaurant,$product->getOptions());
                            $em->flush();

                        } else {
                            $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire'.$price);
                        }
                    }
                }
                $em->persist($product);

                $em->flush();

                return $this->redirectToRoute('board_product_edit', array('context' => $context, 'contextPk' => $contextPk, 'slug' => $product->getSlug()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        if ($context == 'restaurant') {
            $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $categories = $this->get('app_restaurant.product_category_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }

        return $this->render('ClabBoardBundle:Product:edit.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'product' => $product,
            'form' => $form->createView(),
            'categories' => $categories,
            'restaurant' => $restaurant,
            'hasCaisse' => $hasCaisse,
            'associatedRestaurants' => $associatedRestaurants,
            'associatedProductsData' => $associatedProductsData,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deleteAction($context, $contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $productManager = $this->get('app_restaurant.product_manager');

        if ($context == 'restaurant') {
            $product = $productManager->getOneForRestaurant($this->get('board.helper')->getProxy(), $slug);
        } else {
            $product = $productManager->getOneForChainStore($this->get('board.helper')->getProxy(), $slug);
        }

        if($context == 'client') {
            $this->get('clab_board.restaurant_manager')->deleteProducts($product);
        }
        $productManager->remove($product);

        return $this->redirectToRoute('board_product_library', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function optionsAction($context, $contextPk, $slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $productManager = $this->get('app_restaurant.product_manager');
        $optionManager = $this->get('app_restaurant.product_option_manager');

        if ($context == 'restaurant') {
            $product = $productManager->getOneForRestaurant($this->get('board.helper')->getProxy(), $slug);
            $options = $optionManager->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $product = $productManager->getOneForChainStore($this->get('board.helper')->getProxy(), $slug);
            $options = $optionManager->getForChainStore($this->get('board.helper')->getProxy());
        }

        $form = $this->createFormBuilder()
            ->add('options', 'entity', array(
                'class' => 'ClabRestaurantBundle:ProductOption',
                'choices' => $options,
                'multiple' => true,
                'expanded' => true,
                'data' => clone $product->getOptions(), // clone necessary otherwise form update product data directly
            ))
        ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $checkedOptions = array();
            if ($optionsData = $form->get('options')->getData()) {
                if (is_array($optionsData)) {
                    $checkedOptions = $optionsData;
                } else {
                    $checkedOptions = $optionsData->toArray();
                }
            }

            foreach ($options as $option) {
                if (in_array($option, $checkedOptions)) {
                    $productManager->addOptionToProduct($product, $option);
                } else {
                    $productManager->removeOptionFromProduct($product, $option);
                }
            }

            return $this->redirectToRoute('board_product_library', array('context' => $context, 'contextPk' => $contextPk, 'product' => $product->getSlug()));
        }

        return $this->render('ClabBoardBundle:Product:options.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'product' => $product,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function assignRestaurantAction($context, $contextPk, $slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $product = $em->getRepository('ClabRestaurantBundle:Product')->findOneBy(array('slug' => $slug));

        $restaurants = $em->getRepository('ClabRestaurantBundle:Restaurant')->findBy(array('client' => $this->get('board.helper')->getProxy()));

        $restaurants_data = array();
        $product_data = array();

        $products = $em->getRepository('ClabRestaurantBundle:Product')->findBy(array('parent' => $product));

        foreach ($products as $children) {
            if ($children->isOnline()) {
                $restaurants_data[] = $children->getRestaurant();
            }
            $product_data[$children->getRestaurant()->getSlug()] = $children;
        }

        $form = $this->createFormBuilder()
            ->add('restaurants', 'entity', array(
                'class' => 'ClabRestaurantBundle:Restaurant',
                'choices' => $restaurants,
                'multiple' => true,
                'expanded' => true,
                'data' => $restaurants_data, // clone necessary otherwise form update product data directly
            ))->getForm();

        if ($request->isMethod('POST')) {
            if ($form->handleRequest($request)->isValid()) {
                $checkedRestaurants = array();
                if ($restaurantData = $form->get('restaurants')->getData()) {
                    if (is_array($restaurantData)) {
                        $checkedRestaurants = $restaurantData;
                    } else {
                        $checkedRestaurants = $restaurantData->toArray();
                    }
                }
                foreach ($restaurants as $restaurant) {
                    $price = $request->request->get('price-'.$restaurant->getSlug());
                    if (is_numeric($price) and $price >= 0) {

                        $childMenuClassic = $em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('restaurant' => $restaurant, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT));
                        $childMenuDelivery = $em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('restaurant' => $restaurant, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY));

                        $child=$this->get('clab_board.restaurant_manager')->synchroOneProduct($restaurant,$childMenuClassic,$childMenuDelivery,$product);

                        if (in_array($restaurant, $checkedRestaurants)) {
                            $child->setIsOnline(true);
                        }else {
                            $child->setIsOnline(false);
                        }

                        $child->setPosition($product->getPosition());
                        $child->setPrice($price);
                        $em->persist($child);
                        $em->flush();

                        $this->get('clab_board.restaurant_manager')->synchroOptions($restaurant,$product->getOptions());
                        $em->flush();

                    } else {
                        $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');

                        return $this->redirectToRoute('board_product_edit',
                            array('context' => $context, 'contextPk' => $contextPk, 'slug' => $product->getSlug()));
                    }
                }
                $this->get('session')->getFlashBag()->add('formSuccess',
                    'Les restaurants associés ont bien été sauvegardés');

                return $this->redirectToRoute('board_product_edit',
                    array('context' => $context, 'contextPk' => $contextPk, 'slug' => $product->getSlug()));
            }
        }

        return $this->render('ClabBoardBundle:Product:assignRestaurants.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'product' => $product,
            'form' => $form->createView(),
            'products' => $product_data,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function catalogAction($context, $contextPk,  Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($this->get('board.helper')->getProxy());
            $meals = $this->get('app_restaurant.meal_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $meals = $this->get('app_restaurant.meal_manager')->getForChainStore($this->get('board.helper')->getProxy());
            $categories = $this->get('app_restaurant.product_category_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }

        foreach ($categories as $key => $category) {
            if (count($category->getProducts()) == 0) {
                unset($categories[$key]);
            }
        }

        $categoryType = new CatalogType(array(
            'categories' => $categories,
            'meals' => $meals ,
            'client' => 'client' == $context)
        );

        $form = $this->createForm($categoryType);

        if ($form->handleRequest($request)->isValid()) {
            $em->flush();

            return $this->redirectToRoute('board_catalog', array('context' => $context, 'contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Product:catalog.html.twig', $this->get('board.helper')->getParams());
    }
}
