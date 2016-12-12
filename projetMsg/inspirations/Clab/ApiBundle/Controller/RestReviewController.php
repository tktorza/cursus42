<?php

namespace Clab\ApiBundle\Controller;

use Clab\MediaBundle\Entity\Image;
use Clab\MediaBundle\Service\ImageManager;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ReviewBundle\Entity\Review;
use Clab\ReviewBundle\Entity\Vote;
use Clab\ReviewBundle\Service\ReviewManager;
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
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Restaurant id"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function listAction(Request $request, $id)
    {
        /**
         * @var $reviewManager ReviewManager
         */
        $reviewManager = $this->get('clab_review.review_manager');
        $source = $request->query->get('source');
        $reviews = $reviewManager->getAll($id, $source);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($reviews, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Review",
     *      description="New review",
     *      requirements={
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Restaurant id"}
     *      },
     *      parameters={
     *          {"name"="cookScore", "dataType"="integer", "required"=true, "description"="cook score 0 -> 10"},
     *          {"name"="serviceScore", "dataType"="integer", "required"=true, "description"="service score 0 -> 10"},
     *          {"name"="qualityScore", "dataType"="integer", "required"=true, "description"="quality score 0 -> 10"},
     *          {"name"="hygieneScore", "dataType"="integer", "required"=true, "description"="hygiene score 0 -> 10"},
     *          {"name"="title", "dataType"="string", "required"=true, "description"="title of review"},
     *          {"name"="body", "dataType"="string", "required"=true, "description"="comment"},
     *          {"name"="is_recommended", "dataType"="boolean", "required"=true, "description"="recommended or not"},
     *          {"name"="url", "dataType"="string", "required"=false, "description"="url of review user"},
     *          {"name"="image", "dataType"="file", "required"=false, "description"="posted image"},
     *      },
     * )
     */
    public function newAction(Request $request, $id)
    {
        /**
         * @var $imageManager ImageManager
         */
        $imageManager = $this->get('app_media.image_manager');

        /**
         * @var $reviewManager ReviewManager
         */
        $reviewManager = $this->get('clab_review.review_manager');

        $user = $this->getUser();
        $query = $request->request->all();
        $image = $imageManager->upload('restaurant', $id, $user, 'public')[1];

        $query['image'] = $image instanceof Image ? $image : null;

        if ($reviewManager->createReview($id, $user, $query)) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Review was added',
            ], 200);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Review was not added, probably bad parameters',
        ], 422);
    }

    public function deleteAllAction($id) {
        /**
         * @var $reviewManager ReviewManager
         */
        $reviewManager = $this->get('clab_review.review_manager');

        if ($reviewManager->deleteAll($id)) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Reviews were deleted',
            ], 200);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Review were not deleted',
        ], 422);

    }

    /**
     *
     * @return JsonResponse
     */
    public function voteAction($reviewId, $voteType)
    {
        $user = $this->getUser();
        $review = $this->getDoctrine()->getRepository('ClabReviewBundle:Review')->find($reviewId);

        if($voteType == 'upvote') {
            $hasAlreadyVoted = $this->get('clickeat.review_manager')->hasAlreadyVoted($user, $review, 100);
        } else if ($voteType == 'downvote') {
            $hasAlreadyVoted = $this->get('clickeat.review_manager')->hasAlreadyVoted($user, $review, 0);
        }

        if ($hasAlreadyVoted == false) {
            $reviewUserVote = $this->getDoctrine()->getRepository('ClabReviewBundle:Vote')->findOneBy(array(
                'user' => $this->getUser(),
                'review' => $review,
            ));
            if ($reviewUserVote !== null) {

                if($voteType == 'upvote') {
                    $reviewUserVote->setState(100);
                    $user->setUpCount($user->getUpCount() + 1);
                    if ($user->getDownCount() > 0) {
                        $user->setDownCount($user->getDownCount() - 1);
                    }
                    $review->setUpCount($review->getUpCount() + 1);
                    if ($review->getDownCount() > 0) {
                        $review->setDownCount($review->getDownCount() - 1);
                    }
                } else if ($voteType == 'downvote') {
                    $reviewUserVote->setState(0);
                    $user->setDownCount($user->getDownCount() + 1);
                    if ($user->getUpCount() > 0) {
                        $user->setUpCount($user->getUpCount() - 1);
                    }
                    $review->setDownCount($review->getDownCount() + 1);
                    if ($review->getUpCount() > 0) {
                        $review->setUpCount($review->getUpCount() - 1);
                    }
                }
                $this->getDoctrine()->getManager()->flush();
            } else {
                $vote = new Vote();
                $vote->setReview($review);
                $vote->setUser($user);
                $user->addVote($vote);
                $review->addVote($vote);

                if ($voteType == 'upvote') {
                    $vote->setState(100);
                    $user->setUpCount($user->getUpCount() + 1);
                    if ($user->getDownCount() > 0) {
                        $user->setDownCount($user->getDownCount() - 1);
                    }
                    $review->setUpCount($review->getUpCount() + 1);
                    if ($review->getDownCount() > 0) {
                        $review->setDownCount($review->getDownCount() - 1);
                    }
                } else if ($voteType == 'downvote') {
                    $vote->setState(0);
                    $user->setDownCount($user->getDownCount() + 1);
                    if ($user->getUpCount() > 0) {
                        $user->setUpCount($user->getUpCount() - 1);
                    }
                    $review->setDownCount($review->getDownCount() + 1);
                    if ($review->getUpCount() > 0) {
                        $review->setUpCount($review->getUpCount() - 1);
                    }
                    $this->getDoctrine()->getManager()->persist($vote);
                    $this->getDoctrine()->getManager()->flush();
                }
                $this->getDoctrine()->getManager()->persist($vote);
                $this->getDoctrine()->getManager()->flush();
            }

            return new JsonResponse(array(
                'state' => 200,
                'message' => 'ok',
                'upvote' => $review->getUpCount(),
                'downvote' => $review->getDownCount(),
            ));
        } else {
            return  new JsonResponse(array(
                'state' => 400,
                'message' => 'arealdy voted',
            ));
        }
    }
}
