<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\RestaurantBundle\Entity\ProductCategory;
use Clab\BoardBundle\Form\Type\ProductCategory\ProductCategoryType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductCategoryController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant' && !$this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'category')) {
            return $this->redirectToRoute('board_feature_showcase', array('feature' => 'category', 'contextPk' => $contextPk));
        }

        if ($context == 'restaurant') {
            $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $categories = $this->get('app_restaurant.product_category_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }
        $types = array();
        foreach($categories as $category) {
            $types[$category->getType()] = $category->getType();
        }

        return $this->render('ClabBoardBundle:ProductCategory:library.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'categories' => $categories,
            'types' => $types,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $categories = $this->get('app_restaurant.product_category_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }

        $types = array();
        foreach($categories as $category) {
            $types[$category->getType()] = $category->getType();
        }

        return $this->render('ClabBoardBundle:ProductCategory:libraryList.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'categories' => $categories,
            'types' => $types,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction($context, $contextPk, $slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $categoryManager = $this->get('app_restaurant.product_category_manager');

        if ($slug) {
            if ($context == 'restaurant') {
                $category = $categoryManager->getRepository()->findOneBy(array(
                    'restaurant' => $this->get('board.helper')->getProxy(),
                    'slug' => $slug,
                ));
            } else {
                $category = $categoryManager->getRepository()->findOneBy(array(
                    'client' => $this->get('board.helper')->getProxy(),
                    'slug' => $slug,
                ));
            }
        } else {
            if ($context == 'restaurant') {
                $category = new ProductCategory();
                $category->setRestaurant($this->get('board.helper')->getProxy());
            } else {
                $category = new ProductCategory();
                $category->setClient($this->get('board.helper')->getProxy());
            }
        }
        $form = $this->createForm(new ProductCategoryType(), $category);

        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                if ($context == 'client') {
                    $clientProxy = $this->get('board.helper')->getProxy();
                    $client = $em->getRepository('ClabBoardBundle:Client')->find($clientProxy);
                    $em->flush();
                    $this->get('clab_board.restaurant_manager')->synchroOneCategory($client,$category);

                    $this->get('session')->getFlashBag()->add('formSuccess', 'La catégorie a bien été sauvegardée');
                } else {
                    $em->persist($category);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('formSuccess', 'La catégorie a bien été sauvegardée');
                }

                return $this->redirectToRoute('board_category_edit',
                    array('context' => $context, 'contextPk' => $contextPk, 'slug' => $category->getSlug()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');

                return $this->redirectToRoute('board_category_edit',
                    array('context' => $context, 'contextPk' => $contextPk, 'slug' => $category->getSlug()));
            }
        }

        return $this->render('ClabBoardBundle:ProductCategory:edit.html.twig',
                array_merge($this->get('board.helper')->getParams(),
                    array(
                        'category' => $category,
                        'form' => $form->createView(),
                    )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deleteAction($context, $contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $categoryManager = $this->get('app_restaurant.product_category_manager');

        if ($context == 'restaurant') {
            $category = $categoryManager->getRepository()->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
        } else {
            $category = $categoryManager->getRepository()->findOneBy(array('client' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
        }

        $categoryManager->remove($category);

        return $this->redirectToRoute('board_category_library', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function assignAction($context, $contextPk, $slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $categoryManager = $this->get('app_restaurant.product_category_manager');
        $productManager = $this->get('app_restaurant.product_manager');

        if ($context == 'restaurant') {
            $category = $categoryManager->getRepository()->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
            $products = $productManager->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $category = $categoryManager->getRepository()->findOneBy(array('client' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
            $products = $productManager->getForChainStore($this->get('board.helper')->getProxy());
        }

        $currentProducts = $category->getProducts();

        $form = $this->createFormBuilder()
            ->add('products', 'entity', array(
                'class' => 'ClabRestaurantBundle:Product',
                'choices' => $products,
                'multiple' => true,
                'expanded' => true,
                'data' => clone($currentProducts),
            ))
        ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            foreach ($form->get('products')->getData() as $product) {
                $product->setCategory($category);
                if(!$category->getProducts()->contains($product)) {
                    $category->addProduct($product);
                }
                $em->flush();

                foreach ($currentProducts as $key => $currentProduct) {
                    if ($currentProduct == $product) {
                        unset($currentProducts[$key]);
                    }
                }
            }

            foreach ($currentProducts as $currentProduct) {
                if($category->getProducts()->contains($product)) {
                    $category->removeProduct($product);
                }
                $currentProduct->setCategory(null);
                $em->flush();
            }
            if($context == 'client') {
                $this->get('clab_board.restaurant_manager')->synchroOneCategoryProducts($this->get('board.helper')->getProxy()->getRestaurants(),$category);
            }

            return $this->redirectToRoute('board_category_library', array('context' => $context, 'contextPk' => $contextPk));
        }

        return $this->render('ClabBoardBundle:ProductCategory:assign.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'category' => $category,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function reorderAction($context, $contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $sort = $this->getRequest()->get('sort');

        if ($request->isMethod('POST') && $sort) {
            if ($context == 'restaurant') {
                $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($this->get('board.helper')->getProxy());
            } else {
                $categories = $this->get('app_restaurant.product_category_manager')->getForChainStore($this->get('board.helper')->getProxy());
            }
            if($context =='client') {
                foreach ($categories as $category) {
                    if (in_array($category->getId(), $sort)) {
                        $category->setPosition(array_search($category->getId(), $sort));
                        foreach($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                            $this->get('clab_board.restaurant_manager')->synchroOneCategoryPosition($restaurant,$category);
                        }
                    } else {
                        $category->setPosition(9999);
                        foreach($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                            $this->get('clab_board.restaurant_manager')->synchroOneCategoryPosition($restaurant,$category);
                        }
                    }
                    $em->flush();
                }
            }else{
                foreach ($categories as $category) {
                    if (in_array($category->getId(), $sort)) {
                        $category->setPosition(array_search($category->getId(), $sort));
                    } else {
                        $category->setPosition(9999);
                    }

                    $em->flush();
                }
            }

        }

        if ($context == 'restaurant') {
        $categories = $this->get('app_restaurant.product_category_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $categories = $this->get('app_restaurant.product_category_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }
        $types = array();
        foreach($categories as $category) {
            $types[$category->getType()] = $category->getType();
        }

        return $this->render('ClabBoardBundle:ProductCategory:libraryList.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'categories' => $categories,
            'types' => array_unique($types),
        )));
    }
}
