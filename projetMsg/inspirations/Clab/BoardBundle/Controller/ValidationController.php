<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\RestaurantBundle\Entity\TimesheetValidation;
use Clab\RestaurantBundle\Entity\TimeSheet;

class ValidationController extends Controller
{
    public function validateAction($slug, $date, $start, $hash = null)
    {
        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->find($slug, true);
        $validationManager = $this->get('clab_ttt.validation_manager');

        if (array_key_exists($date, $foodtruck->getPlanning())) {
            foreach ($foodtruck->getPlanning()[$date] as $event) {
                if ($event['start']->getTimestamp() == $start) {
                    if ($hash == sha1($slug.$event['timestamp'].$event['start']->getTimestamp())
                        || $foodtruck->getRestaurant()->hasManager($this->getUser())) {
                        if ($this->getRequest()->get('message')) {
                            $validationManager->validate($foodtruck, $event, $this->getRequest()->get('message'));
                        } else {
                            $validationManager->validate($foodtruck, $event);
                        }

                        if (!$this->getUser() || !$foodtruck->getRestaurant()->hasManager($this->getUser())) {
                            return $this->redirectToRoute('board_validation_done', array('slug' => $foodtruck->getSlug()));
                        }

                        return $this->redirectToRoute('board_validation_console', array('contextPk' => $foodtruck->getRestaurant()->getSlug(), 'validation_done' => 'ok'));
                    }
                }
            }
        }

        return $this->redirectToRoute('board_dashboard');
    }

    public function closeAction($slug, $date, $start, $hash = null)
    {
        $em = $this->getDoctrine()->getManager();
        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->find($slug, true);
        $validationManager = $this->get('clab_ttt.validation_manager');

        if (array_key_exists($date, $foodtruck->getPlanning())) {
            foreach ($foodtruck->getPlanning()[$date] as $event) {
                if ($event['start']->getTimestamp() == $start) {
                    if ($hash == sha1($slug.$event['timestamp'].$event['start']->getTimestamp())
                        || $foodtruck->getRestaurant()->hasManager($this->getUser())) {
                        $timesheet = new TimeSheet();
                        $timesheet->setType(TimeSheet::TIMESHEET_TYPE_CLOSED);

                        $date = date_create('now');
                        $date->setTimeStamp($event['timestamp']);
                        $timesheet->setStartDate($date)->setEndDate($date);

                        $timesheet->setStart($event['start']);
                        $timesheet->setEnd($event['end']);

                        $timesheet->setRestaurant($foodtruck->getRestaurant());

                        try {
                            $validationRequests = $this->getDoctrine()->getManager()->getRepository('ClabTTTBundle:ValidationRequest')
                                    ->findBy(array(
                                        'restaurant' => $foodtruck->getRestaurant(),
                                        'date' => $date,
                                        'start' => $event['start'],
                                    ));
                            foreach ($validationRequests as $validationRequest) {
                                $validationRequest->setIsValidated(true);
                            }

                            $validations = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:TimesheetValidation')
                                    ->findBy(array(
                                        'restaurant' => $foodtruck->getRestaurant(),
                                        'date' => $date,
                                        'start' => $event['start'],
                                    ));
                            foreach ($validations as $validation) {
                                $em->remove($validation);
                            }
                        } catch (\Exception $e) {
                        }

                        $em->persist($timesheet);
                        $em->flush();

                        $this->container->get('clab_ttt.planning_print_manager')->planningPrint($foodtruck->getRestaurant());

                        if (!$this->getUser() || !$foodtruck->getRestaurant()->hasManager($this->getUser())) {
                            return $this->redirectToRoute('board_validation_done', array('slug' => $foodtruck->getSlug()));
                        }

                        return $this->redirectToRoute('board_validation_console', array('contextPk' => $foodtruck->getRestaurant()->getSlug(), 'validation_done' => 'close'));
                    }
                }
            }
        }

        return $this->redirectToRoute('board_dashboard');
    }

    public function validationDoneAction()
    {
        $this->get('session')->getFlashBag()->add('success', 'Merci de votre confirmation !');

        return $this->render('ClabBoardBundle:Validation:validationDone.html.twig');
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function cancelValidationAction($contextPk, $date, $start)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->get('board.helper')->getProxy(), '3 days', false);

        $day = date_create('now');
        $day->setTimestamp($date);
        $startTime = date_create('now');
        $startTime->setTimestamp($start);

        $validations = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:TimesheetValidation')
            ->findBy(array('restaurant' => $foodtruck->getRestaurant()->getId(), 'date' => $day, 'start' => $startTime));

        foreach ($validations as $validation) {
            $em->remove($validation);
        }

        try {
            $validationRequests = $this->getDoctrine()->getManager()->getRepository('ClabTTTBundle:ValidationRequest')
                    ->findBy(array(
                        'restaurant' => $foodtruck->getRestaurant(),
                        'date' => $day,
                        'start' => $startTime,
                    ));

            foreach ($validationRequests as $validationRequest) {
                $validationRequest->setIsValidated(false);
            }
        } catch (\Exception $e) {
        }

        $em->flush();

        $this->container->get('clab_ttt.planning_print_manager')->planningPrint($foodtruck->getRestaurant());

        return $this->redirectToRoute('board_validation_console', array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function publishValidationAction($contextPk, $date, $start)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->get('board.helper')->getProxy(), '3 days', false);

        if (array_key_exists($date, $foodtruck->getPlanning())) {
            foreach ($foodtruck->getPlanning()[$date] as $event) {
                if ($event['start']->getTimestamp() == $start && isset($event['validation']) && $event['validation']) {
                    $validationManager = $this->get('clab_ttt.validation_manager');

                    $message = null;
                    if ($this->getRequest()->get('message')) {
                        $message = $this->getRequest()->get('message');
                    }

                    $validationManager->publishValidation($foodtruck, $event, $message);
                }
            }
        }

        return $this->redirectToRoute('board_validation_console', array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function customPublishAction($contextPk, $date, $start)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->get('board.helper')->getProxy(), '3 days', false);

        $this->get('board.helper')->addParams(array('foodtruck' => $foodtruck, 'date' => $date, 'start' => $start));

        if (array_key_exists($date, $foodtruck->getPlanning())) {
            foreach ($foodtruck->getPlanning()[$date] as $event) {
                if ($event['start']->getTimestamp() == $start) {
                    $this->get('board.helper')->addParam('event', $event);
                }
            }
        }

        return $this->render('ClabBoardBundle:Validation:customPublish.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function consoleAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->get('board.helper')->getProxy(), '3 days', false);

        $now = date_create('today');
        if (array_key_exists($now->getTimestamp(), $foodtruck->getPlanning())) {
            $events = $foodtruck->getPlanning()[$now->getTimestamp()];
        } else {
            $events = array();
        }

        if ($this->getRequest()->get('validation_done') && $this->getRequest()->get('validation_done') == 'ok') {
            $this->get('session')->getFlashBag()->add('success', 'Votre présence est bien validée');
        } elseif ($this->getRequest()->get('validation_done') && $this->getRequest()->get('validation_done') == 'close') {
            $this->get('session')->getFlashBag()->add('success', 'Votre fermeture est bien prise en compte');
        }

        foreach ($events as $key => $event) {
            $hash = sha1($foodtruck->getRestaurant()->getSlug().$event['timestamp'].$event['start']->getTimestamp());

            $closeUrl = $this->generateUrl('board_validation_close', array(
                'slug' => $foodtruck->getRestaurant()->getSlug(),
                'date' => $event['timestamp'],
                'start' => $event['start']->getTimestamp(),
            ), true);

            $events[$key]['closeUrl'] = $closeUrl;
        }

        $this->get('board.helper')->addParams(array('foodtruck' => $foodtruck, 'events' => $events));

        list($validationCount, $validationRequestCount, $validationPercent) = $this->get('clab_ttt.validation_manager')->getValidationPercent($this->get('board.helper')->getProxy());
        $this->get('board.helper')->addParams(array(
            'validationCount' => $validationCount,
            'validationRequestCount' => $validationRequestCount,
            'validationPercent' => $validationPercent,
        ));

        return $this->render('ClabBoardBundle:Validation:console.html.twig', $this->get('board.helper')->getParams());
    }
}
