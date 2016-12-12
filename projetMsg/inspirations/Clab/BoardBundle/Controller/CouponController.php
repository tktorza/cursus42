<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Form\Type\Coupon\CouponType;
use Clab\ShopBundle\Entity\Coupon;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;

class CouponController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $coupons = $this->getDoctrine()->getRepository('ClabShopBundle:Coupon')->findBy(array('restaurant' => $this->get('board.helper')->getProxy()));

        $this->get('board.helper')->addParam('coupons', $coupons);

        if ($this->getRequest()->get('coupon')) {
            $this->get('board.helper')->addParam('coupon', $this->getRequest()->get('coupon'));
        }

        return $this->render('ClabBoardBundle:Coupon:library.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $coupons = $this->getDoctrine()->getRepository('ClabShopBundle:Coupon')->findBy(array('restaurant' => $this->get('board.helper')->getProxy()));

        return $this->render('ClabBoardBundle:Coupon:libraryList.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'coupons' => $coupons,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction($contextPk, $slug, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if ($slug) {
            $coupon = $em->getRepository('ClabShopBundle:Coupon')->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
        } else {
            $coupon = new Coupon();
        }

        $form = $this->createForm(new CouponType(), $coupon);
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                if ($form->get('type')->getData() == 'percent') {
                    $coupon->setPercent($form->get('amount')->getData());
                    $coupon->setAmount(null);
                } elseif ($form->get('type')->getData() == 'amount') {
                    $coupon->setAmount($form->get('amount')->getData());
                    $coupon->setPercent(null);
                }
                $coupon->setRestaurant($this->get('board.helper')->getProxy());
                if ($form->getData()->getEndDay() == null) {
                    $now = new \DateTime('now');
                    $coupon->setEndDay($now->modify('+ 30 year'));
                }
                $em->persist($coupon);
                $em->flush();

                $this->get('session')->getFlashBag()->add('formSuccess', 'Le coupon a bien été sauvegardée');

                return $this->redirectToRoute('board_coupon_edit', array('contextPk' => $contextPk, 'slug' => $coupon->getSlug()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        return $this->render('ClabBoardBundle:Coupon:edit.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'coupon' => $coupon,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deleteAction($contextPk, $slug)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $coupon = $em->getRepository('ClabShopBundle:Coupon')->findOneBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'slug' => $slug));
        $em->remove($coupon);

        $em->flush();

        return $this->redirectToRoute('board_coupon_library', array('contextPk' => $contextPk));
    }
}
