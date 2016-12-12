<?php

namespace Clab\UserBundle\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;

class LogoutSuccessHandler implements LogoutSuccessHandlerInterface
{
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function onLogoutSuccess(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $array = array('success' => true);
            $response = new Response(json_encode($array));
            $response->headers->set('Content-Type', 'application/json');

            return $response;

            // if form login
        } else {
            $referer_url = $request->headers->get('referer');

            $request->getSession()->set('admin_restaurant', null);
            $request->getSession()->set('admin_client', null);
            $host = $request->getHost();
            if (strpos($host, 'order.') !== false) {
                $response = new RedirectResponse($this->router->generate('clickeat_order_home', array('slug' => $request->getSession()->get('iframe_restaurant'))));
            }
            if (strpos($host, 'pro.') !== false) {
                $response = new RedirectResponse($this->router->generate('clab_board_login'));
            } elseif ($referer_url && strpos($host, 'pro.') == false) {
                $response = new RedirectResponse($referer_url);
            } else {
                $response = new RedirectResponse($this->router->generate('clickeat_home'));
            }

            return $response;
        }
    }
}
