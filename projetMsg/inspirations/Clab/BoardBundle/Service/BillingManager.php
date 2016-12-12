<?php

namespace Clab\BoardBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\BoardBundle\Entity\Invoice;
use Clab\BoardBundle\Entity\OrderStatement;
use Clab\BoardBundle\Entity\SubscriptionInvoice;

class BillingManager
{
    protected $container;
    protected $em;
    protected $router;

    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
    }

    public function autoBilling()
    {
        $restaurants = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findByStatus(Restaurant::STORE_STATUS_ACTIVE, 6999);
        $count = 0;

        $month = date_create_from_format('d-m-Y', '01-02-2013');
        $currentMonth = date_create_from_format('d-m-Y', date('01-m-Y'));

        while ($month < $currentMonth) {
            foreach ($restaurants as $restaurant) {

                if($restaurant->needInvoiceForMonth($month))
                {
                    $start = clone($month);
                    $end = clone($start);
                    $end->modify('+1 month')->modify('-1 day');

                    $orders = $this->em->getRepository('ClabShopBundle:OrderDetail')->findAllByRestaurantBetweenDate($restaurant, $start, $end);

                    $ordersToBill = new \Doctrine\Common\Collections\ArrayCollection();
                    foreach ($orders as $order) {
                        if(!$order->getInvoice()) {
                            $ordersToBill->add($order);
                        }
                    }

                    $invoice = new Invoice();
                    $invoice->setRestaurant($restaurant);
                    $invoice->setStart($start);
                    $invoice->setFixedAmount($restaurant->getSubscription()->getAmount());
                    if($restaurant->getSubscription()->getStartDate()->format('m-Y') == $month->format('m-Y')) {
                        $invoice->setStart($restaurant->getSubscription()->getStartDate());
                        $days = (int) $end->format('day') - (int) $restaurant->getSubscription()->getStartDate()->format('d') + 1;
                        $ratio = $days  / (int) $end->format('day');
                        $invoice->setFixedAmount(round($restaurant->getSubscription()->getAmount() * $ratio, 2));
                    }
                    $invoice->setEnd($end);

                    foreach ($ordersToBill as $order) {
                        $order->setInvoice($invoice);
                    }

                    $invoice->setName('Facture ' . $restaurant->getName());

                    $this->em->persist($invoice);

                    $count++;
                }
            }
            $month->modify('+1 month');
        }
        $this->em->flush();
        return $count;
    }

    // generate order statement, launched daily
    public function autoOrderStatement($restaurant = null, $timestamp = null)
    {
        // fetch active restaurants
        if($restaurant) {
            $restaurants = array($restaurant);
        } else {
            $restaurants = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findByStatus(Restaurant::STORE_STATUS_ACTIVE, 6999);
        }

        $weeks = $this->getWeeksPlanning();

        if($timestamp && array_key_exists($timestamp, $weeks)) {
            $start = $weeks[$timestamp]['start'];
            $end = $weeks[$timestamp]['end'];
        } else {
            $start = date_create('Monday last week');
            $end = clone($start);
            $end->modify('+1 week')->modify('-1 second');
        }

        $count = 0;

        foreach ($restaurants as $restaurant) {

            // fetch closed orders from past week 
            $orders = $this->em->getRepository('ClabShopBundle:OrderDetail')->findAllByRestaurantBetweenDate($restaurant, $start, $end);

            // if order not already linked to another order statement
            $ordersToBill = new \Doctrine\Common\Collections\ArrayCollection();
            foreach ($orders as $order) {
                if(!$order->getOrderStatement()) {
                    $ordersToBill->add($order);
                }
            }

            $orderStatement = $this->em->getRepository('ClabBoardBundle:OrderStatement')
                ->findOneBy(array('restaurant' => $restaurant, 'startDate' => $start));

            if(!$orderStatement) {
                $orderStatement = new OrderStatement();
                $orderStatement->setRestaurant($restaurant);
                $orderStatement->setStartDate(clone($start));
                $orderStatement->setEndDate(clone($end));

                if(count($orderStatement->getOrders()) == 0) {
                    $orderStatement->setAdminValidation(true);
                }

                $this->em->persist($orderStatement);
            }

            foreach ($ordersToBill as $order) {
                $order->setOrderStatement($orderStatement);
            }

            $parents = $this->em->getRepository('ClabBoardBundle:OrderStatement')
                ->findBy(array('restaurant' => $restaurant, 'status' => OrderStatement::ORDERSTATEMENT_STATUS_HOLD));

            foreach ($parents as $parent) {
                if($parent->getStartDate() < $orderStatement->getStartDate()) {
                    $orderStatement->addParent($parent);
                }
            }

            $count++;
        }

        $this->em->flush();
        return $count;
    }

    // generate subscription invoice, launched daily
    public function autoSubscriptionInvoice($restaurant = null, $date = null)
    {
        // fetch all active restaurants
        if($restaurant) {
            $restaurants = array($restaurant);
        } else {
            $restaurants = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findByStatus(Restaurant::STORE_STATUS_ACTIVE, 6999);
        }

        // if date not specified, set it to today
        if($date) {
            $today = $date;
        } else {
            $today = date_create('today');
        }

        // day of the month 1->31
        $todayDay = (int) $today->format('j');
        // month of the year 1->12
        $todayMonth = (int) $today->format('n');
        // day in the month 28->31
        $dayInMonth = (int) $today->format('t');

        $tomorrow = date_create('tomorrow');

        $count = 0;

        foreach ($restaurants as $restaurant) {


            // foreach restaurants who have begun their subscription
            // @todo free month and order statement ?
            if($restaurant->getCurrentSubscription() && $restaurant->getCurrentSubscription()->getBillingStart() <= $today) {

                $subscription = $restaurant->getCurrentSubscription();

                if($restaurant->getCurrentSubscription()->getEndDate() == $tomorrow) {
                    $subscriptionEndDate = clone($restaurant->getCurrentSubscription()->getEndDate());
                    $subscriptionEndDate->modify('+1 year');
                    $restaurant->getCurrentSubscription()->setEndDate($subscriptionEndDate);
                }

                // billing day of the month 1->31
                $subscriptionBilllingDay = (int) $subscription->getBillingStart()->format('j');
                // billing month of the year 1->12
                $subscriptionBilllingMonth = (int) $subscription->getBillingStart()->format('n');

                // fetch order statement of past month
                $orderStatementStart = clone($today);
                $orderStatementStart->modify('-1 month');
                $orderStatementEnd = clone($today);
                $orderStatementEnd->modify('-1 second');

                $orderStatements = $this->em->getRepository('ClabBoardBundle:OrderStatement')
                    ->findAllByRestaurantBetweenDate($restaurant, $orderStatementStart, $orderStatementEnd);

                // if it's billing day : day of month = billing day of month or month shorter
                if($subscriptionBilllingDay == $todayDay || ($todayDay == $dayInMonth && $subscriptionBilllingDay > $dayInMonth)) {

                    // fetch potentially already created invoice
                    $subscriptionInvoice = $this->em->getRepository('ClabBoardBundle:SubscriptionInvoice')
                        ->findOneBy(array('restaurant' => $restaurant, 'startDate' => $today));

                    // if there is nothing, initiate one
                    if(!$subscriptionInvoice) {
                        $end = clone($today);

                        // specify length based on billing recurrency
                        if($subscription->getBillingRecurrency() == 12) {
                            $end->modify('+1 year')->modify('-1 sec');
                        } else {
                            $end->modify('+1 month')->modify('-1 sec');
                        }

                        $subscriptionInvoice = new SubscriptionInvoice();
                        $subscriptionInvoice->setRestaurant($restaurant);
                        $subscriptionInvoice->setStartDate(clone($today));
                        $subscriptionInvoice->setEndDate(clone($end));
                        $subscriptionInvoice->setSubscriptionType($subscription->getType());
                    }

                    // if we are on the case of annual billing and not in the billing month, create anyway but put price to 0 and reduce length to current month
                    // @todo handle 24 months recurrency
                    if($subscription->getBillingRecurrency() == 12 && $todayMonth !== $subscriptionBilllingMonth) {
                        $subscriptionInvoice->setPrice(0);
                        $end = clone($today);
                        $end->modify('+1 month')->modify('-1 sec');
                        $subscriptionInvoice->setEndDate(clone($end));
                    // else initiate price
                    } else {
                        $subscriptionInvoice->setPrice($subscription->getMonthAmount() * $subscription->getBillingRecurrency());
                    }

                    // link order statements
                    foreach ($orderStatements as $orderStatement) {
                        $orderStatement->setSubscriptionInvoice($subscriptionInvoice);
                    }

                    $count++;
                    $this->em->persist($subscriptionInvoice);
                }
            }
        }

        $this->em->flush();
        return $count;
    }


    // split year in week Monday->Sunday
    public function getWeeksPlanning()
    {
        $weeks = array();
        $start = date_create('first monday of January');
        $end = clone($start);
        $end->modify('+1 week')->modify('-1 second');
        $finalDay = date_create('first day of January next year midnight');
        $finalDay->modify('-1 second');

        while ($start < $finalDay) {
            $weeks[$start->getTimestamp()] = array('start' => clone($start), 'end' => clone($end));
            $start->modify('+1 week');
            $end->modify('+1 week');
        }

        return $weeks;
    }

    public function getMonthsPlanning()
    {
        $months = cal_info(0)['months'];
        $monthPlanning = array();
        foreach ($months as $month) {
            $date = date_create('first day of ' . $month . ' midnight');
            $monthPlanning[$date->getTimestamp()] = $date;
        }

        return $monthPlanning;
    }
}
