<?php

namespace Clab\UserBundle\Service;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Router;
use Doctrine\ORM\EntityManager;
use Symfony\Component\VarDumper\VarDumper;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    protected $em;
    protected $router;
    protected $translator;

    public function __construct(Router $router, EntityManager $em, $url_pro, Translator $translator)
    {
        $this->em = $em;
        $this->router = $router;
        $this->url_pro = $url_pro;
        $this->translator = $translator;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            $array = array('success' => true,'cover' => $token->getUser()->getCover()); // data to return via JSON
            $response = new Response(json_encode($array));
            $response->headers->set('Content-Type', 'application/json');

            return $response;

            // if form login
        } else {
            $next = null;
            $referer_url = $request->headers->get('referer');
            preg_match_all('/next=(.*)/', $referer_url, $next);

            if (isset($next[1])) {
                $next = empty($next[1][0]) ? null : urldecode($next[1]);
            }

            $baseUrl = $request->getBaseUrl();
            $lastPath = substr($referer_url, strpos($referer_url, $baseUrl) + strlen($baseUrl));

            $request->getSession()->set('admin_restaurant', null);
            $request->getSession()->set('admin_client', null);

            if ($next) {
                return new RedirectResponse($next);
            }

            $host = $request->getHost();
            $explode = $pieces = explode('/', $lastPath);

            if (strpos($host, 'pro.') !== false) {
                $response = new RedirectResponse($this->router->generate('board_dashboard'));
            } elseif ($this->startsWith($lastPath, 'http://preprod-order.clicklab.fr/login-preview/') === true) {
                $response = new RedirectResponse($this->router->generate('clickeat_order_home', array('slug' => $explode[4])));
            } elseif ($this->startsWith($lastPath, 'https://order.click-eat.fr/login-preview/') === true) {
                $response = new RedirectResponse($this->router->generate('clickeat_order_home', array('slug' => $explode[4])));
            } elseif ($this->startsWith($lastPath, 'http://order.click-eat.fr/login-preview/') === true) {
                $response = new RedirectResponse($this->router->generate('clickeat_order_home', array('slug' => $explode[4])));
            } elseif (strpos($host, 'panel.') !== false) {
                $response = new RedirectResponse($this->router->generate('sonata_admin_dashboard'));
            } elseif ($referer_url && strpos($host, 'pro.') == false && strpos($host, 'panel.') == false) {
                $response = new RedirectResponse($referer_url);
            } else {
                $response = new RedirectResponse($this->router->generate('clickeat_home'));
            }

            return $response;
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $array = array('success' => false, 'message' => $exception->getMessage()); // data to return via JSON
            $response = new Response(json_encode($array));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            $result = array(
            'success' => false,
            'function' => 'onAuthenticationFailure',
            'error' => true,
            'message' => $this->translator->trans($exception->getMessage(), array(), 'FOSUserBundle'),
        );
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');
            $referer_url = $request->headers->get('referer');
            $response = new RedirectResponse($referer_url);
            $request->getSession()->getFlashBag()->add('danger', 'Email ou mot de passe invalide,');

            return $response;
        }
    }

    public function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === '' || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
}
