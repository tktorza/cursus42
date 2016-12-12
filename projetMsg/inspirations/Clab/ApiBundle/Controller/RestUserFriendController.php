<?php

namespace Clab\ApiBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\View\View;
use Clab\UserBundle\Entity\User;

class RestUserFriendController extends FOSRestController
{
    /**
     * Get friends of an user.
     *
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/friends",
     *      description="get reviews by user"
     * )
     */
    public function getFriendsAction()
    {
        $friends = $this->getUser()->getMyFriends();

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($friends, 'json');

        return new Response($response);
    }

    /**
     * Add to friendlist.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = "/api/v1/friends",
     *   description = "Add user to friendlist",
     *   requirements={
     *       {"name"="userId", "dataType"="integer", "required"=true, "description"="user Id"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     * @ParamConverter("friend", class="ClabUserBundle:User", options={"id" = "userId"})
     */
    public function postFriendAction(Request $request, User $friend)
    {
        if (!$friend) {
            return new Response('Uilisateur non trouvé', 404);
        }

        $user = $this->getUser();
        $user->addMyFriend($friend);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Remove from friendlist.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = "/api/v1/friends",
     *   description = "Remove user from friendlist",
     *   requirements={
     *       {"name"="userId", "dataType"="integer", "required"=true, "description"="user Id"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     * @ParamConverter("friend", class="ClabUserBundle:User", options={"id" = "userId"})
     */
    public function removeFriendAction(Request $request, User $friend)
    {
        if (!$friend || !$this->getUser()->getMyFriends()->contains($friend)) {
            return new Response('Ami non trouvé', 400);
        }

        $user = $this->getUser();
        $user->removeMyFriend($friend);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }
    
    /**
     * Add to friendlist from facebook.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Add user to friendlist from facebook",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function addUserToFriendListFromFBAction(Request $request)
    {
        $idUser = $request->get('id');
        $friendsFBId = $request->get('friendsFBId');
        $user1 = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($idUser);
        foreach ($friendsFBId as $idFB) {
            $user2 = $this->getDoctrine()->getRepository('ClabUserBundle:User')->findOneBy(array(
                'facebookId' => $idFB,
            ));
            if (!empty($user2) && !is_null($user2)) {
                $user1->addMyFriend($user2);
                $user2->addFriendsWithMe($user2);
            }
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Add to friendlist from facebook.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Add user to friendlist from emails",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function addUserToFriendListFromEmailAction(Request $request)
    {
        $idUser = $request->get('id');
        $emails = $request->get('emails');
        $user1 = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($idUser);
        foreach ($emails as $email) {
            $user2 = $this->getDoctrine()->getRepository('ClabUserBundle:User')->findOneBy(array(
                'email' => $email,
            ));
            if (!empty($user2) && !is_null($user2)) {
                $user1->addMyFriend($user2);
                $user2->addFriendsWithMe($user2);
            }
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Add to friendlist from facebook.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Add user to friendlist from phones",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function addUserToFriendListFromPhoneAction(Request $request)
    {
        $idUser = $request->get('id');
        $phones = $request->get('phones');
        $user1 = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($idUser);
        foreach ($phones as $phone) {
            $user2 = $this->getDoctrine()->getRepository('ClabUserBundle:User')->findOneBy(array(
                'phone' => $phone,
            ));
            if (!empty($user2) && !is_null($user2)) {
                $user1->addMyFriend($user2);
                $user2->addFriendsWithMe($user2);
            }
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Remove to friendlist.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Remove user to friendlist",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function removeUserToFriendListAction(Request $request)
    {
        $idUser = $request->get('id');
        $idUser2 = $request->get('id2');
        $user1 = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($idUser);
        $user2 = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($idUser2);
        $user1->removeMyFriend($user2);
        $user2->removeFriendsWithMe($user1);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
