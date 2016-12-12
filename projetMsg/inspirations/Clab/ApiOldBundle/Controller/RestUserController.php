<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\BoardBundle\Entity\UserDataBase;
use Clab\PeopleBundle\Validator\Constraints\PhoneNumber;
use Clab\RestaurantBundle\Entity\Restaurant;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\View\View;
use Clab\UserBundle\Entity\User;
use Clab\ApiOldBundle\Form\Type\User\RestUserType;
use Clab\ApiOldBundle\Form\Type\User\RestUserProfileCoverType;
use Clab\ApiOldBundle\Form\Type\User\RestFacebookConnectType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class RestUserController extends FOSRestController
{
    /**
     * Return the overall user list.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Return the overall User List",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function getUsersAction()
    {
        $userManager = $this->get('fos_user.user_manager');
        $entity = $userManager->findUsers();
        if (!$entity) {
            throw $this->createNotFoundException('Data not found.');
        }
        $view = View::create();
        $view->setData($entity)->setStatusCode(200);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($entity, 'json');

        return new Response($response);
    }

    /**
     * Return an user identified by username/email.
     *
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Return an user identified by username/email",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @param string $email
     *
     * @return View
     */
    public function getUserAction($email)
    {
        $userManager = $this->container->get('fos_user.user_manager');
        $entity = $userManager->findUserByEmail($email);
        if (!$entity) {
            throw $this->createNotFoundException('Data not found.');
        }
        $view = View::create();
        $view->setData($entity)->setStatusCode(200);

        return $view;
    }

    protected function loginUser(User $user)
    {
        $security = $this->get('security.context');
        $providerKey = $this->container->getParameter('fos_user.firewall_name');
        $roles = $user->getRoles();
        $token = new UsernamePasswordToken($user, null, $providerKey, $roles);
        $security->setToken($token);
    }

    protected function logoutUser()
    {
        $security = $this->get('security.context');
        $token = new AnonymousToken(null, new User());
        $security->setToken($token);
        $session = new Session();
        $session->remove('loginToken');
        $session->clear();
        $session->invalidate();
    }

    protected function checkUserPassword(User $user, $password)
    {
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        if (!$encoder) {
            return false;
        }

        return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
    }
    protected function getUserManager()
    {
        return $this->get('fos_user.user_manager');
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="Check Login",
     *      requirements={
     *      }
     * )
     */
    public function validateLoginAction(Request $request)
    {
        $session = new Session();
        $token = $request->get('token');
        $tokenSerialized = $session->get('loginToken');
        $tokenUnserialized = unserialize($tokenSerialized);
        $serializer = $this->container->get('serializer');
        if ($tokenSerialized == null) {
            return new JsonResponse([
                'success' => false,
                'message' => 'not logged in',
            ]);
        }
        if ($tokenUnserialized->getCredentials() == $token) {
            return new JsonResponse([
               'success' => true,
               'user' => $serializer->serialize($tokenUnserialized->getUser(), 'json'),
               'token' => $tokenUnserialized->getCredentials(),
           ]);
        }
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="Login",
     *      requirements={
     *          {"name"="email", "dataType"="string", "required"=true, "description"="email"},
     *          {"name"="password", "dataType"="string", "required"=true, "description"="password"}
     *      }
     * )
     */
    public function loginAction(Request $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $um = $this->getUserManager();
        $user = $um->findUserByEmail($email);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }
        if (!$this->checkUserPassword($user, $password)) {
            throw new AccessDeniedException('Mot de passe erroné');
        }

        $this->loginUser($user);
        $firewallName = $this->container->getParameter('fos_user.firewall_name');

        $token = new UsernamePasswordToken($user, $user->getPassword(), $firewallName, $user->getRoles());
        $securityContext = $this->container->get('security.token_storage'); // do it your way
        $securityContext->setToken($token);
        $session = new Session();
        $session->set('loginToken', serialize($token));
        $serializer = $this->container->get('serializer');
        if ($user->hasRole('ROLE_SUPER_ADMIN') || $user->hasRole('ROLE_ADMIN')) {
            $restaurants = 'admin';
        } else {
            $restaurants = $user->getAllowedRestaurants();
        }
        if (empty($user->getAllowedRestaurants()->toArray())) {
            return new JsonResponse([
                'success' => true,
                'user' => $serializer->serialize($token->getUser(), 'json'),
                'token' => $token->getCredentials(),
            ]);
        }
        $response = $serializer->serialize($restaurants, 'json');

        return new Response($response);
    }
    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="Logout",
     *      requirements={
     *      }
     * )
     */
    public function logoutAction(Request $request)
    {
        $this->logoutUser();

        return  new JsonResponse('200');
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      description="Validate auth token",
     * )
     */
    public function validateTokenAction(Request $request)
    {
        if ($this->get('api.session_manager')->isAuthenticated()) {
            return $this->get('api.rest_manager')->getResponse(array('is_confirmed' => $this->get('api.session_manager')->getUser()->hasRole('ROLE_MEMBER_CONFIRMED')));
        }

        return $this->get('api.rest_manager')->getErrorResponse('404', 'Token invalide');
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="Managed restaurants",
     *      output="Clab\RestaurantBundle\Entity\Restaurant"
     * )
     */
    public function restaurantsAction()
    {
        $user = $this->get('api.session_manager')->getUser();

        if ($user->hasRole('ROLE_SUPER_ADMIN') || $user->hasRole('ROLE_ADMIN')) {
            $restaurants = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Restaurant')->findByStatus(0, 9999);
        } else {
            $restaurants = $user->getAllowedRestaurants();
        }

        return $this->get('api.rest_manager')->getResponse(array('restaurants' => $restaurants), array('pro'));
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      requirements={
     *          {"name"="email", "dataType"="string", "required"=true, "description"="email"},
     *          {"name"="password", "dataType"="string", "required"=true, "description"="password"},
     *          {"name"="firstName", "dataType"="string", "required"=true, "description"="firstName"},
     *          {"name"="lastName", "dataType"="string", "required"=true, "description"="lastName"},
     *      },
     *      description="Register",
     *      input="Clab\ApiOldBundle\Form\Type\User\RestUserRegisterType",
     * )
     */
    public function registerAction(Request $request)
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $firstName = $request->get('firstName');
        $lastName = $request->get('lastName');
        if (is_null($email) || is_null($password) || is_null($firstName) || is_null($lastName)) {
            return new JsonResponse([
            'success' => false,
            'message' => 'password or email is missing',
        ]);
        }
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $user->setEnabled(true);
        $user->setPlainPassword($password);
        $user->setUsername($email);
        $user->setEmail($email);
        $user->addRole('ROLE_MEMBER');
        $this->getDoctrine()->getManager()->persist($user);
        try {
            $userManager->updateUser($user);
            $userIH = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($user->getId());
            $userIH->setFirstName($firstName);
            $userIH->setLastName($lastName);
            $this->getDoctrine()->getManager()->flush();
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($user, 'json');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));

        return $response;
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      description="Facebook connect",
     *      input="Clab\ApiOldBundle\Form\Type\User\RestFacebookConnectType",
     * )
     */
    public function facebookConnectAction()
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new RestFacebookConnectType());
        $form->submit($this->getRequest());

        if ($form->isValid()) {
            $accessToken = $form->get('accessToken')->getData();

            $fb = new \Facebook\Facebook(array(
                'app_id' => $this->container->getParameter('api_facebook_id'),
                'app_secret' => $this->container->getParameter('api_facebook_secret'),
                'default_graph_version' => 'v2.5',

            ));
            $response = $fb->get('/me?fields=id,gender,email', $accessToken);
            $fbData = $response->getDecodedBody();

            $fbUser = $this->getDoctrine()->getManager()->getRepository('ClabUserBundle:User')->findOneBy(array('facebookId' => $fbData['id']));

            if ($fbUser) {
                $user = $fbUser;
            } else {
                $fbUser = $this->getDoctrine()->getManager()->getRepository('ClabUserBundle:User')->findOneBy(array('email' => $fbData['email']));
                if ($fbUser) {
                    $user = $fbUser;
                }
            }

            $userManager = $this->get('fos_user.user_manager');

            $isNew = false;
            if (empty($user)) {
                $user = $userManager->createUser();
                $user->setEnabled(true);
                $user->addRole('ROLE_MEMBER');

                if (!$user->getPassword()) {
                    $user->setPassword('');
                }

                $user->setSource($this->get('api.session_manager')->getService());

                $isNew = true;
            } else {
                unset($fbData['email']);
            }

            $user->setFBData($fbData);
            $user->setFacebookAccessToken($accessToken);
            $userManager->updateUser($user);
            $em->flush();

            if ($isNew) {
                if (strpos(strtolower($this->get('api.session_manager')->getService()), 'tttruck') !== false) {
                    $mailManager = $this->container->get('clab_ttt.mail_manager');
                    $mailManager->registerMail($user);
                } elseif (strpos(strtolower($this->get('api.session_manager')->getService()), 'clickeat') !== false  && strpos(strtolower($this->get('api.session_manager')->getService()), 'pro') == false) {
                    $mailManager = $this->container->get('app_people.mail_manager');
                    $mailManager->registerMail($user);
                }
            }

            if ($user->getNewsletterClickeat()) {
                try {
                    $this->get('app_people.mailchimp_manager')->subscribeToList($user, 'clickeat');
                } catch (\Exception $e) {
                }
            } else {
                try {
                    $this->get('app_people.mailchimp_manager')->unSubscribeToList($user, 'clickeat');
                } catch (\Exception $e) {
                }
            }
            $this->loginUser($user);
            $firewallName = $this->container->getParameter('fos_user.firewall_name');

            $token = new UsernamePasswordToken($user, $user->getPassword(), $firewallName, $user->getRoles());
            $securityContext = $this->container->get('security.token_storage'); // do it your way
            $securityContext->setToken($token);
            $session = new Session();
            $serializer = $this->container->get('serializer');
            $session->set('loginToken', serialize($token));

            return new JsonResponse([
                'success' => true,
                'user' => $serializer->serialize($token->getUser(), 'json'),
                'token' => $token->getCredentials(),
            ]);
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);
        //return View::create($form, 400);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="Edit user profile",
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestUserType",
     * )
     * @ParamConverter("user", class="ClabUserBundle:User")
     */
    public function editAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(new RestUserType(), $user);
        $form->submit($request, false);

        if ($form->isValid()) {
            if ($form->has('plainPassword') && $form->get('plainPassword')->getData()) {
                if (!$form->has('password') || !$form->get('password')->getData()) {
                    return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Password mandatory for password
                    change');
                }

                $password = $form->get('password')->getData();

                $encoder_service = $this->get('security.encoder_factory');
                $encoder = $encoder_service->getEncoder($this->getUser());
                $encoded_pass = $encoder->encodePassword($password, $this->getUser()->getSalt());

                if ($this->getUser()->getPassword() !== $encoded_pass) {
                    return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Password incorrect');
                }
            }

            $userManager = $this->get('fos_user.user_manager');

            $userManager->updateUser($user);

            $em->flush();

            $response = new Response(204, '');

            return $response;
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="Edit user profile cover",
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestUserProfileCoverType",
     * )
     */
    public function editCoverAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $form = $this->createForm(new RestUserProfileCoverType(), $user);
        $form->submit($request);

        if ($form->isValid()) {
            $user->setImageName('profile');

            $em->flush();

            return new Response(204, '');
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);
        //return View::create($form, 400);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="Reset password",
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestUserProfileCoverType",
     * )
     */
    public function resetPasswordAction(Request $request)
    {
        $email = $request->request->get('email');

        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($email);
        if (!$user || is_null($user)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'User does not exist',
            ]);
        }
        $tokenGenerator = $this->container->get('fos_user.util.token_generator');
        $user->setConfirmationToken($tokenGenerator->generateToken());

        try {
            $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return new JsonResponse('Password reset', 200);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="send validation code",
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestUserProfileCoverType",
     * )
     */
    public function sendValidationCodeAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $phone = trim($request->request->get('phone'));
        if (!$phone) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Tous les champs ne sont pas remplis');
        }
        $phoneUtil = $this->get('libphonenumber.phone_number_util');
        $phoneNumber = $phoneUtil->parse($phone, 'FR');
        $phoneConstraint = new PhoneNumber();
        $errorList = $this->get('validator')->validate($phoneUtil->format($phoneNumber, 'FR'), $phoneConstraint);
        if (count($errorList) > 0) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Numéro de téléphone invalide');
        }
        $profile = $this->getDoctrine()->getManager()->getRepository('AppPeopleBundle:Profile')->findOneBy(array('phone' => $phoneNumber));
        if ($profile) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Ce numéro de téléphone est déjà
            enregistré');
        }
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 5; ++$i) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        $this->get('session')->set('profile_confirm_phone', $phone);
        $this->get('session')->set('profile_confirm_code', $code);
        $em->flush();
        $smsManager = $this->get('app_people.sms_manager');
        $smsManager->sendSms($phone, 'Votre code de confirmation : '.$code);

        return new JsonResponse('ok', 200);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="validate the code",
     *      input="Clab\ApiOldBundle\Form\Type\Product\RestUserProfileCoverType",
     * )
     */
    public function validateAction(Request $request)
    {
        $userCode = strtoupper(trim($request->request->get('code')));
        if (!$userCode) {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Tous les champs ne sont pas remplis');
        }
        $phone = $this->get('session')->get('profile_confirm_phone') ? $this->get('session')->get('profile_confirm_phone') : null;
        $code = $this->get('session')->get('profile_confirm_code') ? $this->get('session')->get('profile_confirm_code') : null;
        if (!$phone || !$code) {
            return $this->get('api.rest_manager')->getErrorResponse(55555, 'Renseignez à nouveau votre numéro de téléphone');
        } else {
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            try {
                $phone = $phoneUtil->parse($phone, 'FR');
            } catch (\libphonenumber\NumberParseException $e) {
                return $this->get('api.rest_manager')->getErrorResponse(55555, 'Renseignez à nouveau votre numéro de téléphone');
            }
        }
        $user = $this->get('api.session_manager')->getUser();
        if ($userCode == $code) {
            $this->get('session')->set('profile_confirm_phone', null);
            $this->get('session')->set('profile_confirm_code', null);
            $user->addRole('ROLE_MEMBER_CONFIRMED');
            $user->setPhone($phone);
            $this->get('fos_user.user_manager')->updateUser($user);
        } else {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Le code saisi n\'est pas valide');
        }

        return new JsonResponse('Ok', 200);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="Login on caisse pin",
     *      requirements={
     *          {"name"="pincode", "dataType"="string", "required"=true, "description"="pincode to login"},
     *          {"name"="Restaurant", "dataType"="string", "required"=true, "description"="restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function loginPinCodeAction(Restaurant $restaurant, Request $request)
    {
        $pincode = $request->get('pincode');

        $pinCodesRestaurant = $this->getDoctrine()->getRepository('ClabUserBundle:Pincode')->findBy(array(
            'restaurant' => $restaurant,
        ));
        if (empty($pinCodesRestaurant)) {
            throw new NotFoundHttpException('Aucun pincode enregistré par le restaurant');
        }
        $results = array();
        foreach ($pinCodesRestaurant as $key => $codes) {
            $results[$key] = $codes->getCode();
        }

        if (in_array($pincode, $results)) {
            $result = $this->getDoctrine()->getRepository('ClabUserBundle:Pincode')->findOneBy(array(
                'code' => $pincode, 'restaurant' => $restaurant
            ));
        } else {
            throw new NotFoundHttpException('Pin invalide pour ce restaurant');
        }

        if (empty($results)) {
            throw new NotFoundHttpException('Aucun pincode enregistré par le restaurant');
        }

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($result, 'json');

        return new Response($response);
    }

    /**
     * Add to friendlist.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Add user to friendlist",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function addUserToFriendListAction(Request $request)
    {
        $idUser = $request->get('id');
        $idUser2 = $request->get('id2');
        $user1 = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($idUser);
        $user2 = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($idUser2);
        $user1->addMyFriend($user2);
        $user2->addFriendsWithMe($user1);
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

    /**
     * Find all images of a user.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Find all images from a user",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     * @ParamConverter("user", class="ClabUserbundle:User", options={"id" = "id"})
     */
    public function findAllImageFromUserAction(User $user, Request $request)
    {
        $images = $this->getDoctrine()->getRepository('ClabMediaBundle:Image')->findBy(array(
            'profile' => $user,
        ));
        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($images, 'json');

        return new Response($response);
    }

    /**
     * Upload a new image of a restaurant.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Upload a new image of a restaurant",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @return View
     */
    public function uploadImageInRestaurantAction($entityId, $userId)
    {
        $user = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($userId);
        $imageManager = $this->get('app_media.image_manager');

        list($success, $image) = $imageManager->upload('restaurant', $entityId, $user, 'public');
        if ($success) {
            $cacheManager = $this->get('liip_imagine.cache.manager');
            $url = $cacheManager->getBrowserPath($image->getWebPath(), 'square_180');

            $data = $this->container->get('jms_serializer')
                ->serialize(array(
                    'success' => $success,
                    'url' => $url,
                    'urlFull' => $url,
                    'id' => $image->getId(),
                ), 'json');

            return new Response($data, 200);
        }

        return new Response('Erreur dans l\'upload', 400);
    }

    /**
     * Add to user database.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Add user to a restaruant user database",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   },
     *  requirements={
     *      {
     *          "name"="restaurantId",
     *          "dataType"="integer",
     *          "description"="ID of the restaurant"
     *      }
     *  },
     * parameters={
     *      {"name"="id", "dataType"="integer", "required"=false, "description"="id of clickeat user"},
     *      {"name"="firstName", "dataType"="string", "required"=false, "description"="firstname"},
     *      {"name"="lastName", "dataType"="string", "required"=false, "description"="lastName"},
     *      {"name"="email", "dataType"="string", "required"=false, "description"="email"},
     *      {"name"="phone", "dataType"="string", "required"=false, "description"="phone"},
     *      {"name"="birthday", "dataType"="string", "required"=false, "description"="birthday (format dd-mm-YYYY)"},
     *      {"name"="comment", "dataType"="string", "required"=false, "description"="comment"},
     *      {"name"="company", "dataType"="string", "required"=false, "description"="name of the company of the user"},
     *      {"name"="newsletter", "dataType"="boolean", "required"=false, "description"="if the user wants to
     * register to the newsletter"},
     *  },
     * )
     *
     * @return View
     */
    public function addUserToUserDatabaseAction(Request $request)
    {
        $idUser = $request->get('id');
        $restaurantId = $request->get('restaurantId');
        $firstName = $request->get('firstName');
        $lastName = $request->get('lastName');
        $email = $request->get('email');
        $phone = $request->get('phone');
        $birthday = $request->get('birthday');
        $comment = $request->get('comment');
        $company = $request->get('company');
        $newsletter = $request->get('newsletter');
        $user = null;
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
        $userDB = new UserDataBase();
        $userDB->setRestaurant($restaurant);
        if (!is_null($idUser)) {
            $user = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($idUser);
            $userDB->setUser($user);
        }
        if (!is_null($firstName)) {
            $userDB->setFirstName($firstName);
        } elseif (!is_null($user)) {
            $userDB->setFirstName($user->getFirstName());
        }
        if (!is_null($lastName)) {
            $userDB->setLastName($lastName);
        } elseif (!is_null($user)) {
            $userDB->setLastName($user->getLastName());
        }
        if (!is_null($email)) {
            $userDB->setEmail($email);
        } elseif (!is_null($user)) {
            $userDB->setEmail($user->getEmail());
        }
        if (!is_null($phone)) {
            $userDB->setPhone($phone);
        } elseif (!is_null($user)) {
            $userDB->setPhone($user->getPhone());
        }
        if (!is_null($birthday)) {
            new \DateTime($birthday);
            $userDB->setBirthday($birthday);
        } elseif (!is_null($user)) {
            $userDB->setBirthday($user->getBirthday());
        }
        if (!is_null($comment)) {
            $userDB->setNote($comment);
        }

        if (!is_null($company)) {
            $userDB->setCompany($company);
        }
        if (!is_null($newsletter) && $newsletter == true) {
            $userDB->setSubscribedNewsletter(true);
        }

        $this->getDoctrine()->getManager()->persist($userDB);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Add to user database.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = true,
     *   description = "Add user to a restaruant user database",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   },
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "description"="ID of the user"
     *      }
     *  },
     * parameters={
     *      {"name"="restaurantID", "dataType"="integer", "required"=false, "description"="id the restaurant"},
     *      {"name"="idCE", "dataType"="integer", "required"=false, "description"="id the clickeat user you want to
     * link to this user"},
     *      {"name"="firstName", "dataType"="string", "required"=false, "description"="firstname"},
     *      {"name"="lastName", "dataType"="string", "required"=false, "description"="lastName"},
     *      {"name"="email", "dataType"="string", "required"=false, "description"="email"},
     *      {"name"="phone", "dataType"="string", "required"=false, "description"="phone"},
     *      {"name"="birthday", "dataType"="string", "required"=false, "description"="birthday (format dd-mm-YYYY)"},
     *      {"name"="comment", "dataType"="string", "required"=false, "description"="comment"},
     *      {"name"="company", "dataType"="string", "required"=false, "description"="name of the company of the user"},
     *      {"name"="newsletter", "dataType"="boolean", "required"=false, "description"="if the user wants to
     * register to the newsletter"},
     *  },
     * )
     *
     * @return View
     */
    public function editUserToUserDatabaseAction(Request $request)
    {
        $idUser = $request->get('id');
        $userDB = $this->getDoctrine()->getRepository('ClabBoardBundle:UserDataBase')->find($idUser);
        if (!$userDB) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No user found',
            ]);
        }
        $idCE = $request->get('idCE');
        $restaurantId = $request->get('restaurantId');
        $firstName = $request->get('firstName');
        $lastName = $request->get('lastName');
        $email = $request->get('email');
        $phone = $request->get('phone');
        $birthday = $request->get('birthday');
        $comment = $request->get('comment');
        $company = $request->get('company');
        $newsletter = $request->get('newsletter');
        if (!is_null($restaurantId)) {
            $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($restaurantId);
            if (!is_null($restaurant)) {
                $userDB->setRestaurant($restaurant);
            }
        }
        if (!is_null($firstName)) {
            $userDB->setFirstName($firstName);
        }

        if (!is_null($lastName)) {
            $userDB->setLastName($lastName);
        }

        if (!is_null($email)) {
            $userDB->setEmail($email);
        }

        if (!is_null($phone)) {
            $userDB->setPhone($phone);
        }

        if (!is_null($birthday)) {
            new \DateTime($birthday);
            $userDB->setBirthday($birthday);
        }

        if (!is_null($comment)) {
            $userDB->setNote($comment);
        }
        if (!is_null($idCE)) {
            $ceUser = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($idCE);
            if (!is_null($ceUser)) {
                $userDB->setUser($ceUser);
            }
        }
        if (!is_null($company)) {
            $userDB->setCompany($company);
        }
        if (!is_null($newsletter) && $newsletter == true) {
            $userDB->setSubscribedNewsletter(true);
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }
}
