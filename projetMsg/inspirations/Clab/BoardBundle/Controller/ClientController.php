<?php

namespace Clab\BoardBundle\Controller;

use Clab\ApiBundle\Entity\SessionCaisse;
use Clab\BoardBundle\Entity\Company;
use Clab\BoardBundle\Form\Type\Company\CompanyType;
use Clab\MediaBundle\Entity\Image;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\CartElement;
use Clab\ShopBundle\Entity\Coupon;
use Clab\ShopBundle\Entity\Discount;
use Clab\ShopBundle\Entity\Loyalty;
use Clab\ShopBundle\Entity\OrderDetail;
use Clab\ShopBundle\Entity\OrderDetailCaisse;
use Clab\ShopBundle\Entity\Payment;
use JMS\Serializer\SerializationContext;
use Clab\RestaurantBundle\Entity\ProductCategory;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\BoardBundle\Form\Type\Client\ClientSettingsType;
use Clab\BoardBundle\Form\Type\Client\ClientRestaurantType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\VarDumper;

class ClientController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function reportingAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('client', $contextPk);

        if ($start = $request->get('start')) {
            $start = date_create_from_format('d/m/Y H:i:s', urldecode($start).' 00:00:00');
        } else {
            $start = date_create('now');
            $start->modify('-1 week');
        }

        if ($end = $request->get('end')) {
            $end = date_create_from_format('d/m/Y', urldecode($end));
        } else {
            $end = date_create('now');
        }

        $restaurants = array();
        foreach ($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
            $em = $this->getDoctrine()->getManager();
            $orders = $em->getRepository('ClabShopBundle:OrderDetail')->findAllBetweenDate($start, $end, $restaurant, array('closed' => true));
            $total = 0;
            $count = 0;
            foreach ($orders as $order) {
                $total += $order->getPrice();
                ++$count;
            }

            $restaurants[] = array('restaurant' => $restaurant, 'count' => $count, 'total' => $total);
        }

        return $this->render('ClabBoardBundle:Client:reporting.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'restaurants' => $restaurants,
            'start' => $start,
            'end' => $end,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editRestaurantAction($contextPk, $slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('client', $contextPk);

        $restaurant = $em->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $slug, 'client' => $this->get('board.helper')->getProxy()));
        if (!$restaurant) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new ClientRestaurantType(), $restaurant);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('board_client_edit_restaurant', array('contextPk' => $contextPk, 'slug' => $slug));
        }

        return $this->render('ClabBoardBundle:Client:editRestaurant.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'restaurant' => $restaurant,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function settingsAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('client', $contextPk);
        $chainstore = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->findOneBy(array(
            'slug' => $contextPk
        ));
        $this->get('board.helper')->addParam('chainstore', $chainstore);
        $form = $this->createForm(new ClientSettingsType(),  $this->get('board.helper')->getProxy());

        $form->handleRequest($request);

        if ($form->isValid()) {
            foreach ($this->get('board.helper')->getProxy()->getRestaurants() as $restaurant) {
                $restaurant->setLogoMcFile($this->get('board.helper')->getProxy()->getLogoFile());
                $restaurant->setLogoMcName($this->get('board.helper')->getProxy()->getLogoName());
            }
            $em->flush();

            return $this->redirectToRoute('board_client_settings', array('contextPk' => $contextPk));
        }

        return $this->render('ClabBoardBundle:Client:settings.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function socialSettingsAction($contextPk)
    {
        $this->get('board.helper')->initContext('client', $contextPk);
            if ($page = $this->get('board.helper')->getProxy()->getFacebookPage()) {
                $facebookManager = $this->get('clab_board.facebook_manager');

                $fbOrder = $facebookManager->checkPageTab($page, 'iframe');
                $this->get('board.helper')->addParam('fbOrder', $fbOrder);
            }

            return $this->render('ClabBoardBundle:Client:settings-social.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editCompanyAction(Request $request, $contextPk, $companyId)
    {
        $this->get('board.helper')->initContext('client', $contextPk);
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->get('serializer');
        $isEdit = false;

        $companies = $em->getRepository(Company::class)->findAll();

        if (!$companyId) {
            $company = new Company();
        } else {
            $company = $em->getRepository(Company::class)->find($companyId);
            $isEdit = true;
        }

        $form = $this->createForm(new CompanyType($isEdit), $company);

        if ($request->isXmlHttpRequest()) {
            $form->submit($request);
            if ($form->isValid()) {

                if (!$isEdit) {
                    $em->persist($company->getAddress());
                    $em->persist($company);
                }
                $em->flush();

                $serializer = $this->get('serializer');
                $result = $serializer->serialize($company, 'json', SerializationContext::create()->setGroups(array('pro')));
                return new Response($result);
            }

            return new JsonResponse('erreur');
        } else {
          if ($form->handleRequest($request)->isValid()) {
              $em->flush();
              $this->get('session')->getFlashBag()->add('success', "Modifications sur la société ".$company->getName()." prises en compte.");
          }
        }

        $this
            ->get('board.helper')
            ->addParams(array('form' => $form->createView(), 'companies' => $companies));

        return $this->render('ClabBoardBundle:Client:companiesEdit.html.twig', $this->get('board.helper')->getParams());
    }

    public function companyModalAction(Request $request, $contextPk, $companyId){
        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository(Company::class)->find($companyId);

        $this->get('board.helper')->initContext('client', $contextPk);

        if (!$company) {
            $this->get('session')->getFlashBag()->add('error', 'Veuillez indiquer une société valide');

            return $this->redirectToRoute('board_client_company', array('contextPk' => $contextPk));
        }

        $form = $this->createForm(new CompanyType(true), $company);

        if ($form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', "Modifications sur la société ".$company->getName()."prises en compte.");

            return $this->redirectToRoute('board_client_company', array('contextPk' => $contextPk));
        }

        $this
            ->get('board.helper')
            ->addParams(array('form' => $form->createView(), 'company' => $company, 'contextPk' => $contextPk));

        return $this->render('ClabBoardBundle:Client:companyModal.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function chargeCompanyAction(Request $request, $contextPk, $companyId)
    {
        $em = $this->getDoctrine()->getManager();
        $company = $em->getRepository(Company::class)->find($companyId);

        if (!$company) {
            $this->get('session')->getFlashBag()->add('error', 'Veuillez indiquer une société valide');
        } else {
            $this->get('session')->getFlashBag()->add('success', "Paiement de la société: ".$company->getName()." pris en compte.");
            $company->chargeCompany();

            $em->flush();
        }

        return $this->redirectToRoute('board_client_company', array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function suggestionsAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('client', $contextPk);
        $chainstore = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->findOneBy(array(
            'slug' => $contextPk
        ));
        $this->get('board.helper')->addParam('chainstore', $chainstore);

        $categories = $this->get('board.helper')->getProxy()->getProductCategories();

        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $catRatios = $request->get('catRatios');
            $prodRatios = $request->get('prodRatios');

            foreach ($categories as $category) {
                if(isset($catRatios[$category->getId()])) {
                    $category->setSuggestionCategoryRatios($catRatios[$category->getId()]);
                }
                if(isset($prodRatios[$category->getId()])) {
                    $category->setSuggestionProductsRatios($prodRatios[$category->getId()]);
                }
                $em->persist($category);
            }

            $em->flush();

            return new Response('success', 200);
        }

        return $this->render('ClabBoardBundle:Client:settingsSuggestions.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'categories' => $categories
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER")
     */
    public function accountingAction($context, $contextPk, Request $request, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'client') {
            $restaurant = $em->getRepository(Restaurant::class)->findOneBy(['slug' => $slug]);
            $restaurants = $this->get('board.helper')->getProxy()->getRestaurants();
        } else {
            $restaurant = $this->get('board.helper')->getProxy();
            $restaurants = null;
        }

        $date = new \DateTime($request->get('date'));

        $params = [
            'restaurants' => $restaurants,
            'restaurant' => $restaurant ? $restaurant : null,
            'date' => $date
        ];

        if($restaurant) {
            $vatAccounts = $em->getRepository(OrderDetail::class)->getAccountingByVAT($restaurant, $date);
            $vatCaisseAccounts = $em->getRepository(OrderDetailCaisse::class)->getAccountingByVAT($restaurant, $date, true);

            $paymentsAccounts = $em->getRepository(OrderDetail::class)->getAccountingByPaymentType($restaurant, $date);
            $paymentsCaisseAccounts = $em->getRepository(OrderDetailCaisse::class)->getAccountingByPaymentType($restaurant, $date, true);

            $paymentAccounts= [
                'STRIPE' => ['code' => 51140000, 'amount' => 0.],
                'ESP' => ['code' => 51170000, 'amount' => 0.],
                'CAR' => ['code' => 51130000, 'amount' => 0.],
                'TR' => ['code' => 51160000, 'amount' => 0.],
                'CHQ' => ['code' => 51120000, 'amount' => 0.],
                'AME' => ['code' => 51150000, 'amount' => 0.],
                'DEL' => ['code' => 51190000, 'amount' => 0.],
                'YUM' => ['code' => 46731000, 'amount' => 0.],
                'AVOIR' => ['code' => 41910000, 'amount' => 0.]
            ];

            $clientAccounts = [];
            $clients = $em->getRepository(Company::class)->findAll();

            foreach ($clients as $client) {
                $clientAccounts[$client->getAccountCode()] = 0.;
            }

            $salesHTByVAT = [
                'E' => [
                    '5.5' => 0.,
                    '10' => 0.,
                    '20' => 0.,
                    'code55' => 70100210,
                    'code10' => 70100220,
                    'code20' => 70100230
                ],
                'L' => [
                    '5.5' => 0.,
                    '10' => 0.,
                    '20' => 0.,
                    'code55' => 70100310,
                    'code10' => 70100320,
                    'code20' => 70100330
                ],
                'P' => [
                    '5.5' => 0.,
                    '10' => 0.,
                    '20' => 0.,
                    'code55' => 70100110,
                    'code10' => 70100120,
                    'code20' => 70100130
                ]
            ];

            $salesTTCByVAT = [
                1 => [
                    '5.5' => 0.,
                    '10' => 0.,
                    '20' => 0.,
                    'code55' => 70100210,
                    'code10' => 70100220,
                    'code20' => 70100230
                ],
                3 => [
                    '5.5' => 0.,
                    '10' => 0.,
                    '20' => 0.,
                    'code55' => 70100310,
                    'code10' => 70100320,
                    'code20' => 70100330
                ],
                4 => [
                    '5.5' => 0.,
                    '10' => 0.,
                    '20' => 0.,
                    'code55' => 70100110,
                    'code10' => 70100120,
                    'code20' => 70100130
                ]
            ];

            $discountAccounts = [
                'E' => [
                    '5.5' => 0.,
                    '10' => 0.,
                    '20' => 0.,
                    'code55' => 70910210,
                    'code10' => 70910220,
                    'code20' => 70910230
                ],
                'L' => [
                    '5.5' => 0.,
                    '10' => 0.,
                    '20' => 0.,
                    'code55' => 70910310,
                    'code10' => 70910320,
                    'code20' => 70910330
                ],
                'P' => [
                    '5.5' => 0.,
                    '10' => 0.,
                    '20' => 0.,
                    'code55' => 70900110,
                    'code10' => 70910120,
                    'code20' => 70910130
                ]
            ];

            $payments = array_merge($paymentsAccounts, $paymentsCaisseAccounts);

            foreach ($payments as $order) {

                $ttc55 = $order->getTva55() / (1-1/1.055);
                $ttc10 = $order->getTva10() / (1-1/1.1);
                $ttc20 = $order->getTva20() / (1-1/1.2);
                $dif = $order->getPrice() - ($ttc55 + $ttc10 + $ttc20);

                if($dif) {
                    $dif55 = round($ttc55 * $dif /  $order->getPrice(), 2);
                    $dif10 = round($ttc10 * $dif /  $order->getPrice(), 2);
                    $dif20 = round($dif - $dif55 - $dif10, 2);

                    $ttc55+=$dif55;
                    $ttc10+=$dif10;
                    $ttc20+=$dif20;
                }

                $ht55 = $ttc55 - $order->getTva55();
                $ht10 = $ttc10 - $order->getTva10();
                $ht20 = $ttc20 - $order->getTva20();
                $dif = $order->getPrice() - $order->getTva55() - $order->getTva10() - $order->getTva20() - ($ht55 + $ht10 + $ht20);

                if($dif) {
                    $dif55 = round($ht55 * $dif /  $order->getPrice(), 2);
                    $dif10 = round($ht10 * $dif /  $order->getPrice(), 2);
                    $dif20 = round($dif - $dif55 - $dif10, 2);

                    $ht55+=$dif55;
                    $ht10+=$dif10;
                    $ht20+=$dif20;
                }

                $type = $order instanceof  OrderDetail ? ($order->getOrderType()->getId() == 3 ? 'L' : 'E'): 'P';

                $salesHTByVAT[$type]['5.5']+=$ht55;
                $salesHTByVAT[$type]['10']+=$ht10;
                $salesHTByVAT[$type]['20']+=$ht20;

                if ($order instanceof  OrderDetail) {
                    if ($order->getOnlinePayment()) {
                        $paymentAccounts['STRIPE']['amount'] += $order->getPrice();

                    } else {
                        $onSitePayment = $order->getOnSitePayments();
                        if (isset($onSitePayment['cash']) and intval($onSitePayment['cash']) > 0) {
                            $paymentAccounts['ESP']['amount'] += $order->getPrice();
                        } else if(isset($onSitePayment['cbOnSite']) and intval($onSitePayment['cbOnSite']) > 0) {
                            $paymentAccounts['CAR']['amount'] += $order->getPrice();

                        } else if(isset($onSitePayment['ticketResto']) and intval($onSitePayment['ticketResto']) > 0){
                            $paymentAccounts['TR']['amount'] += $order->getPrice();
                        } else if($order->getCompany()){;
                            $clientAccounts[$order->getCompany()->getAccountCode()] += $order->getPrice();
                        } else {
                            $paymentAccounts['ESP']['amount'] += $order->getPrice();
                        }
                    }
                } else {
                    foreach ($order->getPayments() as $payment) {
                        switch ($payment->getPaymentMethods()[0]->getSlug()) {
                            case 'credit-card':
                                $paymentAccounts['CAR']['amount'] += $payment->getAmount();
                                break;
                            case 'money':
                                $paymentAccounts['ESP']['amount'] += $payment->getAmount();
                                break;
                            case 'ticket':
                                $paymentAccounts['TR']['amount'] += $payment->getAmount();
                                break;
                            case 'cheque':
                                $paymentAccounts['CHQ']['amount'] += $payment->getAmount();
                                break;
                        };
                    }
                }


                $totalTax = $order->getTva55() + $order->getTva10() + $order->getTva20();

                $discount = $order->getCart()->getDiscount();
                $discount55 = $discount10 = $discount20 = 0.;

                if ($discount) {
                    $amountDiscount =  $discount->getCartDiscountAmount($order->getCart());
                    $discount55 = ($order->getTva55() * $amountDiscount) / $totalTax;
                    $discount10 = ($order->getTva10() * $amountDiscount) / $totalTax;
                    $discount20 = ($order->getTva20() * $amountDiscount) / $totalTax;

                    $discountAccounts[$type]['5.5'] += $discount55;
                    $discountAccounts[$type]['10'] += $discount10;
                    $discountAccounts[$type]['20'] += $discount20;
                }

                $coupon = $order->getCart()->getCoupon();
                $coupon55 = $coupon10 = $coupon20 = 0.;

                if ($coupon) {
                    $amountCoupon =  $coupon->getAmount();
                    $coupon55 = ($order->getTva55() * $amountCoupon) / $totalTax;
                    $coupon10 = ($order->getTva10() * $amountCoupon) / $totalTax;
                    $coupon20 = ($order->getTva20() * $amountCoupon) / $totalTax;

                    $discountAccounts[$type]['5.5'] += $coupon55;
                    $discountAccounts[$type]['10'] += $coupon10;
                    $discountAccounts[$type]['20'] += $coupon20;
                }

                $loyalties = $order->getCart()->getLoyalties();
                $loyalty55 = $loyalty10 = $loyalty20 = 0.;

                if ($loyalties and count($loyalties)) {
                    foreach ($loyalties as $loyalty) {
                        $amountLoyalty =  $loyalty->getValue();
                        $loyalty55 = ($order->getTva55() * $amountLoyalty) / $totalTax;
                        $loyalty10 = ($order->getTva10() * $amountLoyalty) / $totalTax;
                        $loyalty20 = ($order->getTva20() * $amountLoyalty) / $totalTax;

                        $discountAccounts[$type]['5.5'] += $loyalty55;
                        $discountAccounts[$type]['10'] += $loyalty10;
                        $discountAccounts[$type]['20'] += $loyalty20;
                    }
                }
                $ttcNoDiscount55 = ($discount55 + $coupon55 + $loyalty55);
                $ttcNoDiscount10 = ($discount10 + $coupon10 + $loyalty10);
                $ttcNoDiscount20 = ($discount20 + $coupon20 + $loyalty20);

                $type = $order instanceof  OrderDetail ? ($order->getOrderType()->getId() == 3 ? 3 : 1): 4;

                $salesTTCByVAT[$type]['5.5'] += $ttcNoDiscount55 - ($ttcNoDiscount55 / 1.055);
                $salesTTCByVAT[$type]['10'] += $ttcNoDiscount10 - ($ttcNoDiscount10 / 1.1);
                $salesTTCByVAT[$type]['20'] += $ttcNoDiscount20 - ($ttcNoDiscount20 / 1.2);
            }

           //$sessions = $em->getRepository(SessionCaisse::class)->findAllForRestaurant($restaurant, $date, $date);

            $otherCharges = [
                ['key' => 51170000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'1'],//'Fournitures bureaux'
                ['key' => 60640000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'1'],
                ['key' => 51170000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'2'],//'Alimentaire restaurant'
                ['key' => 60100110, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'2'],
                ['key' => 51170000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'3'],//'Fournitures'
                ['key' => 60630000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'3'],
                ['key' => 51170000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'4'],//'Pharmacie'
                ['key' => 64750000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'4'],
                ['key' => 51170000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'5'],//'Repas du personnel'
                ['key' => 60690000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'5'],
                ['key' => 51170000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'6'],//'Essence'
                ['key' => 62510000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'6'],
                ['key' => 51170000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'7'],//'La Poste'
                ['key' => 62600000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'7'],
                ['key' => 51170000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'8'],//'Entretien Scooter'
                ['key' => 61500000, 'amount' => 0. , 'index' => $date->format('Ymd').$restaurant->getId().'8'],
            ];

            $vatCodes = [
                1 => [
                    'tva55' => 44571050,
                    'tva10' => 44571100,
                    'tva20' => 44571200
                ],
                3 => [
                    'tva55' => 44571050,
                    'tva10' => 44571100,
                    'tva20' => 44571200
                ],
                4 => [
                    'tva55' => 44571050,
                    'tva10' => 44571100,
                    'tva20' => 44571200
                ]
            ];

            $params = array_merge($params, [
                'vatAccounts' => array_merge($vatAccounts, $vatCaisseAccounts),
                'paymentsAccount' => $paymentAccounts,
                'salesTTCByVAT' => $salesTTCByVAT,
                'salesHTByVAT' => $salesHTByVAT,
                'vatCodes' => $vatCodes,
                'clientAccounts' => $clientAccounts,
                'discountAccounts' => $discountAccounts,
                'otherCharges' => $otherCharges
            ]);
        }

        return $this->render('ClabBoardBundle:Client:accounting.html.twig', array_merge($this->get('board.helper')->getParams(), $params));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function ordersAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('client', $contextPk);
        $orders = $em->getRepository('ClabShopBundle:OrderDetail')->findInProgress(date_create());

        return $this->render('ClabBoardBundle:Client:orders.html.twig', array_merge($this->get('board.helper')->getParams(), array('orders' => $orders)));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function orderJsonAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('client', $contextPk);
        $orders = $em->getRepository('ClabShopBundle:OrderDetail')->findInProgress(date_create());
        $serializer = $this->get('serializer');
        $toSubmit = $serializer->serialize($orders, 'json', SerializationContext::create()->setGroups(array('follow')));

        return new JsonResponse($toSubmit);
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function ordersByRestaurantAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('client', $contextPk);
        $orders = $em->getRepository('ClabShopBundle:OrderDetail')->findInProgressRestaurant(date_create());
        $serializer = $this->get('serializer');
        $toSubmit = $serializer->serialize($orders, 'json', SerializationContext::create()->setGroups(array('follow')));

        return new JsonResponse($toSubmit);
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function ordersByDateAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('client', $contextPk);
        $orders = $em->getRepository('ClabShopBundle:OrderDetail')->findInProgressDate(date_create());
        $serializer = $this->get('serializer');
        $toSubmit = $serializer->serialize($orders, 'json', SerializationContext::create()->setGroups(array('follow')));

        return new JsonResponse($toSubmit);
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function ordersByTypeAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('client', $contextPk);
        $orders = $em->getRepository('ClabShopBundle:OrderDetail')->findInProgressType(date_create());
        $serializer = $this->get('serializer');
        $toSubmit = $serializer->serialize($orders, 'json', SerializationContext::create()->setGroups(array('follow')));

        return new JsonResponse($toSubmit);
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function canceledOrderAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isXmlHttpRequest()){
            $id = $request->get('id');
            $state = $request->get('state');
            $order = $em->getRepository('ClabShopBundle:OrderDetail')->find($id);
            $order->setState($state);
            $em->flush();

            return new Response('success', 200);
        }

        return new Response('fail', 400);
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function preparationStateChangeOrderAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->isXmlHttpRequest()){
            $id = $request->get('id');
            $preparationState = $request->get('preparationState');
            $order = $em->getRepository('ClabShopBundle:OrderDetail')->find($id);
            $order->setPreparationState($preparationState);
            $em->flush();

            return new Response('success', 200);
        }

        return new Response('fail', 400);

    }
}
