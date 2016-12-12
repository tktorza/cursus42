<?php

namespace Clab\BoardBundle\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Clab\BoardBundle\Exception\SubscriptionException;

class SubscriptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if($exception instanceof SubscriptionException) {
            $response = new RedirectResponse($exception->getRedirectUrl());
            $event->setResponse($response);
        }
    }
}