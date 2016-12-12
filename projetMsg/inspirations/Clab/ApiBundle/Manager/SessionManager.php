<?php

namespace Clab\ApiBundle\Manager;

use Clab\RestaurantBundle\Entity\Restaurant;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Clab\ApiBundle\Entity\Session;
use Clab\UserBundle\Entity\User;

class SessionManager
{
    protected $em;
    protected $request;

    protected $repository;
    protected $sessionCaisseRepository;

    protected $requestToken;
    protected $service = null;
    protected $system = null;
    protected $apiVersion = 1.0;
    protected $deviceIdentifier = null;

    protected $session;

    public function __construct(EntityManager $em, Request $request)
    {
        $this->em = $em;
        $this->repository = $em->getRepository('ClabApiBundle:Session');
        $this->sessionCaisseRepository = $em->getRepository('ClabApiBundle:SessionCaisse');
        $this->request = $request;

        if($this->request->headers->get('requestToken')) {
            $this->requestToken = $this->request->headers->get('requestToken');

            $data = json_decode(base64_decode($this->requestToken), true);

            if($data) {
                if(isset($data['system'])) {
                    $this->system = $data['system'];
                }

                if(isset($data['appSource'])) {
                    $this->service = $data['appSource'];
                }

                if(isset($data['apiVersion'])) {
                    $this->apiVersion = $data['apiVersion'];
                }

                if(isset($data['deviceIdentifier'])) {
                    $this->deviceIdentifier = str_replace(array('<', '>', ' '), '', trim($data['deviceIdentifier']));
                }

                if(isset($data['authToken'])) {
                    $authToken = $data['authToken'];

                    $session = $this->repository->findOneBy(array(
                        'token' => $authToken,
                        'isActive' => true,
                        'service' => $this->service,
                    ));

                    if(!is_null($session)) {
                        $this->session = $session;
                        $session->setDeviceIdentifier($this->deviceIdentifier);
                        $session->setLastLogin(date_create('now'));
                        $session->setSystem($this->system);
                        $this->em->flush();
                    }
                } else {
                    $this->createSession();
                }
            }
        }
    }

    public function getSession()
    {
        return $this->session;
    }

    public function getUser()
    {
        if($this->getSession()) {
            return $this->getSession()->getUser();
        }

        return null;
    }

    public function createSession(User $user = null)
    {
        $session = new Session();

        $session->setSystem($this->system);
        $session->setService($this->service);
        $session->setDeviceIdentifier($this->deviceIdentifier);

        if($user) {
            $session->setUser($user);
            $session->setToken(sha1(time() . $user->getUsername() . rand(11111, 99999) . $user->getEmail() . time()));
        }

        $this->em->persist($session);
        $this->em->flush();

        $this->session = $session;

        return $session;
    }

    public function closeSession()
    {
        if($this->session) {
            $this->session->setIsActive(false);
            $this->em->flush();

            $this->session = null;
        }
    }

    public function getService()
    {
        return $this->service;
    }

    public function getDeviceIdentifier()
    {
        return $this->deviceIdentifier;
    }

    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    public function getAllCaisseBeetweenDates( Restaurant $restaurant, $start = null, $end = null)
    {
        return $this->sessionCaisseRepository->findAllForRestaurant($restaurant, $start, $end);
    }
}
