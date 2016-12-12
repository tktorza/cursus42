<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;

class ToolsController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function synchAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        if (!$this->get('board.helper')->getProxy()->isMobile()) {
            throw $this->createNotFoundException();
        }

        if (!$this->get('app_admin.subscription_manager')->hasAccess($this->get('board.helper')->getProxy(), 'synch')) {
            return $this->redirectToRoute('board_feature_showcase', array('feature' => 'synch', 'contextPk' => $contextPk));
        }

        $this->get('board.helper')->addParams(array(
            'facebook_clickeat_menu_id' => $this->getParameter('app_facebook_menu_id'),
            'facebook_ttt_menu_id' => $this->getParameter('app_facebook_ttt_menu_id'),
            'facebook_ttt_planning_id' => $this->getParameter('app_facebook_ttt_planning_id'),
            'widgetDomain' => $this->getParameter('widgetDomain'),
        ));

        return $this->render('ClabBoardBundle:Tools:synch.html.twig', $this->get('board.helper')->getParams());
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function loyaltyAction($contextPk)
    {
        $this->get('board.helper')->initContext('restaurant', $contextPk);

        return $this->redirectToRoute('board_feature_showcase', array('feature' => 'loyalty', 'contextPk' => $contextPk));
    }
}
