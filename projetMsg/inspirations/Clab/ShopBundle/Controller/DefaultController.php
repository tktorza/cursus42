<?php

namespace Clab\ShopBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('ClabShopBundle:Default:index.html.twig');
    }
}
