<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Form\Type\User\PincodeType;
use Clab\BoardBundle\Form\Type\User\UserRestrictedType;
use Clab\UserBundle\Entity\Pincode;
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
use Clab\BoardBundle\Form\Type\User\UserType;
use Clab\BoardBundle\Form\Type\User\PasswordType;
use Clab\BoardBundle\Form\Type\User\StaffMemberType;
use Clab\BoardBundle\Form\Type\User\RegisterType;

class UserChainStoreController extends Controller
{

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $users = $this->get('app_user.user_manager')->getEmployeesForProxy($this->get('board.helper')->getProxy());
        $this->get('board.helper')->addParam('users', $users);

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

        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData();
            $restaurant = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Restaurant')
                ->findOneBy(array('slug' => $contextPk));
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
        if (!in_array('ROLE_MANAGER', $this->getUser()->getRoles())) {
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
        } else {
            $form = $this->createFormBuilder()
                ->add('email', 'email', array(
                    'required' => true,
                    'label' => 'pro.users.emailLabel',
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
        }

        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData();

            $user = $this->getDoctrine()->getManager()->getRepository('ClabUserBundle:User')
                ->findOneBy(array('email' => $data['email']));

            if ($user) {
                if (!$this->get('board.helper')->getProxy()->getManagers()->contains($user)) {
                    $this->get('board.helper')->getProxy()->addManager($user);
                    foreach ($data['roles'] as $role) {
                        $user->addRole($role);
                    }
                    $em->flush();
                }

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

                return $this->redirectToRoute('board_user_library', array('context' => $context, 'contextPk' => $contextPk));
            } else {
                return $this->redirectToRoute('board_user_request', array(
                    'context' => $context,
                    'contextPk' => $contextPk,
                    'email' => $data['email'],
                    'roles' => serialize($data['roles']),
                ));
            }
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:User:add.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction($context, $contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $user = $this->get('app_user.user_manager')->getEmployeeForProxy($this->get('board.helper')->getProxy(), $id);

        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        if (!in_array('ROLE_MANAGER', $this->getUser()->getRoles())) {
            $form = $this->createForm(new UserRestrictedType(), $user);
        } else {
            $form = $this->createForm(new UserType(), $user);
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

}
