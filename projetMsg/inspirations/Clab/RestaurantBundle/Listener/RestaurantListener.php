<?php

namespace Clab\RestaurantBundle\Listener;

use Clab\BoardBundle\Service\SubscriptionManager;
use Clab\RestaurantBundle\Entity\TimeSheet;
use Clab\RestaurantBundle\Manager\TimeSheetManager;
use Clab\ShopBundle\Entity\Discount;
use Clab\SocialBundle\Service\SocialManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\Serializer\Serializer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Clab\RestaurantBundle\Entity\Restaurant as RestaurantEntity;
use Clab\LocationBundle\Entity\Address;
use Clab\RestaurantBundle\Entity\Deal;
use Clab\RestaurantBundle\Entity\Restaurant;

class RestaurantListener
{
    protected $container;

    private $typesArray = array(
        Discount::DISCOUNT_TYPE_ALL => 'Promotion sur toute la carte',
        Discount::DISCOUNT_TYPE_MEAL => 'Promotion sur toute les formules',
        Discount::DISCOUNT_TYPE_PRODUCT => 'Promotion sur tout les produits');

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $restaurant = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($restaurant instanceof Restaurant) {
            if (!$restaurant->getAddress()) {
                $address = new Address();
                $restaurant->setAddress($address);
                $entityManager->persist($address);
            }

            if (!$restaurant->getDeal()) {
                $deal = new Deal();
                $restaurant->setDeal($deal);
                $deal->addStatusHistory(RestaurantEntity::STORE_STATUS_NEW, $restaurant->getCreated()->getTimestamp());
                $entityManager->persist($deal);
            }

            $planning = $this->container->get('app_restaurant.timesheet_manager')->getFlatPlanning($restaurant);

            if (count($planning)) {
                $restaurant->setFlatTimeSheet($planning);
            }

            $ft = array();

            if (count($restaurant->getTags()) > 0) {
                $ft['category'] = array();
                foreach ($restaurant->getTags() as $flatTag) {
                    $ft['category'][] = $flatTag->getName();
                }
            }
            if (count($restaurant->getExtraTags()) > 0) {
                $ft['regime'] = array();
                foreach ($restaurant->getExtraTags() as $flatExtraTag) {
                    $ft['regime'][] = $flatExtraTag->getName();
                }
            }

            if (count($ft) > 0) {
                $restaurant->setFlatTags($ft);
            }

            $bestReview = $entityManager->getRepository('ClabReviewBundle:Review')->findBestForRestaurant($restaurant);

            if (count($bestReview)) {
                $bestReview = $this->container->get('serializer')->serialize($bestReview, 'json');

                $restaurant->setBestReview(json_decode($bestReview));
            }

            $discount = $entityManager->getRepository(Discount::class)->getActualDiscountAsArray($restaurant);

            if (count($discount) > 0) {
                $actualDiscount = $discount[0];
                $actualDiscount['type'] = $this->typesArray[$actualDiscount['type']];

                $restaurant->setActiveDiscount($actualDiscount);
            }

            $entityManager->flush();

            if (!$restaurant->getSubscriptionTerms()) {
                $this->container->get('app_admin.subscription_manager')->initSubscriptionTerms($restaurant);
            }

            if (!$restaurant->getSocialProfile()) {
                $this->container->get('clab.social_manager')->initSocialProfile($restaurant);
            }
        }
    }

    public function preUpdate(LifecycleEventArgs $args)
    {
        $restaurant = $args->getEntity();
        $entityManager = $args->getEntityManager();

        if ($restaurant instanceof Restaurant) {

            $planning = $this->container->get('app_restaurant.timesheet_manager')->getFlatPlanning($restaurant);

            if (count($planning)) {
                $restaurant->setFlatTimeSheet($planning);
            }

            $ft = array();

            if (count($restaurant->getTags()) > 0) {
                $ft['category'] = array();
                foreach ($restaurant->getTags() as $flatTag) {
                    $ft['category'][] = $flatTag->getName();
                }
            }
            if (count($restaurant->getExtraTags()) > 0) {
                $ft['regime'] = array();
                foreach ($restaurant->getExtraTags() as $flatExtraTag) {
                    $ft['regime'][] = $flatExtraTag->getName();
                }
            }

            if (count($ft) > 0) {
                $restaurant->setFlatTags($ft);
            }

            $bestReview = $entityManager->getRepository('ClabReviewBundle:Review')->findBestForRestaurant($restaurant);

            if (count($bestReview)) {
                $bestReview = $this->container->get('serializer')->serialize($bestReview, 'json');

                $restaurant->setBestReview(json_decode($bestReview));
            }

            $discount = $entityManager->getRepository(Discount::class)->getActualDiscountAsArray($restaurant);

            if (count($discount) > 0) {
                $actualDiscount = $discount[0];
                $actualDiscount['type'] = $this->typesArray[$actualDiscount['type']];

                $restaurant->setActiveDiscount($actualDiscount);
            }
        }
    }

}
