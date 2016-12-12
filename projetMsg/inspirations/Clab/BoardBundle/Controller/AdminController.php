<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Form\Type\Store\RestaurantType;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Clab\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ShopBundle\Entity\OrderType;
use Clab\SocialBundle\Entity\SocialPost;
use Clab\BoardBundle\Form\Type\Social\SocialPostType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class AdminController extends Controller
{
    /**
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
   public function restaurantAdminAction($contextPk)
   {
       $this->get('board.helper')->initContext('client', $contextPk);
           $restaurants = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findBy(array
           ('client' => $this->get('board.helper')->getProxy()));


           $catalogNeedSync = array();

           foreach($restaurants as $restaurant){
               $currentDate = $restaurant->getLastSyncCatalog();
               $catalogNeedSync[$restaurant->getId()] =
                   count($this->getDoctrine()->getRepository('ClabRestaurantBundle:Product')->getUpdatedForRestaurant($restaurant, $currentDate))
                   + count($this->getDoctrine()->getRepository('ClabRestaurantBundle:Meal')->getUpdatedForRestaurant($restaurant, $currentDate))
                   + count($this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductCategory')->getUpdatedForRestaurant($restaurant, $currentDate))
                   + count($this->getDoctrine()->getRepository('ClabRestaurantBundle:ProductOption')->getUpdatedForRestaurant($restaurant, $currentDate))>0;
           }


           $this->get('board.helper')->addParams(array(
                'restaurants' => $restaurants,
                'catalogNeedSync' => $catalogNeedSync));


       return $this->render('ClabBoardBundle:Admin:restaurant-library.html.twig', $this->get('board.helper')->getParams());
   }

    /**
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function allowOrderAction(Restaurant $restaurant, $contextPk)
    {
        $this->get('board.helper')->initContext('client', $contextPk);
        $restaurant->setIsOpen(1);
        $preorder = $this->getDoctrine()->getRepository('ClabShopBundle:OrderType')->findOneBy(array(
            'slug' => 'preorder'
        ));
        $restaurant->addOrderType($preorder);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success','Commande en ligne activée');
        return $this->redirectToRoute('board_admin_library_restaurant',array('contextPk' => $contextPk));

    }

    /**
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function disallowOrderAction(Restaurant $restaurant, $contextPk)
    {
        $this->get('board.helper')->initContext('client', $contextPk);
        $restaurant->setIsOpen(0);
        $preorder = $this->getDoctrine()->getRepository('ClabShopBundle:OrderType')->findOneBy(array(
            'slug' => 'preorder'
        ));
        $restaurant->removeOrderType($preorder);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success','Commande en ligne activée');
        return $this->redirectToRoute('board_admin_library_restaurant',array('contextPk' => $contextPk));

    }

    /**
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function closeRestaurantAction(Restaurant $restaurant, $contextPk)
    {
        $this->get('board.helper')->initContext('client', $contextPk);
        $restaurant->setStatus(0);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success','Restaurant hors ligne');
        return $this->redirectToRoute('board_admin_library_restaurant',array('contextPk' => $contextPk));

    }

    /**
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function openRestaurantAction(Restaurant $restaurant, $contextPk)
    {
        $this->get('board.helper')->initContext('client', $contextPk);
        $restaurant->setStatus(3000);
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success','Restaurant en ligne');
        return $this->redirectToRoute('board_admin_library_restaurant',array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_SUPER_ADMIN")
     * @ParamConverter("restaurant", class="ClabRestaurantBundle:Restaurant")
     */
    public function synchroRestaurantAction(Restaurant $restaurant, $contextPk)
    {
        $this->get('board.helper')->initContext('client', $contextPk);
        $client = $this->get('board.helper')->getProxy();
        $this->get('clab_board.restaurant_manager')->synchroniseCatalog($restaurant, $client);

        $restaurant->setLastSyncCatalog(new \Datetime());
        $this->getDoctrine()->getEntityManager()->flush();

        $this->addFlash('success','Carte du restaurant '.$restaurant->getName().' mise à jour');
        return $this->redirectToRoute('board_admin_library_restaurant',array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function managerAction($contextPk)
    {
        $this->get('board.helper')->initContext('client', $contextPk);
        $client = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->find(1);
        $managers = $client->getManagers();
        $this->get('board.helper')->addParam('managers', $managers);
        return $this->render('ClabBoardBundle:Admin:manager-library.html.twig', $this->get('board.helper')->getParams());
    }

    public function removeManagerAction($contextPk, $id)
    {
        $client = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->find(1);
        $user = $this->getDoctrine()->getRepository('ClabUserBundle:User')->find($id);
        $client->removeManager($user);
        $this->getDoctrine()->getManager()->remove($user);
        $this->getDoctrine()->getManager()->flush();
        $this->addFlash('success','Manageur supprimé');
        return $this->redirectToRoute('board_admin_library_manager',array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function addManagerAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $client = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->find(1);
        $this->get('board.helper')->initContext('client', $contextPk);
            $form = $this->createFormBuilder()
                ->add('email', 'email', array(
                    'required' => true,
                    'label' => 'pro.users.emailLabel',
                ))
               ->add('plainPassword','password',array(
                   'required' => true,
                   'label' => 'Mot de passe',
               ))
                ->add('firstName','text',array(
                    'required' => true,
                    'label' => 'Prénom',
                ))
                ->add('lastName','text',array(
                    'required' => true,
                    'label' => 'Nom',
                ))
                ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData();
                $newUser = $this->get('fos_user.user_manager')->createUser();
                $newUser->setEmail($data['email']);
                $newUser->setUsername($data['email']);
                $newUser->setPlainPassword($data['plainPassword']);
                $newUser->setEnabled(true);
                $newUser->addRole('ROLE_ADMIN');
                $newUser->addRole('ROLE_MANAGER');
                $this->get('fos_user.user_manager')->updateUser($newUser);
                $this->getDoctrine()->getManager()->flush();
            $user = $this->getDoctrine()->getRepository('ClabUserBundle:User')->findOneBy(array('email' =>
                $data['email']));
                $client->addManager($user);
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success','Manageur bien ajouté pour l\'enseigne');
           return $this->redirectToRoute('board_admin_library_manager',array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());

        return $this->render('ClabBoardBundle:Admin:add.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_SUPER_ADMIN")
     */
    public function editManagerAction($email, $contextPk, Request $request)
    {
        $client = $this->getDoctrine()->getRepository('ClabBoardBundle:Client')->find(1);
        $this->get('board.helper')->initContext('client', $contextPk);
        $user = $this->getDoctrine()->getRepository('ClabUserBundle:User')->findOneBy(array(
            'email' => $email
        ));
        $form = $this->createFormBuilder($user)
            ->add('email', 'email', array(
                'required' => true,
                'label' => 'pro.users.emailLabel',
            ))
            ->add('plainPassword','password',array(
                'required' => true,
                'label' => 'Mot de passe',
            ))
            ->add('firstName','text',array(
                'required' => true,
                'label' => 'Prénom',
            ))
            ->add('lastName','text',array(
                'required' => true,
                'label' => 'Nom',
            ))
            ->getForm();

        if ($form->handleRequest($request)->isValid()) {

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success','Manageur bien ajouté pour l\'enseigne');
            return $this->redirectToRoute('board_admin_library_manager',array('contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParam('form', $form->createView());
        $this->get('board.helper')->addParam('user', $user);

        return $this->render('ClabBoardBundle:Admin:edit.html.twig', $this->get('board.helper')->getParams());
    }
}
