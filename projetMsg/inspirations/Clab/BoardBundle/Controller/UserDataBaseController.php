<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Form\Type\UserDataBase\UserDataBaseType;
use Clab\BoardBundle\Entity\UserDataBase;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;

class UserDataBaseController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($context,$contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $users = $this->getDoctrine()->getRepository('ClabBoardBundle:UserDataBase')->findBy(array('restaurant' => $this->get('board.helper')->getProxy()));
        } else {
            $users = $this->getDoctrine()->getRepository('ClabBoardBundle:UserDataBase')->findAllForChainStore($this->get('board.helper')->getProxy());
        }

        $this->get('board.helper')->addParam('users', $users);
        $this->get('board.helper')->addParam('context', $context);
        $this->get('board.helper')->addParam('contextPk', $contextPk);

        if ($this->getRequest()->get('users')) {
            $this->get('board.helper')->addParam('users', $this->getRequest()->get('users'));
        }

        return $this->render('ClabBoardBundle:UserDataBase:library.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($context == 'restaurant') {
            $users = $this->getDoctrine()->getRepository('ClabBoardBundle:UserDataBase')->findBy(array('restaurant' => $this->get('board.helper')->getProxy()));
        } else {
            $users = $this->getDoctrine()->getRepository('ClabBoardBundle:UserDataBase')->findAllForChainStore($this->get('board.helper')->getProxy());
        }

        return $this->render('ClabBoardBundle:UserDataBase:libraryList.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'users' => $users,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction($contextPk,$context, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($id) {
            $user = $em->getRepository('ClabBoardBundle:UserDataBase')->findOneBy(array('id' => $id));
        } else {
            $user = new UserDataBase();
        }

        $form = $this->createForm(new UserDataBaseType(), $user);
        if($context == 'client'){
            $restaurants = $em->getRepository('ClabRestaurantBundle:Restaurant')->findBy(array('client'=>$this->get('board.helper')->getProxy()));
            $form->add('restaurant','entity',array(
                'class' => 'Clab\RestaurantBundle\Entity\Restaurant',
                'choice_label' => function ($restaurant) {
                return $restaurant->getName().' - '.$restaurant->getAddress()->getCity();
                },
                'choices' => $restaurants,
                'multiple' => false,
                'required' => true,
                'data' => $user->getRestaurant(),
            ));
        }
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                $ceUser = $this->getDoctrine()->getRepository('ClabUserBundle:User')->findOneBy(array(
                    'email' => $form->getData()->getEmail(),
                ));
                if(!empty($form['day']->getData())) {
                    $birthday = date_create_from_format("d/m/Y",$form['day']->getData());
                    if($birthday!==false) {
                        $user->setBirthday($birthday);
                    }
                }
                if($context == 'restaurant') {
                    $user->setRestaurant($this->get('board.helper')->getProxy());
                }
                $user->setUser($ceUser);
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add('formSuccess', 'L\'utilisateur à bien été sauvegardé');

                return $this->redirectToRoute('board_user_database_edit', array('contextPk' => $contextPk, 'context' => $context, 'id' => $user->getId()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        return $this->render('ClabBoardBundle:UserDataBase:edit.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'user' => $user,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deleteAction($context,$contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext($context, $contextPk);

        $user = $em->getRepository('ClabBoardBundle:UserDataBase')->findOneBy(array('id' => $id));
        $em->remove($user);

        $em->flush();

        return $this->redirectToRoute('board_user_database_library', array('context'=>$context,'contextPk' => $contextPk));
    }
}
