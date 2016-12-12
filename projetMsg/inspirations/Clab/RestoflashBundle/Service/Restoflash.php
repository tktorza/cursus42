<?php

namespace Clab\RestoflashBundle\Service;

use Buzz\Client\Curl;
use Buzz\Browser;
use Buzz\Listener\BasicAuthListener;

abstract class Restoflash
{
    protected $env = 'prod';
    protected $imei = null;
    protected $login = null;
    protected $password = null;
    protected $endPoint = null;
    protected $endPointDemo = null;
    protected $endPointUser = null;
    protected $endPointUserDemo = null;

    public function initParameters(array $parameters)
    {
        if(isset($parameters['login']) && $parameters['login']) {
            $this->login = $parameters['login'];
        }

        if(isset($parameters['password']) && $parameters['password']) {
            $this->password = $parameters['password'];
        }

        if(isset($parameters['imei']) && $parameters['imei']) {
            $this->imei = $parameters['imei'];
        }

        if(isset($parameters['end_point']) && $parameters['end_point']) {
            $this->endPoint = $parameters['end_point'];
        }

        if(isset($parameters['end_point_demo']) && $parameters['end_point_demo']) {
            $this->endPointDemo = $parameters['end_point_demo'];
        }

        if(isset($parameters['end_point_user']) && $parameters['end_point_user']) {
            $this->endPointUser = $parameters['end_point_user'];
        }

        if(isset($parameters['end_point_user_demo']) && $parameters['end_point_user_demo']) {
            $this->endPointUserDemo = $parameters['end_point_user_demo'];
        }
    }

    public function setEnv($env)
    {
        if($env == 'prod' || $env == 'test') {
            $this->env = $env;
        }

        return $this;
    }

    public function getImei()
    {
        return $this->imei;
    }

    public function addCustomImeiParameter($parameter)
    {
        $this->imei = $this->imei . '[' . $parameter . ']';
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getBrowser()
    {
        $client = new Curl();
        $browser = new Browser($client);
        $authListener = new BasicAuthListener($this->getLogin(), $this->getPassword());
        $browser->setListener($authListener);

        return $browser;
    }

    public function getEndPoint()
    {
        if($this->env == 'test') {
            return $this->endPointDemo;
        } else {
            return $this->endPoint;
        }
    }

    public function getEndPointUser()
    {
        if($this->env == 'test') {
            return $this->endPointUserDemo;
        } else {
            return $this->endPointUser;
        }
    }

    public function getSignature(array $parameters)
    {
        $data = '';
        $signature = null;

        foreach ($parameters as $parameter) {
            $data = $data . $parameter . '|';
        }

        $data = substr($data, 0, -1);

        $privateKey = file_get_contents($this->container->get('kernel')->getRootDir() . '/cert/restoflashprivate.pem');
        //$pkeyid = openssl_pkey_get_private($privateKey);
        openssl_sign($data, $signature, $privateKey, 'RSA-SHA256');
        //openssl_sign($data, $signature, $privateKey, 'sha256WithRSAEncryption');
        //openssl_free_key($pkeyid);

        $signature = $this->base64url_encode($signature);

        return $signature;
    }

    public function base64url_encode($data) { 
        return strtr(base64_encode($data), '+/', '-_'); 
    } 

    public function base64url_decode($data) { 
        return base64_decode(strtr($data, '-_', '+/')); 
    }

    public function getMilliTime()
    {
        return time() * 1000;
    }
}