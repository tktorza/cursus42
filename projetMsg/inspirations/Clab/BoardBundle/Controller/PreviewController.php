<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;

class PreviewController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function previewAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        $multisiteManager = $this->get('clab_multisite.multisite_manager');
        $site = $multisiteManager->getOrCreateForRestaurant($this->get('board.helper')->getProxy(), true);

        return $this->render('ClabBoardBundle:Preview:preview.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'assetUrl' => $this->getParameter('domain'),
            'url' => $multisiteManager->getUrlForRestaurant($this->get('board.helper')->getProxy(), true),
        )));
    }
}
