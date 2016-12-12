<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Entity\AdditionalSaleProduct;
use Clab\BoardBundle\Form\Type\AdditionalSale\AdditionalSaleEditType;
use Clab\BoardBundle\Form\Type\AdditionalSale\AdditionalSaleType;
use Clab\BoardBundle\Entity\AdditionalSale;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\Restaurant;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormError;

class AdditionalSaleController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function createAction(Request $request, $contextPk)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $additionalSale = new AdditionalSale();

        $form = $this->createForm(new AdditionalSaleType(), $additionalSale);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            if ($form->isValid()) {
                $additionalSale->setRestaurant($this->get('board.helper')->getProxy());
                $additionalSale->setMultiple(false);
                $additionalSale->setIsOnline(false);
                $em->persist($additionalSale);
                $em->flush();

                $this->get('session')->getFlashBag()->add('formSuccess', 'La vente additionnelle a bien été sauvegardée');

                return $this->redirectToRoute('board_additional_sale_edit', array('contextPk' => $contextPk, 'slug' => $additionalSale->getSlug()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        return $this->render('ClabBoardBundle:AdditionalSale:create.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'additionalSale' => $additionalSale,
            'form' => $form->createView(),
        )));
    }
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $additionalSales = $this->getDoctrine()->getRepository('ClabBoardBundle:AdditionalSale')->findBy(array('restaurant' => $this->get('board.helper')->getProxy()));

        foreach ($additionalSales as $key => $additionalSale) {
            if (!is_null($additionalSale->getProduct()) && $additionalSale->getProduct()->isDeleted() || !is_null($additionalSale->getMeal()) && $additionalSale->getMeal()->isDeleted()) {
                unset($additionalSales[$key]);
            }

            $additionalSaleProducts = $additionalSale->getAdditionalSaleProducts();
            foreach ($additionalSaleProducts as $key => $additionalSaleProduct) {
                if ($additionalSaleProduct->getProduct()->isDeleted()) {
                    unset($additionalSaleProducts[$key]);
                }
            }
        }

        $this->get('board.helper')->addParam('additionalSales', $additionalSales);

        if ($this->getRequest()->get('additionalSales')) {
            $this->get('board.helper')->addParam('additionalSales', $this->getRequest()->get('additionalSales'));
        }

        return $this->render('ClabBoardBundle:AdditionalSale:library.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $additionalSales = $this->getDoctrine()->getRepository('ClabBoardBundle:AdditionalSale')->findBy(array('restaurant' => $this->get('board.helper')->getProxy()));

        return $this->render('ClabBoardBundle:AdditionalSale:libraryList.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'additionalSales' => $additionalSales,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function assignProductsAction(Request $request, $contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $additionalSale = $em->getRepository('ClabBoardBundle:AdditionalSale')->findOneBy(array('slug' => $slug));

        $products = $em->getRepository('ClabRestaurantBundle:Product')->getForRestaurant($this->get('board.helper')->getProxy());

        $data = array();
        foreach ($additionalSale->getAdditionalSaleProducts() as $additionalSaleProduct) {
            if (in_array($additionalSaleProduct->getProduct(), $products)) {
                $data[] = $additionalSaleProduct->getProduct();
            }
        }

        $form = $this->createFormBuilder()
            ->add('choices', 'entity', array(
                'class' => 'ClabRestaurantBundle:Product',
                'choices' => $products,
                'multiple' => true,
                'expanded' => true,
                'data' => $data,
            ))
            ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $checkedProducts = array();
            if ($choicesData = $form->get('choices')->getData()) {
                if (is_array($choicesData)) {
                    $checkedProducts = $choicesData;
                } else {
                    $checkedProducts = $choicesData->toArray();
                }
            }
            foreach ($products as $product) {
                if (in_array($product, $checkedProducts)) {
                    $additionalSaleProduct = $em->getRepository('ClabBoardBundle:AdditionalSaleProduct')->findOneBy(array('product' => $product, 'additionalSale' => $additionalSale));
                    if (is_null($additionalSaleProduct)) {
                        $additionalSaleProduct = new AdditionalSaleProduct();
                    }

                    $additionalSaleProduct->setProduct($product);
                    $additionalSaleProduct->setPrice($product->getPrice());
                    $additionalSale->addAdditionalSaleProduct($additionalSaleProduct);
                } else {
                    $additionalSaleProduct = $em->getRepository('ClabBoardBundle:AdditionalSaleProduct')->findOneBy(array('product' => $product, 'additionalSale' => $additionalSale));
                    if (!is_null($additionalSaleProduct)) {
                        $additionalSale->removeAdditionalSaleProduct($additionalSaleProduct);
                        $em->remove($additionalSaleProduct);
                    }
                }
            }

            $em->persist($additionalSale);
            $em->flush();

            $this->get('session')->getFlashBag()->add('formSuccess', 'La vente additionnelle a bien été sauvegardée');

            return $this->redirectToRoute('board_additional_sale_edit', array('contextPk' => $contextPk, 'slug' => $additionalSale->getSlug()));
        }

        return $this->render('ClabBoardBundle:AdditionalSale:assignProducts.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'form' => $form->createView(),
            'additionalSale' => $additionalSale,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction($contextPk, $slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($slug) {
            $additionalSale = $em->getRepository('ClabBoardBundle:AdditionalSale')->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
        } else {
            $additionalSale = new AdditionalSale();
        }

        $products = $em->getRepository('ClabRestaurantBundle:Product')->getForRestaurant($this->get('board.helper')->getProxy());
        $meals = $em->getRepository('ClabRestaurantBundle:Meal')->getForRestaurant($this->get('board.helper')->getProxy());

        $choices = array();
        $data = 0;

        foreach ($products as $key => $product) {
            if (is_null($product->getAdditionalSale())) {
                $choices['Produits']['P,'.$product->getSlug()] = $product->getName();
            } elseif ($product->getAdditionalSale() == $additionalSale) {
                $choices['Produits']['P,'.$product->getSlug()] = $product->getName();
                $data = 'P,'.$product->getSlug();
            }
        }

        foreach ($meals as $key => $meal) {
            if (is_null($meal->getAdditionalSale())) {
                $choices['Formules']['M,'.$meal->getSlug()] = $meal->getName();
            } elseif ($meal->getAdditionalSale() == $additionalSale) {
                $choices['Formules']['M,'.$meal->getSlug()] = $meal->getName();
                $data = 'M,'.$meal->getSlug();
            }
        }

        $form = $this->createForm(new AdditionalSaleEditType(), $additionalSale);
        $form->add('choices', 'choice', array(
                'choices' => $choices,
                'multiple' => false,
                'mapped' => false,
                'data' => $data,
                'label' => ' ',
            ));

        $form->handleRequest($request);
        if ($request->isMethod('POST')) {
            if ($form->isValid()) {
                $choice = $form->get('choices')->getData();

                $choice = explode(',', $choice);

                if ($choice[0] == 'P') {
                    $prod = $em->getRepository('ClabRestaurantBundle:Product')->findOneBy(array('slug' => $choice[1]));
                    if (!is_null($prod)) {
                        if (!is_null($additionalSale->getProduct())) {
                            $additionalSale->getProduct()->setAdditionalSale(null);
                        }
                        if (!is_null($additionalSale->getMeal())) {
                            $additionalSale->getMeal()->setAdditionalSale(null);
                        }
                        $additionalSale->setMeal(null);
                        $additionalSale->setProduct($prod);
                    }
                } elseif ($choice[0] == 'M') {
                    $meal = $em->getRepository('ClabRestaurantBundle:Meal')->findOneBy(array('slug' => $choice[1]));
                    if (!is_null($meal)) {
                        if (!is_null($additionalSale->getProduct())) {
                            $additionalSale->getProduct()->setAdditionalSale(null);
                        }
                        if (!is_null($additionalSale->getMeal())) {
                            $additionalSale->getMeal()->setAdditionalSale(null);
                        }
                        $additionalSale->setProduct(null);
                        $additionalSale->setMeal($meal);
                    }
                }

                if ($form->get('multiple')->getData() == 1 && $form->get('minimum')->getData() == $form->get('maximum')->getData()) {
                    $additionalSale->setMinimum(null);
                    $additionalSale->setMaximum(null);
                }

                $this->get('session')->getFlashBag()->add('formSuccess', 'La vente additionnelle a bien été sauvegardée');
                $em->persist($additionalSale);
                $em->flush();

                return $this->redirectToRoute('board_additional_sale_edit', array('contextPk' => $contextPk, 'slug' => $additionalSale->getSlug()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        return $this->render('ClabBoardBundle:AdditionalSale:edit.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'additionalSale' => $additionalSale,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deleteAction($contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $additionalSale = $em->getRepository('ClabBoardBundle:AdditionalSale')->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
        $em->remove($additionalSale);

        $em->flush();

        return $this->redirectToRoute('board_additional_sale_library', array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function duplicateAction($contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $additionalSale = $em->getRepository('ClabBoardBundle:AdditionalSale')->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
        $duplicateAdditionalSale = clone $additionalSale;
        $duplicateAdditionalSale->setProduct(null);
        $duplicateAdditionalSale->setId(null);
        $duplicateAdditionalSale->setMeal(null);
        $duplicateAdditionalSale->setSlug(null);
        $duplicateAdditionalSale->setCreated(new \DateTime('now'));
        $duplicateAdditionalSale->setUpdated(new \DateTime('now'));

        foreach ($additionalSale->getAdditionalSaleProducts() as $additionalSaleProduct) {
            $duplicateAdditionalSale->addAdditionalSaleProduct(clone $additionalSaleProduct);
        }

        $em->persist($duplicateAdditionalSale);
        $em->flush();

        return $this->redirectToRoute('board_additional_sale_library', array('contextPk' => $contextPk));
    }
}
