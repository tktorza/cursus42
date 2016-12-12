<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\RestaurantBundle\Entity\ProductOption;
use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\BoardBundle\Form\Type\Option\OptionType;
use Clab\BoardBundle\Form\Type\Option\OptionChoiceType;

class OptionController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $options = $this->get('app_restaurant.product_option_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $options = $this->get('app_restaurant.product_option_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }

        return $this->render('ClabBoardBundle:Option:library.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'options' => $options,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $options = $this->get('app_restaurant.product_option_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $options = $this->get('app_restaurant.product_option_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }

        return $this->render('ClabBoardBundle:Option:libraryList.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'options' => $options,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction(Request $request, $context, $contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $optionManager = $this->get('app_restaurant.product_option_manager');

        if ($id) {
            if ($context == 'restaurant') {
                $option = $optionManager->getRepository()->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'id' => $id));
            } else {
                $option = $optionManager->getRepository()->findOneBy(array('client' => $this->get('board.helper')->getProxy(), 'id' => $id));
            }
        } else {
            if ($context == 'restaurant') {
                $option = $optionManager->createForRestaurant($this->get('board.helper')->getProxy());
            } else {
                $option = $optionManager->createForChainStore($this->get('board.helper')->getProxy());
            }
        }

        // special conditions for subway
        $subway = strpos($this->get('board.helper')->getProxy()->getSlug(), 'subway') !== false || ($this->container->get('kernel')->getEnvironment() == 'dev' && strpos($this->get('board.helper')->getProxy()->getSlug(), 'grill') !== false);
        $form = $this->createForm(new OptionType($this->get('board.helper')->getProxy(), $subway), $option);

        $previousChoices = $option->getChoices()->toArray();

        if ($request->isMethod('POST')) {
            $form->bind($this->getRequest());

            if ($form->isValid()) {
                if(!$form->getData()->getRequired() && !$form->getData()->getMultiple()) {
                    $option->setMultiple(true);
                    $option->setMinimum(0);
                    $option->setMaximum(1);
                }
                elseif($form->getData()->getRequired() && $form->getData()->getMultiple() && (is_null($form->getData()->getMinimum()) || $form->getData()->getMinimum()==0 ))
                {
                    $option->setMinimum(1);
                    if($form->getData()->getMaximum()<=$form->getData()->getMinimum()) {
                        $option->setMaximum(null);
                    }
                }
                if ($context == 'client') {
                    foreach($option->getChoices() as $choices) {
                        $choices->setClient($this->get('board.helper')->getProxy());
                    }
                }else {
                    foreach($option->getChoices() as $choices) {
                        $choices->setRestaurant($this->get('board.helper')->getProxy());
                    }
                }
                $em->persist($option);
                $em->flush();
                if ($context == 'client') {

                    foreach($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                        $this->get('clab_board.restaurant_manager')->synchroOneOption($restaurant, $option);
                    }
                }
                $this->get('session')->getFlashBag()->add('formSuccess', 'L\'option a bien été sauvegardée');

                return $this->redirectToRoute('board_option_edit', array('context' => $context, 'contextPk' => $contextPk, 'id' => $option->getId()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        return $this->render('ClabBoardBundle:Option:edit.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'option' => $option,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deleteAction($context, $contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $optionManager = $this->get('app_restaurant.product_option_manager');

        if ($context == 'restaurant') {
            $option = $optionManager->getRepository()->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'id' => $id));
        } else {
            $option = $optionManager->getRepository()->findOneBy(array('client' => $this->get('board.helper')->getProxy(), 'id' => $id));
        }
        if($context == 'client') {
            $this->get('clab_board.restaurant_manager')->deleteOptions($option);
        }
        $optionManager->remove($option);

        return $this->redirectToRoute('board_option_library', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function assignAction($context, $contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $productManager = $this->get('app_restaurant.product_manager');
        $optionManager = $this->get('app_restaurant.product_option_manager');

        if ($context == 'restaurant') {
            $option = $optionManager->getRepository()->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'id' => $id));
            $products = $productManager->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $option = $optionManager->getRepository()->findOneBy(array('client' => $this->get('board.helper')->getProxy(), 'id' => $id));
            $products = $productManager->getForChainStore($this->get('board.helper')->getProxy());
        }

        $form = $this->createFormBuilder()
            ->add('products', 'entity', array(
                'class' => 'ClabRestaurantBundle:Product',
                'choices' => $products,
                'multiple' => true,
                'expanded' => true,
                'data' => clone($option->getProducts()),
            ))
        ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $checkedProducts = array();
            if ($productData = $form->get('products')->getData()) {
                if (is_array($productData)) {
                    $checkedProducts = $productData;
                } else {
                    $checkedProducts = $productData->toArray();
                }
            }

            foreach ($products as $product) {
                if (in_array($product, $checkedProducts)) {
                    $productManager->addOptionToProduct($product, $option);
                } else {
                    $productManager->removeOptionFromProduct($product, $option);
                }
            }

            $em->flush();

            return $this->redirectToRoute('board_option_library', array('context' => $context, 'contextPk' => $contextPk, 'option' => $option->getId()));
        }

        return $this->render('ClabBoardBundle:Option:assign.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'option' => $option,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function assignChoicesAction(Request $request, $context, $contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $optionManager = $this->get('app_restaurant.product_option_manager');

        if ($context == 'restaurant') {
            $option = $optionManager->getRepository()->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'id' => $id));
            $choices = $this->get('app_restaurant.product_option_manager')->getChoicesForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $option = $optionManager->getRepository()->findOneBy(array('client' => $this->get('board.helper')->getProxy(), 'id' => $id));
            $choices = $this->get('app_restaurant.product_option_manager')->getChoicesForChainStore($this->get('board.helper')->getProxy());
        }

        $data = array();
        foreach ($option->getChoices() as $choice) {
            if (in_array($choice->getParent(), $choices)) {
                $data[] = $choice->getParent();
            }
        }

        $form = $this->createFormBuilder()
            ->add('choices', 'entity', array(
                'class' => 'ClabRestaurantBundle:OptionChoice',
                'choices' => $choices,
                'multiple' => true,
                'expanded' => true,
                'data' => $data,
            ))
        ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $checkedChoices = array();
            if ($choicesData = $form->get('choices')->getData()) {
                if (is_array($choicesData)) {
                    $checkedChoices = $choicesData;
                } else {
                    $checkedChoices = $choicesData->toArray();
                }
            }

            foreach ($choices as $choice) {
                if (in_array($choice, $checkedChoices)) {
                    if(!is_null($choice->getOption())) {
                        $optionManager->addChoiceToOption($option, $choice);
                    }else{
                        $option->addChoice($choice);
                        $choice->setOption($option);
                        $em->persist($choice);
                    }

                } else {
                    $optionManager->removeChoiceFromOption($option, $choice);
                }
            }

            $em->flush();
            if($context == 'client') {
                foreach($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                    $this->get('clab_board.restaurant_manager')->synchroChoices($restaurant, $option->getChoices());
                }
            }

            return $this->redirectToRoute('board_option_library', array('context' => $context, 'contextPk' => $contextPk, 'option' => $option->getId()));
        }

        return $this->render('ClabBoardBundle:Option:assignChoices.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'option' => $option,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function duplicateAction($context, $contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $optionManager = $this->get('app_restaurant.product_option_manager');

        $newOption = new ProductOption();

        if ($context == 'restaurant') {
            $option = $optionManager->getRepository()->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'id' => $id));
            $newOption->setRestaurant($this->get('board.helper')->getProxy());
        } else {
            $option = $optionManager->getRepository()->findOneBy(array('client' => $this->get('board.helper')->getProxy(), 'id' => $id));
            $newOption->setClient($this->get('board.helper')->getProxy());
        }

        $newOption->setName($option->getName());
        $newOption->setRequired($option->getRequired());
        $newOption->setMultiple($option->getMultiple());
        $newOption->setMinimum($option->getMinimum());
        $newOption->setMaximum($option->getMaximum());
        $newOption->setPosition($option->getPosition());
        $em->persist($newOption);
        $em->flush();

        foreach ($option->getChoices() as $choice) {
            $optionManager->addChoiceToOption($newOption, $choice->getParent());
        }

        $em->flush();

        return $this->redirectToRoute('board_option_library', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function choiceLibraryAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $choices = $this->get('app_restaurant.product_option_manager')->getChoicesForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $choices = $this->get('app_restaurant.product_option_manager')->getChoicesForChainStore($this->get('board.helper')->getProxy());
        }

        return $this->render('ClabBoardBundle:Option:choiceLibrary.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'choices' => $choices,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function choiceLibraryListAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $choices = $this->get('app_restaurant.product_option_manager')->getChoicesForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $choices = $this->get('app_restaurant.product_option_manager')->getChoicesForChainStore($this->get('board.helper')->getProxy());
        }

        return $this->render('ClabBoardBundle:Option:choiceLibraryList.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'choices' => $choices,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function choiceEditAction(Request $request, $context, $contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $optionManager = $this->get('app_restaurant.product_option_manager');

        if ($id) {
            if ($context == 'restaurant') {
                $choice = $optionManager->getChoiceRepository()->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'id' => $id));
            } else {
                $choice = $optionManager->getChoiceRepository()->findOneBy(array('client' => $this->get('board.helper')->getProxy(), 'id' => $id));
            }
        } else {
            if ($context == 'restaurant') {
                $choice = $optionManager->createChoiceForRestaurant($this->get('board.helper')->getProxy());
            } else {
                $choice = $optionManager->createChoiceForChainStore($this->get('board.helper')->getProxy());
            }
        }

        $form = $this->createForm(new OptionChoiceType($this->getProxy()), $choice);
        $form->handleRequest($request);
        if ($request->isMethod('POST')) {
            if ($form->isValid()) {
                $em->persist($choice);
                $em->flush();
                if ($context == 'client') {
                    foreach($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                        $this->get('clab_board.restaurant_manager')->synchroOneChoice($restaurant, $choice);
                    }
                }
                $this->addFlash('formSuccess', 'L\'option a bien été sauvegardée');

                return $this->redirectToRoute('board_option_choice_edit', array('context' => $context, 'contextPk' => $contextPk, 'id' => $choice->getId()));
            } else {
                $this->addFlash('formError', 'Erreur dans le formulaire');
            }
        }

        if ($choice->getId()) {
            $options = $optionManager->getOptionsForChoice($choice);
        }

        return $this->render('ClabBoardBundle:Option:choiceEdit.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'choice' => $choice,
            'form' => $form->createView(),
            'options' => isset($options) ? $options : null,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function choiceDeleteAction($context, $contextPk, $id)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $optionManager = $this->get('app_restaurant.product_option_manager');

        if ($context == 'restaurant') {
            $choice = $optionManager->getChoiceRepository()->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'id' => $id));
        } else {
            $choice = $optionManager->getChoiceRepository()->findOneBy(array('client' => $this->get('board.helper')->getProxy(), 'id' => $id));
        }
        if($context == 'client') {
            $this->get('clab_board.restaurant_manager')->deleteChoices($choice);
        }
        $optionManager->removeChoice($choice);

        return $this->redirectToRoute('board_option_choice_library', array('context' => $context, 'contextPk' => $contextPk));
    }
}
