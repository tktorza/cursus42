<?php

namespace Clab\BoardBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Clab\BoardBundle\Entity\Subscription;
use Clab\BoardBundle\Entity\SubscriptionInvoice;
use Clab\BoardBundle\Entity\SubscriptionTerms;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\OrderType;

class SubscriptionManager
{
    protected $container;
    protected $em;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function planUpgradable(Restaurant $restaurant)
    {
        $tttPlans = $this->em->getRepository('ClabRestaurantBundle:Plan')->findTTTPlan();
        $cePlans = $this->em->getRepository('ClabRestaurantBundle:Plan')->findClickEatPlan();
        $results = array();
        $subscriptions = $this->em->getRepository('ClabBoardBundle:Subscription')->findBy(array(
            'restaurant' => $restaurant,
            'type' => 0,
            'is_online' => true,
        ));

        if ($restaurant->isMobile() == true) {
            foreach ($subscriptions as $subscription) {
                if (substr($subscription->getPlan()->getStripePlanId(), 0, 3) == 'ttt' || substr($subscription->getPlan()->getStripePlanId(), 0, 3) == 'old') {
                    $priceSub = $subscription->getPlan()->getPrice();

                    foreach ($tttPlans as $tttPlan) {
                        if ($tttPlan->getPrice() > $priceSub) {
                            $results[] = $tttPlan->getStripePlanId();
                        }
                    }
                }
            }
        } else {
            foreach ($subscriptions as $subscription) {
                if (substr($subscription->getPlan()->getStripePlanId(), 0, 5) == 'click' || substr($subscription->getPlan()->getStripePlanId(), 0, 3) == 'old') {
                    $priceSub = $subscription->getPlan()->getPrice();
                    foreach ($cePlans as $cePlan) {
                        if ($cePlan->getPrice() > $priceSub) {
                            $results[] = $cePlan->getStripePlanId();
                        }
                    }
                }
            }
        }

        return $results;
    }
    public function getCurrentSubscription(Restaurant $restaurant)
    {
        $currentSubscription = null;
        foreach ($restaurant->getSubscriptions() as $subscription) {
            $currentSubscription = $subscription;
        }

        return $currentSubscription;
    }

    public function getNextSubscriptions($restaurant)
    {
        $subscriptions = array();
        $now = date_create('now');

        return $subscriptions;
    }

    public function getPreviousSubscriptions($restaurant)
    {
        $subscriptions = array();
        $now = date_create('now');

        foreach ($restaurant->getSubscriptions() as $subscription) {
        }

        return $subscriptions;
    }

    public function initSubscriptionTerms($restaurant)
    {
        if (!$restaurant->getSubscriptionTerms()) {
            $terms = new SubscriptionTerms();
            $terms->setLastEdit(date_create('now'));
            $restaurant->setSubscriptionTerms($terms);
            $terms->addRestaurant($restaurant);
            $this->em->persist($terms);
            $this->em->flush();
        }

        return $restaurant->getSubscriptionTerms();
    }

    public function initStartSubscription($restaurant)
    {
        if (!$restaurant->getStartSubscription()) {
            $subscription = new Subscription();
            $subscription->setType(Subscription::SUBSCRIPTION_TYPE_DEFAULT);
            $subscription->setMonthLength(12);
            $subscription->initPricing();
            $restaurant->setStartSubscription($subscription);
            $this->em->persist($subscription);
            $this->em->flush();
        }

        return $restaurant->getStartSubscription();
    }

    public function hasAccess($restaurant, $feature)
    {
        $subscription = $this->getCurrentSubscription($restaurant);

        switch ($feature) {
            case 'preorder':
                return $restaurant->hasOrderType(OrderType::ORDERTYPE_PREORDER);
                break;
            case 'delivery':
                return $restaurant->hasOrderType(OrderType::ORDERTYPE_DELIVERY);
                break;
            case 'website':
                return in_array('website', $restaurant->getDiscoverFeatures());
                //return $subscription && $subscription->hasModuleMultisite();
                break;
            case 'iframe':
                return in_array('iframe', $restaurant->getDiscoverFeatures());
                //return $subscription && $subscription->getType() >= 20;
                break;
            case 'app':
                return $subscription && $subscription->hasModuleAppleApp();
                break;
            case 'discount':
                return in_array('discount', $restaurant->getDiscoverFeatures());
                break;
            case 'share':
                return in_array('share', $restaurant->getDiscoverFeatures());
                break;
            case 'share_social_networks':
                return true;
                //return $subscription && $subscription->getType() >= 20;
                break;
            case 'synch':
                return in_array('synch', $restaurant->getDiscoverFeatures());
                break;
            case 'reporting':
                return in_array('reporting', $restaurant->getDiscoverFeatures());
                break;
            case 'analytics':
                return false;

                return in_array('analytics', $restaurant->getDiscoverFeatures());
                break;
            case 'category':
                return in_array('category', $restaurant->getDiscoverFeatures());
                break;
            case 'product':
                return in_array('product', $restaurant->getDiscoverFeatures());
                break;
            case 'meal':
                return in_array('meal', $restaurant->getDiscoverFeatures());
                break;
            case 'loyalty':
                return false;
                break;
            default:
                return false;
                break;
        }
    }

    public function activateFeature($restaurant, $feature)
    {
        $subscription = $this->getCurrentSubscription($restaurant);

        switch ($feature) {
            case 'preorder':
                $orderType = $this->em->getRepository('ClabShopBundle:OrderType')->findOneBy(array('slug' => 'preorder'));
                if ($orderType && !$restaurant->getOrderTypes()->contains($orderType)) {
                    $restaurant->addOrderType($orderType);
                }
                $paymentMethods = $this->em->getRepository('ClabShopBundle:PaymentMethod')->findBy(array('is_online' => true));
                foreach ($paymentMethods as $paymentMethod) {
                    if (!$restaurant->getPaymentMethods()->contains($paymentMethod)) {
                        $restaurant->addPaymentMethod($paymentMethod);
                    }
                }
                $restaurant->setIsOpen(true);
                break;
            case 'delivery':
                $orderType = $this->em->getRepository('ClabShopBundle:OrderType')->findOneBy(array('slug' => 'delivery'));
                if ($orderType && !$restaurant->getOrderTypes()->contains($orderType)) {
                    $restaurant->addOrderType($orderType);
                }
                $paymentMethods = $this->em->getRepository('ClabShopBundle:PaymentMethod')->findBy(array('is_online' => true));
                foreach ($paymentMethods as $paymentMethod) {
                    if (!$restaurant->getPaymentMethods()->contains($paymentMethod)) {
                        $restaurant->addPaymentMethod($paymentMethod);
                    }
                }
                break;
            case 'website':
                $restaurant->addDiscoverFeature('website');
                break;
            case 'iframe':
                $restaurant->addDiscoverFeature('iframe');
                break;
            case 'discount':
                $restaurant->addDiscoverFeature('discount');
                break;
            case 'share':
                $restaurant->addDiscoverFeature('share');
                break;
            case 'synch':
                $restaurant->addDiscoverFeature('synch');
                break;
            case 'reporting':
                $restaurant->addDiscoverFeature('reporting');
                break;
            case 'analytics':
                $restaurant->addDiscoverFeature('analytics');
                break;
            case 'category':
                $restaurant->addDiscoverFeature('category');
                break;
            case 'product':
                $restaurant->addDiscoverFeature('product');
                break;
            case 'meal':
                $restaurant->addDiscoverFeature('meal');
                break;
            case 'loyalty':
                return false;
                break;
            default:
                return false;
                break;
        }

        $this->em->flush();

        return true;
    }

    public function desactivateFeature($restaurant, $feature)
    {
        $subscription = $this->getCurrentSubscription($restaurant);

        switch ($feature) {
            case 'preorder':
                $orderType = $this->em->getRepository('ClabShopBundle:OrderType')->findOneBy(array('slug' => 'preorder'));
                if ($orderType) {
                    $restaurant->removeOrderType($orderType);
                }
                break;
            case 'delivery':
                $orderType = $this->em->getRepository('ClabShopBundle:OrderType')->findOneBy(array('slug' => 'delivery'));
                if ($orderType) {
                    $restaurant->removeOrderType($orderType);
                }
                break;
            case 'multisite':
                $restaurant->removeDiscoverFeature('website');
                break;
            case 'discount':
                $restaurant->removeDiscoverFeature('discount');
                break;
            case 'share':
                $restaurant->removeDiscoverFeature('share');
                break;
            case 'synch':
                $restaurant->removeDiscoverFeature('synch');
                break;
            case 'reporting':
                $restaurant->removeDiscoverFeature('reporting');
                break;
            case 'analytics':
                $restaurant->removeDiscoverFeature('analytics');
                break;
            case 'category':
                $restaurant->removeDiscoverFeature('category');
                break;
            case 'product':
                $restaurant->removeDiscoverFeature('product');
                break;
            case 'meal':
                $restaurant->removeDiscoverFeature('meal');
                break;
            case 'loyalty':
                return false;
                break;
            default:
                return false;
                break;
        }

        $this->em->flush();

        return true;
    }

    public function hasModule($restaurant, $module)
    {
        $subscription = $this->getCurrentSubscription($restaurant);

        switch ($module) {
            case 'multisite':
                return $subscription->moduleMultisite();
                break;
            default:
                return false;
                break;
        }
    }

    public function activateModule($restaurant, $module)
    {
        $subscription = $this->getCurrentSubscription($restaurant);

        switch ($module) {
            case 'multisite':
                $subscription->setModuleWebsite(true);
                //avenant
                //factu
                break;
            default:
                return false;
                break;
        }

        $this->em->flush();

        return true;
    }

    public function isValid($restaurant)
    {
        if ($restaurant->getSubscription()) {
        }

        return false;
    }

    public function isOnline($restaurant)
    {
        return $restaurant->isClickeat() || $restaurant->isTTT();
    }

    public function prospect(Restaurant $restaurant, $commercial = null)
    {
        $restaurant->setStatus(Restaurant::STORE_STATUS_PROSPECT);
        $restaurant->getDeal()->addStatusHistory(Restaurant::STORE_STATUS_PROSPECT);

        if ($commercial) {
            $restaurant->setCommercial($commercial);
        }

        $this->em->flush();
    }

    public function ready($restaurant)
    {
        $restaurant->getDeal()->setReady(true);
        $restaurant->getDeal()->addStatusHistory('ready');

        $this->em->flush();

        try {
            $this->container->get('clab_board.mail_manager')->adminReadyNotification($restaurant);
        } catch (\Exception $e) {
            $this->container->get('logger')->error('Bug board ready mail : '.$e->getMessage());
        }
    }

    public function launchBoard($restaurant)
    {
        $restaurant->setStatus(1000);
        $restaurant->getDeal()->addStatusHistory(Restaurant::STORE_STATUS_TEST);

        $this->em->flush();

        try {
            $this->container->get('clab_board.mail_manager')->launchBoard($restaurant);
        } catch (\Exception $e) {
            $this->container->get('logger')->error('Bug board ready mail : '.$e->getMessage());
        }
    }

    public function startTest($restaurant)
    {
        $restaurant->setStatus(Restaurant::STORE_STATUS_TEST);

        try {
            $this->container->get('clab_board.mixpanel')->track('Start test period', array(), $restaurant);
        } catch (\Exception $e) {
        }

        $this->em->flush();
    }

    public function onboard($restaurant)
    {
        list($todo, $percent) = $this->container->get('clab_board.dashboard_manager')->getTodo($restaurant);

        if ($percent != 100) {
            return false;
        }

        if ($restaurant->getStatus() >= Restaurant::STORE_STATUS_ACTIVE) {
            return false;
        }

        $restaurant->setStatus(Restaurant::STORE_STATUS_WAITING_PLANS);
        $restaurant->getDeal()->addStatusHistory(Restaurant::STORE_STATUS_WAITING);

        try {
            $this->container->get('clab_board.mixpanel')->track('Start onboarding process', array(), $restaurant);
        } catch (\Exception $e) {
        }

        $this->em->flush();

        return true;
    }

    public function checkTest()
    {
        // deprecated
        return 0;

        $today = date_create('today');
        $threeDays = clone($today);
        $threeDays->modify('+3 days');
        $eightDays = clone($today);
        $eightDays->modify('+8 days');
        $mailManager = $this->container->get('clab_board.mail_manager');
        $count = 0;

        $restaurants = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findByStatus(1000, 1999);

        foreach ($restaurants as $restaurant) {
            if ($restaurant->getStatusTestEndDate() == $today) {
                ++$count;
                try {
                    $this->container->get('clab_board.mixpanel')->track('Test period closed', array(), $restaurant);
                } catch (\Exception $e) {
                }
                $mailManager->testEnd($restaurant);
            } elseif ($restaurant->getStatusTestEndDate() == $threeDays) {
                $mailManager->testReminder($restaurant, 3);
                ++$count;
            } elseif ($restaurant->getStatusTestEndDate() == $eightDays) {
                $mailManager->testReminder($restaurant, 8);
                ++$count;
            }
        }

        return $count;
    }

    public function activate($restaurant, $plan = null)
    {
        $subscription = new Subscription();

        $restaurant->setStatus(Restaurant::STORE_STATUS_ACTIVE);
        $restaurant->getDeal()->addStatusHistory(Restaurant::STORE_STATUS_ACTIVE);

        if ($restaurant->isMobile()) {
            $restaurant->setIsTTT(true);
            if (count($restaurant->getOrderTypes()) > 0) {
                $restaurant->setIsClickeat(true);
            }
        } else {
            $restaurant->setIsClickeat(true);
        }

        $end = date_create('today');
        if (is_null($plan)) {
        }
        if ($plan->__toArray()['interval'] == 'year') {
            $end->modify('+ 1 year');
        } else {
            $end->modify('+ 1 month');
        }
        if ($this->startsWith($plan->__toArray()['id'], 'app')) {
            $subscription->setType(10);
        } else {
            $subscription->setType(0);
        }
        $comission = substr($plan->__toArray()['id'], -1);
        $subscription->setCommission(9);

        if ($restaurant->isMobile() == false) {
            $subscription->setCommissionExternal($comission);
        } else {
            $subscription->setCommissionExternal(9);
        }
        $subscription->setTransactionCommission(2);
        $planOwn = $this->em->getRepository('ClabRestaurantBundle:Plan')->findOneBy(array(
                'stripePlanId' => $plan->__toArray()['id'],
            ));
        $subscription->setPlan($planOwn);

        if ($plan->__toArray()['trial_period_days'] != null) {
            $end->modify('+'.$plan->__toArray()['trial_period_days'].' days');
        }
        $subscription->setNextDueDate($end);
        $subscription->setRestaurant($restaurant);

        try {
            $this->container->get('clab_board.mail_manager')->online($restaurant);

            if ($this->container->get('kernel')->getEnvironment() == 'prod') {
                $this->container->get('clab_board.mail_manager')->adminOnlineNotification($restaurant);
            }
        } catch (\Exception $e) {
        }
        $this->em->persist($subscription);
        $this->em->flush();

        return true;
    }

    public function upgradePlan(Restaurant $restaurant, $plan, $subId)
    {
        $actualSubscription = $this->em->getRepository('ClabBoardBundle:Subscription')->findOneBy(array(
            'restaurant' => $restaurant,
            'type' => 0,
            'is_online' => true,
        ));
        $actualSubscription->setIsOnline(false);
        $subscription = new Subscription();
        $subscription->setStripeSubscriptionId($subId);

        $end = date_create('today');
        if (is_null($plan)) {
        }
        if ($plan->__toArray()['interval'] == 'year') {
            $end->modify('+ 1 year');
        } else {
            $end->modify('+ 1 month');
        }
        if ($this->startsWith($plan->__toArray()['id'], 'app')) {
            $subscription->setType(10);
        } else {
            $subscription->setType(0);
        }
        $comission = substr($plan->__toArray()['id'], -1);
        $subscription->setCommission(9);

        if ($restaurant->isMobile() == false) {
            $subscription->setCommissionExternal($comission);
        } else {
            $subscription->setCommissionExternal(9);
        }
        $subscription->setTransactionCommission(2);
        $planOwn = $this->em->getRepository('ClabRestaurantBundle:Plan')->findOneBy(array(
            'stripePlanId' => $plan->__toArray()['id'],
        ));
        $subscription->setPlan($planOwn);

        if ($plan->__toArray()['trial_period_days'] != null) {
            $end->modify('+'.$plan->__toArray()['trial_period_days'].' days');
        }
        $subscription->setNextDueDate($end);
        $subscription->setRestaurant($restaurant);

        $this->em->persist($subscription);
        $this->em->flush();

        return true;
    }

    public function goOnline($restaurant)
    {
        $subscription = $restaurant->getCurrentSubscription();
        list($todo, $percent) = $this->container->get('clab_board.dashboard_manager')->getTodo($restaurant);

        if ($percent == 100) {
            $restaurant->setStatus(Restaurant::STORE_STATUS_ACTIVE);
            $restaurant->setIsOnline(true);

            if ($restaurant->isMobile()) {
                $restaurant->setIsTTT(true);
                if ($subscription->hasPreorder() || $subscription->hasDelivery()) {
                    $restaurant->setIsClickeat(true);
                }
            } else {
                $restaurant->setIsClickeat(true);
            }

            try {
                $this->container->get('clab_board.mixpanel')->track('Online', array(), $restaurant);
            } catch (\Exception $e) {
            }

            $this->em->flush();

            return true;
        } else {
            return false;
        }
    }

    public function changePlan($restaurant, $subscription)
    {
        if ($restaurant->getStatus() < Restaurant::STORE_STATUS_ACTIVE) {
            $currentSubscription = $restaurant->getStartSubscription();
            $currentSubscription->setType($subscription->getType());
            $currentSubscription->setModuleMultisite($subscription->moduleMultisite());
            $currentSubscription->setModuleAppleApp($subscription->moduleAppleApp());
            $currentSubscription->initPricing();
        } else {
            $currentSubscription = $restaurant->getCurrentSubscription();

            if ($currentSubscription) {
                if ($subscription->getType() > $currentSubscription->getType()) {
                    $today = date_create('today');
                    $yesterday = clone($today);
                    $yesterday->modify('-1 day');

                    $subscription->setStartDate($today);
                    $currentSubscription->setEndDate($yesterday);

                    $subscription->setRestaurant($restaurant);
                    $subscription->initPricing();
                    $this->em->persist($subscription);
                    $this->em->flush();
                    //boum avenant
                } elseif ($subscription->getType() < $currentSubscription->getType()) {
                    $end = date_create('last day of this month');
                    $start = date_create('first day of next month');

                    $subscription->setStartDate($start);
                    $currentSubscription->setEndDate($end);

                    $subscription->setRestaurant($restaurant);
                    $subscription->initPricing();
                    $this->em->persist($subscription);
                    $this->em->flush();
                }
            }
        }
    }

    public function verboseStatus($status)
    {
        if ($status === 'ready') {
            return 'Prospect (prêt)';
        } elseif ($status < Restaurant::STORE_STATUS_PROSPECT) {
            return 'Suspect';
        } elseif ($status < Restaurant::STORE_STATUS_TEST) {
            return 'Prospect';
        } elseif ($status < Restaurant::STORE_STATUS_WAITING) {
            return 'Compte ouvert';
        } elseif ($status < Restaurant::STORE_STATUS_ACTIVE) {
            return 'Compte ouvert (mise en ligne)';
        } elseif ($status < Restaurant::STORE_STATUS_TRASH) {
            return 'Actif';
        }

        return;
    }

    public function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
}
