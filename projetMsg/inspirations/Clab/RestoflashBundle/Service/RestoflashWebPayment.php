<?php

namespace Clab\RestoflashBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Clab\PayzenBundle\Service\ParameterResolver;

use Clab\RestoflashBundle\Service\Restoflash;
use Clab\RestoflashBundle\Entity\RestoflashTransaction;

class RestoflashWebPayment extends Restoflash
{
    protected $em;
    protected $factory;
    protected $container;

    protected $createUrl = 'rf_server/service/rest/admin/newtransac.json';
    protected $confirmUrl = 'confirmationweb/';
    protected $checkUrl = 'rf_server/service/rest/admin/transac/';

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

    public function createTransaction($amount, $redirectUrl, array $parameters = array())
    {
        $transaction = new RestoflashTransaction();
        $transaction->setAmount($amount);
        $this->em->persist($transaction);
        $this->em->flush($transaction);
        $time = $this->getMilliTime();

        if(isset($parameters['customImei']) && $parameters['customImei']) {
            $this->addCustomImeiParameter($parameters['customImei']);
        }

        $parameters = array(
            'encodedReference' => $this->base64url_encode($transaction->getReference()),
            'transacTimeInMilis' => $time,
            'encodedValue' => $this->base64url_encode($transaction->getAmount()),
            'acceptPartial' => false,
            'encodedIMEI' => $this->base64url_encode($this->getImei()),
            'encodedRedirectUrl' => $this->base64url_encode($redirectUrl),
            'encodedSignature' => $this->getSignature(array($this->getImei(), $transaction->getReference(), $time, $transaction->getFormattedAmount(), $redirectUrl)),
        );

        $browser = $this->getBrowser();
        $headers = array('Content-Type' => 'application/json');
        $data = json_encode($parameters);

        $response = $browser->post($this->getEndPoint() . $this->createUrl, $headers, $data);

        $transactionResponse = json_decode($response->getContent(), true);

        if($transactionResponse) {
            $transaction->updateStatus($transactionResponse['state']);
            $this->em->persist($transaction);
            $this->em->flush();
        } else {
            return null;
        }

        return $transaction;
    }

    public function getRedirectUrl($transaction)
    {
        return $this->getEndPointUser() . $this->confirmUrl . $this->getLogin() . '/' . $this->base64url_encode($transaction->getReference());
    }

    public function checkTransaction(RestoflashTransaction $transaction)
    {
        $url = $this->getEndPoint() . 'rf_server/service/rest/admin/transac/' . $this->base64url_encode($transaction->getReference());

        $browser = $this->getBrowser();
        $response = $browser->get($url);

        $data = json_decode($response->getContent(), true);

        if($data) {
            $transaction->updateStatus($data['state']);
            $this->em->flush();
        }

        return $transaction;
    }
}