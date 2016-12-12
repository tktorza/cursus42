<?php

namespace Clab\DeliveryBundle\Service;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Clab\DeliveryBundle\Entity\Delivery;
use Clab\DeliveryBundle\Entity\DeliveryDay;
use Clab\DeliveryBundle\Entity\DeliverySchedule;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\VarDumper\VarDumper;

class DeliveryManager
{
    protected $em;
    protected $container;
    protected $timesheetManager;

    public function __construct(EntityManager $em, ContainerInterface $container)
    {
        $this->em = $em;
        $this->container = $container;
        $this->timesheetManager = $this->container->get('app_restaurant.timesheet_manager');
    }
    
    public function checkLocationApi($address, $deliveryDay)
    {
        $geocoder = $this->container->get('app_location.location_manager')->getGeocoder();
        $response = $geocoder->geocode($address);

        $results = $response->getResults();
        $location = $results[0];
        $coordinates = $location->getGeometry()->getLocation();
        $lat = $coordinates->getLatitude();
        $lng = $coordinates->getLongitude();

        $areas = $deliveryDay->getDeliverySchedule()->getAreas()->toArray();
        $IHAreas = array();
        $IHAreas['distance'] = null;

        foreach ($areas as $area) {
            $polyX = array();
            $polyY = array();

            foreach ($area->getPoints() as $point) {
                $polyX[] = $point[0];
                $polyY[] = $point[1];
            }

            $inPoly = $this->pointInPolygon(count($area->getPoints()), $polyX, $polyY, $lat, $lng);

            if ($inPoly) {
                $distance = $this->haversine($lat, $lng, $area->getCenterLat(), $area->getCenterLng());
                if ($IHAreas['distance'] == null || $IHAreas['distance'] > $distance) {
                    $IHAreas['distance'] = $distance;
                    $IHAreas['area'] = $area;
                }
            }
        }

        if (empty($IHAreas) || !isset($IHAreas['area'])) {
            return false;
        } else {
            return array('success' => true, 'lat' => $lat, 'lng' => $lng, 'area' => $IHAreas['area']);
        }
    }

    public function getCurrentDeliveryPeriod($restaurant)
    {
        return $this->em->getRepository('ClabDeliveryBundle:DeliveryPeriod')
            ->getCurrentForRestaurant($restaurant);
    }

    public function getWeekDayPlanning($restaurant, $deliverySchedule = null)
    {
        $params = array(
            'is_online' => true,
            'is_deleted' => false,
            'restaurant' => $restaurant,
            'day' => null,
        );

        if ($deliverySchedule) {
            $params['deliverySchedule'] = $deliverySchedule;
        }

        $deliveryDays = $this->em->getRepository('ClabDeliveryBundle:DeliveryDay')->findBy($params);

        $weekDayPlanning = array(1 => array(), 2 => array(), 3 => array(), 4 => array(), 5 => array(), 6 => array(), 7 => array());
        foreach ($deliveryDays as $deliveryDay) {
            if (in_array($deliveryDay->getWeekDay(), array_keys($weekDayPlanning))) {
                $weekDayPlanning[$deliveryDay->getWeekDay()][] = $deliveryDay;
            }
        }

        return $weekDayPlanning;
    }

    public function getDayPlanning($restaurant, $date = 'today')
    {
        if ($date == 'today') {
            $date = date_create('today');
        }

        $planning = $this->em->getRepository('ClabDeliveryBundle:DeliveryDay')->getDayPlanning($restaurant, $date);

        return $planning;
    }

    public function getNextDeliveryScheduleExtended(Restaurant $restaurant)
    {
        if (!$restaurant->getIsOpenDelivery()) {
            return;
        }

        $weekDayPlanning = $this->getWeekDayPlanning($restaurant);

        $now = date_create('now');
        $currentDay = (int) $now->format('N');

        $day = $currentDay + 1;
        if ($day == 8) {
            $day = 1;
        }
        while ($day != $currentDay) {
            if (count($weekDayPlanning[$day]) > 0) {
                return $day;
            }

            if ($day == 7) {
                $day = 1;
            } else {
                ++$day;
            }
        }

        return;
    }

    public function pointInPolygon($polySides, $polyX, $polyY, $x, $y)
    {
        $j = $polySides - 1;
        $oddNodes = 0;
        for ($i = 0; $i < $polySides; ++$i) {
            if ($polyY[$i] < $y && $polyY[$j] >= $y
                ||  $polyY[$j] < $y && $polyY[$i] >= $y) {
                if ($polyX[$i] + ($y - $polyY[$i]) / ($polyY[$j] - $polyY[$i]) * ($polyX[$j] - $polyX[$i]) < $x) {
                    $oddNodes = !$oddNodes;
                }
            }
            $j = $i;
        }

        return $oddNodes;
    }

    public function getSlotsForDay(Restaurant $restaurant, array $parameters = array())
    {
        $now = date_create('now');
        $firstSlot = clone($now);
        $firstSlot->modify('+15 minute');
VarDumper::dump($firstSlot);
        if (!$restaurant->getIsOpenDelivery()) {
            return array();
        }

        $timesheetManager = $this->container->get('app_restaurant.timesheet_manager');

        $planning = $this->getDayPlanning($restaurant, 'today');

        if (count($planning) == 0) {
            return false;
        }

        $slots = array();
        $slotFilter = array();
        $slotLength = 0;
        $IHAreas = array();
        $IHAreas['distance'] = null;

        foreach ($planning as $deliveryDay) {
            $start = clone($deliveryDay->getStart());

            $end = clone($deliveryDay->getEnd());
            $areas = $deliveryDay->getDeliverySchedule()->getAreas()->toArray();

            if (isset($parameters['latitude']) && isset($parameters['longitude'])) {
                foreach ($areas as $area) {
                    $polyX = array();//horizontal coordinates of corners
                    $polyY = array();
                    foreach ($area->getPoints() as $point) {
                        $polyX[] = $point[0];
                        $polyY[] = $point[1];
                    }
                    $inPoly = $this->pointInPolygon(count($area->getPoints()), $polyX, $polyY, $parameters['latitude'], $parameters['longitude']);

                    if ($inPoly) {
                        $distance = $this->haversine($parameters['latitude'], $parameters['longitude'], $area->getCenterLat(), $area->getCenterLng());
                        if ($IHAreas['distance'] == null || $IHAreas['distance'] < $distance) {
                            $IHAreas['distance'] = $distance;
                            $IHAreas['area'] = $area;
                        }
                    }
                }

                if (empty($IHAreas) || !isset($IHAreas['area'])) {
                    continue;
                }
                $session = new Session();
                if (!is_null($session->get('areaDelivery')) && !$session->get('areaDelivery')) {
                    $session->remove('areaDelivery');
                }
                $session->set('areaDelivery', $IHAreas['area']->getSlug());
                $slotLength = $IHAreas['area']->getSlotLength();
                $loop = 0;
                while ($start <= $end) {
                    $today = date_create('today');
                    $slotStart = date_create_from_format('d-m-Y G:i', $today->format('d-m-Y').' '.$start->format('G:i'));
                    if(!$loop) {
                        $slotStart->modify('+'.$slotLength.' min');
                    }
                    $slotEnd = clone($slotStart);
                    $slot = array(
                        'deliveryDay' => $deliveryDay,
                        'start' => $slotStart,
                        'end' => $slotEnd,
                        'slotLength' => $slotLength
                    );

                    if (!in_array(array('start' => $slot['start'], 'end' => $slot['end']), $slotFilter)) {
                        $slots[$slotStart->getTimestamp().'-'.$slotEnd->getTimestamp().'-'.$deliveryDay->getId()] = $slot;
                        $slotFilter[] = array('start' => $slot['start'], 'end' => $slot['end']);
                    }
                    $start->modify('+15 min');
                    $loop = 1;
                }
            }
        }


        ksort($slots);

        return $slots;
    }

    public function getDeliveryMen(Restaurant $restaurant)
    {
        $deliveryMen = $this->em->getRepository('ClabDeliveryBundle:DeliveryMan')->findBy(array('restaurant' => $restaurant, 'isOnline' => true));
        return $deliveryMen;
    }

    public function getDeliveriesForUser($user)
    {
        $deliveries = array();
        $deliveryMen = $this->em->getRepository('ClabDeliveryBundle:DeliveryMan')->findBy(array('user' => $user));

        foreach ($deliveryMen as $deliveryMan) {
            foreach ($deliveryMan->getDeliveries() as $delivery) {
                $deliveries[] = $delivery;
            }
        }

        return $deliveries;
    }

    public function createDelivery($order)
    {
        $delivery = new Delivery();
        $delivery->setOrder($order);
        $delivery->setState(Delivery::DELIVERY_STATE_INITIAL);
        $delivery->setRestaurant($order->getCart()->getRestaurant());
        $delivery->setCode(1234);
        $delivery->setCodeCustomer(1234);

        return $delivery;
    }

    public function createCustomDelivery(Restaurant $restaurant, $slot)
    {
        $delivery = new Delivery();
        $delivery->setState(Delivery::DELIVERY_STATE_INITIAL);
        $delivery->setRestaurant($restaurant);
        $delivery->setCode(1234);
        $delivery->setCodeCustomer(1234);

        $delivery->setStart($slot['start']);
        $delivery->setEnd($slot['end']);

        return $delivery;
    }

    public function checkLocation(Delivery $delivery, $deliveryDay)
    {
        $address = $delivery->getAddress();

        if (!$address->getLatitude() || !$address->getLongitude()) {
            $geocoder = $this->container->get('app_location.location_manager')->getGeocoder();
            $response = $geocoder->geocode($address->getStreet().' '.$address->getZip().' '.$address->getCity());

            if ($response->getStatus() == 'OK') {
                $results = $response->getResults();
                $location = $results[0];

                if (isset($location) && $location) {
                    $coordinates = $location->getGeometry()->getLocation();
                    $address->setLatitude($coordinates->getLatitude());
                    $address->setLongitude($coordinates->getLongitude());
                }
            } else {
                return array('success' => false, 'message' => 'Adresse introuvable');
            }
        }

        $pickupLatitude = $delivery->getRestaurant()->getAddress()->getLatitude();
        $pickupLongitude = $delivery->getRestaurant()->getAddress()->getLongitude();
        $areas = $deliveryDay->getDeliverySchedule()->getAreas()->toArray();
        $IHAreas = array();
        $IHAreas['distance'] = null;
        foreach ($areas as $area) {
            $polyX = array();//horizontal coordinates of corners
                $polyY = array();
            foreach ($area->getPoints() as $point) {
                $polyX[] = $point[0];
                $polyY[] = $point[1];
            }
            $inPoly = $this->pointInPolygon(count($area->getPoints()), $polyX, $polyY, $address->getLatitude(), $address->getLongitude());

            if ($inPoly) {
                $distance = $this->haversine($address->getLatitude(), $address->getLongitude(), $area->getCenterLat(), $area->getCenterLng());
                if ($IHAreas['distance'] == null || $IHAreas['distance'] > $distance) {
                    $IHAreas['distance'] = $distance;
                    $IHAreas['area'] = $area;
                }
            }
        }

        if (empty($IHAreas) || !isset($IHAreas['area'])) {
            return array('success' => false, 'message' => 'Le restaurant ne livre pas à cette adresse pour ce créneau');
        } else {
            return array('success' => true, 'message' => 'Ok');
        }
    }

    public function getDeliveryCarts(Restaurant $restaurant)
    {
        $deliveryCarts = $this->em->getRepository('ClabDeliveryBundle:DeliveryCart')
            ->findBy(array('restaurant' => $restaurant, 'is_online' => true, 'is_deleted' => false), array('min' => 'asc'));

        return $deliveryCarts;
    }

    public function getDeliveryCartForCart($cart)
    {
        $deliveryCarts = $this->em->getRepository('ClabDeliveryBundle:DeliveryCart')
            ->findBy(array('restaurant' => $cart->getRestaurant(), 'is_online' => true, 'is_deleted' => false), array('extra' => 'asc'));

        if ($cart->getDeliveryCart()) {
            $price = $cart->getBasePrice() - $cart->getDeliveryCart()->getExtra();
        } else {
            $price = $cart->getBasePrice();
        }

        foreach ($deliveryCarts as $deliveryCart) {
            if ((!$deliveryCart->getMin() || $price >= $deliveryCart->getMin())
                && (!$deliveryCart->getMax() || $price <= $deliveryCart->getMax())) {
                return $deliveryCart;
            }
        }

        return;
    }

    public function haversine($latFrom, $lonFrom, $latTo, $lonTo)
    {
        // convert from degrees to radians
      $latFrom = deg2rad($latFrom);
        $lonFrom = deg2rad($lonFrom);
        $latTo = deg2rad($latTo);
        $lonTo = deg2rad($lonTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
        cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * 6371000;
    }
}
