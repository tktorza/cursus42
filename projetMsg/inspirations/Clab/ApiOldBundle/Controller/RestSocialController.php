<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\SocialBundle\Entity\SocialPost;
use Clab\ApiOldBundle\Form\Type\Social\RestSocialPostType;

class RestSocialController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Social",
     *      resource=true,
     *      description="List of social post by restaurant",
     *      requirements={
     *      }
     * )
     */
    public function postListAction(Request $request)
    {
        $page = $request->get('page');
        if (!$page) {
            $page = 1;
        }

        $posts = $this->get('clab.social_manager')->getLatestPost($request, array(), $page);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($posts, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="Social",
     *      description="New post",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"idRestaurant" = "id"})
     * @ParamConverter("menu", class="ClabRestaurantBundle:RestaurantMenu", options={"idMenu" = "id"})
     */
    public function postNewAction(Restaurant $restaurant, RestaurantMenu $restaurantMenu, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $products = $this->get('app_restaurant.product_manager')->getForRestaurant($restaurant);
        $meals = $this->get('app_restaurant.meal_manager')->getForRestaurantMenu($restaurantMenu);
        $discounts = $this->get('app_shop.discount_manager')->getAvailableDiscountsByRestaurant($restaurant);

        if ($restaurant->isMobile()) {
            $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($restaurant, '10 day', false);
            $events = array();
            foreach ($foodtruck->getPlanning() as $timestamp => $day) {
                foreach ($day as $event) {
                    $events[$timestamp.'-'.$event['start']->getTimestamp()] = $event;
                }
            }
        }

        $socialPost = new SocialPost();
        $socialPost->setRestaurant($restaurant);
        $form = $this->createForm(new RestSocialPostType(array(
            'products' => $products,
            'meals' => $meals,
            'discounts' => $discounts,
        )), $socialPost, array('method' => 'POST'));
        $form->submit($request);

        if ($form->isValid()) {
            $em->persist($socialPost);
            $em->flush();

            $socialManager = $this->get('clab.social_manager');
            $socialManager->pushSocialPost(
                $socialPost,
                $form->get('add_link')->getData(),
                array(
                    'facebook' => $form->get('to_facebook')->getData(),
                    'twitter' => $form->get('to_twitter')->getData(),
                )
            );

            $response = new Response(204, '');

            return new JsonResponse($response);
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);
    }

    /**
     * @ApiDoc(
     *      section="Social",
     *      description="Delete post",
     *      requirements={
     *          {"name"="restaurantId", "dataType"="integer", "required"=true, "description"="Id restaurant"},
     *          {"name"="id", "dataType"="integer", "required"=true, "description"="Id produit"}
     *      },
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "id"})
     */
    public function postDeleteAction(Restaurant $restaurant, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $socialPost = $this->getDoctrine()->getManager()->getRepository('ClabSocialBundle:SocialPost')->findOneBy(array(
            'restaurant' => $restaurant,
            'id' => $id,
        ));

        if (!$socialPost || !$socialPost->isAllowed($this->getUser())) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Vous n\'avez pas accès à ce post');
        }

        $em->remove($socialPost);
        $em->flush();

        return new JsonResponse('Deleted', 200);
    }
}
