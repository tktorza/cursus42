<?php

namespace Clab\ApiBundle\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;


class SerializerEvent
{
    public function onKernelResponse(FilterResponseEvent $event) {
        if (preg_match('/api/', $event->getRequest()->getUri())) {
            $object = json_decode($event->getResponse()->getContent(), true);

            if (is_array($object)) {
                array_walk_recursive($object, function (&$value, $key) {
                    if ('price' === $key) {
                        $value = str_replace(',00', '', number_format((float) $value, 2, ',', ','));
                    }
                });

                $event->setResponse(new JsonResponse($object));
            }
        }
    }
}