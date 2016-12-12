<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\RestaurantBundle\Entity\TimeSheet;
use Clab\LocationBundle\Entity\Address;
use Clab\BoardBundle\Form\Type\Planning\PlanningType;
use Clab\BoardBundle\Form\Type\Planning\TimesheetExtendedType;
use Clab\BoardBundle\Form\Type\Location\AddressType;
use Clab\BoardBundle\Form\Type\Foodtruck\SettingsOrderType;
use Symfony\Component\HttpFoundation\Request;

class PlanningController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function planningAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($this->get('board.helper')->getProxy()->isMobile()) {
            return $this->redirectToRoute('board_foodtruck_planning', array('contextPk' => $contextPk));
        }

        $timesheetManager = $this->get('app_restaurant.timesheet_manager');
        $timesheets = $em->getRepository('ClabRestaurantBundle:Timesheet')->findBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'type' => Timesheet::TIMESHEET_TYPE_CLASSIC));
        $upcomingClose = $timesheetManager->getUpcomingClose($this->get('board.helper')->getProxy());
        $upcomingEvent = $timesheetManager->getUpcomingEvent($this->get('board.helper')->getProxy());
        $days = array('MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY');

        $weekDays = array('MONDAY' => [], 'TUESDAY' => [], 'WEDNESDAY' => [], 'THURSDAY' => [], 'FRIDAY' => [], 'SATURDAY' => [], 'SUNDAY' => []);
        foreach ($timesheets as $timesheet) {
            foreach ($timesheet->getDays() as $day) {
                $weekDays[$day][$timesheet->getId()] = $timesheet;
            }
        }

        $form = $this->createForm(new PlanningType(array('weekDays' => $weekDays, 'upcomingEvent' => $upcomingEvent)), $this->get('board.helper')->getProxy());

        if ($form->handleRequest($request)->isValid()) {
            foreach ($days as $day) {
                $dayValue = $form->get('is_weekday_'.$day)->getData();

                if ($dayValue) {
                    $timesheets = $form->get('timesheets_'.$day)->getData();
                    foreach ($timesheets as $timesheet) {
                        $timesheet->setRestaurant($this->get('board.helper')->getProxy());
                        $timesheet->setDays(array($day));
                        $em->persist($timesheet);
                        unset($weekDays[$day][$timesheet->getId()]);
                    }
                }

                foreach ($weekDays[$day] as $timesheet) {
                    $em->remove($timesheet);
                }
            }

            $em->flush();

            if ($this->get('board.helper')->getProxy()->getSocialProfile() && $this->get('board.helper')->getProxy()->getSocialProfile()->getFacebookSynch()) {
                $this->get('clab_board.facebook_manager')->updateFacebookInfos($this->get('board.helper')->getProxy());
            }

            return $this->redirectToRoute('board_restaurant_planning', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParams(array(
            'form' => $form->createView(),
            'days' => $days,
            'upcomingClose' => $upcomingClose,
            'upcomingEvent' => $upcomingEvent,
        ));

        return $this->render('ClabBoardBundle:Planning:planning.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function closeAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $today = date_create('today');
        $tomorrow = date_create('tomorrow');

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->add('day', 'choice', array(
                'required' => true,
                'choices' => array(
                    1 => 'Aujourd\'hui',
                    2 => 'Demain',
                    3 => 'Un jour',
                    4 => 'Une période',
                ),
                'data' => 1,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('custom_day', 'datetime', array('required' => false, 'widget' => 'single_text', 'data' => date_create('today'), 'format' => 'dd/MM/yyyy'))
            ->add('start_day', 'datetime', array('required' => false, 'widget' => 'single_text', 'format' => 'dd/MM/yyyy'))
            ->add('end_day', 'datetime', array('required' => false, 'widget' => 'single_text', 'format' => 'dd/MM/yyyy'))
        ;

        $form = $formBuilder->getForm();

        $this->get('board.helper')->addParams(array(
            'form' => $form->createView(),
        ));

        if ($form->handleRequest($request)->isValid()) {
            $day = $form->get('day')->getData();
            $customDay = $form->get('custom_day')->getData();
            $startDay = $form->get('start_day')->getData();
            $endDay = $form->get('end_day')->getData();
            $start = date_create_from_format('G:i', '00:00');
            $end = date_create_from_format('G:i', '23:59');
            switch ($day) {
                case 1:
                    $timesheet = $this->createEvent(Timesheet::TIMESHEET_TYPE_CLOSED, $today, $today, $start, $end);
                    $em->persist($timesheet);
                    break;
                case 2:
                    $timesheet = $this->createEvent(Timesheet::TIMESHEET_TYPE_CLOSED, $tomorrow, $tomorrow, $start, $end);
                    $em->persist($timesheet);
                    break;
                case 3:
                    if ($customDay) {
                        $timesheet = $this->createEvent(Timesheet::TIMESHEET_TYPE_CLOSED, $customDay, $customDay, $start, $end);
                        $em->persist($timesheet);
                    }
                    break;
                case 4:
                    if ($startDay && $endDay) {
                        $timesheet = $this->createEvent(Timesheet::TIMESHEET_TYPE_CLOSED, $startDay, $endDay, $start, $end);
                        $em->persist($timesheet);
                    }
                    break;
                default:
                    break;
            }

            $em->flush();

            if ($this->get('board.helper')->getProxy()->isMobile()) {
                $this->container->get('clab_ttt.planning_print_manager')->planningPrint($this->get('board.helper')->getProxy());
            }

            return $this->redirectToRoute('board_restaurant_planning', array('contextPk' => $contextPk));
        }

        return $this->render('ClabBoardBundle:Planning:close.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function changeAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $today = date_create('today');
        $tomorrow = date_create('tomorrow');

        $formBuilder = $this->createFormBuilder();
        $formBuilder
            ->add('day', 'choice', array(
                'required' => true,
                'choices' => array(
                    1 => 'Aujourd\'hui',
                    2 => 'Demain',
                    3 => 'Un jour',
                    4 => 'Une période',
                ),
                'data' => 1,
                'expanded' => true,
                'multiple' => false,
            ))
            ->add('start', 'time', array('required' => true, 'widget' => 'single_text', 'data' => date_create_from_format('G:i', '10:00'), 'label' => 'Heure de début'))
            ->add('end', 'time', array('required' => true, 'widget' => 'single_text', 'data' => date_create_from_format('G:i', '20:00'), 'label' => 'Heure de fin'))
            ->add('custom_day', 'datetime', array('required' => false, 'widget' => 'single_text', 'data' => date_create('today'), 'format' => 'dd/MM/yyyy'))
            ->add('start_day', 'datetime', array('required' => false, 'widget' => 'single_text', 'format' => 'dd/MM/yyyy'))
            ->add('end_day', 'datetime', array('required' => false, 'widget' => 'single_text', 'format' => 'dd/MM/yyyy'))
        ;

        $form = $formBuilder->getForm();

        $this->get('board.helper')->addParams(array(
            'form' => $form->createView(),
        ));

        if ($form->handleRequest($request)->isValid()) {
            $day = $form->get('day')->getData();
            $customDay = $form->get('custom_day')->getData();
            $startDay = $form->get('start_day')->getData();
            $endDay = $form->get('end_day')->getData();
            $start = $form->get('start')->getData();
            $end = $form->get('end')->getData();
            switch ($day) {
                case 1:
                    $timesheet = $this->createEvent(Timesheet::TIMESHEET_TYPE_EVENT, $today, $today, $start, $end);
                    $em->persist($timesheet);
                    break;
                case 2:
                    $timesheet = $this->createEvent(Timesheet::TIMESHEET_TYPE_EVENT, $tomorrow, $tomorrow, $start, $end);
                    $em->persist($timesheet);
                    break;
                case 3:
                    if ($customDay) {
                        $timesheet = $this->createEvent(Timesheet::TIMESHEET_TYPE_EVENT, $customDay, $customDay, $start, $end);
                        $em->persist($timesheet);
                    }
                    break;
                case 4:
                    if ($startDay && $endDay) {
                        $timesheet = $this->createEvent(Timesheet::TIMESHEET_TYPE_EVENT, $startDay, $endDay, $start, $end);
                        $em->persist($timesheet);
                    }
                    break;
                default:
                    break;
            }

            $em->flush();

            return $this->redirectToRoute('board_restaurant_planning', array('contextPk' => $contextPk));
        }

        return $this->render('ClabBoardBundle:Planning:change.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction($contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($id) {
            $timesheet = $em->getRepository('ClabRestaurantBundle:Timesheet')->findOneBy(array('id' => $id, 'restaurant' => $this->get('board.helper')->getProxy()));
        } else {
            $timesheet = new TimeSheet();
        }

        $type = (int) $this->getRequest()->get('type');
        if ($type == TimeSheet::TIMESHEET_TYPE_EVENT) {
            $timesheet->setType($type);
            if ($this->getRequest()->get('start') && $this->getRequest()->get('end') && !$timesheet->getId()) {
                $start = date_create('now');
                $start->setTimeStamp($this->getRequest()->get('start'));
                $end = date_create('now');
                $end->setTimeStamp($this->getRequest()->get('end'));
                $timesheet->setStartDate($start);
                $timesheet->setStart($start);
                $timesheet->setEnd($end);
            }
        } else {
            $type = TimeSheet::TIMESHEET_TYPE_CLASSIC;
        }

        $form = $this->createForm(new TimesheetExtendedType(), $timesheet);

        if ($form->handleRequest($request)->isValid()) {
            $timesheet->setRestaurant($this->get('board.helper')->getProxy());

            if ($timesheet->getType() == TimeSheet::TIMESHEET_TYPE_EVENT && !$timesheet->getEndDate()) {
                $timesheet->setEndDate($timesheet->getStartDate());
            }

            $em->persist($timesheet);
            $em->flush();

            if ($this->get('board.helper')->getProxy()->isMobile()) {
                $this->container->get('clab_ttt.planning_print_manager')->planningPrint($this->get('board.helper')->getProxy());
            }

            return $this->redirectToRoute('board_restaurant_planning', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParams(array(
            'timesheet' => $timesheet,
            'form' => $form->createView(),
        ));

        return $this->render('ClabBoardBundle:Planning:edit.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deleteAction($contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($request->isMethod('POST')) {
            $timesheet = $em->getRepository('ClabRestaurantBundle:Timesheet')->findOneBy(array('id' => $id, 'restaurant' => $this->get('board.helper')->getProxy()));
            $em->remove($timesheet);
            $em->flush();
        }

        if ($this->get('board.helper')->getProxy()->isMobile()) {
            $this->container->get('clab_ttt.planning_print_manager')->planningPrint($this->get('board.helper')->getProxy());
        }

        if ($this->getRequest()->get('backUrl')) {
            return $this->redirect($this->getRequest()->get('backUrl'));
        }

        return $this->redirectToRoute('board_restaurant_planning', array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function foodtruckAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $em = $this->getDoctrine()->getManager();

        if (!$this->get('board.helper')->getProxy()->isMobile()) {
            return $this->redirectToRoute('board_restaurant_planning', array('contextPk' => $contextPk));
        }

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->get('board.helper')->getProxy(), '3 months', false);

        $this->get('board.helper')->addParams(array(
            'timesheets' => $em->getRepository('ClabRestaurantBundle:Timesheet')->findBy(array('restaurant' => $this->get('board.helper')->getProxy())),
            'planning' => $foodtruck->getPlanning(),
        ));

        $now = date_create('today');
        if (array_key_exists($now->getTimestamp(), $foodtruck->getPlanning())) {
            $events = $foodtruck->getPlanning()[$now->getTimestamp()];
            foreach ($events as $key => $event) {
                if ($event['type'] == 0) {
                    unset($events[$key]);
                }
            }
            $this->get('board.helper')->addParam('events', $events);
        } else {
            $this->get('board.helper')->addParam('events', null);
        }

        return $this->render('ClabBoardBundle:Planning:foodtruck.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function foodtruckListAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->get('board.helper')->getProxy(), '3 months', false);

        $this->get('board.helper')->addParams(array(
            'timesheets' => $em->getRepository('ClabRestaurantBundle:Timesheet')->findBy(array('restaurant' => $this->get('board.helper')->getProxy())),
            'planning' => $foodtruck->getPlanning(),
        ));

        return $this->render('ClabBoardBundle:Planning:foodtruckList.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function calendarDetailAction($contextPk, Request $request)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->find($this->get('board.helper')->getProxy()->getSlug());
        $this->get('board.helper')->addParam('planning', $foodtruck->getPlanning());

        return $this->rener('ClabBoardBundle:Planning:calendar.html.twig', array(
            'planning' => $foodtruck->getPlanning(),
        ));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function createAddressAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $address = new Address();
        $form = $this->createForm(new AddressType(true), $address);

        if ($form->handleRequest($request)->isValid()) {
            $this->get('board.helper')->getProxy()->addRecurrentAddress($address);
            $em->persist($address);
            $em->flush();

            return $this->redirectToRoute('board_foodtruck_planning', array('contextPk' => $contextPk));
        }

        return $this->render('ClabBoardBundle:Planning:createAddress.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'address' => $address,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editEventAction($contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($id) {
            $timesheet = $em->getRepository('ClabRestaurantBundle:Timesheet')->findOneBy(array('id' => $id, 'restaurant' => $this->get('board.helper')->getProxy()));
        } else {
            $timesheet = new TimeSheet();
        }

        if (!$timesheet->getId()) {
            $timesheet->setType(Timesheet::TIMESHEET_TYPE_EVENT);
            $date = $this->getRequest()->get('date');

            if ($date) {
                $date = date_create_from_format('Y-m-d', $date);
                $timesheet->setStartDate($date)->setEndDate($date);
            }
        }

        $flow = $this->get('clab_board.form.flow.event');
        $flow->bind($timesheet);

        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {
                $timesheet->setRestaurant($this->get('board.helper')->getProxy());

                if ($timesheet->getType() == TimeSheet::TIMESHEET_TYPE_EVENT && !$timesheet->getEndDate()) {
                    $timesheet->setEndDate($timesheet->getStartDate());
                }

                $timesheet->setDays($form->get('days')->getData());

                $em->persist($timesheet);
                $em->flush();

                $flow->reset();

                if ($this->get('board.helper')->getProxy()->isMobile()) {
                    $this->container->get('clab_ttt.planning_print_manager')->planningPrint($this->get('board.helper')->getProxy());
                }

                return $this->redirectToRoute('board_restaurant_planning', array('contextPk' => $contextPk));
            }
        }

        return $this->render('ClabBoardBundle:Foodtruck:editEvent.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'timesheet' => $timesheet,
            'form' => $form->createView(),
            'flow' => $flow,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function calendarEditAction($contextPk, $date, $id)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $em = $this->getDoctrine()->getManager();

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->get('board.helper')->getProxy(), '3 months', false);
        $planning = $foodtruck->getPlanning();

        $timesheet = null;
        if (isset($planning[$date])) {
            foreach ($planning[$date] as $event) {
                if ($event['timesheetId'] == $id) {
                    $timesheet = $em->getRepository('ClabRestaurantBundle:Timesheet')->findOneBy(array('id' => $event['timesheetId'], 'restaurant' => $this->get('board.helper')->getProxy()));
                }
            }
        }

        if ($timesheet) {
            $this->get('board.helper')->addParams(array(
                'date' => $date,
                'timesheet' => $timesheet,
            ));

            return $this->render('ClabBoardBundle:Foodtruck:calendarEdit.html.twig', $this->get('board.helper')->getParams());
        }

        throw $this->createNotFoundException();
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function quickCloseAction($contextPk, $date, $id)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->get('board.helper')->getProxy(), '3 months', false);
        $planning = $foodtruck->getPlanning();

        $timesheet = null;
        if (isset($planning[$date])) {
            foreach ($planning[$date] as $event) {
                if ($event['timesheetId'] == $id) {
                    $this->get('clab_ttt.validation_manager')->quickClose($this->get('board.helper')->getProxy(), $event);
                }
            }
        }

        return $this->redirectToRoute('board_restaurant_planning', array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function settingsOrderAction($contextPk,  Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $form = $this->createForm(new SettingsOrderType(), $this->get('board.helper')->getProxy());

        if ($form->handleRequest($request)->isValid()) {
            $em->flush();

            return $this->redirectToRoute('board_foodtruck_planning', array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Foodtruck:settings-order.html.twig', $this->get('board.helper')->getParams());
    }

    public function createEvent($type, $startDate, $endDate, $start, $end)
    {
        $timesheet = new TimeSheet();
        $timesheet->setRestaurant($this->get('board.helper')->getProxy());
        $timesheet->setType($type);

        $timesheet->setStartDate($startDate);
        $timesheet->setEndDate($endDate);
        $timesheet->setStart($start);
        $timesheet->setEnd($end);

        return $timesheet;
    }
}
