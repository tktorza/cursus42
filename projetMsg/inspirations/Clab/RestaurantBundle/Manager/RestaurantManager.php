<?php

namespace Clab\RestaurantBundle\Manager;

use Clab\BoardBundle\Entity\Client;
use Clab\MediaBundle\Entity\Gallery;
use Doctrine\ORM\EntityManager;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Clab\RestaurantBundle\Entity\Restaurant;

class RestaurantManager
{
    protected $em;
    protected $repository;

    /**
     * @param EntityManager $em
     *                          Constructor
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('ClabRestaurantBundle:Restaurant');
    }

    public function populateAvgScore()
    {
        $count = 0;
        $restaurants = $this->repository->findAll();
        foreach ($restaurants as $restaurant) {
            $score = $this->em->getRepository('ClabReviewBundle:Review')->findAllScoreForRestaurant($restaurant);
            $restaurant->setAvgReviewScore($score);
            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function populateAvgCleanScore()
    {
        $count = 0;
        $restaurants = $this->repository->findAll();
        foreach ($restaurants as $restaurant) {
            $score = $this->em->getRepository('ClabReviewBundle:Review')->findCleanScoreForRestaurant($restaurant);
            $restaurant->setAvgCleanScore($score);
            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function populateAvgCookScore()
    {
        $count = 0;
        $restaurants = $this->repository->findAll();
        foreach ($restaurants as $restaurant) {
            $score = $this->em->getRepository('ClabReviewBundle:Review')->findCookScoreForRestaurant($restaurant);
            $restaurant->setAvgCookScore($score);
            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function populateAvgServiceScore()
    {
        $count = 0;
        $restaurants = $this->repository->findAll();
        foreach ($restaurants as $restaurant) {
            $score = $this->em->getRepository('ClabReviewBundle:Review')->findServiceScoreForRestaurant($restaurant);
            $restaurant->setAvgServiceScore($score);
            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function populateAvgPriceScore()
    {
        $count = 0;
        $restaurants = $this->repository->findAll();
        foreach ($restaurants as $restaurant) {
            $score = $this->em->getRepository('ClabReviewBundle:Review')->findPriceScoreForRestaurant($restaurant);
            $restaurant->setAvgPriceScore($score);
            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function migrateDays()
    {
        $timesheets = $this->em->getRepository('ClabRestaurantBundle:TimeSheet')->findAll();
        $count = 0;

        foreach ($timesheets as $timesheet) {
            if ($timesheet->getMonday() == true) {
                $timesheet->addDay('MONDAY');
            }
            if ($timesheet->getTuesday() == true) {
                $timesheet->addDay('TUESDAY');
            }
            if ($timesheet->getWednesday() == true) {
                $timesheet->addDay('WEDNESDAY');
            }
            if ($timesheet->getThursday() == true) {
                $timesheet->addDay('THURSDAY');
            }
            if ($timesheet->getFriday() == true) {
                $timesheet->addDay('FRIDAY');
            }
            if ($timesheet->getSaturday() == true) {
                $timesheet->addDay('SATURDAY');
            }
            if ($timesheet->getSunday() == true) {
                $timesheet->addDay('SUNDAY');
            }

            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function createStandardMenu()
    {
        $restaurants = $this->repository->findAll();
        $count = 0;

        foreach ($restaurants as $restaurant) {
            $menuDefault = new RestaurantMenu();
            $menuDefault->setRestaurant($restaurant);
            $menuDefault->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT);
            $menuDefault->setName('Carte classique');
            $this->em->persist($menuDefault);

            $menuDelivery = new RestaurantMenu();
            $menuDelivery->setRestaurant($restaurant);
            $menuDelivery->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY);
            $menuDelivery->setName('Carte livraison');
            $this->em->persist($menuDelivery);

            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function migrateGallery()
    {
        $restaurants = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findAll();
        $count = 0;

        foreach ($restaurants as $restaurant) {
            $gallery = new Gallery();
            $gallery->setDirName(sha1(time().rand(10, 100)).'/');
            $default = $this->em->getRepository('ClabMediaBundle:Image')->find(88888888);
            $gallery->setDefault($default);
            $restaurant->setGalleryMenu($gallery);
            $this->em->persist($gallery);

            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function migratePublicGallery()
    {
        $restaurants = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findAll();
        $count = 0;

        foreach ($restaurants as $restaurant) {
            $gallery = new Gallery();
            $gallery->setDirName(sha1(time().rand(10, 100)).'/');
            $default = $this->em->getRepository('ClabMediaBundle:Image')->find(88888888);
            $gallery->setDefault($default);
            $restaurant->setPublicGallery($gallery);
            $this->em->persist($gallery);

            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function createStandardMenuClient()
    {
        $clients = $this->em->getRepository('ClabBoardBundle:Client')->findAll();
        $count = 0;

        foreach ($clients as $client) {
            $menuDefault = new RestaurantMenu();
            $menuDefault->setChainStore($client);
            $menuDefault->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT);
            $menuDefault->setName('Carte classique');
            $this->em->persist($menuDefault);

            $menuDelivery = new RestaurantMenu();
            $menuDelivery->setChainStore($client);
            $menuDelivery->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY);
            $menuDelivery->setName('Carte livraison');
            $this->em->persist($menuDelivery);

            if ($count % 100 == 0) {
                $this->em->flush();
            }

            ++$count;
        }
        $this->em->flush();

        return true;
    }

    public function findAllFiltered() {
        return $this->repository->findAllFiltered(array(
            'status_min' => Restaurant::STORE_STATUS_ACTIVE,
            'status_max' => 6999,
            'service' => 'clickeat'
        ));
    }

    public function findNearbyPaginated($lat, $long, $options) {
        return $this->repository->findNearbyPaginated($lat, $long, $options);
    }

    public function findNearbyPaginatedFiltered($lat, $lng, $options)
    {
        return $this->repository->findNearbyPaginated($lat, $lng, $options);
    }

    public function findByIds($ids, $location = null) {
        return $this->repository->findByIds($ids, $location);
    }
}
