<?php

namespace Clab\RestaurantBundle\Manager;

use Doctrine\ORM\EntityManager;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\TimeSheet;
use Clab\ShopBundle\Entity\Cart;
use Clab\ShopBundle\Entity\OrderDetail;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\VarDumper\VarDumper;

class TimeSheetManager
{
    const IS_UNDER_START_END = 0;
    const CLOSED = 1;
    const SOON_CLOSED = 2;
    const SOON_OPENED = 3;

    protected $em;
    protected $repository;
    protected $statusText;

    /**
     * @param EntityManager $em
     *                          Constructor
     */
    public function __construct(EntityManager $em, $statusText)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('ClabRestaurantBundle:TimeSheet');
        $this->statusText = $statusText;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return array
     *               Get the planning of today
     */
    public function getTodayPlanning(Restaurant $restaurant)
    {
        if (is_null($restaurant->getTodayPlanning())) {
            $restaurant->setTodayPlanning($this->getDayPlanning($restaurant, date_create('today')));
        }

        return $restaurant->getTodayPlanning();
    }

    /**
     * @param Restaurant $restaurant
     * @param \DateTime  $day
     *
     * @return array
     *               Get a restaurant planning for a given restaurant
     */
    public function getDayPlanning(Restaurant $restaurant, \DateTime $day)
    {
        $events = $this->em->getRepository('ClabRestaurantBundle:TimeSheet')->getDayEvents($restaurant, $day);

        $planning = array();

        foreach ($events as $event) {
            $today = new \DateTime('today');
            $eventStart = date_create_from_format('d-m-Y G:i', $day->format('d-m-Y').' '.$event->getStart()->format('G:i'));
            $eventEnd = date_create_from_format('d-m-Y G:i', $day->format('d-m-Y').' '.$event->getEnd()->format('G:i'));
            //pour chaque évènement déjà existant
            $ok = true;
            foreach ($planning as $key => $planningEvent) {
                //si il y a un croisement
                if (!($planningEvent['start'] > $eventEnd && $planningEvent['end'] > $eventEnd) && !($planningEvent['start'] < $eventStart && $planningEvent['end'] < $eventStart)) {
                    $ok = false;
                    // on passe par dessus selon les règles de prio
                    if ($planningEvent['type'] == TimeSheet::TIMESHEET_TYPE_CLASSIC && $event->getType() == TimeSheet::TIMESHEET_TYPE_EVENT
                        || $planningEvent['type'] == TimeSheet::TIMESHEET_TYPE_CLASSIC && $event->getType() == TimeSheet::TIMESHEET_TYPE_CLOSED
                        || $planningEvent['type'] == TimeSheet::TIMESHEET_TYPE_EVENT && $event->getType() == TimeSheet::TIMESHEET_TYPE_CLOSED) {
                        unset($planning[$key]);
                        $ok = true;
                    }
                }
            }

            //si ok on ajoute
            if ($ok) {
                $planning[] = array(
                   'id' => $event->getId(),
                   'type' => $event->getType(),
                   'address' => $event->getAddress() ? $event->getAddress() : $event->getRestaurant()->getAddress(),
                   'start' => $eventStart,
                   'end' => $eventEnd,
                   'timestamp' => $day->getTimeStamp(),
                   'timesheetId' => $event->getId(),
                   'place' => $event->getPlace(),
                   'event' => $event->getEvent(),
                   'maxPreorderTime' => $event->getMaxPreorderTime(),
                );
            }
        }

        return $planning;
    }

    /**
     * @param Restaurant $restaurant
     * @param null       $duration
     * @param array      $options
     *
     * @return array
     *               Get the planing for a duration (optionnal), for a given restaurant
     */
    public function getPlanning(Restaurant $restaurant, $duration = null, array $options = array())
    {
        $planningStart = new \DateTime('today');
        $planningEnd = clone($planningStart);
        if ($duration && is_string($duration)) {
            $planningEnd->modify('+'.$duration);
        } else {
            $planningEnd->modify('+3 month');
        }

        $timesheets = $this->repository->findTimesheetsForDates($restaurant, $planningStart, $planningEnd);

        $days = array();

        $place = isset($options['place']) ? $options['place'] : null;
        $placeEvent = isset($options['event']) ? $options['event'] : null;

        foreach ($timesheets as $timesheet) {
            if (($timesheet->getType() == TimeSheet::TIMESHEET_TYPE_EVENT || $timesheet->getType() == TimeSheet::TIMESHEET_TYPE_CLOSED) && (!$timesheet->getStartDate() || !$timesheet->getEndDate())) {
                continue;
            }

            if ((!$place || $place == $timesheet->getPlace()) && (!$placeEvent || $placeEvent == $timesheet->getEvent())) {
                $start = date_create('today');

                if ($timesheet->getStartDate() > $start) {
                    $start = clone($timesheet->getStartDate());
                }

                $end = date_create('now');

                if ($duration && is_string($duration)) {
                    $end->modify('+'.$duration);
                } else {
                    $end->modify('+3 month');
                }

                if ($timesheet->getEndDate() && $timesheet->getEndDate() < $end) {
                    $end = clone($timesheet->getEndDate());
                }

                while ($start <= $end) {
                    if (in_array(strtoupper($start->format('l')), $timesheet->getDays())) {
                        $day = clone($start);
                        $eventStart = date_create_from_format('d-m-Y G:i', $day->format('d-m-Y').' '.$timesheet->getStart()->format('G:i'));

                        $newDay = clone($day);
                        if ($timesheet->getEnd() < $timesheet->getStart()) {
                            $newDay->modify('+1 day');
                        }
                        $eventEnd = date_create_from_format('d-m-Y G:i', $newDay->format('d-m-Y').' '.$timesheet->getEnd()->format('G:i'));

                        $ok = true;
                        // si des évènements existent ce jour là
                        if (array_key_exists($day->getTimeStamp(), $days)) {
                            foreach ($days[$day->getTimeStamp()] as $key => $event) {
                                // on vérifie si les horaires sont différentes, sinon on annule
                                if (!($event['start'] > $eventEnd && $event['end'] > $eventEnd) && !($event['start'] < $eventStart && $event['end'] < $eventStart)) {
                                    $ok = false;
                                    if ($event['type'] == TimeSheet::TIMESHEET_TYPE_CLASSIC && $timesheet->getType() == TimeSheet::TIMESHEET_TYPE_EVENT
                                        || $event['type'] == TimeSheet::TIMESHEET_TYPE_CLASSIC && $timesheet->getType() == TimeSheet::TIMESHEET_TYPE_CLOSED
                                        || $event['type'] == TimeSheet::TIMESHEET_TYPE_EVENT && $timesheet->getType() == TimeSheet::TIMESHEET_TYPE_CLOSED) {
                                        unset($days[$day->getTimeStamp()][$key]);
                                        $ok = true;
                                    }
                                }
                            }
                        }

                        if ($ok) {
                            $days[$day->getTimeStamp()][] = array(
                                'id' => $timesheet->getId(),
                                'type' => $timesheet->getType(),
                                'address' => $timesheet->getAddress() ? $timesheet->getAddress() : $timesheet->getRestaurant()->getAddress(),
                                'start' => $eventStart,
                                'end' => $eventEnd,
                                'place' => $timesheet->getPlace(),
                                'event' => $timesheet->getEvent(),
                                'timestamp' => $day->getTimeStamp(),
                                'timesheetId' => $timesheet->getId(),
                                'private' => $timesheet->isPrivate(),
                            );
                        }
                    }
                    $start->modify('+1 day');
                }
            }
        }

        ksort($days);

        return $days;
    }

    /**
     * @param Restaurant $restaurant
     * @param Cart|null  $cart
     *
     * @return array
     *               Get PreOrder slots for a restaurant, and optionnaly for a cart
     */
    public function getPreorderSlots(Restaurant $restaurant, Cart $cart = null, $parameters = array())
    {
        if (empty($restaurant->getTodayPlanning())) {
            $restaurant->setTodayPlanning($this->getTodayPlanning($restaurant));
        }

        if (isset($parameters['day'])) {
            $planning = $this->getDayPlanning($restaurant, $parameters['day']);
            $now = date_create('now');
            if($parameters['day'] > $now) {
                $now = $planning[0]['start'];
            }

        } else {
            $planning = $restaurant->getTodayPlanning();
            $now = date_create('now');
        }

        $firstSlot = clone($now);

        if ($cart) {
            $delay = max($cart->getExtraMakingTime(), $restaurant->getOrderDelay());
        } elseif ($restaurant->getOrderDelay()) {
            $delay = $restaurant->getOrderDelay();
        } else {
            $delay = 0;
        }

        $firstSlot->modify('+'.$delay.' minute');

        $slots = array();
        foreach ($planning as $event) {
            if ($event['type'] == TimeSheet::TIMESHEET_TYPE_CLOSED) {
                break;
            }
            $start = clone($event['start']);
            $end = clone($event['end']);
            $maxPreorderTimeElapsed = false;

            if ($restaurant->getIsMobile()) {
                if ($restaurant->getOrderStart() !== null) {
                    if ($restaurant->getOrderStart() == 0) {
                        $maxPreorderTime = clone($event['start']);
                    } else {
                        $maxPreorderTime = clone($event['end']);
                        $maxPreorderTime->modify((string) $restaurant->getOrderStart().' hour');
                    }
                } else {
                    $maxPreorderTime = null;
                }
            } else {
                $maxPreorderTime = $event['maxPreorderTime'];
            }

            if ($maxPreorderTime && $maxPreorderTime->format('H:i') < $now->format('H:i')) {
                $maxPreorderTimeElapsed = true;
            }

            if (!$maxPreorderTimeElapsed) {
                while ($start <= $end) {
                    if (!in_array($start, $slots)
                        //&& !in_array($start->format('H:i'), $fullSlots)
                        && $start >= $firstSlot
                    ) {
                        $slots[] = clone($start);
                    }
                    $start->modify('+5 minute');
                }
            }
        }
        sort($slots);

        return $slots;
    }

    /**
     * @param OrderDetail $order
     *
     * @return \Clab\LocationBundle\Entity\Address
     *                                             Get order location
     */
    public function getOrderLocation(OrderDetail $order)
    {
        $planning = $this->getDayPlanning($order->getRestaurant(), $order->getTime());

        foreach ($planning as $event) {
            if ($order->getTime() >= $event['start'] && $order->getTime() <= $event['end']) {
                return $event['address'];
            }
        }

        return $order->getRestaurant()->getAddress();
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return array
     *               Get the planning of a restaurant for a week
     */
    public function getWeekDayPlanning(Restaurant $restaurant)
    {
        $timesheets = $this->em->getRepository('ClabRestaurantBundle:Timesheet')->findBy(array('restaurant' => $restaurant, 'type' => Timesheet::TIMESHEET_TYPE_CLASSIC));

        $weekDayPlanning = array();
        foreach ($timesheets as $timesheet) {
            foreach ($timesheet->getDays() as  $day) {
                $weekDayPlanning[$day][$timesheet->getId()]['start'] = $timesheet->getStart();
                $weekDayPlanning[$day][$timesheet->getId()]['end'] = $timesheet->getEnd();
            }
        }

        return $weekDayPlanning;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return array
     *               Get the planning of a restaurant for a week
     */
    public function getFlatPlanning(Restaurant $restaurant)
    {
        $timesheets = $this->em->getRepository('ClabRestaurantBundle:Timesheet')->findBy(array('restaurant' => $restaurant, 'type' => Timesheet::TIMESHEET_TYPE_CLASSIC));
        $weekDayPlanning = array();

        foreach ($timesheets as $timesheet) {
            foreach ($timesheet->getDays() as $day) {
                $start = $timesheet->getStart()->format('H:i');
                $end = $timesheet->getEnd()->format('H:i');

                $weekDayPlanning[$day]['start'] = $start;
                $weekDayPlanning[$day]['end'] = $end;
                $weekDayPlanning[$day]['amplitude'] = sprintf('%s - %s', $start, $end);
            }
        }

        return $weekDayPlanning;
    }

    /**
     * @param $planning
     * @return string
     */
    public function getOpenedStatus($planning)
    {
        if (empty($planning)) {
            return 1;
        }

        $now = new \DateTime('now');
        $nowHourMinute = $now->format('Hi');

        $day = strtoupper($now->format('l'));

        if (!array_key_exists($day, $planning)) {
            return $this->statusText[$this::CLOSED];
        }

        $planningDay = $planning[$day];
        list($hourOpen, $minuteOpen) = explode(':', $planningDay['start']);
        list($hourClose, $minuteClose) = explode(':', $planningDay['end']);
        $startHourMinute = str_replace($planningDay['start'], ':', '');
        $endHourMinute = str_replace($planningDay['end'], ':', '');

        $isUnderStartEnd = $nowHourMinute > $startHourMinute && $nowHourMinute < $endHourMinute;
        $soonClosed = $hourClose == '00' && $minuteClose <= 30 && $minuteClose >= 0;
        $soonOpened = $hourOpen == '00' && $minuteOpen <= 30 && $minuteOpen >= 0;

        switch (true) {
            case $isUnderStartEnd:
                $opened = $this::IS_UNDER_START_END;
                break;
            case $soonClosed:
                $opened = $this::SOON_CLOSED;
                break;
            case $soonOpened:
                $opened = $this::SOON_OPENED;
                break;
            default:
                $opened = $this::CLOSED;
        }

        return $this->statusText[$opened];
    }

    public function isRestaurantOpen(Restaurant $restaurant, $time = null)
    {
        $planning = $this->getTodayPlanning($restaurant);

        if (is_null($time)) {
            $time = new \DateTime('now');
        }

        foreach ($planning as $event) {
            if ($event['type'] !== 0) {
                if ($event['start'] <= $time && $event['end'] >= $time) {
                    return 1;
                } elseif ($time->format('h:i') < $event['end']->modify('-30 min')->format('h:i')) {
                    return 2;
                } else {
                    return 0;
                }
            }
        }

        return true;
    }

    public function isRestaurantOpenForOrder(Restaurant $restaurant, $time = null)
    {
        $planning = $this->getTodayPlanning($restaurant);

        if (is_null($time)) {
            $time = new \DateTime('now');
        }

        foreach ($planning as $event) {
            if ($event['type'] !== 0) {
                if ($event['end'] >= $time) {
                    $end = clone($event['end']);
                    //@todo a tester
                    if ($time->format('h:i') < $end->modify('-30 min')->format('h:i')) {
                        return 1;
                    } else {
                        return 2;
                    }
                }
            }
        }

        return 0;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return array
     *               Get next close time for a restaurant
     */
    public function getUpcomingClose(Restaurant $restaurant)
    {
        return $this->repository->getUpcomingClose($restaurant);
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return array
     *               Get next event of a restaurant
     */
    public function getUpcomingEvent(Restaurant $restaurant)
    {
        return $this->repository->getUpcomingEvent($restaurant);
    }
}
