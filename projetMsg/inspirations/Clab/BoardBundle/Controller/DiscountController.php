<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Clab\ShopBundle\Entity\Discount;
use Clab\BoardBundle\Form\Type\Discount\DiscountType;

class DiscountController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);


        $discounts = $this->get('app_shop.discount_manager')->getForRestaurant($this->get('board.helper')->getProxy());
        $this->get('board.helper')->addParam('discounts', $discounts);

        if ($this->getRequest()->get('discount')) {
            $this->get('board.helper')->addParam('discount', $this->getRequest()->get('discount'));
        }

        return $this->render('ClabBoardBundle:Discount:library.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $discounts = $this->get('app_shop.discount_manager')->getForRestaurant($this->get('board.helper')->getProxy());

        return $this->render('ClabBoardBundle:Discount:libraryList.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'discounts' => $discounts,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction($contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($slug) {
            $discount = $em->getRepository('ClabShopBundle:Discount')->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
        } else {
            $discount = new Discount();
        }

        if (!$this->getRequest()->isXmlHttpRequest()) {
            // return $this->redirectToRoute('board_discount_library', array('contextPk' => $contextPk, 'discount' => $discount->getSlug()));
        }

        $form = $this->createForm(new DiscountType(), $discount);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST') {
            $form->bind($this->getRequest());

            if ($form->isValid()) {
                $discount->setRestaurant($this->get('board.helper')->getProxy());

                $em->persist($discount);
                $em->flush();

                $this->get('session')->getFlashBag()->add('formSuccess', 'L\'offre a bien été sauvegardée');

                return $this->redirectToRoute('board_discount_edit', array('contextPk' => $contextPk, 'slug' => $discount->getSlug()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
            'slug' => $contextPk,
        ));

        return $this->render('ClabBoardBundle:Discount:edit.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'discount' => $discount,
            'form' => $form->createView(),
            'restaurant' => $restaurant,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deleteAction($contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $discount = $em->getRepository('ClabShopBundle:Discount')->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
        $em->remove($discount);

        $em->flush();

        return $this->redirectToRoute('board_discount_library', array('contextPk' => $contextPk));
    }
}
