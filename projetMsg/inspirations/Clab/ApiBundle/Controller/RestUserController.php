<?php

namespace Clab\ApiBundle\Controller;

use Clab\ApiBundle\Form\Type\User\RestFacebookConnectType;
use Clab\ApiBundle\Form\Type\User\RestUserRegisterType;
use Clab\ApiBundle\Manager\RestManager;
use Clab\BoardBundle\Entity\UserDataBase;
use Clab\CoreBundle\Service\Mailer;
use Clab\DeliveryBundle\Entity\DeliveryMan;
use Clab\LocationBundle\Entity\Address;
use Clab\PeopleBundle\Validator\Constraints\PhoneNumber;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\Loyalty;
use Clab\UserBundle\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use FOS\RestBundle\View\View;
use Clab\UserBundle\Entity\User;
use Clab\ApiBundle\Form\Type\User\RestUserType;
use Clab\ApiBundle\Form\Type\User\RestUserProfileCoverType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use JMS\Serializer\SerializationContext;

class RestUserController extends FOSRestController
{
    /**
     * Return the overall user list.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = "/api/v1/users/list",
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
     *   resource="/api/v1/users/{email}",
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
        $userManager = $this->get('app_user.user_manager');
        $user = $userManager->findUserBy(array('email' => $email));

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No user found.',
            ]);
        }
        $serializer = $this->get('serializer');

        $reviews = $this->get('app_user.user_manager')->getReviews($user);

        $user->setReviews($reviews);

        $manager = $this->get('app_restaurant.restaurant_manager');
        $timeSheetManager = $this->get('app_restaurant.timesheet_manager');
        $ids = array_keys($user->getFavorites());

        $restaurants = $manager->findByIds($ids, null);

        if (count($restaurants)) {
            foreach ($restaurants as $key => $restaurantData) {
                $restaurant = $restaurantData[0];

                $restaurant->setDistance($restaurantData['distance']);
                $isOpen = $timeSheetManager->getOpenedStatus($restaurant->getFlatTimeSheet());
                $restaurant->setIs_Open($isOpen);

                $restaurants[$key] = $restaurant;
            }

            $restaurants = $serializer->serialize($restaurants, 'json',  SerializationContext::create()->setGroups(array('favorite')));
            $user->setFavoritesRestaurants(json_decode($restaurants, true));
        }

        $response = $serializer->serialize($user, 'json');

        return new Response($response);
    }

    /**
     * Return an user identified by username/email.
     *
     *
     * @ApiDoc(
     *   section="User",
     *   resource="/api/v1/search/users/{id}",
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
    public function getUserByIdAction($id)
    {
        $user =  $this->get('doctrine')->getRepository(User::class)->find($id);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No user found.',
            ]);
        }
        $serializer = $this->get('serializer');

        $reviews = $this->get('app_user.user_manager')->getReviews($user);

        $reviews = $serializer->serialize($reviews, 'json',  SerializationContext::create()->setGroups(array('profile','favorite')));
        $user->setReviews(json_decode($reviews, true));

        $manager = $this->get('app_restaurant.restaurant_manager');
        $timeSheetManager = $this->get('app_restaurant.timesheet_manager');
        if ($user->getFavorites()) {
            $ids = array_keys($user->getFavorites());

            $restaurants = $manager->findByIds($ids, null);

            if (count($restaurants)) {
                foreach ($restaurants as $key => $restaurant) {

                    $isOpen = $timeSheetManager->getOpenedStatus($restaurant->getFlatTimeSheet());
                    $restaurant->setIs_Open($isOpen);

                    $restaurants[$key] = $restaurant;
                }

                $restaurants = $serializer->serialize($restaurants, 'json',  SerializationContext::create()->setGroups(array('favorite')));
                $user->setFavoritesRestaurants(json_decode($restaurants, true));
            }
        }

        $response = $serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('search','searchUser')));

        return new Response($response);
    }

    protected function loginUser(User $user)
    {
        $jwt = $this->get('lexik_jwt_authentication.jwt_manager')->create($user);

        return new JsonResponse(['token' => $jwt]);
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
     *      resource="/api/v1/users/validate",
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
        $user = $this->getUser();

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
     *      input="Clab\ApiBundle\Form\Type\User\RestUserRegisterType",
     * )
     */
    public function registerAction(Request $request)
    {
        /**
         * @var RestManager
         */
        $restManager = $this->get('api.rest_manager');
        $form = $this->createForm(new RestUserRegisterType(), new User());

        if ($form->submit($request, false) && $form->isValid()) {
            /**
             * @var UserManager
             */
            $userManager = $this->container->get('fos_user.user_manager');

            $user = $form->getData();

            $user->setPlainPassword($user->getPassword());
            $user->setEnabled(true);
            $user->setRole('ROLE_USER');
            $user->setUsername($user->getFirstName().' '.$user->getLastName());

            $userManager->updateUser($user, true);

            return $this->loginUser($user);
        }

        return $restManager->getFormErrorResponse($form);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      description="Deliveror connect"
     * )
     */
    public function postDeliverorConnectAction(Request $request) {
        $data = $request->request;
        $em = $this->getDoctrine()->getManager();
        /**
         * @var UserRepository
         */
        $repository = $em->getRepository(DeliveryMan::class);
        $deliveryMan = $repository->findOneBy(array(
            'phone' => $data->get('phone'),
            'code' => $data->get('pin')
        ));

        if ($deliveryMan) {
            return $this->loginUser($deliveryMan->getUser());
        }

        return new JsonResponse(array('error' => true));
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      description="Facebook connect",
     *      input="Clab\ApiBundle\Form\Type\User\RestFacebookConnectType",
     * )
     */
    public function facebookConnectAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * @var UserRepository
         */
        $repository = $em->getRepository(User::class);

        $form = $this->createForm(new RestFacebookConnectType());
        $form->submit($request);

        if ($form->isValid()) {
            $accessToken = $form->get('accessToken')->getData();

            $fb = new \Facebook\Facebook(array(
                'app_id' => $this->container->getParameter('api_facebook_id'),
                'app_secret' => $this->container->getParameter('api_facebook_secret'),
                'default_graph_version' => 'v2.5'
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
                } elseif (strpos(strtolower($this->get('api.session_manager')->getService()),
                        'clickeat') !== false && strpos(strtolower($this->get('api.session_manager')->getService()),
                        'pro') == false
                ) {
                    /**
                     * @var $mailManager Mailer
                     */
                    $mailManager = $this->container->get('core.mailer');
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

            return $this->loginUser($user);
        }

        $facebookId = $request->request->get('facebookId');
        $email = $request->request->get('email');

        /**
         * @var User
         */
        $user = $repository->findFacebookOrEmail($facebookId, $email);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'message' => 'User not found, please register',
            ], 404);
        }

        $user->setFacebookId($facebookId);
        $em->persist($user);
        $em->flush();


        return $this->loginUser($user);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/profile",
     *      description="Edit user profile",
     *      input="Clab\ApiBundle\Form\Type\Product\RestUserType",
     * )
     */
    public function editAction(Request $request)
    {
	$user = $this->getUser();
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

            $response = new Response('ok');

            return $response;
        }

        return $this->get('api.rest_manager')->getFormErrorResponse($form);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/profile/cover",
     *      description="Edit user profile cover",
     *      input="Clab\ApiBundle\Form\Type\Product\RestUserProfileCoverType",
     * )
     */
    public function editCoverAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $form = $this->createForm(new RestUserProfileCoverType());
        $form->submit($request);

        if ($form->isValid()) {
            $user->setFile($form->get('image')->getData());
	    $user->upload();
            $em->persist($user);
            $em->flush();

            return new Response($user->getImage());
        }
        return $this->get('api.rest_manager')->getFormErrorResponse($form);
    }


    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/password/reset",
     *      description="Reset password",
     *      input="Clab\ApiBundle\Form\Type\Product\RestUserProfileCoverType",
     * )
     */
    public function resetPasswordAction(Request $request)
    {
        $email = $request->request->get('email');

        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($email);

        if (!$user || is_null($user)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Email incorrect',
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

        return new JsonResponse([
            'success' => true,
            'message' => 'Mot de passe reinitialisé',
        ]);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="send validation code",
     *      input="Clab\ApiBundle\Form\Type\Product\RestUserProfileCoverType",
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
     *      input="Clab\ApiBundle\Form\Type\Product\RestUserProfileCoverType",
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
            return new Response('Aucun pincode enregistré par le restaurant', 400);
        }
        $results = array();
        foreach ($pinCodesRestaurant as $key => $codes) {
            $results[$key] = $codes->getCode();
        }

        if (in_array($pincode, $results)) {
            $restaurant->setClient(null);
            $result = $this->getDoctrine()->getRepository('ClabUserBundle:Pincode')->findOneBy(array(
                'code' => $pincode, 'restaurant' => $restaurant,
            ));
        } else {
            return new Response('Pin invalide pour ce restaurant', 400);
        }

        if (empty($results)) {
            return new Response('Aucun pincode enregistré par le restaurant', 400);
        }

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($result, 'json');

        return new Response($response,200);
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
     *   resource="/api/v1/users/database",
     *   description = "Add user to a restaurant user database",
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
     *  }
     * )
     *
     * @return View
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function addUserDatabaseAction(Request $request, Restaurant $restaurant)
    {
        $userDB = new UserDataBase();
        $user = null;
        $idUser = $request->get('id');

        $userDB->setRestaurant($restaurant);

        $firstName = $user ? $user->getFirstName() : $request->get('firstName');
        $lastName = $user ? $user->getLastName() : $request->get('lastName');
        $email = $user ? $user->getEmail() : $request->get('email');
        $phone = $user ? $user->getPhone() : $request->get('phone');
        $birthday = $user ? $user->getBirthday() : $request->get('birthday');

        $comment = $request->get('comment');
        $company = $request->get('company');
        $newsletter = $request->get('newsletter');

        $userDB->setFirstName($firstName);
        $userDB->setLastName($lastName);
        $userDB->setEmail($email);
        $userDB->setPhone($phone);
        $userDB->setBirthday($birthday);
        $userDB->setNote($comment);

        if (!is_null($idUser)) {
            $u = $this->getDoctrine()->getRepository(User::class)->find($idUser);
            $userDB->setUser($u);
        } else {
            $u = new User();
            $u->setEmail($userDB->getEmail());
            $u->setPlainPassword(md5($userDB->getEmail()));
            $u->setFirstName($userDB->getFirstName());
            $u->setLastName($userDB->getLastName());
            $u->setPhone($userDB->getPhone());
            $u->setBirthday($userDB->getBirthday());
            $userDB->setUser($u);
            $this->getDoctrine()->getManager()->persist($u);

            $this
                ->get('app_shop.loyalty_manager')
                ->generateFirstLoyalties($u);
            ;
        }

        if (!is_null($company)) {
            $userDB->setCompany($company);
        }

        if (!is_null($newsletter) && $newsletter == true) {
            $userDB->setSubscribedNewsletter(true);
        }

        $this->getDoctrine()->getManager()->persist($userDB);
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(array('success' => true, 'id' => $u->getId()));
    }

    /**
     * Add to user database.
     *
     * @ApiDoc(
     *   section="User",
     *   resource="/api/v1/users/database",
     *   description = "edit user from userDatabase",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   },
     *  requirements={
     *     {
     *          "name"="restaurantId",
     *          "dataType"="integer",
     *          "description"="ID of the restaurant"
     *      },
     *      {
     *          "name"="userId",
     *          "dataType"="integer",
     *          "description"="ID of the user to edit"
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
     *  }
     * )
     *
     * @return View
     *
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function editUserDatabaseAction(Request $request, Restaurant $restaurant, $userId)
    {
        $idUser = $request->get('id');
        $userDB = $this->getDoctrine()->getRepository('ClabBoardBundle:UserDataBase')->findOneBy(array('id' => $userId, 'restaurant' => $restaurant, 'isDeleted' => false));
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
        $homeAddress = explode($request->get('homeAddress'));
        $jobAddress = explode($request->get('jobAddress'));

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
        if (!is_null($homeAddress)) {
            if(!$userDB->getHomeAddress()) {
                $userHomeAddress = new Address();
            }else {
                $userHomeAddress = $userDB->getHomeAddress();
            }

            if(isset($homeAddress[0])) {
                $userHomeAddress->setStreet($homeAddress[0]);
            }

            if(isset($homeAddress[1])) {
                $userHomeAddress->setCity($homeAddress[1]);
            }

            if(isset($homeAddress[2])) {
                $userHomeAddress->setZip($homeAddress[2]);
            }



        }
        if (!is_null($newsletter) && $newsletter == true) {
            $userDB->setSubscribedNewsletter(true);
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/database",
     *      description="get userDatabase of given restaurant",
     *      requirements={
     *          {
     *              "name"="restaurantId",
     *              "dataType"="integer",
     *              "description"="ID of the restaurant"
     *          }
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function getUserDatabaseAction(Request $request, Restaurant $restaurant)
    {
        $search = $request->get('search');

        if($search) {
            $usersDB = $this->getDoctrine()->getRepository(User::class)->findAllFiltered($search, 'ROLE_MEMBER');

            $users = [];

            foreach ($usersDB as $u) {
                $loyalties = new ArrayCollection();
                $now = new \DateTime();
                foreach ($u->getLoyalties() as $l) {
                    if(!$l->getIsUsed() && $l->getValidUntil() > $now) {
                        $loyalties->add($l);
                    }
                }

                $u->setLoyalties($loyalties);

                $users[] = $u;
            }
        } else {
            $usersDB = $this->getDoctrine()->getRepository(UserDataBase::class)->findBy(array('restaurant' => $restaurant),array('updated' => 'DESC'),1000);

            $users = [];

            foreach ($usersDB as $user) {
                $u = $user->getUser();
                if($u) {
                    $loyalties = new ArrayCollection();
                    $now = new \DateTime();
                    foreach ($u->getLoyalties() as $l) {
                        if(!$l->getIsUsed() && $l->getValidUntil() > $now) {
                            $loyalties->add($l);
                        }
                    }

                    $u->setLoyalties($loyalties);

                    $users[] = $u;
                }
            }
        }

        $serializer = $this->container->get('jms_serializer');

        $response = $serializer->serialize($users, 'json',  SerializationContext::create()->setGroups(array('pro')));

        return new Response($response, 200);

    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/database",
     *      description="remove user from userDatabase of given restaurant",
     *      requirements={
     *          {
     *              "name"="restaurantId",
     *              "dataType"="integer",
     *              "description"="ID of the restaurant"
     *          },
     *          {
     *              "name"="userId",
     *              "dataType"="integer",
     *              "description"="ID of the user to remove"
     *          }
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant", options={"id" = "restaurantId"})
     */
    public function removeUserDatabaseAction(Request $request, Restaurant $restaurant, $userId)
    {
        $userDB = $this->getDoctrine()->getRepository(UserDataBase::class)->findOneBy(array('restaurant' => $restaurant, 'id' => $userId));

        $userDB->setIsDeleted(true);

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }


    public function getTokenAction()
    {
        return new Response('', 401);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/me",
     *      description="get current user"
     * )
     */
    public function meAction(Request $request)
    {
        $location = null;
        $latitude = $request->query->get('lat');
        $longitude = $request->query->get('lng');

        if ($latitude && $longitude) {
            $location = array('lat' => $latitude, 'lng' => $longitude);
        }

        $serializer = $this->container->get('jms_serializer');
        $user = $this->getUser();
        if ($user) {

            $reviews = $this->get('app_user.user_manager')->getReviews($user);

            $reviews = $serializer->serialize($reviews, 'json',  SerializationContext::create()->setGroups(array('profile','favorite')));
            $user->setReviews(json_decode($reviews, true));
        }

        $manager = $this->get('app_restaurant.restaurant_manager');
        $timeSheetManager = $this->get('app_restaurant.timesheet_manager');
        $ids = array_keys($user->getFavorites());

        $restaurants = $manager->findByIds($ids, $location);

        foreach ($user->getFollowers() as $follower) {
            $follower->setEmail(null);
        }
        foreach ($user->getFollowed() as $followed) {
            $followed->setEmail(null);
        }

        if (count($restaurants) && $location) {
            foreach ($restaurants as $key => $restaurantData) {
                $restaurant = $restaurantData[0];

                $restaurant->setDistance($restaurantData['distance']);
                $isOpen = $timeSheetManager->getOpenedStatus($restaurant->getFlatTimeSheet());
                $restaurant->setIs_Open($isOpen);

                $restaurants[$key] = $restaurant;
            }

            $restaurants = $serializer->serialize($restaurants, 'json',  SerializationContext::create()->setGroups(array('favorite')));
            $user->setFavoritesRestaurants(json_decode($restaurants, true));
        }

        $response = $serializer->serialize($user, 'json',  SerializationContext::create()->setGroups(array('app')));

        return new Response($response, 200);
    }

        /**
         * Get reviews by user.
         *
         * @ApiDoc(
         *      section="User",
         *      resource="/api/v1/users/{email}/reviews",
         *      description="get reviews by user"
         * )
         */
        public function getReviewsAction()
        {
            $reviews = $this->get('app_user.user_manager')->getReviews($this->getUser());

            $serializer = $this->get('serializer');
            $response = $serializer->serialize($reviews, 'json');

            return new Response($response);
        }

    /**
     * Get favorites restaurants id and slug for current user.
     *
     * ### Response format ###
     *
     *     {
     *       "Favorites":{
     *         "1": "nakee-s",
     *         "29": "bagelstein",
     *         ...
     *       }
     *     }
     *
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/favorites/restaurant",
     *      description="get favorite restaurants"
     * )
     */
    public function getFavoritesAction()
    {
        if(count($this->getUser()->getFavorites()) <= 0) {
            return new JsonResponse('Aucun favoris.');
        }

        $restaurants = $this->get('app_user.user_manager')->getFavoritesAPI($this->getUser());
        /**
         * var $timeSheetManager TimeSheetManager.
         */
        $timeSheetManager = $this->get('app_restaurant.timesheet_manager');

        $results = array();

        foreach ($restaurants as $restaurant) {
            $isOpen = $timeSheetManager->getOpenedStatus($restaurant->getFlatTimeSheet());
            $restaurant->setIs_Open($isOpen);
            $results[] = $restaurant;
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($results, 'json',  SerializationContext::create()->setGroups(array('favorite')));

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/favorites",
     *      description="Remove restaurant from favorite",
     *      requirements={
     *          {"name"="restaurant", "dataType"="entity", "required"=true, "description"=" restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function removeFavoriteAction(Restaurant $restaurant)
    {
        $user = $this->getUser();

        $this->get('app_user.user_manager')->removeFavorite($user, $restaurant);
        $this->get('app_restaurant.restaurant_manager')->removeUserWhoLike($restaurant, $user->getId());

        return new JsonResponse('restaurant removed', 200);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/favorites",
     *      description="Add restaurant to favorite",
     *      requirements={
     *          {"name"="user", "dataType"="entity", "required"=true, "description"=" user"},
     *          {"name"="restaurant", "dataType"="entity", "required"=true, "description"=" restaurant"}
     *      }
     * )
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function addFavoriteAction(Restaurant $restaurant)
    {
        $serializer = $this->get('serializer');

        $user = $this->getUser();
        $id = $this->getUser()->getId();
        $userSerialized = $serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('app')));

        $this->get('app_user.user_manager')->addFavorite($user, $restaurant);
        $this->get('app_restaurant.restaurant_manager')->addUserWhoLike($restaurant, $id, $userSerialized);

        return new JsonResponse('restaurant added', 200);
    }

    public function getFriendsAction()
    {
        $friends = $this->get('app_user.user_manager')->getMyFriends($this->getUser());

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($friends, 'json');

        return new Response($response);
    }

    /**
     * Get followers of an user.
     *
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/followers",
     *      description="get reviews by user"
     * )
     */
    public function getFollowersAction()
    {
        $friends = $this->getUser()->getFollowers();

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($friends, 'json');

        return new Response($response);
    }

    /**
     * Add to followers.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = "/api/v1/users/follow",
     *   description = "Add user to friendlist",
     *   requirements={
     *       {"name"="userId", "dataType"="integer", "required"=true, "description"="user Id"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     * @ParamConverter("follower", class="ClabUserBundle:User", options={"id" = "userId"})
     */
    public function postFollowedAction(Request $request, User $follower)
    {
        if (!$follower) {
            return new Response('Uilisateur non trouvé', 404);
        }

        $user = $this->getUser();
        $user->addFollowed($follower);
        $follower->addFollower($user);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($follower);
        $manager->persist($user);
        $manager->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Remove from followers.
     *
     * @ApiDoc(
     *   section="User",
     *   resource = "/api/v1/users/unfollow",
     *   description = "Remove user from followers",
     *   requirements={
     *       {"name"="userId", "dataType"="integer", "required"=true, "description"="user Id"}
     *   },
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     * @ParamConverter("follower", class="ClabUserBundle:User", options={"id" = "userId"})
     */
    public function removeFollowedAction(Request $request, User $follower)
    {
        if (!$follower || !$this->getUser()->getFollowed()->contains($follower)) {
            return new Response('utilisateur suivi non trouvé', 400);
        }

        $user = $this->getUser();
        $user->removeFollowed($follower);
        $follower->removeFollower($user);

        $manager = $this->getDoctrine()->getManager();
        $manager->persist($follower);
        $manager->persist($user);
        $manager->flush();

        return new JsonResponse([
            'success' => true,
        ]);
    }

    /**
     * Get favorites restaurants id and slug for current user.
     *
     * ### Response format ###
     *
     *     {
     *       "Favorites":{
     *         "1": "nakee-s",
     *         "29": "bagelstein",
     *         ...
     *       }
     *     }
     *
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/favorites/search",
     *      description="get favorite searches for current user"
     * )
     */
    public function getFavoriteSearchesAction()
    {
        $favoriteSearches = $this->getUser()->getFavoriteSearch();

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($favoriteSearches, 'json');

        return new Response($response);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/favorites/search",
     *      description="Remove search from favorite searches",
     *      requirements={
     *          {"name"="searchKey", "dataType"="string", "required"=true, "description"="search Name or Key assigned by the user serves as Id"}
     *      }
     * )
     */
    public function removeFavoriteSearchAction($searchKey)
    {
        $this->getUser()->removeFavoriteSearch($searchKey);

        $this->getDoctrine()->getEntityManager()->flush();

        return new JsonResponse('la recherche a été supprimée de vos favoris', 200);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/favorites/search",
     *      description="add search to favorite searches",
     *      requirements={
     *          {"name"="searchKey", "datatype"="string", "required"="true", "description"="search Name or Key assigned by the user serves as Id"}
     *      },
     *      parameters={
     *          {"name"="location", "dataType"="string", "required"=false, "description"="address of the location (if
     *          not lat and lng)"},
     *          {"name"="lat", "dataType"="integer", "required"=false, "description"="latitude of the position (only
     *          if location is null)"},
     *          {"name"="lng", "dataType"="integer", "required"=false, "description"="longitude of the position (only
     *          if location is null)"},
     *          {"name"="limit", "dataType"="integer", "required"=false, "description"="search max results"},
     *          {"name"="offset", "dataType"="integer", "required"=false, "description"="offset for pagination results"},
     *          {"name"="categories", "dataType"="string", "required"=false, "description"="set categories of restaurants to search from separated by a comma ex: &categories=kebab,burger,americain"},
     *          {"name"="regimes", "dataType"="string", "required"=false, "description"="set restaurant's regimes to search from separated by a comma ex: &regimes=bio,vegetarien"},
     *          {"name"="type", "dataType"="string", "required"=false, "description"="set type of ordering for restaurant to search from, valid values are 'takeaway' or 'delivery' "},
     *          {"name"="price", "dataType"="int", "required"=false, "description"="set price range 0 / 1 / 2 / 3 / 4"},
     *          {"name"="discounts", "dataType"="int", "required"=false, "description"="true / false, have discounts"},
     *          {"name"="open", "dataType"="int", "required"=false, "description"="true, opened"},
     *          {"name"="online_order", "dataType"="int", "required"=false, "description"="true, have online order"}
     *      }
     * )
     */
    public function addFavoriteSearchAction(Request $request)
    {
        $favoriteSearch = $request->request->all();

        if (isset($favoriteSearch['searchKey'])) {
            $name = $favoriteSearch['searchKey'];
            unset($favoriteSearch['searchKey']);

            $favoriteSearch['offset'] = 0;

            $this->getUser()->addFavoriteSearch($name, $favoriteSearch);

            $this->getDoctrine()->getManager()->flush();

            return new JsonResponse('Recherche favorite enregistrée', 200);
        } else {
            return new JsonResponse('Veuillez indiquer un nom associé à votre recherche', 404);
        }
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/addresses",
     *      description="add address to user",
     *      parameters={
     *          {"name"="type", "dataType"="string", "required"=false, "description"="job or home"},
     *          {"name"="name", "dataType"="string", "required"=false, "description"="Title for address"},
     *          {"name"="company", "dataType"="string", "required"=false, "description"="Name of company"},
     *          {"name"="city", "dataType"="string", "required"=false, "description"="Name of city"},
     *          {"name"="zip", "dataType"="string", "required"=false, "description"="Zipcode"},
     *          {"name"="street", "dataType"="string", "required"=false, "description"="Street details"},
     *          {"name"="comment", "dataType"="string", "required"=false, "description"="additionnal informations"},
     *      }
     * )
     */
    public function postAddressAction(Request $request)
    {
        $parameters = $request->request;
        $em = $this->getDoctrine()->getManager();
        $setter = sprintf('set%sAddress', ucfirst($parameters->get('type')));

        $address = new Address();
        $address->setName($parameters->get('name'));
        $address->setCompany($parameters->get('company'));
        $address->setCity($parameters->get('city'));
        $address->setZip($parameters->get('zip'));
        $address->setStreet($parameters->get('street'));
        $address->setComment($parameters->get('comment'));

        $user = $this->getUser();
        $user->$setter($address);

        $em->persist($user);
        $em->flush();

        return new JsonResponse('Addresse ajoutée.', 200);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/phone-search",
     *      description="get users info by phone number",
     *      parameters={
     *          {"name"="phone", "dataType"="string", "required"=true, "description"="user phone"}
     *      }
     * )
     */
    public function findUsersInfoByPhoneAction(Request $request)
    {

        $phone = $request->query->get('phone');

        if ($phone) {
            $userManager = $this->get('app_user.user_manager');
            $foundUser = $userManager->findUserByPhone($phone);

            if ($foundUser) {

                $serializer = $this->get('serializer');
                $response = $serializer->serialize($foundUser, 'json', SerializationContext::create()->setGroups(array('white')));

                return new Response($response,200);
            }

        }

        return new JsonResponse('Veuillez indiquer un telephone valide.', 404);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/phone-search",
     *      description="get users info by array of phone number",
     *      parameters={
     *          {"name"="phones", "dataType"="json", "required"=true, "description"="user phones array"}
     *      }
     * )
     */
    public function findUsersIdByPhoneArrayAction(Request $request)
    {
        $phones = $request->request->get('phones');

        if (!is_array($phones)) {
            $phones = json_decode($phones);
        }

        if ($phones) {
            $userManager = $this->get('app_user.user_manager');

            $phonesFormated = array();
            $phonesIndex = array();
            foreach ($phones as $p) {
                $ph = preg_replace("/[^0-9,\s.-]*/",'',$p);
                $phoneSuffix = preg_replace('/^(0|\+33|33)*/','',$ph);
                $phonesIndex = array_merge($phonesIndex, array(" ".$p => $phoneSuffix));
                $phonesFormated = array_merge($phonesFormated,array('+33'.$phoneSuffix,'33'.$phoneSuffix,'0'.$phoneSuffix));
            }

            $foundUsers = $userManager->findUserByPhone($phonesFormated);
            $response = array();

            if ($foundUsers) {
                foreach($foundUsers as $foundUser) {
                    $phone = array_search(preg_replace("/[\s.-]*/",'',preg_replace('/^(0|\+33|33)*/','',$foundUser['phone'])), $phonesIndex);

                    if($phone){
                        $response[str_replace(" ","",$phone)] = $foundUser['id'];
                    }
                }


            }

            return new Response(json_encode($response),200);
        }

        return new JsonResponse('Veuillez indiquer un telephone valide.', 404);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/users/fb-search",
     *      description="get users info by array of facebook ids",
     *      parameters={
     *          {"name"="ids", "dataType"="json", "required"=true, "description"="user facebook ids array"}
     *      }
     * )
     */
    public function findUsersIdByFBArrayAction(Request $request)
    {
        $ids = $request->request->get('ids');

        if (!is_array($ids)) {
            $ids = json_decode($ids);
        }

        if ($ids) {
            $userManager = $this->get('app_user.user_manager');

            $foundUsers = $userManager->findUsersByFbIds($ids);

            $response = array();

            if ($foundUsers) {
                $em = $this->get('doctrine')->getManager();

                foreach($foundUsers as $foundUser) {
                    $response[$foundUser->getFacebookId()] = [
                                                                "id" => $foundUser->getId(),
                                                                "first_name" => $foundUser->getFirstName() ? $foundUser->getFirstName() : "",
                                                                "last_name" => $foundUser->getLastName() ? $foundUser->getLastName() : ""
                                                              ];

                    if(!$foundUser->getFollowers()->contains($this->getUser())) {
                        $foundUser->addFollower($this->getUser());
                    }
                    if(!$foundUser->getFollowed()->contains($this->getUser())) {
                        $foundUser->addFollowed($this->getUser());
                    }
                    if(!$this->getUser()->getFollowers()->contains($foundUser)) {
                        $this->getUser()->addFollower($foundUser);
                    }
                    if(!$this->getUser()->getFollowed()->contains($foundUser)) {
                        $this->getUser()->addFollowed($foundUser);
                    }

                    $em->persist($foundUser);
                }

                $em->persist($this->getUser());
                $em->flush();

            }

            return new Response(json_encode($response),200);
        }

        return new JsonResponse('Veuillez indiquer des id valides.', 404);
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource="/api/v1/search/users",
     *      description="get users info by phone number",
     *      parameters={
     *          {"name"="name", "dataType"="string", "required"=true, "description"="user name search if social ranking is true not required"},
     *          {"name"="limit", "dataType"="integer", "required"=true, "description"="number limit of search"},
     *          {"name"="offset", "dataType"="integer", "required"=true, "description"="offset from which to search from"},
     *          {"name"="social_ranking", "dataType"="boolean", "required"=true, "description"="social ranking search if name search is filled filters search by socialRanking"},
     *          {"name"="top_followed", "dataType"="boolean", "required"=true, "description"="order by most followed users"},
     *          {"name"="user_id", "dataType"="boolean", "required"=true, "description"="only show users that current user doesnt follow"},
     *     }
     * )
     */
    public function searchAction(Request $request)
    {
        $name = $request->get('name');
        $limit = $request->get('limit') ? $request->get('limit') : 10;
        $offset = $request->get('offset') ? $request->get('offset') : 0;
        $socialRanking = $request->get('social_ranking');
        $topFollowed = $request->get('top_followed');
        $followedUsers = array();

        if ($topFollowed && $this->getUser()) {
            foreach ($this->getUser()->getFollowed() as $followed) {
                $followedUsers[] = $followed->getId();
            }
        }

        $searchParams = array(
            'name' => $name,
            'limit' => $limit,
            'offset' => $offset,
            'socialRanking' => $socialRanking,
            'topFollowed' => $topFollowed,
            'followed' => $followedUsers
        );

        $foundUsers = $this->get('doctrine')->getRepository(User::class)->searchUsers($searchParams);

        if($foundUsers) {
            if ($topFollowed) {
                $fUsers = array();
                foreach ($foundUsers as $foundUserData) {
                   $fUsers[] = $foundUserData[0];
                }
                $foundUsers = $fUsers;
            }
            $serializer = $this->get('serializer');
            $response = $serializer->serialize($foundUsers, 'json', SerializationContext::create()->setGroups(array('social')));

            return new Response($response,200);
        }

        return new JsonResponse('Aucun utilisateur trouvé.', 404);

    }

}
