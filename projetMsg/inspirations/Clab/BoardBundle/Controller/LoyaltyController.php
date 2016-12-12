<?php

namespace Clab\BoardBundle\Controller;

use Clab\BoardBundle\Form\Type\Loyalty\LoyaltyConfigType;
use Clab\ShopBundle\Entity\Loyalty;
use Clab\ShopBundle\Entity\LoyaltyConfig;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class LoyaltyController extends Controller
{

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function editLoyaltyConfigAction($contextPk, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $this->get('board.helper')->initContext('client', $contextPk);

        $loyaltyConfig = $em->getRepository(LoyaltyConfig::class)->find(1);

        if (!$loyaltyConfig) {
            $loyaltyConfig = new LoyaltyConfig();
        }

        $form = $this->createForm(new LoyaltyConfigType(),$loyaltyConfig);

        if ($form->handleRequest($request)->isValid()) {
            $loyaltyConfig->setId(1);
            $em->persist($loyaltyConfig);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success','Modifications bien prises en compte');
        }

        return $this->render('ClabBoardBundle:Loyalty:editConfig.html.twig',array_merge($this->get('board.helper')->getParams(),array(
            'form' => $form->createView()
        )));

    }
}
