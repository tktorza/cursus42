<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Entity\Client;
use Clab\DeliveryBundle\Entity\AreaDelivery;
use Clab\DeliveryBundle\Form\Type\AreaDeliveryType;
use Clab\DeliveryBundle\Form\Type\DeliveryManType;
use Clab\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Clab\DeliveryBundle\Entity\DeliveryPeriod;
use Clab\DeliveryBundle\Entity\DeliveryDay;
use Clab\DeliveryBundle\Entity\DeliverySchedule;
use Clab\DeliveryBundle\Entity\DeliveryMan;
use Clab\DeliveryBundle\Entity\DeliveryCart;
use Clab\DeliveryBundle\Form\Type\DeliveryDayType;
use Clab\DeliveryBundle\Form\Type\DeliveryScheduleType;
use Clab\DeliveryBundle\Form\Type\DeliveryScheduleEventType;
use Clab\DeliveryBundle\Form\Type\DeliveryCartType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\VarDumper;

class DeliveryController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function dashboardAction($context, $contextPk)
    {
        if ($context == 'client') {
            $this->redirectToRoute('board_delivery_zones', array('context' => $context, 'contextPk' => $contextPk));
        }

        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $deliveryManager = $this->get('clab_delivery.delivery_manager');
        $deliveryPeriod = $deliveryManager->getCurrentDeliveryPeriod($this->get('board.helper')->getProxy());

        if (!$deliveryPeriod) {
            $deliveryPeriod = new DeliveryPeriod();
            $deliveryPeriod->setRestaurant($this->get('board.helper')->getProxy());

            $em->persist($deliveryPeriod);
            $em->flush();
        }

        $deliveryMen = $em->getRepository('ClabDeliveryBundle:DeliveryMan')->findBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
        ));

        $deliveryAreas = $em->getRepository('ClabDeliveryBundle:AreaDelivery')->findBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
        ));

        $weekDayPlanning = $deliveryManager->getWeekDayPlanning($this->get('board.helper')->getProxy());
        $start = date_create('last monday');
        $planning = array();
        for ($i = 0; $i < 28; ++$i) {
            $dayPlanning = $deliveryManager->getDayPlanning($this->get('board.helper')->getProxy(), clone $start);

            $planning[$start->getTimestamp()] = $dayPlanning;

            $start->modify('+1 day');
        }

        return $this->render('ClabBoardBundle:Delivery:dashboard.html.twig',
            array_merge($this->get('board.helper')->getParams(),
                array(
                    'deliveryPeriod' => $deliveryPeriod,
                    'weekDayPlanning' => $weekDayPlanning,
                    'planning' => $planning,
                    'deliveryMen' => $deliveryMen,
                    'deliveryAreas' => $deliveryAreas,
                )))
            ;
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function dayViewAction($context, $contextPk, $timestamp)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $date = date_create();
        $date->setTimestamp($timestamp);

        $deliveryManager = $this->get('clab_delivery.delivery_manager');
        $planning = $deliveryManager->getDayPlanning($this->get('board.helper')->getProxy(), $date);

        return $this->render('ClabBoardBundle:Delivery:dayView.html.twig',
            array_merge($this->get('board.helper')->getParams(),
                array(
                    'planning' => $planning,
                    'date' => $date,
                )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function toggleAction($context, $contextPk)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {

            if ($this->get('board.helper')->getProxy()->getIsOpenDelivery()) {
                $this->get('board.helper')->getProxy()->setIsOpenDelivery(false);
            } else {
                $this->get('board.helper')->getProxy()->setIsOpenDelivery(true);
            }
            $em->flush();
        }

        return $this->redirectToRoute('board_delivery', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function zonesAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getEntityManager();

        if ($context == 'restaurant') {

            $areaDeliveries = $em->getRepository('ClabDeliveryBundle:AreaDelivery')->findBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'isDeleted' => false));
            $form = $this->createForm(new AreaDeliveryType(), new AreaDelivery());
            $this->get('board.helper')->addParams(array(
                'lat' => $this->get('board.helper')->getProxy()->getAddress()->getLatitude(),
                'lng' => $this->get('board.helper')->getProxy()->getAddress()->getLongitude(),
                'restaurant' => $this->get('board.helper')->getProxy(),
                'areaDeliveries' => $areaDeliveries,
                'form' => $form->createView(),
            ));
        } else {
            $client = $this->get('board.helper')->getProxy();
            $restaurants = $client->getRestaurants();
            $areaDeliveries = new ArrayCollection();
            foreach ($restaurants as $restaurant) {
                $areaD = $em->getRepository('ClabDeliveryBundle:AreaDelivery')->findBy(array('restaurant' => $restaurant, 'isOnline' => true, 'isDeleted' => false));
                $areaDeliveries = new ArrayCollection(array_merge($areaDeliveries->toArray(), $areaD));
            }
            $form = $this->createForm(new AreaDeliveryType(), new AreaDelivery());
            $form->add('restaurant', null, array(
                'label' => 'restaurant',
                'required' => true,
                'choices' => $restaurants,
                'expanded' => false,
                'required' => false, ));
            $this->get('board.helper')->addParams(array(
                'restaurants' => $restaurants,
                'areaDeliveries' => $areaDeliveries,
                'form' => $form->createView(),
            ));
        }

        return $this->render('ClabBoardBundle:Delivery:zones.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function zonesAddAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $slug = $request->get('slug');
            $zoneValue = $request->get('zone-value');
            $zoneColor = $request->get('zone-color');
            $zonePrice = $request->get('zone-price');
            $zonePriceMin = $request->get('zone-price-min');
            $points = $request->get('points');
            $slotLength = $request->get('zone-slot-length');
            $isOnline = $request->get('is-online');
            $centerLat = $request->get('centerLat');
            $centerLng = $request->get('centerLng');

            $em = $this->getDoctrine()->getEntityManager();
            $restaurant = $em->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $slug));

            if (!is_null($restaurant)) {
                $areaDelivery = new AreaDelivery();

                $areaDelivery->setCreated(new \DateTime());
                $areaDelivery->setRestaurant($restaurant);
                $areaDelivery->setPrice($zonePrice);
                $areaDelivery->setMinPanier($zonePriceMin);
                $areaDelivery->setColor($zoneColor);
                $areaDelivery->setPoints($points);
                $areaDelivery->setZone($zoneValue);
                $areaDelivery->setSlotLength($slotLength);
                $areaDelivery->setIsOnline(($isOnline == 'true' ? true : false));
                $areaDelivery->setCenterLat($centerLat);
                $areaDelivery->setCenterLng($centerLng);

                $em->persist($areaDelivery);
                $em->flush();
                $slug = $areaDelivery->getSlug();
            } else {
                return new JsonResponse('error');
            }

            return new JsonResponse(array('points' => $points, 'color' => $zoneColor, 'slug' => $slug));
        } else {
            return new JsonResponse(array('error' => 'erreur requete'));
        }
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function zonesEditAction(Request $request)
    {
        if ($request->isXmlHttpRequest() && $request->isMethod('POST')) {
            $slug = $request->get('slug');
            $zoneValue = $request->get('zone-value');
            $zoneColor = $request->get('zone-color');
            $zonePrice = $request->get('zone-price');
            $zonePriceMin = $request->get('zone-price-min');
            $points = $request->get('points');
            $slotLength = $request->get('zone-slot-length');
            $isOnline = $request->get('is-online');
            $centerLat = $request->get('centerLat');
            $centerLng = $request->get('centerLng');

            $em = $this->getDoctrine()->getEntityManager();
            $areaDelivery = $em->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array('slug' => $slug));

            if (!is_null($areaDelivery)) {
                $areaDelivery->setUpdated(new \DateTime());
                $areaDelivery->setPrice($zonePrice);
                $areaDelivery->setMinPanier($zonePriceMin);
                $areaDelivery->setColor($zoneColor);
                $areaDelivery->setPoints($points);
                $areaDelivery->setZone($zoneValue);
                $areaDelivery->setSlotLength($slotLength);
                $areaDelivery->setIsOnline(($isOnline == 'true' ? true : false));
                $areaDelivery->setCenterLat($centerLat);
                $areaDelivery->setCenterLng($centerLng);
                $em->flush();
            } else {
                return new JsonResponse('error');
            }

            return new JsonResponse(array('points' => $points));
        } else {
            return new JsonResponse(array('error' => 'erreur requete'));
        }
    }

    public function zonesRemoveAction(Request $request, $contextPk, $context, $slug) {

            $em = $this->getDoctrine()->getEntityManager();
            $areaDelivery = $em->getRepository('ClabDeliveryBundle:AreaDelivery')->findOneBy(array('slug' => $slug));

            if (!is_null($areaDelivery)) {
                $areaDelivery->setIsOnline(false);
                $areaDelivery->setIsDeleted(true);
                $em->flush();
            }

            $this->get('session')->getFlashBag()->add('success','zone bien supprimée');

            return $this->redirectToRoute('board_delivery_zones',array('contextPk' => $contextPk, 'context' => $context));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function scheduleCreateAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        return $this->render('ClabBoardBundle:Delivery:scheduleCreate.html.twig',
            $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function scheduleEditAction($context, $contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($id) {
            $schedule = $em->getRepository('ClabDeliveryBundle:DeliverySchedule')->find($id);
        } else {
            $schedule = new DeliverySchedule();
        }

        $deliveryManager = $this->get('clab_delivery.delivery_manager');
        $deliveryPeriod = $deliveryManager->getCurrentDeliveryPeriod($this->get('board.helper')->getProxy());

        $weekDayPlanning = $deliveryManager->getWeekDayPlanning($this->get('board.helper')->getProxy(), $schedule);

        $form = $this->createForm(new DeliveryScheduleType(array('weekDays' => $weekDayPlanning)), $schedule);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $schedule->setDeliveryPeriod($deliveryPeriod);
            if ($context == 'restaurant') {
                $schedule->setRestaurant($this->get('board.helper')->getProxy());
                if (!$this->get('board.helper')->getProxy()->getDeliverySchedules()->contains($schedule)) {
                    $this->get('board.helper')->getProxy()->addDeliverySchedule($schedule);
                }
            }
            $em->persist($schedule);
            $em->flush();

            for ($i = 1; $i < 8; ++$i) {
                $availability = $form->get('is_weekday_'.$i)->getData();
                $start = $form->get('start_'.$i)->getData();
                $end = $form->get('end_'.$i)->getData();

                if ($availability && $start && $end) {
                    if (count($weekDayPlanning[$i]) > 0) {
                        foreach ($weekDayPlanning[$i] as $deliveryDay) {
                            $deliveryDay->setStart($start);
                            $deliveryDay->setEnd($end);
                        }
                    } else {
                        $deliveryDay = new DeliveryDay();
                        $deliveryDay->setDeliverySchedule($schedule);
                        $deliveryDay->setStart($start);
                        $deliveryDay->setEnd($end);
                        $deliveryDay->setWeekDay($i);
                        $deliveryDay->setRestaurant($this->get('board.helper')->getProxy());
                        $em->persist($deliveryDay);
                    }
                } else {
                    if (count($weekDayPlanning[$i]) > 0) {
                        foreach ($weekDayPlanning[$i] as $deliveryDay) {
                            $em->remove($deliveryDay);
                            $em->flush();
                        }
                    }
                }
            }

            $em->flush();

            return $this->redirectToRoute('board_delivery', array('context' => $context, 'contextPk' => $contextPk));
        }

        return $this->render('ClabBoardBundle:Delivery:scheduleEdit.html.twig',
            array_merge($this->get('board.helper')->getParams(),
                array(
                    'schedule' => $schedule,
                    'form' => $form->createView(),
                )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function scheduleEditEventAction($context, $contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $schedule = $em->getRepository('ClabDeliveryBundle:DeliverySchedule')->find($id);
        if (!$this->getUser() || !$schedule || !$schedule->isAllowed($this->getUser())) {
            return new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        } else {
            $schedule = new DeliverySchedule();
        }

        $deliveryManager = $this->get('clab_delivery.delivery_manager');
        $deliveryPeriod = $deliveryManager->getCurrentDeliveryPeriod($this->get('board.helper')->getProxy());

        $form = $this->createForm(new DeliveryScheduleEventType(array(
            'deliveryMen' => $deliveryManager->getDeliveryMen($this->get('board.helper')->getProxy()),
        )),
            $schedule);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $schedule->setDeliveryPeriod($deliveryPeriod);
            foreach ($schedule->getDeliveryDays() as $deliveryDay) {
                $deliveryDay->setDeliverySchedule($schedule);
                $deliveryDay->setRestaurant($this->get('board.helper')->getProxy());
            }
            $em->persist($schedule);
            $em->flush();

            return $this->redirectToRoute('board_delivery', array('context' => $context, 'contextPk' => $contextPk));
        }

        return $this->render('ClabBoardBundle:Delivery:scheduleEditEvent.html.twig',
            array_merge($this->get('board.helper')->getParams(),
                array(
                    'schedule' => $schedule,
                    'form' => $form->createView(),
                )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function scheduleDeleteAction($context, $contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $schedule = $em->getRepository('ClabDeliveryBundle:DeliverySchedule')->find($id);
        if (!$this->getUser() || !$schedule || !$schedule->isAllowed($this->getUser())) {
            return new \Symfony\Component\Security\Core\Exception\AccessDeniedException();
        }

        if ($context == 'restaurant') {
            $this->get('board.helper')->getProxy()->removeDeliveryschedule($schedule);
        }
        $em->remove($schedule);
        $em->flush();

        return $this->redirectToRoute('board_delivery', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function calendarEditAction($context, $contextPk, $timestamp, $id)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $deliveryDay = $em->getRepository('ClabDeliveryBundle:DeliveryDay')->findOneBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
            'id' => $id,
        ));

        if ($deliveryDay->getDay()) {
            throw $this->createNotFoundException();
        } else {
            return $this->render('ClabBoardBundle:Delivery:calendarEdit.html.twig',
                array_merge($this->get('board.helper')->getParams(),
                    array(
                        'deliveryDay' => $deliveryDay,
                        'timestamp' => $timestamp,
                    )));
        }
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function dayCreateAction($context, $contextPk, $value, $cancel, Request $request)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();

        $deliveryDay = new DeliveryDay();
        $day = date_create();
        $day->setTimestamp($value);
        $deliveryDay->setDay($day);

        $cancel = $em->getRepository('ClabDeliveryBundle:DeliveryDay')->findOneBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
            'id' => $cancel,
        ));

        $deliveryDay->setStart($cancel->getStart());
        $deliveryDay->setEnd($cancel->getEnd());
        $deliveryDay->setDeliverySchedule($cancel->getDeliverySchedule());

        foreach ($cancel->getDeliveryMen() as $deliveryMan) {
            $deliveryDay->addDeliveryMan($deliveryMan);
        }

        $deliveryManager = $this->get('clab_delivery.delivery_manager');

        $form = $this->createForm(new DeliveryDayType(array(
            'deliveryMen' => $deliveryManager->getDeliveryMen($this->get('board.helper')->getProxy()),
        )),
            $deliveryDay);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $deliveryDay->setRestaurant($this->get('board.helper')->getProxy());
            $em->persist($deliveryDay);
            $cancel->addCancelDay($deliveryDay->getDay()->getTimestamp());

            $em->flush();

            return $this->redirectToRoute('board_delivery', array('context' => $context, 'contextPk' => $contextPk));
        }

        return $this->render('ClabBoardBundle:Delivery:dayCreate.html.twig',
            array_merge($this->get('board.helper')->getParams(),
                array(
                    'deliveryDay' => $deliveryDay,
                    'value' => $value,
                    'cancel' => $cancel,
                    'form' => $form->createView(),
                )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function dayEditAction($context, $contextPk, $id, Request $request)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();

        $deliveryDay = $em->getRepository('ClabDeliveryBundle:DeliveryDay')->findOneBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
            'id' => $id,
        ));

        $deliveryManager = $this->get('clab_delivery.delivery_manager');

        $form = $this->createForm(new DeliveryDayType(array(
            'deliveryMen' => $deliveryManager->getDeliveryMen($this->get('board.helper')->getProxy()),
        )),
            $deliveryDay);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute('board_delivery', array('context' => $context, 'contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('deliveryDay', $deliveryDay);
        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Delivery:dayEdit.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function dayDeleteAction($context, $contextPk, $id)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $deliveryDay = $em->getRepository('ClabDeliveryBundle:DeliveryDay')->findOneBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
            'id' => $id,
        ));

        $em->remove($deliveryDay);
        $em->flush();

        return $this->redirectToRoute('board_delivery', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function dayCancelRecurrentAction($context, $contextPk, $timestamp, $id = null)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $day = date_create();
        $day->setTimestamp($timestamp);

        $deliveryManager = $this->get('clab_delivery.delivery_manager');

        $planning = $deliveryManager->getDayPlanning($this->get('board.helper')->getProxy(), $day);

        foreach ($planning as $deliveryDay) {
            if ($deliveryDay->getWeekDay()) {
                if (!$id || $id == $deliveryDay->getId()) {
                    $deliveryDay->addCancelDay($day->getTimestamp());
                }
            }
        }

        $em->flush();

        return $this->redirectToRoute('board_delivery', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deliveryManListAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $deliveryManager = $this->get('clab_delivery.delivery_manager');
        $deliveryMen = $deliveryManager->getDeliveryMen($this->get('board.helper')->getProxy());

        $this->get('board.helper')->addParam('deliveryMen', $deliveryMen);

        $weekDayPlanning = $deliveryManager->getWeekDayPlanning($this->get('board.helper')->getProxy());
        $this->get('board.helper')->addParam('weekDayPlanning', $weekDayPlanning);

        return $this->render('ClabBoardBundle:Delivery:deliveryManList.html.twig',
            $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deliveryManSwitchDeliveryDayAction(Request $request, $context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $deliveryMan = $request->request->get('deliveryMan') ? $request->request->get('deliveryMan') : null;
        $deliveryDay = $request->request->get('deliveryDay') ? $request->request->get('deliveryDay') : null;

        if (!$deliveryMan || !$deliveryDay) {
            return new Response('ko', 200);
        }

        $deliveryMan = $em->getRepository('ClabDeliveryBundle:DeliveryMan')->find($deliveryMan);
        $deliveryDay = $em->getRepository('ClabDeliveryBundle:DeliveryDay')->find($deliveryDay);

        if ($deliveryMan->getDeliveryDays()->contains($deliveryDay)) {
            $deliveryDay->removeDeliveryMan($deliveryMan);
        } else {
            $deliveryDay->addDeliveryMan($deliveryMan);
        }

        $em->flush();

        return new Response('ok', 200);
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deliveryCartListAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $deliveryManager = $this->get('clab_delivery.delivery_manager');
        $deliveryCarts = $deliveryManager->getDeliveryCarts($this->get('board.helper')->getProxy());

        $this->get('board.helper')->addParam('deliveryCarts', $deliveryCarts);

        return $this->render('ClabBoardBundle:Delivery:deliveryCartList.html.twig',
            $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deliveryCartListContentAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $deliveryManager = $this->get('clab_delivery.delivery_manager');
        $deliveryCarts = $deliveryManager->getDeliveryCarts($this->get('board.helper')->getProxy());

        $this->get('board.helper')->addParam('deliveryCarts', $deliveryCarts);

        return $this->render('ClabBoardBundle:Delivery:deliveryCartListContent.html.twig',
            $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deliveryCartEditAction($context, $contextPk, $id, Request $request)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();

        if ($id) {
            $deliveryCart = $em->getRepository('ClabDeliveryBundle:DeliveryCart')->findOneBy(array(
                'restaurant' => $this->get('board.helper')->getProxy(),
                'id' => $id,
            ));
        } else {
            $deliveryCart = new DeliveryCart();
            $deliveryCart->setRestaurant($this->get('board.helper')->getProxy());
        }

        $form = $this->createForm(new DeliveryCartType(), $deliveryCart);

        if ($request->isMethod('POST')) {
            $form->bind($this->getRequest());
            $em = $this->getDoctrine()->getManager();
            if ($form->isValid()) {
                $em->persist($deliveryCart);
                $em->flush();

                $this->get('session')->getFlashBag()->add('formSuccess', 'Le produit a bien été sauvegardé');

                return $this->redirectToRoute('board_delivery_deliverycart_edit',
                    array('context' => $context, 'contextPk' => $contextPk, 'id' => $deliveryCart->getId()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        $this->get('board.helper')->addParam('deliveryCart', $deliveryCart);
        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Delivery:deliveryCartEdit.html.twig',
            $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deliveryCartDeleteAction($context, $contextPk, $id)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $deliveryCart = $em->getRepository('ClabDeliveryBundle:DeliveryCart')->find($id);

        $deliveryCart->remove();
        $em->flush();

        return $this->redirectToRoute('board_delivery_deliverycart_list', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function changeStateDeliveryManAction($context, $contextPk, $id)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $deliveryMan = $em->getRepository('ClabDeliveryBundle:DeliveryMan')->find($id);
        if ($deliveryMan->getIsOnline() == false) {
            $deliveryMan->setIsOnline(true);
            $this->addFlash('succes', 'Votre livreur est maintenant iisponible');
        } else {
            $deliveryMan->setIsOnline(false);
            $this->addFlash('succes', 'Votre livreur est maintenant indisponible');
        }

        $em->flush();

        return $this->redirectToRoute('board_delivery', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function addDeliveryManAction($context, $contextPk, Request $request)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();
        $deliveryMan = new DeliveryMan();

        $form = $this->createForm(new DeliveryManType(), $deliveryMan);
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                $alreadyExist = $em->getRepository('ClabDeliveryBundle:DeliveryMan')->findOneBy(array(
                    'phone' => $form->getData()->getPhone(),
                ));
                if (!is_null($alreadyExist)) {
                    $deliveryMan->setName($alreadyExist->getName());
                    $deliveryMan->setCode($alreadyExist->getCode());
                    $deliveryMan->setPhone($alreadyExist->getPhone());
                } else {
                    $deliveryMan->setIsOnline(true);
                    $deliveryMan->setRestaurant($this->get('board.helper')->getProxy());
                    $deliveryMan->setPhone($form->getData()->getPhone());
                    $deliveryMan->setName($form->getData()->getName());
                    $deliveryMan->setIsDeleted(false);
                    $user = new User();
                    $user->setUsername($form->getData()->getName());
                    $user->setEmail($form->getData()->getName().'@'.$deliveryMan->getPhone().'.com');
                    $user->addRole('ROLE_DELIVERYMAN');
                    $user->setPlainPassword($deliveryMan->getCode());
                    $user->setEnabled('true');

                    $digits = 4;
                    $deliveryMan->setCode(str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT));
                    $user->setPlainPassword($deliveryMan->getCode());
                    $deliveryMan->setUser($user);
                    $em->persist($user);
                    $em->persist($deliveryMan);
                }

                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Le livreur a bien été sauvegardée, sont
                code d\'accès est '.$deliveryMan->getCode());

                return $this->redirectToRoute('board_delivery', array('context' => $context, 'contextPk' => $contextPk));
            } else {
                $this->get('session')->getFlashBag()->add('notice', 'Erreur dans le formulaire');
            }
        }

        return $this->render('ClabBoardBundle:Delivery:add-delivery-man.html.twig',
            array_merge($this->get('board.helper')
                ->getParams(),
                array(
                    'form' => $form->createView(),
                )));
    }
}
