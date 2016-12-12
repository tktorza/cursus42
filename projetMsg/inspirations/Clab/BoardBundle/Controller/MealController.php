<?php

namespace Clab\BoardBundle\Controller;

use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\MealSlot;
use Clab\BoardBundle\Form\Type\Meal\MealType;
use Clab\BoardBundle\Form\Type\Meal\MealSlotType;
use Clab\BoardBundle\Form\Type\Meal\MealSlotCategoriesType;

class MealController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction(Request $request, $context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant' && !$this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'meal')) {
            return $this->redirectToRoute('board_feature_showcase', array('feature' => 'meal', 'contextPk' => $contextPk));
        }

        if ($context == 'restaurant') {
            $meals = $this->get('app_restaurant.meal_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $meals = $this->get('app_restaurant.meal_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }

        if ($request->get('meal')) {
            $meal = $request->get('meal');
        }

        return $this->render('ClabBoardBundle:Meal:library.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'meals' => $meals,
            'meal' => isset($meal) ? $meal : null,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction(Request $request, $context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $meals = $this->get('app_restaurant.meal_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $meals = $this->get('app_restaurant.meal_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }

        if ($request->get('meal')) {
            $meal = $request->get('meal');
        }

        return $this->render('ClabBoardBundle:Meal:libraryList.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'meals' => $meals,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction(Request $request, $context, $contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $mealManager = $this->get('app_restaurant.meal_manager');
        $associatedRestaurants = array();
        $associatedProductsData = array();
        $hasDelivery = true;
        $application = $this->getDoctrine()->getRepository('ClabRestaurantBundle:App')->findOneBy(array(
            'slug' => 'livraison',
        ));

        if ($slug) {
            if ($context == 'restaurant') {
                $meal = $mealManager->getOneForRestaurant($this->get('board.helper')->getProxy(), $slug);
            } else {
                $meal = $mealManager->getOneForChainStore($this->get('board.helper')->getProxy(), $slug);
                $childMeals = $em->getRepository('ClabRestaurantBundle:Meal')->findBy(array('parent' => $meal));
                foreach ($childMeals as $children) {
                    if ($children->isOnline() == true) {
                        $associatedRestaurants[] = $children->getRestaurant();
                        $associatedProductsData[$children->getRestaurant()->getSlug()] = $children;
                    }
                }
            }
        } else {
            $meal = $mealManager->create();
        }

        $form = $this->createForm(new MealType($this->get('board.helper')->getProxy(), $hasDelivery), $meal);

        if ($request->isMethod('POST')) {
            $form->bind($this->getRequest());

            if ($form->isValid()) {
                $em->persist($meal);
                $em->flush();
                if ($context == 'client') {
                    $this->get('clab_board.restaurant_manager')->synchroOneMeal
                    ($this->get('board.helper')->getProxy()->getRestaurants(),$meal);
                    foreach ($associatedRestaurants as $associatedRestaurant) {
                        $child = $this->getDoctrine()->getEntityManager()->getRepository('ClabRestaurantBundle:Meal')->getForRestaurantAndParent($associatedRestaurant,$meal);

                        $price = $request->request->get('price-'.$associatedRestaurant->getSlug());
                        $price = str_replace(',', '.', $price);
                        if (is_numeric($price) and $price >= 0) {
                            $child->setPrice($price);
                            $em->persist($child);
                            $em->flush();
                        } else {
                            $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire'.$price);
                        }
                    }
                }
                $this->get('session')->getFlashBag()->add('formSuccess', 'La formule a bien été sauvegardée');

                return $this->redirectToRoute('board_meal_edit', array('context' => $context, 'contextPk' => $contextPk, 'slug' => $meal->getSlug()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }
        $restaurant = null;
        if ($context == 'restaurant') {
            $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
                'slug' => $contextPk,
            ));
        }

        return $this->render('ClabBoardBundle:Meal:edit.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'meal' => $meal,
            'restaurant' => $restaurant,
            'form' => $form->createView(),
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
        $mealManager = $this->get('app_restaurant.meal_manager');

        if ($context == 'restaurant') {
            $meal = $mealManager->getOneForRestaurant($this->get('board.helper')->getProxy(), $slug);
        } else {
            $meal = $mealManager->getOneForChainStore($this->get('board.helper')->getProxy(), $slug);
        }
        $this->get('clab_board.restaurant_manager')->deleteMeals($meal);
        $mealManager->remove($meal);

        return $this->redirectToRoute('board_meal_library', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function slotsAction(Request $request, $context, $contextPk, $slug)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $mealManager = $this->get('app_restaurant.meal_manager');

        if ($context == 'restaurant') {
            $meal = $mealManager->getOneForRestaurant($this->get('board.helper')->getProxy(), $slug);
            $slots = $mealManager->getSlotForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $meal = $mealManager->getOneForChainStore($this->get('board.helper')->getProxy(), $slug);
            $slots = $mealManager->getSlotForChainStore($this->get('board.helper')->getProxy());
        }

        $form = $this->createFormBuilder()
            ->add('slots', 'entity', array(
                'class' => 'ClabRestaurantBundle:MealSlot',
                'choices' => $slots,
                'multiple' => true,
                'expanded' => true,
                'data' => $meal->getSlots(),
            ))
        ->getForm();

        $currentSlots = array();
        foreach ($meal->getSlots() as $slot) {
            $currentSlots[$slot->getId()] = $slot;
        }

        if ($form->handleRequest($request)->isValid()) {
            foreach ($form->get('slots')->getData() as $slot) {
                if (!$slot->getMeals()->contains($meal)) {
                    $mealManager->addSlotToMeal($meal, $slot);
                }

                if (in_array($slot->getId(), array_keys($currentSlots))) {
                    unset($currentSlots[$slot->getId()]);
                }
            }

            foreach ($currentSlots as $slot) {
                $mealManager->removeSlotFromMeal($meal, $slot);
            }

            $em->flush();

            return $this->redirectToRoute('board_meal_library', array('context' => $context, 'contextPk' => $contextPk, 'meal' => $meal->getSlug()));
        }

        return $this->render('ClabBoardBundle:Meal:slots.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'meal' => $meal,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function slotLibraryAction(Request $request, $context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $slots = $this->get('app_restaurant.meal_manager')->getSlotForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $slots = $this->get('app_restaurant.meal_manager')->getSlotForChainStore($this->get('board.helper')->getProxy());
        }

        if ($request->get('slot')) {
            $slot = $request->get('slot');
        }

        return $this->render('ClabBoardBundle:Meal:slotLibrary.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'slots' => $slots,
            'slot' => isset($slot) ? $slot : null,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function slotLibraryListAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $slots = $this->get('app_restaurant.meal_manager')->getSlotForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $slots = $this->get('app_restaurant.meal_manager')->getSlotForChainStore($this->get('board.helper')->getProxy());
        }

        return $this->render('ClabBoardBundle:Meal:slotLibraryList.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'slots' => $slots,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function slotEditAction($context, $contextPk, $slug, Request $request)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $mealManager = $this->get('app_restaurant.meal_manager');

        if ($slug) {
            if ($context == 'restaurant') {
                $slot = $mealManager->getOneSlotForRestaurant($this->get('board.helper')->getProxy(), $slug);
            } else {
                $slot = $mealManager->getOneSlotForChainStore($this->get('board.helper')->getProxy(), $slug);
            }
        } else {
            if ($context == 'restaurant') {
                $slot = $mealManager->createMealSlotForRestaurant($this->get('board.helper')->getProxy());
            } else {
                $slot = $mealManager->createMealSlotForChainStore($this->get('board.helper')->getProxy());
            }
        }

        $form = $this->createForm(new MealSlotType(), $slot);

        if ($request->isMethod('POST')) {
            $form->bind($this->getRequest());

            if ($form->isValid()) {
                $em->persist($slot);

                $disabledProducts = array();
                $customPrices = array();
                foreach ($slot->getProductCategories() as $productCategory) {
                    foreach ($productCategory->getProducts() as $product) {
                        if (!$form->get('product_'.$product->getId().'_disabled')->getData()) {
                            $disabledProducts[] = $product;
                        }

                        if ($customPrice = $form->get('product_'.$product->getId().'_price')->getData()) {
                            $customPrices[] = array('product' => $product, 'price' => $customPrice);
                        }
                    }
                }

                $mealManager->setDisabledProductsToSlot($slot, $disabledProducts);
                $mealManager->setCustomPricesToSlot($slot, $customPrices);

                $em->flush();

                if ($context == 'client') {
                    foreach($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                        $this->get('clab_board.restaurant_manager')->synchroOneSlot($restaurant ,$slot ,$customPrices);
                    }
                }

                $this->get('session')->getFlashBag()->add('formSuccess', 'L\'étape a bien été sauvegardée');

                return $this->redirectToRoute('board_meal_slot_edit', array('context' => $context, 'contextPk' => $contextPk, 'slug' => $slot->getSlug()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        return $this->render('ClabBoardBundle:Meal:slotEdit.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'slot' => $slot,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function slotDeleteAction($context, $contextPk, $slug)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $mealManager = $this->get('app_restaurant.meal_manager');

        if ($context == 'restaurant') {
            $slot = $mealManager->getOneSlotForRestaurant($this->get('board.helper')->getProxy(), $slug);
        } else {
            $slot = $mealManager->getOneSlotForChainStore($this->get('board.helper')->getProxy(), $slug);
        }
        $this->get('clab_board.restaurant_manager')->deleteSlots($slot);
        $mealManager->removeMealSlot($slot);

        return $this->redirectToRoute('board_meal_slot_library', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function slotCategoriesAction(Request $request, $context, $contextPk, $slug)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $mealManager = $this->get('app_restaurant.meal_manager');
        $categoryManager = $this->get('app_restaurant.product_category_manager');

        if ($context == 'restaurant') {
            $slot = $mealManager->getOneSlotForRestaurant($this->get('board.helper')->getProxy(), $slug);
            $categories = $categoryManager->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $slot = $mealManager->getOneSlotForChainStore($this->get('board.helper')->getProxy(), $slug);
            $categories = $categoryManager->getForChainStore($this->get('board.helper')->getProxy());
        }

        $form = $this->createForm(new MealSlotCategoriesType(array('categories' => $categories)), $slot);

        if ($form->handleRequest($request)->isValid()) {
            $em->flush();
            if ($context == 'client') {
                foreach($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                    $this->get('clab_board.restaurant_manager')->synchroOneSlot($restaurant ,$slot);
                }
            }
            return $this->redirectToRoute('board_meal_slot_library', array('context' => $context, 'contextPk' => $contextPk));
        }

        return $this->render('ClabBoardBundle:Meal:slotCategories.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'slot' => $slot,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function slotAssignAction(Request $request, $context, $contextPk, $slug)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $mealManager = $this->get('app_restaurant.meal_manager');

        if ($context == 'restaurant') {
            $slot = $mealManager->getOneSlotForRestaurant($this->get('board.helper')->getProxy(), $slug);
            $meals = $mealManager->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $slot = $mealManager->getOneSlotForChainStore($this->get('board.helper')->getProxy(), $slug);
            $meals = $mealManager->getForChainStore($this->get('board.helper')->getProxy());
        }

        $form = $this->createFormBuilder()
            ->add('meals', 'entity', array(
                'class' => 'ClabRestaurantBundle:Meal',
                'choices' => $meals,
                'multiple' => true,
                'expanded' => true,
                'data' => clone($slot->getMeals()), // clone to avoid direct update in entity, use of manager instead
            ))
        ->getForm();

        $currentMeals = array();
        foreach ($slot->getMeals() as $meal) {
            $currentMeals[$meal->getId()] = $meal;
        }

        if ($form->handleRequest($request)->isValid()) {
            foreach ($form->get('meals')->getData() as $meal) {
                if (!$slot->getMeals()->contains($meal)) {
                    $mealManager->addSlotToMeal($meal, $slot);
                }

                if (in_array($meal->getId(), array_keys($currentMeals))) {
                    unset($currentMeals[$meal->getId()]);
                }

            }

            foreach ($currentMeals as $meal) {
                $mealManager->removeSlotFromMeal($meal, $slot);
            }

            $em->flush();
            if ($context == 'client') {
                foreach($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                    $this->get('clab_board.restaurant_manager')->synchroOneSlot($restaurant ,$slot);
                }
                $em->flush();
            }

            return $this->redirectToRoute('board_meal_slot_library', array('context' => $context, 'contextPk' => $contextPk));
        }

        return $this->render('ClabBoardBundle:Meal:slotAssign.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'slot' => $slot,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function reorderAction($context, $contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $mealManager = $this->get('app_restaurant.meal_manager');
        $this->get('board.helper')->initContext($context, $contextPk);

        $sort = $request->get('sort');
        if ($request->isMethod('POST') && $sort) {
            if ($context == 'restaurant') {
                $meals = $mealManager->getForRestaurant($this->get('board.helper')->getProxy());
            } else {
                $meals = $mealManager->getForChainStore($this->get('board.helper')->getProxy());
            }

            if($context == 'client') {
                set_time_limit(0);
                $client = $this->get('board.helper')->getProxy();
                foreach ($meals as $meal) {
                    if (in_array($meal->getId(), $sort)) {
                        $meal->setPosition(array_search($meal->getId(), $sort));
                    } else {
                        $meal->setPosition(9999);
                    }
                    $em->flush();
                    $this->get('clab_board.restaurant_manager')->synchroOneMealPosition($client,$meal);
                }

            }else {
                foreach ($meals as $meal) {
                    if (in_array($meal->getId(), $sort)) {
                        $meal->setPosition(array_search($meal->getId(), $sort));

                    } else {
                        $meal->setPosition(9999);
                    }
                    $em->flush();
                }

            }
        }

        if ($context == 'restaurant') {
            $meals = $this->get('app_restaurant.meal_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        } else {
            $meals = $this->get('app_restaurant.meal_manager')->getForChainStore($this->get('board.helper')->getProxy());
        }
        $types = array();
        foreach($meals as $meal) {
            $types[$meal->getName()] = $meal->getName();
        }

        return $this->render('ClabBoardBundle:Meal:libraryList.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'meals' => $meals,
            'types' => array_unique($types),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function assignRestaurantAction($context, $contextPk, $slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $meal = $em->getRepository('ClabRestaurantBundle:Meal')->findOneBy(array('slug' => $slug));

        $restaurants = $em->getRepository('ClabRestaurantBundle:Restaurant')->findBy(array('client' => $this->get('board.helper')->getProxy()));

        $restaurants_data = array();
        $meal_data = array();

        $meals = $em->getRepository('ClabRestaurantBundle:Meal')->findBy(array('parent' => $meal));

        foreach ($meals as $children) {
            if ($children->isOnline()) {
                $restaurants_data[] = $children->getRestaurant();
            }
            $meal_data[$children->getRestaurant()->getSlug()] = $children;
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

                        $childMenuClassic = $em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('restaurant' =>
                            $restaurant, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT));
                        $childMenuDelivery = $em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('restaurant' => $restaurant, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY));

                        $child=$this->get('clab_board.restaurant_manager')->synchroOneMealChild($restaurant, $childMenuClassic,$childMenuDelivery,$meal);

                        if (in_array($restaurant, $checkedRestaurants)) {
                            $child->setIsOnline(true);
                        }else {
                            $child->setIsOnline(false);
                        }

                        $child->setPosition($meal->getPosition());
                        $child->setPrice($price);
                        $em->persist($child);
                        $em->flush();

                    } else {
                        $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');

                        return $this->redirectToRoute('board_meal_edit',
                            array('context' => $context, 'contextPk' => $contextPk, 'slug' => $meal->getSlug()));
                    }
                }
                $this->get('session')->getFlashBag()->add('formSuccess',
                    'Les restaurants associés ont bien été sauvegardés');

                return $this->redirectToRoute('board_meal_edit',
                    array('context' => $context, 'contextPk' => $contextPk, 'slug' => $meal->getSlug()));
            }
        }

        return $this->render('ClabBoardBundle:Meal:assignRestaurants.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'meal' => $meal,
            'form' => $form->createView(),
            'meals' => $meal_data,
        )));
    }

}
