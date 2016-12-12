<?php

namespace Clab\BoardBundle\Controller;

use Clab\ShopBundle\Entity\OrderDetail;
use Clab\ShopBundle\Entity\OrderDetailCaisse;
use Doctrine\Common\Collections\ArrayCollection;
use Proxies\__CG__\Clab\ShopBundle\Entity\CartElement;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;

class SalesController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function reportingAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if (!$this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'reporting')) {
            return $this->redirectToRoute('board_feature_showcase', array('feature' => 'reporting', 'contextPk' => $contextPk));
        }

        if ($range = $request->get('range')) {
            $formatedRange = str_replace(' ', '', $range);
            $explodedRange = explode('-', $formatedRange);
            $start = date_create_from_format('d/m/Y',$explodedRange[0]);
            $end = date_create_from_format('d/m/Y',$explodedRange[1]);
        } else {
            $start = date_create('now');
            $start->modify('-1 week');
            $end = date_create('now');
        }

        $results = array();
        $results['nbCE'] = 0;
        $results['priceCE'] = 0;
        $results['nbCaisse'] = 0;
        $results['priceCaisse'] = 0;
        $formatedOrders = array('Caisse'=>array(),'Click-eat'=>array());
        $formatedOrdersDays = array('Caisse'=>array(),'Click-eat'=>array());
        $sortDays = array('Caisse'=>array(),'Click-eat'=>array());


        $orders = $this->getDoctrine()->getManager()->getRepository('ClabShopBundle:OrderDetail')->findAllBetweenDate($start, $end, $this->get('board.helper')->getProxy());

        if ($this->get('board.helper')->getProxy()->getHasCaisse()) {
            $orders = array_merge($orders, $this->getDoctrine()->getManager()->getRepository(OrderDetailCaisse::class)->findAllBetweenDate($start, $end, $this->get('board.helper')->getProxy()));
        }

        foreach ($orders as $order) {
            if (strtoupper($order->getSource()) == 'CAISSE') {
                $results['nbCaisse'] = $results['nbCaisse'] + 1;
                $results['priceCaisse'] = $results['priceCaisse'] + $order->getPrice();
                $formatedOrders['Caisse'][] = $order;
                if(array_key_exists($order->getTime()->format('d/m/Y'),$formatedOrdersDays['Caisse'])) {
                    $formatedOrdersDays['Caisse'][$order->getTime()->format('d/m/Y')] += $order->getPrice();
                }else{
                    $formatedOrdersDays['Caisse'][$order->getTime()->format('d/m/Y')] = $order->getPrice();
                }
                $sortDays['Caisse'][$order->getTime()->format('d/m/Y')]= $order->getTime()->format('d/m/Y');
            } else {
                $results['nbCE'] = $results['nbCE'] + 1;
                $results['priceCE'] = $results['priceCE'] + $order->getPrice();
                $formatedOrders['Click-eat'][] = $order;
                if(array_key_exists($order->getTime()->format('d/m/Y'),$formatedOrdersDays['Click-eat'])) {
                    $formatedOrdersDays['Click-eat'][$order->getTime()->format('d/m/Y')] += $order->getPrice();
                }else{
                    $formatedOrdersDays['Click-eat'][$order->getTime()->format('d/m/Y')] = $order->getPrice();
                }
                $sortDays['Click-eat'][$order->getTime()->format('d/m/Y')]= $order->getTime()->format('d/m/Y');
            }
        }
        array_multisort($sortDays['Caisse'],SORT_ASC,$formatedOrdersDays['Caisse']);
        array_multisort($sortDays['Click-eat'],SORT_ASC,$formatedOrdersDays['Click-eat']);

        $this->get('board.helper')->addParams(array(
            'orders' => $formatedOrders,
            'orderDays' => $formatedOrdersDays,
            'start' => $start,
            'end' => $end,
            'results' => $results,
        ));

        return $this->render('ClabBoardBundle:Sales:reporting.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function reportingProductAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if (!$this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'reporting')) {
            return $this->redirectToRoute('board_feature_showcase', array('feature' => 'reporting', 'contextPk' => $contextPk));
        }

        if ($range = $request->get('range')) {
            $formatedRange = str_replace(' ', '', $range);
            $explodedRange = explode('-', $formatedRange);
            $start = date_create_from_format('d/m/Y',$explodedRange[0]);
            $end = date_create_from_format('d/m/Y',$explodedRange[1]);
        } else {
            $start = date_create('now');
            $start->modify('-1 week');
            $end = date_create('now');
        }



        $orders = $this->getDoctrine()->getManager()->getRepository('ClabShopBundle:OrderDetail')->findAllBetweenDate($start, $end, $this->get('board.helper')->getProxy());

        if ($this->get('board.helper')->getProxy()->getHasCaisse()) {
            $orders = array_merge($orders, $this->getDoctrine()->getManager()->getRepository(OrderDetailCaisse::class)->findAllBetweenDate($start, $end, $this->get('board.helper')->getProxy()));
        }

        $results = array();
        $results['price'] =0;

        $daysQuantity = array();
        $daysPrice = array();
        $sortPrices = array();
        $sortQuantities = array();
        $products = array();
        $sortProducts = array();
        $days = array();

        foreach ($orders as $order) {
            foreach($order->getCart()->getElements() as $element) {
                if(!is_null($element->getProduct()) && is_null($element->getParent())) {
                    if (array_key_exists($element->getProduct()->getName(),$daysPrice)) {
                        if(array_key_exists(date_timestamp_get($order->getTime()),$daysPrice[$element->getProduct()->getName()])) {
                            $daysPrice[$element->getProduct()->getName()][date_timestamp_get($order->getTime())]+=($element->getProduct()->getPrice()*$element->getQuantity());
                        }else{
                            $daysPrice[$element->getProduct()->getName()][date_timestamp_get($order->getTime())]=($element->getProduct()->getPrice()*$element->getQuantity());
                        }
                    } else {
                        $daysPrice[$element->getProduct()->getName()][date_timestamp_get($order->getTime())]=($element->getProduct()->getPrice()*$element->getQuantity());
                    }
                    if(array_key_exists($element->getProduct()->getName(),$sortPrices)){
                        $sortPrices[$element->getProduct()->getName()]+=($element->getProduct()->getPrice()*$element->getQuantity());
                    }else {
                        $sortPrices[$element->getProduct()->getName()]=($element->getProduct()->getPrice()*$element->getQuantity());
                    }
                    if (array_key_exists($element->getProduct()->getName(),$daysQuantity)) {
                        if(array_key_exists(date_timestamp_get($order->getTime()),$daysQuantity[$element->getProduct()->getName()])) {
                            $daysQuantity[$element->getProduct()->getName()][date_timestamp_get($order->getTime())]+=($element->getQuantity());
                        }else{
                            $daysQuantity[$element->getProduct()->getName()][date_timestamp_get($order->getTime())]=($element->getQuantity());
                        }
                    } else {
                        $daysQuantity[$element->getProduct()->getName()][date_timestamp_get($order->getTime())]=($element->getQuantity());
                    }
                    if(array_key_exists($element->getProduct()->getName(),$sortQuantities)){
                        $sortQuantities[$element->getProduct()->getName()]+=($element->getQuantity());
                    }else {
                        $sortQuantities[$element->getProduct()->getName()]=($element->getQuantity());
                    }
                    if(array_key_exists($element->getProduct()->getSlug(),$products)) {
                        $products[$element->getProduct()->getSlug()]['quantity'] += $element->getQuantity();
                        if(strpos($products[$element->getProduct()->getSlug()]['sources'],$order->getSource())<0) {
                            $products[$element->getProduct()->getSlug()]['sources'] .= ", ".$order->getSource();
                        }
                        $sortProducts[$element->getProduct()->getSlug()]+=($element->getQuantity()*$element->getProduct()->getPrice());
                    }else{
                        $products[$element->getProduct()->getSlug()] = array(
                            'name' => $element->getProduct()->getName(),
                            'price' => $element->getProduct()->getPrice(),
                            'quantity' => $element->getQuantity(),
                            'sources'   => $order->getSource(),
                        );
                        $sortProducts[$element->getProduct()->getSlug()]=($element->getQuantity()*$element->getProduct()->getPrice());
                    }
                    $results['price'] += $element->getProduct()->getPrice() * $element->getQuantity();
                }

            }

        }
        array_multisort($sortProducts,SORT_DESC,$products);
        array_multisort($sortPrices,SORT_DESC,$daysPrice);
        array_multisort($sortQuantities,SORT_DESC,$daysQuantity);
        $daysQuantity=array_slice($daysQuantity,0,8);
        $daysPrice= array_slice($daysPrice,0,8);

        foreach($daysPrice as $dayPrice){
            foreach($dayPrice as $key=>$date){
                $days[$key]=$key;
            }
        }
        sort($days);
        $tmpDays = array();
        foreach($days as $day){
            foreach($daysPrice as $key=>$dayPrice){
                if(!array_key_exists($day,$dayPrice)){
                    $tmpDays[$key][$day]=null;
                }else{
                    $tmpDays[$key][$day]=$dayPrice[$day];
                }
            }
        }
        $daysPrice = $tmpDays;

        $days = array();
        foreach($daysQuantity as $dayQuantity){
            foreach($dayQuantity as $key=>$date){
                $days[$key]=$key;
            }
        }
        sort($days);
        $tmpDays = array();
        foreach($days as $day){
            foreach($daysQuantity as $key=>$dayQuantity){
                if(!array_key_exists($day,$dayQuantity)){
                    $tmpDays[$key][$day]=null;
                }else{
                    $tmpDays[$key][$day]=$dayQuantity[$day];
                }
            }
        }
        $daysQuantity = $tmpDays;

        $this->get('board.helper')->addParams(array(
            'products' => $products,
            'days' => $days,
            'daysPrice' => $daysPrice,
            'daysQuantity' => $daysQuantity,
            'start' => $start,
            'end' => $end,
            'results' => $results,
        ));

        return $this->render('ClabBoardBundle:Sales:reportingProduct.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function reportingCategoryAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if (!$this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'reporting')) {
            return $this->redirectToRoute('board_feature_showcase', array('feature' => 'reporting', 'contextPk' => $contextPk));
        }

        if ($range = $request->get('range')) {
            $formatedRange = str_replace(' ', '', $range);
            $explodedRange = explode('-', $formatedRange);
            $start = date_create_from_format('d/m/Y',$explodedRange[0]);
            $end = date_create_from_format('d/m/Y',$explodedRange[1]);
        } else {
            $start = date_create('now');
            $start->modify('-1 week');
            $end = date_create('now');
        }

        $orders = $this->getDoctrine()->getManager()->getRepository('ClabShopBundle:OrderDetail')->findAllBetweenDate($start, $end, $this->get('board.helper')->getProxy());

        if ($this->get('board.helper')->getProxy()->getHasCaisse()) {
            $orders = array_merge($orders, $this->getDoctrine()->getManager()->getRepository(OrderDetailCaisse::class)->findAllBetweenDate($start, $end, $this->get('board.helper')->getProxy()));
        }

        $results = array();
        $results['price'] =0;

        $daysQuantity = array();
        $daysPrice = array();
        $sortPrices = array();
        $sortQuantities = array();
        $categories = array();
        $sortCategories = array();
        $days = array();

        foreach ($orders as $order) {
            foreach($order->getCart()->getElements() as $element) {
                if(!is_null($element->getProduct()) && is_null($element->getParent())) {
                    if (array_key_exists($element->getProduct()->getCategory()->getName(),$daysPrice)) {
                        if(array_key_exists(date_timestamp_get($order->getTime()),$daysPrice[$element->getProduct()->getCategory()->getName()])) {
                            $daysPrice[$element->getProduct()->getCategory()->getName()][date_timestamp_get($order->getTime())]+=($element->getProduct()->getPrice()*$element->getQuantity());
                        }else{
                            $daysPrice[$element->getProduct()->getCategory()->getName()][date_timestamp_get($order->getTime())]=($element->getProduct()->getPrice()*$element->getQuantity());
                        }
                    } else {
                        $daysPrice[$element->getProduct()->getCategory()->getName()][date_timestamp_get($order->getTime())]=($element->getProduct()->getPrice()*$element->getQuantity());
                    }
                    if(array_key_exists($element->getProduct()->getCategory()->getName(),$sortPrices)){
                        $sortPrices[$element->getProduct()->getCategory()->getName()]+=($element->getProduct()->getPrice()*$element->getQuantity());
                    }else {
                        $sortPrices[$element->getProduct()->getCategory()->getName()]=($element->getProduct()->getPrice()*$element->getQuantity());
                    }
                    if (array_key_exists($element->getProduct()->getCategory()->getName(),$daysQuantity)) {
                        if(array_key_exists(date_timestamp_get($order->getTime()),$daysQuantity[$element->getProduct()->getCategory()->getName()])) {
                            $daysQuantity[$element->getProduct()->getCategory()->getName()][date_timestamp_get($order->getTime())]+=($element->getQuantity());
                        }else{
                            $daysQuantity[$element->getProduct()->getCategory()->getName()][date_timestamp_get($order->getTime())]=($element->getQuantity());
                        }
                    } else {
                        $daysQuantity[$element->getProduct()->getCategory()->getName()][date_timestamp_get($order->getTime())]=($element->getQuantity());
                    }
                    if(array_key_exists($element->getProduct()->getCategory()->getName(),$sortQuantities)){
                        $sortQuantities[$element->getProduct()->getCategory()->getName()]+=($element->getQuantity());
                    }else {
                        $sortQuantities[$element->getProduct()->getCategory()->getName()]=($element->getQuantity());
                    }
                    if(array_key_exists($element->getProduct()->getCategory()->getSlug(),$categories)) {
                        $categories[$element->getProduct()->getCategory()->getSlug()]['total'] += ($element->getQuantity()*$element->getProduct()->getPrice());
                        $categories[$element->getProduct()->getCategory()->getSlug()]['quantity'] += $element->getQuantity();

                        if(strpos( $categories[$element->getProduct()->getCategory()->getSlug()]['sources'],$order->getSource())<0) {
                            $categories[$element->getProduct()->getCategory()->getSlug()]['sources'] .= ", ".$order->getSource();
                        }
                        $sortCategories[$element->getProduct()->getCategory()->getSlug()]+=($element->getQuantity()*$element->getProduct()->getPrice());
                    }else{
                        $categories[$element->getProduct()->getCategory()->getSlug()] = array(
                            'name' => $element->getProduct()->getCategory()->getName(),
                            'quantity' => $element->getQuantity(),
                            'total' => ($element->getQuantity()*$element->getProduct()->getPrice()),
                            'sources'   => $order->getSource(),
                        );
                        $sortCategories[$element->getProduct()->getCategory()->getSlug()]=($element->getQuantity()*$element->getProduct()->getPrice());
                    }
                    $results['price'] += ($element->getProduct()->getPrice() * $element->getQuantity());
                }

            }

        }
        array_multisort($sortCategories,SORT_DESC,$categories);
        array_multisort($sortPrices,SORT_DESC,$daysPrice);
        array_multisort($sortQuantities,SORT_DESC,$daysQuantity);
        $daysQuantity=array_slice($daysQuantity,0,8);
        $daysPrice= array_slice($daysPrice,0,8);

        foreach($daysPrice as $dayPrice){
            foreach($dayPrice as $key=>$date){
                $days[$key]=$key;
            }
        }
        sort($days);
        $tmpDays = array();
        foreach($days as $day){
            foreach($daysPrice as $key=>$dayPrice){
                if(!array_key_exists($day,$dayPrice)){
                    $tmpDays[$key][$day]=null;
                }else{
                    $tmpDays[$key][$day]=$dayPrice[$day];
                }
            }
        }
        $daysPrice = $tmpDays;

        $days = array();
        foreach($daysQuantity as $dayQuantity){
            foreach($dayQuantity as $key=>$date){
                $days[$key]=$key;
            }
        }
        sort($days);
        $tmpDays = array();
        foreach($days as $day){
            foreach($daysQuantity as $key=>$dayQuantity){
                if(!array_key_exists($day,$dayQuantity)){
                    $tmpDays[$key][$day]=null;
                }else{
                    $tmpDays[$key][$day]=$dayQuantity[$day];
                }
            }
        }
        $daysQuantity = $tmpDays;
        $this->get('board.helper')->addParams(array(
            'categories' => $categories,
            'daysPrice' => $daysPrice,
            'daysQuantity' => $daysQuantity,
            'start' => $start,
            'end' => $end,
            'results' => $results,
        ));

        return $this->render('ClabBoardBundle:Sales:reportingCategory.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function paymentsAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        if ($range = $request->get('range')) {
            $formatedRange = str_replace(' ', '', $range);
            $explodedRange = explode('-', $formatedRange);
            $start = date_create_from_format('d/m/Y',$explodedRange[0]);
            $end = date_create_from_format('d/m/Y',$explodedRange[1]);
        } else {
            $start = date_create('now');
            $start->modify('-1 week');
            $end = date_create('now');
        }

        $orders = $this->getDoctrine()->getManager()->getRepository('ClabShopBundle:OrderDetail')->findAllBetweenDate($start, $end, $this->get('board.helper')->getProxy());

        /*if ($this->get('board.helper')->getProxy()->getHasCaisse()) {
            $orders = array_merge($orders, $this->getDoctrine()->getManager()->getRepository(OrderDetailCaisse::class)->findAllBetweenDate($start, $end, $this->get('board.helper')->getProxy()));
        }*/

        $results = array();
        $results['MG']['volume'] = 0;
        $results['MG']['price'] = 0;
        $results['MGSP']['volume'] = 0;
        $results['MGSP']['price'] = 0;
        $results['ce']['volume'] = 0;
        $results['ce']['price'] = 0;
        $results['restoflash']['volume'] = 0;
        $results['restoflash']['price'] = 0;
        foreach ($orders as $order) {
            if ($order->getOnlinePayment() && ($order->getSource() == 'Click-eat' || $order->getSource() == 'web')) {
                $results['ce']['volume'] = $results['ce']['volume'] + 1;
                $results['ce']['price'] = $results['ce']['price'] + $order->getPrice();
            }
            if ($order->getOnlinePayment() && $order->getSource() == 'Marque grise') {
                $results['MG']['volume'] = $results['MG']['volume'] + 1;
                $results['MG']['price'] = $results['MG']['price'] + $order->getPrice();
            }
            if (!$order->getOnlinePayment() && $order->getSource() == 'Marque grise') {
                $results['MGSP']['volume'] = $results['MGSP']['volume'] + 1;
                $results['MGSP']['price'] = $results['MGSP']['price'] + $order->getPrice();
            }
        }

        $this->get('board.helper')->addParams(array(
            'orders' => $orders,
            'start' => $start,
            'end' => $end,
            'results' => $results,
        ));

        return $this->render('ClabBoardBundle:Sales:payments.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function taxAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        if ($range = $request->get('range')) {
            $formatedRange = str_replace(' ', '', $range);
            $explodedRange = explode('-', $formatedRange);
            $start = date_create_from_format('d/m/Y',$explodedRange[0]);
            $end = date_create_from_format('d/m/Y',$explodedRange[1]);
        } else {
            $start = date_create('now');
            $start->modify('-1 week');
            $end = date_create('now');
        }

        $orders = $this->getDoctrine()->getManager()->getRepository('ClabShopBundle:OrderDetail')->findAllBetweenDate($start, $end, $this->get('board.helper')->getProxy());

        if ($this->get('board.helper')->getProxy()->getHasCaisse()) {
            $orders = array_merge($orders, $this->getDoctrine()->getManager()->getRepository(OrderDetailCaisse::class)->findAllBetweenDate($start, $end, $this->get('board.helper')->getProxy()));
        }

        $results = array();
        $results['10']['price'] = 0;
        $results['20']['price'] = 0;
        $results['5.5']['price'] = 0;
        $results['7']['price'] = 0;
        foreach ($orders as $order) {
            $elements = $this->getDoctrine()->getRepository('ClabShopBundle:CartElement')->findBy(array(
                'cart' => $order->getCart(),
            ));
            foreach ($elements as $element) {
                if ($element->getProxy()->getTax()->getValue() == 10) {
                    $results['10']['price'] = $results['10']['price'] + ($element->getPrice() * $element->getQuantity());
                }
                if ($element->getProxy()->getTax()->getValue() == 7) {
                    $results['7']['price'] = $results['7']['price'] + ($element->getPrice() * $element->getQuantity());
                }
                if ($element->getProxy()->getTax()->getValue() == 5.5) {
                    $results['5.5']['price'] = $results['5.5']['price'] + ($element->getPrice() * $element->getQuantity());
                }
                if ($element->getProxy()->getTax()->getValue() == 20) {
                    $results['20']['price'] = $results['20']['price'] + ($element->getPrice() * $element->getQuantity());
                }
            }
        }
        $this->get('board.helper')->addParams(array(
            'orders' => $orders,
            'start' => $start,
            'end' => $end,
            'results' => $results,
        ));

        return $this->render('ClabBoardBundle:Sales:tax.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function caisseAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        return $this->redirectToRoute('board_feature_showcase', array('feature' => 'analytics', 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function analyticsAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        return $this->redirectToRoute('board_feature_showcase', array('feature' => 'analytics', 'contextPk' => $contextPk));
    }
}
