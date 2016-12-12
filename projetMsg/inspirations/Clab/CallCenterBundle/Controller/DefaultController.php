<?php

namespace Clab\CallCenterBundle\Controller;

use Clab\DeliveryBundle\Entity\DeliveryDay;
use Clab\LocationBundle\Entity\Address;
use Clab\ShopBundle\Entity\OrderType;
use Clab\CallCenterBundle\Form\Type\User\RegisterType;
use Clab\UserBundle\Entity\User;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Clab\ShopBundle\Entity\Cart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\Timesheet;
use Clab\BoardBundle\Exception\SubscriptionException;
use Clab\BoardBundle\Form\Type\Onboard\CustomInfosType;
use Clab\BoardBundle\Form\Type\Onboard\CustomCatalogType;
use Clab\BoardBundle\Form\Type\Onboard\CustomPlanningType;
use Clab\BoardBundle\Form\Type\Onboard\CustomMobilePlanningType;
use Clab\BoardBundle\Form\Type\Onboard\CustomOrderType;
use Clab\BoardBundle\Form\Type\Onboard\CustomLegalType;
use Clab\BoardBundle\Form\Type\Onboard\CustomSubscriptionType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\VarDumper\VarDumper;

class DefaultController extends Controller
{
    /**
     * @Secure(roles="ROLE_CALL_CENTER")
     */
    public function clientAction(Request $request ,$page = 0, $error = false)
    {
        $nbPages = $this->getDoctrine()->getRepository('ClabUserBundle:User')->countUsers('ROLE_MEMBER');
        $nbPages = round($nbPages[1] / 250)-1;
        $users = $this->getDoctrine()->getRepository('ClabUserBundle:User')->findByRole('ROLE_MEMBER', null, 250, $page * 250);

        $user = new User();
        $form = $this->createForm(new RegisterType(), $user);



        if ($form->handleRequest($request)->isValid()) {
            $userManager = $this->container->get('fos_user.user_manager');

            $user->setEnabled(true);
            $user->addRole('ROLE_MEMBER');
            if (!$form->get('email')->getData()) {
                $user->setEmail(sprintf("%s@call.matsuri.fr",$user->getPhone()));
            } else {
                $user->setEmail($form->get('email')->getData());
            }

            if (!$form->get('plainPassword')->getData()) {
                $user->setPlainPassword(md5($user->getEmail()));
            } else {
                $user->setPlainPassword($form->get('plainPassword')->getData());
            }
            $address = $user->getHomeAddress();
            $address->setUser($user);
            $user->addAddress($address);
            $this->getDoctrine()->getManager()->persist($address);

            try {
                $userManager->updateUser($user);
                $this->getDoctrine()->getManager()->flush();
            }catch(\Exception $e){
                $this->addFlash("notice", "Numéro de téléphone déjà utilisé");
                return $this->redirectToRoute("clab_call_center_homepage", array('page' => $page, 'error' => true));
            }
            //$this->get('clab_core.mail_manager')->registerMail($user);

            $session = $this->get('session');
            $session->set('customer', $user->getId());

            return $this->redirectToRoute('clab_call_center_order_type');
        }

        $apiUser = $this->getParameter('clickeat_user');
        $apiDomain = $this->getParameter('clickeat_domain');
        return $this->render('ClabCallCenterBundle:Default:index.html.twig',array(
            'users' => $users,
            'form'  => $form->createView(),
            'apiUser' => $apiUser,
            'apiDomain' => $apiDomain,
            'page' => $page,
            'nbPages' => $nbPages,
            'error' => $error
        ));
    }

        public function chooseCustomerAction($id)
    {
        $session = $this->get('session');
        $session->set('customer', $id);

        return $this->redirectToRoute('clab_call_center_order_type');
    }

    /**
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function chooseRestaurantAction(Restaurant $restaurant)
    {
        $session = $this->get('session');
        $session->set('restaurant', $restaurant->getSlug());
        if ('delivery' == $session->get('orderType') && $session->get('address')) {
            $address = new Address();
            $address->setStreet($session->get('street') ? $session->get('street') : $session->get('address'));

            if ($session->get('city')) {
                $address->setCity($session->get('city'));
            }

            if ($session->get('zip')) {
                $address->setZip($session->get('zip'));
            }

            $address->setLatitude($session->get('lat'));
            $address->setLongitude($session->get('lng'));

            $session->set($restaurant->getSlug().'_delivery_address', $address);
        }

        return $this->redirectToRoute('clab_call_center_order');
    }


    public function orderAction()
    {
        $session = $this->get('session');
        $time = new \DateTime($session->get('day'));

        $restaurantSlug = $session->get('restaurant');
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
            'slug' => $restaurantSlug
        ));
        if(!$restaurant)
        {
            $this->addFlash('Veuillez choisir une restaurant valide','error');
            return $this->redirectToRoute('clab_call_center_restaurant_list');
        }
        $cart = $this->get('app_shop.cart_manager')->getCart($restaurant);

        $area = null;

        if( $session->get('areaDelivery')) {
            $area = $this->getDoctrine()->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array(
                'slug' => $session->get('areaDelivery'),
            ));
        }

        $discounts = $this->getDoctrine()->getRepository('ClabShopBundle:Discount')->findAllAvailable($restaurant->getId());
        $types = array();
        if ($session->get('orderType') == 'delivery')
        {
            $slots = $this->get('app_shop.order_manager')->getSlotsForCart($cart, array('address' => $session->get($restaurant->getSlug().'_delivery_address')));
            $menu = $this->getDoctrine()->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                'restaurant' => $restaurant,
                'type' => 200,
            ));
            $categories = $this->get('app_restaurant.product_category_manager')->getAvailableForRestaurant($restaurant);
            $products = $this->get('app_restaurant.product_manager')->getAvailableForRestaurantMenu($menu);
            $pdjs = $this->get('app_restaurant.product_manager')->getAllPDJForRestaurantMenu($menu);
            foreach($pdjs as $key => $pdj) {
                if ($pdj->getStartDate() > $time || $pdj->getEndDate() < $time) {
                    unset($pdjs[$key]);
                    continue;
                }
            }
            $options = $this->get('app_restaurant.product_option_manager')->getAvailableForProducts($products);
            $meals = $this->get('app_restaurant.meal_manager')->getAvailableForRestaurantMenu($menu);
        } else {
            $menu = $this->get('clab.restaurant_menu_manager')->getDefaultMenuForRestaurant($restaurant);
            $categories = $this->get('app_restaurant.product_category_manager')->getAvailableForRestaurant($restaurant);
            $products = $this->get('app_restaurant.product_manager')->getAvailableForRestaurantMenu($menu);
            $pdjs = $this->get('app_restaurant.product_manager')->getAllPDJForRestaurantMenu($menu);
            foreach($pdjs as $key => $pdj) {
                if ($pdj->getStartDate() > $time || $pdj->getEndDate() < $time) {
                    unset($pdjs[$key]);
                    continue;
                }
            }
            $options = $this->get('app_restaurant.product_option_manager')->getAvailableForProducts($products);
            $meals = $this->get('app_restaurant.meal_manager')->getAvailableForRestaurantMenu($menu);
            $slots = $this->get('app_shop.order_manager')->getSlotsForCart($cart);
        }
        $this->getDoctrine()->getManager()->flush();
        foreach ($categories as $category) {
            $types[] = $category->getName();
        }

        sort($types);

        $customerId = $session->get('customer');
        $customer = $this->getDoctrine()->getRepository(User::class)->find($customerId);

        $params = array(
            'restaurant' => $restaurant,
            'categories' => $categories,
            'products' => $products,
            'pdjs' => $pdjs,
            'options' => $options,
            'meals' => $meals,
            'cart' => $cart,
            'area' => $area,
            'customer' => $customer,
            'types' => array_unique($types),
            'discounts' => $discounts,
            'slots' => $slots,
        );


        return $this->render('ClabCallCenterBundle:Default:order.html.twig', $params);
    }

    public function orderTypeAction(Request $request)
    {
        $session = $this->get('session');
        $session->set('orderType', 'preorder');

        $now = new \DateTime();

        $em = $this->getDoctrine()->getManager();

        $user = $this
                ->getDoctrine()
                ->getRepository(User::class)
                ->find($this->get('session')->get('customer'));

        $address = $user->getHomeAddress() ? $user->getHomeAddress() : $user->getAddresses()[0];
        $form = $this->createFormBuilder()
            ->add('orderType', 'choice', array(
                'choices'  => array(
                    'Commande à emporter' => 'preorder',
                    'Commande en livraison' => 'delivery',
                ),
                'choices_as_values' => true,
                'expanded' => true,
                'multiple' => false,
                'data' => 'preorder'
            ))
            ->add('search', 'text', array(
                'required' => false,
                'label' => 'Recherche',
                'attr' => array('value' => ($address ? sprintf("%s %s %s",$address->getStreet(),$address->getCity(),$address->getZip()) : "")),
                'data' => ($address ? sprintf("%s %s %s",$address->getStreet(),$address->getCity(),$address->getZip()) : "")
            ))
            ->add('street', 'hidden', array(
                'required' => false,
                'label' => ' ',
                'attr' => array('value' => ($address ? $address->getStreet() : "")),
                'data' => ($address ? $address->getStreet() : "")
            ))
            ->add('city', 'hidden', array(
                'required' => false,
                'label' => ' ',
                'attr' => array('value' => ($address ? $address->getCity() : "")),
                'data' => ($address ? $address->getCity() : "")
            ))
            ->add('zip', 'hidden', array(
                'required' => false,
                'label' => ' ',
                'attr' => array('value' => ($address ? $address->getZip() : "")),
                'data' => ($address ? $address->getZip() : "")
            ))
            ->add('day', 'date', array(
                'label' => 'Date de début de validité',
                'required' => true,
                'widget' => 'single_text',
                'input' => 'datetime',
                'format' => 'dd/MM/yyyy',
                'attr' => array('class' => 'date'),
                'data' => $now
            ))
            ->getForm();

        $options = array(
            'status_min'=>Restaurant::STORE_STATUS_ACTIVE,
            'status_max'=>6999
        );

        $timesheetManager = $this->get('app_restaurant.timesheet_manager');

        if (!$request->isXmlHttpRequest()) {
            $restaurants = $this->getDoctrine()->getRepository(Restaurant::class)->findAllFiltered($options);

            $cartManager = $this->get('app_shop.cart_manager');
            foreach ($restaurants as $restaurant) {
                $restaurant->setIsOpen($timesheetManager->isRestaurantOpenForOrder($restaurant, $now));
                $session->remove($cartManager->getSessionNamespace($restaurant));
            }

        } else {
            $form->submit($request->request->all()['form']);

            if ($form->isValid()) {
                $data = $form->getData();

                if (isset($data['search'])) {
                    $session->set('address', $data['search']);
                    $session->set('street', $data['street']);
                    $session->set('city', $data['city']);
                    $session->set('zip', $data['zip']);
                }

                $session->set('day', $data['day']->format('Y-m-d'));
                $now = new \DateTime();

                $time = new \Datetime($data['day']->format('Y-m-d')." ".$now->format('H:i'));

                if ($data['orderType'] == 'preorder') {
                    $session->set('orderType', 'preorder');
                } else {
                    $session->set('orderType', 'delivery');
                }

                $options['type'] = $data['orderType'];

                if (isset($data['search'])) {
                    $coordinates = $this->get('app_location.location_manager')->getCoordinateFromAddress($data['search']);
                    $lat = $coordinates['latitude'];
                    $long = $coordinates['longitude'];

                    $session->set('lat', $lat);
                    $session->set('lng', $long);

                    if ($data['orderType'] == 'preorder') {
                        $restaurants = $this->getDoctrine()->getRepository(Restaurant::class)->findNearbyPaginatedWithTag($lat,
                            $long, null, $options);
                    } else {
                        $deliveryDays = $em->getRepository(DeliveryDay::class)->getAvailableForDay($data['day']);
                        $restaurants = [];
                        $filterResto = [];

                        foreach ($deliveryDays as $deliveryDay) {
                            if($this->get('clab_delivery.delivery_manager')->checkLocationApi($data['search'], $deliveryDay)) {
                                $restaurant = $deliveryDay->getRestaurant();
                                if(! in_array($restaurant, $filterResto)) {
                                    $distance = $this->get('clab_delivery.delivery_manager')->haversine($lat, $long, $restaurant->getAddress()->getLatitude(), $restaurant->getAddress()->getLongitude());
                                    $restaurants[] = array(0=>$restaurant, 'distance' => round($distance/1000,4));

                                    $filterResto[] = $restaurant;
                                }
                            }
                        }
                    }


                    $results = array();

                    foreach ($restaurants as $key => $restaurantData) {
                        $r = $restaurantData[0];
                        $isOpen = false;

                        $planning = $timesheetManager->getDayPlanning($r,$time);

                        foreach ($planning as $event) {
                            if ($event['type'] != 0) {
                                if ( $event['end']->format('H:i') >= $time->format('H:i')) {
                                    $isOpen=true;
                                }
                            }
                        }

                        if (!$isOpen) {
                            unset($restaurants[$key]);
                            continue;
                        }
                        $results[] = array(
                            'id' => $r->getId(),
                            'name' => $r->getName(),
                            'customer' => $user,
                            'address' => sprintf('%s %s %s',$r->getAddress()->getStreet(),$r->getAddress()->getCity(),$r->getAddress()->getZip()),
                            'distance' => round($restaurantData['distance'],2)." km"
                        );

                    }
                } else {
                    $restaurants = $this->getDoctrine()->getRepository(Restaurant::class)->findAllFiltered($options);

                    $results = array();

                    foreach ($restaurants as $key => $r) {
                        $isOpen = false;

                        $planning = $timesheetManager->getDayPlanning($r,$time);

                        foreach ($planning as $event) {
                            if ($event['type'] != 0) {
                                if ($event['end']->format('H:i') >= $time->format('H:i')) {
                                    $isOpen=true;
                                }
                            }
                        }

                        if (!$isOpen) {
                            unset($restaurants[$key]);
                            continue;
                        }
                        $results[] = array(
                            'id' => $r->getId(),
                            'name' => $r->getName(),
                            'customer' => $user,
                            'address' => sprintf('%s %s %s',$r->getAddress()->getStreet(),$r->getAddress()->getCity(),$r->getAddress()->getZip()),
                            'distance' => "-"
                        );
                    }
                }


                return new JsonResponse($results);

            }
            return new JsonResponse('Error',400);
        }

        return $this->render('ClabCallCenterBundle:Default:orderType.html.twig',array(
            'form' => $form->createView(),
            'customer' => $user,
            'restaurants' => $restaurants
        ));
    }

    public function changeOrderTypeAction(Request $request)
    {
        $preorder = boolval($request->get('preorder'));
        $session = $this->get('session');

        if ($preorder) {
            $session->set('orderType', 'preorder');
        } else {
            $session->set('orderType', 'delivery');
        }

        return new Response('success',200);
    }

    /**
     * @Secure(roles="ROLE_CALL_CENTER")
     */
    public function restaurantListAction(Request $request)
    {
        $session = $this->get('session');
        $location = $session->get('address');
        $orderType = $session->get('orderType');
        $time = new \Datetime($session->get('day')." ".$session->get('time'));

        $session->get('time');

        if ($session->get('address') == null) {
            $this->addFlash('Addresse invalide','error');
            $this->redirectToRoute('clab_call_center_order_type');
        }

        $coordinates = $this->get('app_location.location_manager')->getCoordinateFromAddress($location);
        $lat = $coordinates['latitude'];
        $long = $coordinates['longitude'];

        $options = array(
                        'status_min'=>Restaurant::STORE_STATUS_ACTIVE,
                        'status_max'=>6999,
                        'type' => $orderType
                        );

        $restaurants = $this->getDoctrine()->getRepository(Restaurant::class)->findNearbyPaginatedWithTag($lat, $long,null,$options);


        $timesheetManager = $this->get('app_restaurant.timesheet_manager');


        foreach ($restaurants as $key => $restaurantData) {
            $r = $restaurantData[0];
            $isOpen = false;

            $planning = $timesheetManager->getDayPlanning($r,$time);

            foreach ($planning as $event) {
                foreach ($planning as $event) {
                    if ($event['type'] != 0) {
                        if ($event['start']->format('H:i')  <= $time->format('H:i') && $event['end']->format('H:i') >= $time->format('H:i')) {
                            $isOpen=true;
                        }
                    }
                }
            }
            if (!$isOpen) {
                unset($restaurants[$key]);
                continue;
            }
        }

        if(count($restaurants) > 0) {
            return $this->render('ClabCallCenterBundle:Default:restaurantChoice.html.twig',array(
                'restaurants' => $restaurants,
            ));
        } else {
            $this->addFlash('notice','Aucun restaurant trouvé selon vos criteres de recherche');
            return $this->redirectToRoute('clab_call_center_order_type');
        }
    }

    /**
     * @Secure(roles="ROLE_CALL_CENTER")
     */
    public function addAction(Request $request)
    {
        $session = $this->get('session');

            $form = $this->createFormBuilder()
                ->add('firstName', 'text', array(
                    'required' => true,
                    'label' => 'Prénom',
                ))
                ->add('lastName', 'text', array(
                    'required' => true,
                    'label' => 'Nom',
                ))
                ->add('email', 'email', array(
                    'required' => true,
                    'label' => 'pro.users.emailLabel',
                ))
                ->add('plainPassword', 'password', array(
                    'required' => true,
                    'label' => 'Mot de passe',
                ))
                ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData();

                $newUser = $this->get('fos_user.user_manager')->createUser();
                $newUser->setEmail($data['email']);
                $newUser->setUsername($data['email']);
                $newUser->setPlainPassword($data['plainPassword']);
                $this->get('fos_user.user_manager')->updateUser($newUser);
                $this->getDoctrine()->getManager()->flush();
                $user = $this->getDoctrine()->getRepository('ClabUserBundle:User')->findOneBy(array('email' => $data['email']));
                $user->setFirstName($data['firstName']);
                $user->setLastName($data['lastName']);
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success','Manageur bien ajouté pour le restaurant');

            }

        $customerId = $session->get('customer');
        $customer = $this->getDoctrine()->getRepository(User::class)->find($customerId);

        $this->get('board.helper')->addParams(
            array(
                'form', $form->createView(),
                'customer' => $customer
            ));

        return $this->render('ClabBoardBundle:User:add.html.twig', $this->get('board.helper')->getParams());
    }

    public function checkUserExistAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->isMethod('GET')) {
            $email = $request->get('email');
            $phone = $request->get('phone');

            $repo = $this->getDoctrine()->getRepository(User::class);

            $alreadyUsed = true;
            $filter = [];

            if ($email) {
               $filter = ['email' => $email];
            }

            if ($phone) {
               $filter = ['phone' => $phone];
            }
            $alreadyUsed = !is_null($repo->findOneBy($filter));

            return new JsonResponse(['userExist' => $alreadyUsed]);
        }
        return new Response('error',400);
    }
}
