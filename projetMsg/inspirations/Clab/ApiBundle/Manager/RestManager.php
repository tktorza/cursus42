<?php

namespace Clab\ApiBundle\Manager;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;

use Clab\ApiBundle\Entity\Session;

class RestManager
{
	protected $container;
    protected $em;
    protected $formFactory;
    protected $sessionManager;
    protected $serializer;
    protected $apiVersion;

    public function __construct(ContainerInterface $container, EntityManager $em, SessionManager $sessionManager, Serializer $serializer, Request $request, FormFactoryInterface $formFactory)
    {
    	$this->container = $container;
        $this->em = $em;
        $this->sessionManager = $sessionManager;
        $this->serializer = $serializer;
        $this->request = $request;
        $this->formFactory = $formFactory;

        if($request->get('apiVersion')) {
            $this->apiVersion = (float) $request->request->get('apiVersion');
        } elseif ($request->query->get('apiVersion')) {
            $this->apiVersion = (float) $request->query->get('apiVersion');
        } elseif($this->sessionManager->getApiVersion()) {
            $this->apiVersion = $this->sessionManager->getApiVersion();
        } else {
            $this->apiVersion = 1.0;
        }
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function getErrorResponse($code, $message, $values = array())
    {
    	$session = $this->sessionManager->getSession();

    	if($session instanceof Session) {
    		$values['authToken'] = $session->getToken();
    	}

    	$values['success'] = false;
    	$values['code'] = $code;
    	$values['error'] = $message;

    	$error = $this->serializer->serialize($values, 'json');

    	$response = new Response($error);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function getFormErrorResponse($form)
    {
        $errors = $this->getErrorArray($form);

        $values['success'] = false;
        $values['error'] = 'Erreur dans le formulaire';
        $values['errors'] = $errors;

        return new Response($values, 400);
    }

    public function getErrorArray($form)
    {
        $errors = array();

        $errors = $this->getErrorMessages($form, $errors);

        return $errors;
    }

    public function getErrorMessages(\Symfony\Component\Form\Form $form, $errors) 
    {
        foreach ($form->getErrors() as $key => $error) {
            $key = $this->getFormKey($form);
            $errors[$key ? $key : 'form'][] = $error->getMessage();
        }

        foreach($form->all() as $child) {
            if (!$child->isValid()) {
                $errors = $this->getErrorMessages($child, $errors);
            }
        }

        return $errors;
    }

    public function getFormKey($form)
    {
        $key = '';
        $currentKey = $form->getName();

        while($form->getParent()) {
            $key = $form->getParent()->getName() ? $form->getParent()->getName() . '_' . $key : $key;
            $form = $form->getParent();
        }

        $key = $key . $currentKey;

        return $key;
    }

    public function getResponse($values, $groups = array())
    {
    	$session = $this->sessionManager->getSession();

    	if($session instanceof Session && $this->apiVersion < 3.0) {
    		$values['authToken'] = $session->getToken();

            if($session->getRestaurant()) {
                $restaurant = $session->getRestaurant();

                $this->populateCover($restaurant);
                $values['cover'] = $restaurant->getApiCover();
                $values['restaurantSlug'] = $restaurant->getSlug();
                $values['restaurantName'] = $restaurant->getName();

                if($restaurant->isMobile()) {
                    $values['foodtruck'] = true;
                }

                if($socialProfile = $restaurant->getSocialProfile()) {
                    if($socialProfile->getTwitterAccessToken()) {
                        $values['share_twitter'] = true;
                    }
                    if(count($socialProfile->getOnlineFacebookPages()) > 0) {
                        $values['share_facebook'] = true;
                    }
                    $values['ttt_event_validation_message'] = $restaurant->getSocialProfile()->getTTTEventValidationMessage();
                }

            }

            if($session->getUser() && (count($session->getUser()->getRestaurants()) > 1 || $session->getUser()->hasRole('ROLE_ADMIN') || $session->getUser()->hasRole('ROLE_SUPER_ADMIN'))) {
                $values['switch-restaurant'] = true;
            }
    	}

        $values['success'] = true;

        $serializationContext = SerializationContext::create();

        if(count($groups) > 0) {
            $groups = array_merge($groups, array('Default'));
            $serializationContext->setGroups($groups);
        } else {
            $serializationContext->setGroups(array('Default'));
        }

        $serializationContext->setVersion($this->apiVersion);

        $json = $this->serializer->serialize($values, 'json', $serializationContext);

    	$response = new Response($json);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function populateCover($elements)
    {
        $cacheManager = $this->container->get('liip_imagine.cache.manager');

        if(!is_array($elements) && !$elements instanceof \Doctrine\Common\Collections\ArrayCollection) {
            $elements = array($elements);
        }

        foreach ($elements as $element) {
            $cover = $element->getGallery()->getCover();
            $element->setApiCover($this->container->get('request')->getHost() . $cacheManager->getBrowserPath($cover->getWebPath(), 'square_200'));
        }
    }

    public function processForm($entity, $formType, $route = null, array $routeParameters = array())
    {
        $statusCode = !$entity->getId() ? 201 : 204;

        $form = $this->formFactory->create($formType, $entity, array('method' => $this->request->getMethod()));
        $form->submit($this->request, 'PATCH' !== $this->request->getMethod());

        if ($form->isValid()) {
            $this->em->persist($entity);
            $this->em->flush();

            $response = new Response($statusCode, '');

            if (201 === $statusCode && $route) {
                $response->headers->set('Location', $this->container->get('router')->generate($route, array_merge($routeParameters, array('id' => $entity->getId()))));
            }

            return $response;
        }

        return $this->getFormErrorResponse($form);
    }

}
