<?php

namespace Clab\WhiteBundle\Controller;

use Clab\BoardBundle\Entity\AdditionalSale;
use Clab\BoardBundle\Entity\AdditionalSaleProduct;
use Clab\ShopBundle\Entity\Cart;
use Clab\ShopBundle\Entity\CartElement;
use Clab\ShopBundle\Entity\Loyalty;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\OptionChoice;

class CartController extends Controller
{

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function renderAction(Restaurant $restaurant)
    {
        $cart = $this->get('app_shop.cart_manager')->getCart($restaurant);

        $area = null;
        $session = $this->get('session');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
            'cart' => $cart,
            'area' => $area,
            'restaurant' => $restaurant
        ));
    }

    /**
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"repository_method" = "findOneAvailable"})
     */
    public function addProductToCartAction(Request $request, Product $product)
    {
        $cart = $this->get('app_shop.cart_manager')->addProductToCart($product);

        $area = null;
        $session = $this->get('session');
        $noInteraction = $request->get('noInteraction');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('clab_white_order_home', array(
                'slug' => $cart->getRestaurant()->getSlug(),
                'cart' => $cart,
                'area' => $area
            ));
        } else {
            return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
                'cart' => $cart,
                'restaurant' => $cart->getRestaurant(),
                'additionalSale' => $product->getAdditionalSale(),
                'area' => $area,
                'noInteraction' => $noInteraction,
            ));
        }
    }

    /**
     * @ParamConverter("additionalSale", class="ClabBoardBundle:AdditionalSale", options={"repository_method" = "findOneAvailable"})
     */
    public function addAdditionalSaleToCartAction(Request $request, AdditionalSale $additionalSale)
    {
        $form = $this->get('clab_board.additional_sale_manager')->getAdditionalSaleForm($additionalSale);

        $cart = $this->get('app_shop.cart_manager')->getCart($additionalSale->getRestaurant());

        $area = null;
        $session = $this->get('session');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        $parent = (!is_null($additionalSale->getProduct())) ? $additionalSale->getProduct() : $additionalSale->getMeal();

        $parentElement = new CartElement();

        foreach ($cart->getElements() as $element) {
            if ($element->getProxy()->getSlug() == $parent->getSlug()) {
                $parentElement = $element;
                break;
            }
        }

        if ($request->isMethod('POST')) {
            if ($form->handleRequest($request)->isValid()) {
                $choices = array();

                foreach ($form->getData() as $fields) {
                    foreach ($fields as $field) {
                        if ($field instanceof AdditionalSaleProduct) {
                            $choices[] = $field;
                        }
                    }
                    if ($fields instanceof AdditionalSaleProduct) {
                        $choices[] = $fields;
                    }
                }

                foreach ($choices as $additionalSaleProduct) {
                    $element = new CartElement();
                    $element->setProduct($additionalSaleProduct->getProduct());
                    $element->setQuantity(1);
                    $element->setTax($additionalSaleProduct->getProduct()->getTax());
                    $element->setAdditionalSaleProduct($additionalSaleProduct);
                    $element->updatePrice();
                    $parentElement->addAddSaleProductCartElement($element);
                    $cart->addElement($element);
                }
                $this->get('app_shop.cart_manager')->updateCart($cart);
            }
        }

        return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
            'cart' => $cart,
            'area' => $area,
            'restaurant' => $cart->getRestaurant(),
        ));
    }

    /**
     * @ParamConverter("additionalSale", class="ClabBoardBundle:AdditionalSale", options={"repository_method" = "findOneAvailable"})
     */
    public function addAdditionalSaleWithOptionToCartAction(Request $request, AdditionalSale $additionalSale, $slugProd = null)
    {
        $product = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Product')->findOneBy(array('slug' => $slugProd));

        $form = $this->get('clab_board.additional_sale_manager')->getAdditionalSaleForm($additionalSale);
        $formChoice = $this->get('app_restaurant.product_option_manager')->getOptionFormForProduct($product);

        $cart = $this->get('app_shop.cart_manager')->getCart($additionalSale->getRestaurant());

        $area = null;
        $session = $this->get('session');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        $parent = (!is_null($additionalSale->getProduct())) ? $additionalSale->getProduct() : $additionalSale->getMeal();

        $parentElement = new CartElement();

        foreach ($cart->getElements() as $element) {
            if ($element->getProxy()->getSlug() == $parent->getSlug()) {
                $parentElement = $element;
            }
        }

        $choices_option = array();
        if ($request->isMethod('POST')) {
            if ($form->handleRequest($request)->isValid() && $formChoice->handleRequest($request)->isValid()) {
                $choices = array();

                foreach ($form->getData() as $field) {
                    if ($field instanceof AdditionalSaleProduct) {
                        $choices[] = $field;
                    }
                    foreach ($field as $fields) {
                        if ($fields instanceof AdditionalSaleProduct) {
                            $choices[] = $fields;
                        }
                    }
                }

                foreach ($formChoice->getData() as $choiceFields) {
                    if ($choiceFields instanceof OptionChoice) {
                        $choicesOption[] = $choiceFields;
                    } elseif (count($choiceFields) > 0) {
                        foreach ($choiceFields as $choice) {
                            $option = $this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductOption')->find($choiceFields[0]->getOption()->getId());
                            if (count($choiceFields) < $option->getMinimum() || count($choiceFields) > $option->getMaximum()) {
                                if (!$request->isXmlHttpRequest()) {
                                    $this->addFlash('notice',
                                        'Vous devez choisir un nombre de choix d\'options compris entre'.$option->getMinimum().' et '.$option->getMaximum());

                                    return $this->redirectToRoute('clickeat_order_home',
                                        array('slug' => $product->getRestaurant()->getSlug()));
                                }
                            }
                            $choicesOption[] = $choice;
                        }
                    }
                }

                foreach ($choices as $additionalSaleProduct) {
                    $element = new CartElement();
                    $element->setProduct($additionalSaleProduct->getProduct());
                    $element->setQuantity(1);
                    $element->setTax($additionalSaleProduct->getProduct()->getTax());
                    $element->setAdditionalSaleProduct($additionalSaleProduct);

                    if (count($additionalSaleProduct->getProduct()->getOptions()) > 0) {
                        foreach ($choicesOption as $choice) {
                            $element->addChoice($choice);
                        }
                    }
                    $element->updatePrice();
                    $parentElement->addAddSaleProductCartElement($element);
                    $cart->addElement($element);
                }

                $this->get('app_shop.cart_manager')->updateCart($cart);
            }
        }

        return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
            'cart' => $cart,
            'area' => $area,
            'restaurant' => $cart->getRestaurant(),
        ));
    }

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function updateQuantityAction(Request $request, Restaurant $restaurant, $hash, $quantity)
    {
        $cart = $this->get('app_shop.cart_manager')->updateQuantityByNumber($restaurant, $hash, $quantity);

        $area = null;
        $session = $this->get('session');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('clab_white_order_home', array('slug' => $cart->getRestaurant()->getSlug()));
        } else {
            return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
                'cart' => $cart,
                'restaurant' => $restaurant,
                'area' => $area
            ));
        }
    }

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function updateSaucesAction(Request $request, Restaurant $restaurant)
    {
        if($restaurant) {
            $cartmanager = $this->get('app_shop.cart_manager');
            $cart = $cartmanager->getCart($restaurant);

            $area = null;
            $session = $this->get('session');

            if( $session->get('areaDelivery')) {
                $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                    'slug' => $session->get('areaDelivery'),
                ));
            }

            $form = $this->get('app_shop.order_manager')->getSauceForm($cart);

            $form->submit($request);

            if ($form->isValid()) {

                $cartmanager->updateSauces($cart);

                return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
                    'cart' => $cart,
                    'area' => $area,
                    'restaurant' => $restaurant,
                    'noInteraction' => true
                ));
            }
        }

        return new Response('Erreur', 400);
    }

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function updateWoodStickAction(Request $request, Restaurant $restaurant)
    {
        if($restaurant) {
            $cartmanager = $this->get('app_shop.cart_manager');
            $cart = $cartmanager->getCart($restaurant);

            $area = null;
            $session = $this->get('session');

            if( $session->get('areaDelivery')) {
                $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                    'slug' => $session->get('areaDelivery'),
                ));
            }

            $form = $this->get('app_shop.order_manager')->getWoodStickForm($cart);

            $form->submit($request);

            if ($form->isValid()) {

                $cartmanager->updateWoodSticks($cart);

                return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
                    'cart' => $cart,
                    'area' => $area,
                    'restaurant' => $restaurant,
                    'noInteraction' => true
                ));
            }
        }

        return new Response('Erreur', 400);
    }

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function emptyAction(Request $request, Restaurant $restaurant)
    {
        $this->get('app_shop.cart_manager')->emptyCart($restaurant);

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('clab_white_order_home', array('slug' => $restaurant->getSlug()));
        } else {
            $cart = $this->get('app_shop.cart_manager')->getCart($restaurant);

            $area = null;
            $session = $this->get('session');

            if( $session->get('areaDelivery')) {
                $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                    'slug' => $session->get('areaDelivery'),
                ));
            }

            return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
                'cart' => $cart,
                'area' => $area,
                'restaurant' => $restaurant,
            ));
        }
    }

    /**
     * @ParamConverter("product", class="ClabRestaurantBundle:Product", options={"repository_method" = "findOneAvailable"})
     */
    public function productOptionFormAction(Request $request, Product $product, $token = null, $slot = null)
    {
        $form = $this->get('app_restaurant.product_option_manager')->getOptionFormForProduct($product, $token);
        if ($form->handleRequest($request)->isValid()) {
            $choices = array();
            foreach ($form->getData() as $field) {
                if ($field instanceof OptionChoice) {
                    $choices[] = $field;
                } else {
                    foreach ($field as $choice) {
                        $choices[] = $choice;
                    }
                }
            }

            if ($token) {
                $mealManager = $this->get('app_restaurant.meal_manager');
                $mealData = $mealManager->unserialize($this->get('session')->get($token));
                $restaurantMenu = $this->get('clab.restaurant_menu_manager')->getDefaultMenuForRestaurant($mealData['meal']->getRestaurant());

                foreach ($mealData['slots'] as $key => $mealSlot) {
                    if ($mealSlot['slot']->getId() == $slot) {
                        $availableProducts = $mealManager->getProductsForMealSlotAndMenu($mealSlot['slot'], $restaurantMenu);

                        foreach ($availableProducts as $availableProduct) {
                            if ($availableProduct['product'] == $product) {
                                $mealData['slots'][$key]['product'] = $availableProduct['product'];
                                $mealData['slots'][$key]['price'] = $availableProduct['price'];
                                $mealData['slots'][$key]['options'] = $choices;

                                $serial = $mealManager->serialize($mealData);

                                $this->get('session')->set($token, $serial);

                                return $this->redirectToRoute('clab_white_meal_compose', array('token' => $token));
                            }
                        }
                    }
                }

                if (!$request->isXmlHttpRequest()) {
                    return $this->redirectToRoute('clab_white_meal_compose', array('token' => $token));
                } else {
                    $cart = $this->get('app_shop.cart_manager')->getCart($product->getRestaurant());

                    return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
                        'cart' => $cart,
                        'restaurant' => $product->getRestaurant(),
                        'additionalSale' => $product->getAdditionalSale(),
                    ));
                }
            }

            $cart = $this->get('app_shop.cart_manager')->addProductToCart($product, $choices);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirectToRoute('clab_white_order_home', array('slug' => $cart->getRestaurant()->getSlug()));
            } else {
                $cart = $this->get('app_shop.cart_manager')->getCart($product->getRestaurant());

                $area = null;
                $session = $this->get('session');

                if( $session->get('areaDelivery')) {
                    $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                        'slug' => $session->get('areaDelivery'),
                    ));
                }

                return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
                    'cart' => $cart,
                    'area' => $area,
                    'restaurant' => $product->getRestaurant(),
                    'additionalSale' => $product->getAdditionalSale(),
                ));
            }
        }

        $params = array(
            'product' => $product,
            'form' => $form->createView(),
            'token' => $token,
            'slot' => $slot,
        );

        if (in_array($product->getId(), array(10436, 10464))) {
            $params['subway'] = true;
        }

        return $this->render('ClabWhiteBundle:Order:productOptionForm.html.twig', $params);
    }

    /**
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal", options={"repository_method" = "findOneAvailable"})
     */
    public function mealAction(Meal $meal)
    {
        $data = array();
        foreach ($meal->getSlots() as $slot) {
            $data[] = array('slot' => $slot->getId(), 'product' => null, 'options' => array(), 'price' => 0);
        }
        $data = array('meal' => $meal->getId(), 'slots' => $data);
        $serial = serialize($data);

        $token = sha1($meal->getId().time());

        $this->get('session')->set($token, $serial);

        return $this->redirectToRoute('clab_white_meal_compose', array('token' => $token));
    }

    public function mealComposeAction(Request $request, $token, $slot = null, $choice = null)
    {
        $mealManager = $this->get('app_restaurant.meal_manager');
        $mealData = $mealManager->unserialize($this->get('session')->get($token));
        $restaurant = $mealData['meal']->getRestaurant();
        $restaurantMenu = $this->get('clab.restaurant_menu_manager')->getDefaultMenuForRestaurant($mealData['meal']->getRestaurant());
        $menu = $this->get('clab.restaurant_menu_manager')->getDefaultMenuForRestaurant($restaurant);
        $categories = $this->get('app_restaurant.product_category_manager')->getAvailableForRestaurant($restaurant);
        $meals = $this->get('app_restaurant.meal_manager')->getAvailableForRestaurantMenu($menu);
        $cart = $this->get('app_shop.cart_manager')->getCart($restaurant);
        $planning = $this->get('app_restaurant.timesheet_manager')->getWeekDayPlanning($restaurant);
        $isOpen = $this->get('app_restaurant.timesheet_manager')->isRestaurantOpenForOrder($restaurant);
        $types = array();
        $meal = $this->getDoctrine()->getRepository(Meal::class)->find($mealData['meal']);

        foreach ($categories as $category) {
            $types[] = $category->getType();
        }
        foreach ($mealData['slots'] as $key => $mealSlot) {
            if ((!$slot && !$mealSlot['product']) || $mealSlot['slot']->getId() == $slot) {
                $availableProducts = $mealManager->getProductsForMealSlotAndMenu($mealSlot['slot'], $restaurantMenu);

                if ($choice) {
                    foreach ($availableProducts as $availableProduct) {
                        if ($availableProduct['product']->getId() == $choice) {
                            $mealData['slots'][$key]['product'] = $availableProduct['product'];
                            $mealData['slots'][$key]['price'] = $availableProduct['price'];

                            $serial = $mealManager->serialize($mealData);

                            $this->get('session')->set($token, $serial);

                            return $this->redirectToRoute('clab_white_meal_compose', array('token' => $token));
                        }
                    }
                }

                // fetch options
                $products = array();
                foreach ($availableProducts as $product) {
                    $products[] = $product['product'];
                }

                $options = $this->get('app_restaurant.product_option_manager')->getAvailableForProducts($products);

                return $this->render('ClabWhiteBundle:Order:mealCompose.html.twig', array(
                    'meal' => $mealData,
                    'currentMeal' => $meal,
                    'slot' => $mealSlot['slot'],
                    'productChoices' => $availableProducts,
                    'options' => $options,
                    'currentChoice' => $mealSlot['product'],
                    'restaurant' => $restaurant,
                    'categories' => $categories,
                    'products' => $products,
                    'meals' => $meals,
                    'cart' => $cart,
                    'planning' => $planning,
                    'isOpen' => $isOpen,
                    'types' => array_unique($types),
                    'token' => $token,
                ));
            }
        }

        $this->get('app_shop.cart_manager')->addMealToCart($mealData['meal'], $mealData['slots']);

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('clab_white_meal', array('slug' => $mealData['meal']->getSlug()));
        } else {
            $cartManager = $this->get('app_shop.cart_manager');
            $cart = $cartManager->getCart($restaurant);

            $area = null;
            $session = $this->get('session');

            if( $session->get('areaDelivery')) {
                $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                    'slug' => $session->get('areaDelivery'),
                ));
            }

            if ($cartManager->countMeals($cart) == 1) {
                $session->getFlashBag()->add('meal-alert', 'les formules sont exclusivement réservées aux commandes passées pour midi.');
            }

            return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
            'cart' => $cart,
            'area' => $area,
            'restaurant' => $restaurant,
            'additionalSale' => $mealData['meal']->getAdditionalSale(),
            'isMenu' => true,
          ));
        }
    }

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function couponAction(Request $request, Restaurant $restaurant)
    {
        $cart = $this->get('app_shop.cart_manager')->getCart($restaurant);

        $area = null;
        $session = $this->get('session');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        $em = $this->getDoctrine()->getManager();

        $code = $request->get('code');
        $coupon = $this->getDoctrine()->getManager()->getRepository('ClabShopBundle:Coupon')->findOneBy(array('name' => $code));
        if (!$coupon) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Coupon non valide',
            ]);
        }

        $availability = $coupon->isAvailableForCart($cart);
        $availabilityUser = $coupon->isAvailableForUser($this->getUser());

        if ($availability && $availabilityUser) {
            if (!$coupon->getUsedBy()->contains($this->getUser())) {
                $coupon->addUsedBy($this->getUser());
            }
            $cart->setCoupon($coupon);
            $em->flush();
        } elseif ($availability === false) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Coupon non disponible',
            ]);
        } else {
            return new JsonResponse([
                'success' => false,
                'message' => 'Vous avez déjà utilisé ce coupon',
            ]);
        }
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('clab_white_order_home', array('slug' => $cart->getRestaurant()->getSlug()));
        } else {
            return new JsonResponse([
                'success' => true,
                'cart' => $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
                    'cart' => $cart,
                    'area' => $area,
                    'restaurant' => $cart->getRestaurant(),
                    'noInteraction' => true
                ))->getContent(),
                'couponname' => $coupon->getName(),
                'couponverbose' => $coupon->verbose()
            ]);
        }
    }

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function couponRemoveAction(Request $request, Restaurant $restaurant)
    {
        $cart = $this->get('app_shop.cart_manager')->getCart($restaurant);

        $area = null;
        $session = $this->get('session');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        $em = $this->getDoctrine()->getManager();

        if ($cart && $cart->getCoupon()) {
            $coupon = $cart->getCoupon();
            $coupon->removeUsedBy($this->getUser());
            $cart->setCoupon(null);
            $em->flush();
        }

        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('clab_white_order_home', array('slug' => $cart->getRestaurant()->getSlug()));
        } else {
            return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
                'cart' => $cart,
                'area' => $area,
                'restaurant' => $cart->getRestaurant(),
            ));
        }
    }

    public function deliveryConditionsAction($slug)
    {
        $restaurant = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $slug));
        $deliveryCarts = $this->get('clab_delivery.delivery_manager')->getDeliveryCarts($restaurant);

        return $this->render('ClabWhiteBundle:Cart:deliveryConditions.html.twig', array(
            'deliveryCarts' => $deliveryCarts,
            'restaurant' => $restaurant,
        ));
    }

    /**
     * @ParamConverter("product", class="ClabRestaurantBundle:Product")
     */
    public function removeProductFromCartAction(Product $product)
    {
        $restaurant = $product->getRestaurant();
        $cart = $this->get('app_shop.cart_manager')->removeProductFromCart($product);

        $area = null;
        $session = $this->get('session');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
            'cart' => $cart,
            'area' => $area,
            'restaurant' => $restaurant,
        ));
    }

    /**
     * @ParamConverter("meal", class="ClabRestaurantBundle:Meal")
     */
    public function removeMealFromCartAction(Meal $meal)
    {
        $restaurant = $meal->getRestaurant();
        $cart = $this->get('app_shop.cart_manager')->removeMealFromCart($meal);

        $area = null;
        $session = $this->get('session');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
            'cart' => $cart,
            'area' => $area,
            'restaurant' => $restaurant,
        ));
    }

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function removeElementFromCartAction(Request $request, $elementHash, Restaurant $restaurant)
    {
        $cart = $this->get('app_shop.cart_manager')->removeElementFromCart($elementHash, $restaurant);

        $area = null;
        $session = $this->get('session');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
            'cart' => $cart,
            'area' => $area,
            'restaurant' => $restaurant,
        ));
    }

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function updateLoyaltiesAction(Request $request, $loyaltyId, Restaurant $restaurant) {

        $loyalty = $this->getDoctrine()->getRepository(Loyalty::class)->find($loyaltyId);

        $cart = $this->get('app_shop.cart_manager')->getCart($restaurant);

        $area = null;
        $session = $this->get('session');

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        if (!$loyalty->getIsUsed() && (!$loyalty->getOrderType()
            || $loyalty->getOrderType() == $cart->getOrderType())
            && $loyalty->getValidUntil() >= new \DateTime()) {

            $hasLoyalty = false;

            $loyalties = $cart->getLoyalties();

            foreach ($loyalties as $key => $l) {
                if($l->getId() == $loyaltyId) {
                    unset($loyalties[$key]);
                    $hasLoyalty = true;
                }
            }
            $hasLoyalty
                ?
                : $cart->addLoyalty($loyalty)
            ;

            $this->get('app_shop.cart_manager')->updateCart($cart);
        }

        return $this->render('ClabWhiteBundle:Cart:cart.html.twig', array(
            'cart' => $cart,
            'area' => $area,
            'restaurant' => $restaurant,
            'noInteraction' => true
        ));
    }
}
