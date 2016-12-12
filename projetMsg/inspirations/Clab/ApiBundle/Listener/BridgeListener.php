<?php

namespace Clab\ApiBundle\Listener;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Security;


class BridgeListener
{
    private $session;
    private $em;
    private $security;
    private $request;
    private $bridge;
    private $apiId;
    private $firstCall;
    private $token;

    public function __construct(Session $session, EntityManager $em, TokenStorage $security)
    {
        $this->session = $session;
        $this->em = $em;
        $this->security = $security;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->request = $event->getRequest();
        $this->bridge = $this->session->get('bridge');
        $this->token = $this->session->get('token');

        return $this->handleBridge();
    }

    private function handleBridge() {
        $restaurantId = null;
        $callToRestaurant = in_array($this->request->attributes->get('_route'), array('api_rest_get_one_store', 'api_rest_get_clients'));
        $thereIsNoBridge = !$this->bridge;
        preg_match('/\/([0-9]+)/', $this->request->getRequestUri(), $ids);

        if (isset($ids[0])) {
            $restaurantId = $ids[1];
        }

        if ($callToRestaurant && $thereIsNoBridge && $restaurantId) {
            $this->firstCall = true;

            if (!$this->initBridge($restaurantId)) {
                return false;
            }
        } else if ($callToRestaurant && $restaurantId !== $this->session->get('restaurantId')) {
            if (!$this->initBridge($restaurantId)) {
                return false;
            }
        }

        return $this->callTheBridge($callToRestaurant);
    }

    private function initBridge($restaurantId) {
        $this->session->set('restaurantId', $restaurantId);
        $restaurant = $this->em->getRepository(Restaurant::class)->find($restaurantId);
        $this->bridge = $restaurant->getApiUrl();
        $this->apiId = $restaurant->getApiId();

        if (!$this->bridge) {
            return false;
        }

        if ($this->firstCall) {
            $this->createUserAndGetToken();
            $this->session->set('bridge', $this->bridge);
        }
    }

    private function callTheBridge($replaceRestaurantId = false) {
        if ($this->bridge) {
            $uri = $replaceRestaurantId ? preg_replace('/[0-9]+/', $this->apiId, $this->request->getRequestUri()) : $this->request->getRequestUri();
            $response = $this->call($uri, $this->request->getMethod());

            return new JsonResponse($response['body']);
        }

        return false;
    }

    private function createUserAndGetToken() {
        /**
         * @var $user User
         */
        $user = $this->security->getToken()->getUser();
        $email = str_replace('@', '@_fakeuser_', $user->getEmail());
        $password = strtoupper($user->getEmail());

        $registerData = array(
            'email' => $email,
            'password' => $password,
            'first_name' => $user->getFirstName(),
            'last_name' => $user->getLastName()
        );

        $loginData = array(
            'email' => $email,
            'password' => $password
        );

        $response = $this->call('/api/register', 'POST', $registerData);

        if (array_key_exists('error', $response)) {
            $response = $this->call('/api/token', 'POST', $loginData);
        }

        $tokenObject = json_decode($response['body']);
        $this->token = $tokenObject->token;
        $this->session->set('token', $this->token);
    }

    private function call($url, $method, $data = null, $query = null) {
        $relay = new \GuzzleHttp\Client();
        $query = array('query' => $query);
        $data = array('form_params' => $data);
        $uri = sprintf('%s%s', $this->bridge, $url);
        $options = in_array($method, array('POST', 'PUT')) ? array_merge($query, $data) : $query;

        if ($this->token) {
            $options['headers'] = array('authorization' => 'Bearer ' . $this->token);
        }

        try {
            $response = $relay->request(
                $method,
                $uri,
                $options
            );
        } catch (ClientException $e) {
            return array('error' => $e->getResponse());
        }

        return array(
            'body' => $response->getBody()->getContents(),
            'status' => $response->getStatusCode()
        );
    }
}