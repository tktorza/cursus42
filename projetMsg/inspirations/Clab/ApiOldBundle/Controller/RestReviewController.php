<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ReviewBundle\Entity\Review;
use Clab\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestReviewController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Review",
     *      resource=true,
     *      description="List of review by restaurant",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function listAction(Restaurant $restaurant)
    {
        $reviews = $this->getDoctrine()->getRepository('ClabReviewBundle:Review')->findBy(array(
            'restaurant' => $restaurant,
            'source' => 'click-eat',
        ));

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($reviews, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Review",
     *      description="New review",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     */
    public function newAction(Request $request)
    {
        $review = new Review();
        $restaurantId = $request->get('restaurantId');
        $userId = $request->get('userId');
        $user = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($userId);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
        $imageManager = $this->get('app_media.image_manager');
        $imageManager->upload('restaurant', $restaurantId, $user, 'public');
        $cookScore = $request->get('cookScore');
        $serviceScore = $request->get('serviceScore');
        $qualityScore = $request->get('qualityScore');
        $hygieneScore = $request->get('hygieneScore');
        $score = ceil(($qualityScore + $cookScore + $qualityScore + $hygieneScore) / 4);
        $review->setCookScore($cookScore);
        $review->setServiceScore($serviceScore);
        $review->setQualityScore($qualityScore);
        $review->setHygieneScore($hygieneScore);
        $review->setScore($score);
        $review->setRestaurant($restaurant);
        $review->setBody($request->get('body'));
        $review->setProfile($user);
        $this->getDoctrine()->getManager()->persist($review);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse('review added', 200);
    }
}
