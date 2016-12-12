<?php

namespace Clab\UserBundle\Security\Http\Authentication;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Http\HttpUtils;

class CustomAuthenticationFailureHandler extends DefaultAuthenticationFailureHandler
{
    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($failureUrl = $request->get($this->options['failure_path_parameter'], null, true)) {
            $this->options['failure_path'] = $failureUrl;
        }

        if (null === $this->options['failure_path']) {
            $this->options['failure_path'] = $this->options['login_path'];
        }

        if ($this->options['failure_forward']) {
            if (null !== $this->logger) {
                $this->logger->debug(sprintf('Forwarding to %s', $this->options['failure_path']));
            }

            $subRequest = $this->httpUtils->createRequest($request, $this->options['failure_path']);
            $subRequest->attributes->set(SecurityContextInterface::AUTHENTICATION_ERROR, $exception);

            return $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
        }

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Redirecting to %s', $this->options['failure_path']));
        }

        $request->getSession()->set(SecurityContextInterface::AUTHENTICATION_ERROR, $exception);

        $response = $this->httpUtils->createRedirectResponse($request, $this->options['failure_path']);
    
        $host = $request->getHost();
        $restaurant = $request->get('restaurant');

        if(strpos($host, 'order') !== false) {
            $response->setTargetUrl($response->getTargetUrl() . '?restaurant=' . $restaurant);
        }

        return $response;
    }
}
