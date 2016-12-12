<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Form\Type\User\PincodeType;
use Clab\BoardBundle\Form\Type\User\UserEditType;
use Clab\BoardBundle\Form\Type\User\UserRestrictedType;
use Clab\UserBundle\Entity\Pincode;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use JMS\SecurityExtraBundle\Annotation\Secure;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use Clab\UserBundle\Entity\User;
use Clab\RestaurantBundle\Entity\StaffMember;
use Clab\DeliveryBundle\Entity\DeliveryMan;
use Clab\BoardBundle\Form\Type\User\PasswordType;
use Clab\BoardBundle\Form\Type\User\StaffMemberType;
use Clab\BoardBundle\Form\Type\User\RegisterType;

class UserController extends Controller
{
    public function loginAction()
    {
        $boardHelper = $this->get('board.helper');

        $request = $this->getRequest();
        $session = $request->getSession();

        if ($next = $this->getRequest()->get('next')) {
            $boardHelper->addParam('next', $next);
        }

        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null;
        }

        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        $csrfToken = $this->has('form.csrf_provider')
            ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
            : null;

        return $this->render('ClabBoardBundle:User:login.html.twig', array_merge($boardHelper->getParams(), array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $users = $this->get('app_user.user_manager')->getEmployeesForProxy($this->get('board.helper')->getProxy());
        $this->get('board.helper')->addParam('users', $users);
        if ($context == 'client') {
            $restaurantUsers = array();
            $choices = array();
            $restaurants = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findBy(array('client' => $this->get('board.helper')->getProxy()));
            foreach ($restaurants as $restaurant) {
                $slug = $restaurant->getSlug();
                $restaurantUsers[$slug]['users'] = $this->get('app_user.user_manager')->getEmployeesForProxy($restaurant)->toArray();
                $restaurantUsers[$slug]['restaurant'] = $restaurant;
                $restaurantUsers[$slug]['pincodes'] = $this->getDoctrine()->getRepository('ClabUserBundle:Pincode')->findBy(array('restaurant' => $restaurant));
                $choices[$slug] = sprintf('%s - %s %s', $restaurant->getName(), $restaurant->getAddress()->getStreet(), $restaurant->getAddress()->getCity());
            }

            $form = $this->createFormBuilder()->add('restaurants', 'choice', array(
                'required' => false,
                'choices' => $choices,
                'multiple' => false,
                'expanded' => false,
                ))->getForm();

            $this->get('board.helper')->addParams(array(
                'restaurantUsers' => $restaurantUsers,
                'form' => $form->createView(), ));
        }

        $requests = $this->getDoctrine()->getManager()->getRepository('ClabUserBundle:RegistrationRequest')->getAllForProxy($this->get('board.helper')->getProxy());
        $this->get('board.helper')->addParam('requests', $requests);

        if ($this->get('board.helper')->getProxy() instanceof \Clab\RestaurantBundle\Entity\Restaurant) {
            $pincodes = $this->getDoctrine()->getRepository('ClabUserBundle:Pincode')->findBy(array('restaurant' => $this->get('board.helper')->getProxy()));
            $this->get('board.helper')->addParam('pincodes', $pincodes);
        }

        return $this->render('ClabBoardBundle:User:library.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $users = $this->get('app_user.user_manager')->getEmployeesForProxy($this->get('board.helper')->getProxy());
        $this->get('board.helper')->addParam('users', $users);

        $requests = $this->getDoctrine()->getManager()->getRepository('ClabUserBundle:RegistrationRequest')->getAllForProxy($this->get('board.helper')->getProxy());
        $this->get('board.helper')->addParam('requests', $requests);

        return $this->render('ClabBoardBundle:User:libraryList.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function addPinCodeAction($context, $contextPk, Request $request)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $form = $this->createFormBuilder()
                ->add('name', 'text', array(
                    'required' => true,
                    'label' => 'Nom',
                ))
                ->add('code', 'text', array(
                    'required' => true,
                    'label' => 'Code',
                    'max_length' => 4,

                ))
                ->add('hasRightOnBo', 'checkbox', array(
                    'required' => false,
                    'label' => 'Accèder à myclickeat',
                ))
                ->add('hasRightOnLogs', 'checkbox', array(
                    'required' => false,
                    'label' => 'Accèder aux logs',
                ))
                ->getForm();
        if ($context == 'client') {
            $form->add('restaurant', 'entity', array(
                'class' => 'Clab\RestaurantBundle\Entity\Restaurant',
                'query_builder' => function (EntityRepository $er) {
                    return
                        $er->createQueryBuilder('r')
                            ->where('r.client = :client')
                            ->setParameter('client', $this->get('board.helper')->getProxy());
                },
            ));
        }
        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData();
            if ($context == 'client') {
                $restaurant = $data['restaurant'];
            } else {
                $restaurant = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Restaurant')
                    ->findOneBy(array('slug' => $contextPk));
            }

            $restaurantsPincode = $this->getDoctrine()->getManager()->getRepository('ClabUserBundle:Pincode')->findBy(array('restaurant' => $restaurant));
            $results = array();
            $resName = array();
            foreach ($restaurantsPincode as $key => $code) {
                $results[$key] = $code->getCode();
                $resName[$key] = $code->getName();
            }
            if (in_array($data['name'], $resName)) {
                $this->addFlash('danger', 'Cet utilisateur possède déjà un code pin, veuillez changer le nom d\'utilisateur');

                return $this->redirectToRoute('board_user_library', array('context' => $context, 'contextPk' => $contextPk));
            }
            if (in_array($data['code'], $results)) {
                $this->addFlash('danger', 'Le code pin existe déjà');

                return $this->redirectToRoute('board_user_library', array('context' => $context, 'contextPk' => $contextPk));
            }
            $pincode = new Pincode();
            $pincode->setRestaurant($restaurant);
            $pincode->setCode($data['code']);
            $pincode->setName($data['name']);
            $pincode->setHasRightOnBo($data['hasRightOnBo']);
            $pincode->setHasRightOnLogs($data['hasRightOnLogs']);
            $this->getDoctrine()->getManager()->persist($pincode);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('board_user_library', array('context' => $context, 'contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:User:pin_add.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function addAction($context, $contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        if (in_array('ROLE_MANAGER', $this->getUser()->getRoles()) || in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles()) || in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            $form = $this->createFormBuilder()
                ->add('firstName', 'text', array(
                    'required' => true,
                    'label' => 'Prénom',
                ))
                ->add('lastName', 'text', array(
                    'required' => true,
                    'label' => 'Nom',
                ))
                ->add('email', 'email', array(
                    'required' => true,
                    'label' => 'pro.users.emailLabel',
                ))
                ->add('plainPassword', 'password', array(
                    'required' => true,
                    'label' => 'Mot de passe',
                ))
                ->add('roles', 'choice', array(
                    'choices' => User::getManagerRoles(),
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'pro.users.roleLabel',
                    'data' => array('ROLE_MANAGER'),
                ))
                ->getForm();
            if ($context == 'client') {
                $form->add('chaine', 'checkbox', array(
                    'label' => 'Utilisateur chaine',
                    'mapped' => false,
                    'required' => false,
                ));
                $form->add('restaurant', 'entity', array(
                    'class' => 'Clab\RestaurantBundle\Entity\Restaurant',
                    'query_builder' => function (EntityRepository $er) {
                        return
                            $er->createQueryBuilder('r')
                                ->where('r.client = :client')
                                ->setParameter('client', $this->get('board.helper')->getProxy());
                    }, 'choice_label' => function ($restaurant) {
                        return sprintf('%s - %s %s', $restaurant->getName(), $restaurant->getAddress()->getStreet(), $restaurant->getAddress()->getCity());
                    },
                ));
            }
        } else {
            $form = $this->createFormBuilder()
                ->add('email', 'email', array(
                    'required' => true,
                    'label' => 'pro.users.emailLabel',
                ))
                ->add('roles', 'choice', array(
                    'choices' => User::getManager2Roles(),
                    'required' => false,
                    'multiple' => true,
                    'expanded' => true,
                    'label' => 'pro.users.roleLabel',
                    'data' => array('ROLE_MANAGER_2'),
                ))
                ->getForm();
        }

        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData();

            $user = $this->getDoctrine()->getManager()->getRepository('ClabUserBundle:User')->findOneBy(array('email' => $data['email']));

            if ($user) {
                if (!$this->get('board.helper')->getProxy()->getManagers()->contains($user)) {
                    $this->get('board.helper')->getProxy()->addManager($user);
                    foreach ($data['roles'] as $role) {
                        $user->addRole($role);
                    }
                    $em->flush();
                }

                return $this->redirectToRoute('board_user_library', array('context' => $context, 'contextPk' => $contextPk));
            } else {
                $newUser = $this->get('fos_user.user_manager')->createUser();
                $newUser->setEmail($data['email']);
                $newUser->setUsername($data['email']);
                $newUser->setPlainPassword($data['plainPassword']);
                $newUser->setEnabled(true);
                foreach ($data['roles'] as $role) {
                    $newUser->AddRole($role);
                }
                $this->get('fos_user.user_manager')->updateUser($newUser);
                $em->flush();

                $user = $this->getDoctrine()->getRepository('ClabUserBundle:User')->findOneBy(array('email' => $data['email']));
                if ($context == 'client') {
                    if ($form->has('chaine')) {
                        $chaine = $form->get('chaine')->getData();
                        if ($chaine == false) {
                            $data['restaurant']->addManager($user);
                        } else {
                            $this->get('board.helper')->getProxy()->addManager($user);
                            $user->addRole('ROLE_COMMERCIAL');
                        }
                    }
                } else {
                    if (!$this->get('board.helper')->getProxy()->getManagers()->contains($user)) {
                        $this->get('board.helper')->getProxy()->addManager($user);
                        foreach ($data['roles'] as $role) {
                            $user->addRole($role);
                        }
                    }
                }
                $user->setFirstName($data['firstName']);
                $user->setLastName($data['lastName']);
                $em->flush();
                $restoName = array_key_exists('restaurant', $data) ? $data['restaurant']->getName() : $this->get('board.helper')->getProxy()->getName();
                $this->addFlash('success', 'Manageur bien ajouté pour le restaurant '.$restoName);

                return $this->redirectToRoute('board_user_library', array('contextPk' => $contextPk, 'context' => $context));
            }
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:User:add.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function requestAction($context, $contextPk, Request $request)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($this->getRequest()->get('roles')) {
            $roles = unserialize($this->getRequest()->get('roles'));
        } else {
            $roles = array();
        }

        $form = $this->createFormBuilder()
            ->add('email', 'email', array(
                'required' => true,
                'label' => 'pro.users.emailLabel',
                'data' => $this->getRequest()->get('email'),
            ))
            ->add('roles', 'choice', array(
                'choices' => User::getManagerRoles(),
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'label' => 'pro.users.roleLabel',
                'data' => $roles,
            ))
            ->add('message', 'textarea', array(
                'label' => 'pro.users.messageLabel',
                'required' => false,
            ))
        ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData();

            $this->get('app_user.registration_request_manager')->createRequest(
                $data['email'],
                array($this->get('board.helper')->getProxy()),
                $data['roles'],
                $this->getUser(),
                $data['message']
            );

            return $this->redirectToRoute('board_user_library', array('context' => $context, 'contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:User:request.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function requestResendAction($context, $contextPk, $id)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $request = $this->getDoctrine()->getManager()->getRepository('ClabUserBundle:RegistrationRequest')->getForProxy($this->get('board.helper')->getProxy(), $id);

        if (!$request) {
            throw $this->createNotFoundException();
        }

        $this->get('app_user.registration_request_manager')->sendMail($request);

        return $this->redirectToRoute('board_user_library', array('context' => $context, 'contextPk' => $contextPk));
    }

    public function editAction($context, $contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);
        if ($context == 'client') {
            $user = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($id);
        } else {
            $user = $this->get('app_user.user_manager')->getEmployeeForProxy($this->get('board.helper')->getProxy(), $id);
        }

        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        if (!in_array('ROLE_MANAGER', $this->getUser()->getRoles())) {
            $form = $this->createForm(new UserRestrictedType(), $user);
        } else {
            $form = $this->createForm(new UserEditType(), $user);
        }

        $isDeliveryMan = $user->isDeliveryMan();

        if ($request->isMethod('POST')) {
            $form->bind($this->getRequest());

            if ($form->isValid()) {
                foreach (User::getManagerRoles() as $role => $name) {
                    if (in_array($role, $form->get('roles')->getData())) {
                        $user->addRole($role);
                    } else {
                        $user->removeRole($role);
                    }
                }

                $em->flush();

                if ($user->isDeliveryMan()) {
                    $deliveryMan = $this->getDoctrine()->getManager()->getRepository('ClabDeliveryBundle:DeliveryMan')
                        ->findOneBy(array('user' => $user));

                    if (!$deliveryMan) {
                        $deliveryMan = new DeliveryMan();
                        $deliveryMan->setUser($user);
                        $em->persist($deliveryMan);
                        $em->flush();
                    }
                }

                if ($isDeliveryMan && !$user->isDeliveryMan()) {
                    $deliveryMan = $this->getDoctrine()->getManager()->getRepository('ClabDeliveryBundle:DeliveryMan')
                        ->findOneBy(array('user' => $user));

                    if ($deliveryMan) {
                        foreach ($deliveryMan->getDeliveryDays() as $deliveryDay) {
                            $deliveryMan->removeDeliveryDay($deliveryDay);
                            $deliveryDay->removeDeliveryMan($deliveryMan);
                        }
                        $em->flush();
                    }
                }

                return $this->redirectToRoute('board_user_library', array('context' => $context, 'contextPk' => $contextPk));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        $this->get('board.helper')->addParam('user', $user);
        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:User:edit.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editPasswordAction($context, $contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $user = $this->get('app_user.user_manager')->getEmployeeForProxy($this->get('board.helper')->getProxy(), $id);

        $form = $this->createForm(new PasswordType(), $user);

        if ($form->handleRequest($request)->isValid()) {
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);
            $em->flush();

            return $this->redirectToRoute('board_user_library', array('context' => $context, 'contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('user', $user);
        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:User:editPassword.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function removeAction($context, $contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $product = $this->get('board.helper')->getAllowedOrDeny('ClabUserBundle:User', array('slug' => $slug));

        $product->remove();

        foreach ($product->getChildrens() as $children) {
            $children->remove();
        }

        $em->flush();

        return $this->redirectToRoute('board_user_library', array('context' => $context, 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function staffLibraryListAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $this->get('board.helper')->addParam('staff', $this->get('board.helper')->getProxy()->getStaffMembers());

        return $this->render('ClabBoardBundle:User:staffLibraryList.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function pincodeLibraryListAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('slug' => $contextPk));
        $pincodes = $this->getDoctrine()->getRepository('ClabUserBundle:Pincode')->findby(array('restaurant' => $restaurant));

        return $this->render('ClabBoardBundle:User:pin_list.html.twig', array(
            'pincodes' => $pincodes,
            'restaurant' => $restaurant,
        ));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function pincodeEditAction($contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($id) {
            $pincode = $em->getRepository('ClabUserBundle:Pincode')->findOneBy(array('id' => $id, 'restaurant' => $this->get('board.helper')->getProxy()));
        } else {
            $pincode = new Pincode();
        }

        $form = $this->createForm(new PincodeType(), $pincode);

        if ($form->handleRequest($request)->isValid()) {
            $pincode->setRestaurant($this->get('board.helper')->getProxy());
            $em->persist($pincode);

            $em->flush();

            return $this->redirectToRoute('board_user_library', array('context' => 'restaurant', 'contextPk' => $contextPk));
        };

        $this->get('board.helper')->addParam('pincode', $pincode);
        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:User:pin_edit.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function pincodeDeleteAction($contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($this->getRequest()->getMethod() == 'POST') {
            $pincode = $em->getRepository('ClabUserBundle:Pincode')->findOneBy(array('id' => $id, 'restaurant' => $this->get('board.helper')
                ->getProxy(), ));
            $em->remove($pincode);
            $em->flush();
        }

        return $this->redirectToRoute('board_user_library', array('context' => 'restaurant', 'contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function staffEditAction($contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($id) {
            $staff = $em->getRepository('ClabRestaurantBundle:StaffMember')->findOneBy(array('id' => $id, 'restaurant' => $this->get('board.helper')->getProxy()));
        } else {
            $staff = new StaffMember();
        }

        $form = $this->createForm(new StaffMemberType(), $staff);

        if ($form->handleRequest($request)->isValid()) {
            $staff->setRestaurant($this->get('board.helper')->getProxy());
            $em->persist($staff);

            $storage = $this->container->get('vich_uploader.storage');
            $storage->upload($staff);

            $em->flush();

            return $this->redirectToRoute('board_user_library', array('context' => 'restaurant', 'contextPk' => $contextPk));
        };

        $this->get('board.helper')->addParam('staff', $staff);
        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:User:staffEdit.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function staffDeleteAction($contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($this->getRequest()->getMethod() == 'POST') {
            $staff = $em->getRepository('ClabRestaurantBundle:StaffMember')->findOneBy(array('id' => $id, 'restaurant' => $this->get('board.helper')->getProxy()));
            $em->remove($staff);
            $em->flush();
        }

        return $this->redirectToRoute('board_user_library', array('context' => 'restaurant', 'contextPk' => $contextPk));
    }

    public function registerAction()
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->getRequest();

        $userManager = $this->container->get('fos_user.user_manager');
        $dispatcher = $this->container->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        if ($email = $this->getRequest()->get('email')) {
            $user->setEmail($email);
            $this->get('board.helper')->addParam('email', $email);
        } else {
            $this->get('board.helper')->addParam('email', null);
        }

        if ($firstname = $this->getRequest()->get('firstname')) {
            $user->setFirstName($firstname);
            $this->get('board.helper')->addParam('firstname', $firstname);
        } else {
            $this->get('board.helper')->addParam('firstname', null);
        }

        if ($lastname = $this->getRequest()->get('lastname')) {
            $user->setLastName($lastname);
            $this->get('board.helper')->addParam('lastname', $lastname);
        } else {
            $this->get('board.helper')->addParam('lastname', null);
        }

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->createForm(new RegisterType(), $user);

        if ($form->handleRequest($request)->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $userManager->updateUser($user);

            // define source
            $user->setSource('pro');
            $em->flush();

            if (null === $response = $event->getResponse()) {
                $response = $this->redirectToRoute('board_dashboard');
            }

            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

            return $response;
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:User:register.html.twig', $this->get('board.helper')->getParams());
    }
}
