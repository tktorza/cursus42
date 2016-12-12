<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Form\Type\Review\ReviewType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;

class ReviewController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $application = $this->getDoctrine()->getRepository('ClabRestaurantBundle:App')->findOneBy(array(
            'slug' => 'reponse-aux-avis',
        ));
        $subscription = $this->getDoctrine()->getRepository('ClabBoardBundle:Subscription')->findOneBy(array(
            'restaurant' => $this->get('board.helper')->getProxy(),
            'is_online' => true,
            'type' => 0,
        ));
        $appsInSub = $application->getPlans();
        $restaurantApp = $this->get('board.helper')->getProxy()->getApps();
        if (!in_array($subscription->getPlan(), $appsInSub->toArray()) && !in_array($application, $restaurantApp->toArray())) {
            $this->get('board.helper')->addParam('application', $application);

            return $this->render('ClabBoardBundle:Apps:empty-screen.html.twig', $this->get('board.helper')->getParams());
        }
        $reviews = $this->getDoctrine()->getRepository('ClabReviewBundle:Review')->findBy(array('restaurant' => $this->get('board.helper')
            ->getProxy(), 'isOnline' => true, ), array('created' => 'DESC'));

        $this->get('board.helper')->addParam('reviews', $reviews);

        if ($this->getRequest()->get('review')) {
            $this->get('board.helper')->addParam('review', $this->getRequest()->get('review'));
        }

        return $this->render('ClabBoardBundle:Review:library.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function libraryListAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);
        $reviews = $this->getDoctrine()->getRepository('ClabReviewBundle:Review')->findBy(array('restaurant' => $this->get('board.helper')->getProxy(), 'isOnline' => true), array('created' => 'DESC'));

        return $this->render('ClabBoardBundle:Review:libraryList.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'reviews' => $reviews,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editAction($contextPk, $id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $review = $em->getRepository('ClabReviewBundle:Review')->find($id);

        $form = $this->createForm(new ReviewType(), $review);
        $form->handleRequest($request);
        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                $review->setResponse($form->get('response')->getData());
                $em->flush();

                $this->get('session')->getFlashBag()->add('formSuccess', 'La réponse a bien été sauvegardée');

                return $this->redirectToRoute('board_review_edit', array('contextPk' => $contextPk, 'id' => $review->getId()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        return $this->render('ClabBoardBundle:Review:edit.html.twig', array_merge($this->get('board.helper')
            ->getParams(), array(
            'review' => $review,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function deleteAction($contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $review = $em->getRepository('ClabReviewBundle:Review')->find($id);
        $em->remove($review);

        $em->flush();

        return $this->redirectToRoute('board_review_library', array('contextPk' => $contextPk));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function viewAction($contextPk, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $review = $em->getRepository('ClabReviewBundle:Review')->find($id);
        if ($review->getIsRead() == true) {
            $review->setIsRead(false);
        } else {
            $review->setIsRead(true);
        }

        $em->flush();

        return $this->redirectToRoute('board_review_library', array('contextPk' => $contextPk));
    }
}
