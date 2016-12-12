<?php

namespace Clab\RestoflashBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Clab\PayzenBundle\Service\ParameterResolver;

use Clab\RestoflashBundle\Service\Restoflash;
use Clab\RestoflashBundle\Entity\RestoflashToken as Token;

class RestoflashToken extends Restoflash
{
    protected $em;
    protected $factory;
    protected $container;

    protected $requestUrl = 'rf_server/service/rest/admin/credentialTokenRequest.json';
    protected $authUrl = 'rf_server/webapp/confirmTokenRequest/';
    protected $checkUrl = 'rf_server/service/rest/admin/credentialTokenRequestResult/';
    protected $infoUrl = 'rf_server/service/rest/admin/benefInfo/';

    public function __construct(array $parameters, EntityManager $em, ContainerInterface $container, FormFactoryInterface $factory)
    {
        $this->em = $em;
        $this->factory = $factory;
        $this->container = $container;

        if($this->container->hasParameter('clab_restoflash_env')) {
            $this->setEnv($this->container->getParameter('clab_restoflash_env'));
        }

        $this->initParameters($parameters);
    }

    public function requestToken($redirectUrl)
    {
        $token = new Token();
        $this->em->persist($token);
        $this->em->flush($token);
        $time = $this->getMilliTime();

        $parameters = array(
            'encodedReference' => $this->base64url_encode($token->getReference()),
            'timestampInMilis' => $time,
            'encodedIMEI' => $this->base64url_encode($this->getImei()),
            'encodedRedirectUrl' => $this->base64url_encode($redirectUrl),
            'encodedSignature' => $this->getSignature(array($this->getImei(), $token->getReference(), $time, $redirectUrl)),
        );

        $browser = $this->getBrowser();
        $headers = array('Content-Type' => 'application/json');
        $data = json_encode($parameters);

        $response = $browser->post($this->getEndPoint() . $this->requestUrl, $headers, $data);

        $tokenResponse = json_decode($response->getContent(), true);

        if($tokenResponse) {
            $token->updateStatus($tokenResponse['state']);
            $this->em->persist($token);
            $this->em->flush();
        } else {
            return null;
        }

        return $token;
    }

    public function getRedirectUrl($token)
    {
        return $this->getEndPointUser() . $this->authUrl . $this->getLogin() . '/' . $this->base64url_encode($token->getReference());
    }

    public function checkToken(Token $token)
    {
        $browser = $this->getBrowser();
        $headers = array('Content-Type' => 'application/json');
        $time = $this->getMilliTime();

        $parameters = array(
            'encodedReference' => $this->base64url_encode($token->getReference()),
            'timestampInMilis' => $time,
            'encodedIMEI' => $this->base64url_encode($this->getImei()),
            'encodedRedirectUrl' => $this->base64url_encode('http://click-eat.fr'),
            'encodedSignature' => $this->getSignature(array($this->getImei(), $token->getReference(), $time, 'http://click-eat.fr')),
        );

        $data = json_encode($parameters);

        $response = $browser->post($this->getEndPoint() . $this->checkUrl, $headers, $data);

        $tokenResponse = json_decode($response->getContent(), true);

        if($tokenResponse) {
            $token->setToken($tokenResponse['data']);
            $token->updateStatus($tokenResponse['state']);
            $this->em->flush();
            $this->getInfo($token);
        }

        return $token;
    }

    public function getInfo(Token $token)
    {
        $browser = $this->getBrowser();
        $headers = array('Content-Type' => 'application/json');
        $time = $this->getMilliTime();

        $url = $this->getEndPoint() . $this->infoUrl;
        $url = $url . $this->base64url_encode($this->getImei()) . '/' ;
        $url = $url . $this->base64url_encode($token->getToken()) . '/';
        $url = $url . $time . '/';
        $url = $url . $this->getSignature(array($this->getImei(), $token->getToken(), $time));

        $response = $browser->get($url, $headers);

        $infos = json_decode($response->getContent(), true);

        if($infos) {
            $token->updateInfos($infos);
            $this->em->flush();
        }

        return $token;
    }
}