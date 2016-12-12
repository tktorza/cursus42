<?php

namespace Clab\ReviewBundle\Service;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Clab\ReviewBundle\Entity\Review;

class ReviewManager
{
    protected $em;
    protected $repository;
    protected $request;

    public function __construct(EntityManager $em, Request $request)
    {
        $this->em = $em;
        $this->request = $request;
        $this->repository = $this->em->getRepository('ClabReviewBundle:Review');
    }

    public function getEntity($entity, $entityId)
    {
        switch ($entity) {
            case 'restaurant':
                $entity = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('id' => $entityId));
                break;
            case 'place':
                $entity = $this->em->getRepository('ClabLocationBundle:Place')->findOneBy(array('id' => $entityId));
                break;
            case 'event':
                $entity = $this->em->getRepository('ClabLocationBundle:Event')->findOneBy(array('id' => $entityId));
                break;
            default:
                return;
                break;
        }

        return $entity;
    }

    public function getForEntity($entity, $entityId)
    {
        $entity = $this->getEntity($entity, $entityId);

        $reviews = array();

        foreach ($entity->getReviews() as $review) {
            if ($review->isOnline()) {
                $reviews[] = $review;
            }
        }

        return $reviews;
    }

    public function getReviewsForEntity($entity)
    {
        $reviews = array();
        $bestCount = 0;
        $totalScore = 0;
        $stats = array(1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0);

        foreach ($entity->getReviews() as $review) {
            if ($review->isOnline()) {
                $reviews[] = $review;
                $stats[$review->getScore()] = $stats[$review->getScore()] + 1;
                $totalScore += $review->getScore();
            }
        }

        $count = count($reviews);
        if ($count > 0) {
            $average = round($totalScore / count($reviews));
        } else {
            $average = null;
        }

        $data = array('reviews' => $reviews, 'average' => $average, 'count' => $count, 'stats' => $stats);

        return $data;
    }

    public function findAllBetweenDate($start, $end)
    {
        return $this->em->getRepository('ClabReviewBundle:Review')->findAllBetweenDate($start, $end);
    }

    /**
     * @param $restaurant
     * @param User       $user
     *
     * @return bool
     */
    public function createReview($restaurant, User $user, $query)
    {
        if (!is_object($restaurant)) {
            $restaurant = $this->getEntity('restaurant', $restaurant);
        }

        $review = new Review();

        $cookScore = $query['cookScore'];
        $serviceScore = $query['serviceScore'];
        $qualityScore = $query['qualityScore'];
        $hygieneScore = $query['hygieneScore'];

        $review->setCookScore($cookScore);
        $review->setServiceScore($serviceScore);
        $review->setQualityScore($qualityScore);
        $review->setHygieneScore($hygieneScore);
        $review->setScore();

        $review->setRestaurant($restaurant);
        $review->setBody($query['body']);
        $review->setProfile($user);

        if ($query['image']) {
            $review->setImage($query['image']);
        }

        $this->em->persist($review);
        $this->em->flush();

        return true;
    }

    public function getAll($restaurantId, $source = null) {
        $options = array();
        $options['restaurant'] = $restaurantId;

        if ($source) {
            $options['source'] = $source;
        }

        return $this->repository->findBy($options);
    }
}
