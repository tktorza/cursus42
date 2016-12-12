<?php

namespace Clab\SocialBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Opauth\Opauth as Opauth;
use Opauth\Strategy\Facebook\Strategy;

use Clab\SocialBundle\Entity\SocialProfile;
use Clab\SocialBundle\Entity\SocialFacebookPage;

class OpauthController extends Controller
{
    public function opauthAction($type)
    {
        $redirect = $this->getRequest()->get('redirect');
        $socialProfileId = $this->getRequest()->get('socialProfileId');
        $socialProfile = $this->getDoctrine()->getManager()->getRepository('ClabSocialBundle:SocialProfile')->find($socialProfileId);

        if(!$redirect || !$socialProfileId) {
            throw $this->createNotFoundException();
        }

        $this->get('session')->set('opauth_redirect', urldecode($redirect));
        $this->get('session')->set('opauth_social_profile_id', $socialProfileId);

        $opauth = new Opauth\Opauth($this->getConfig($type, $socialProfile->getService()));
        $opauth->run();

        throw $this->createNotFoundException();
    }

    public function callbackAction($type)
    {
        $em = $this->getDoctrine()->getManager();
        
        $redirect = $this->get('session')->get('opauth_redirect');
        $socialProfileId = $this->get('session')->get('opauth_social_profile_id');

        if($socialProfileId) {
            $socialProfile = $this->getDoctrine()->getManager()->getRepository('ClabSocialBundle:SocialProfile')->find($socialProfileId);
        }

        try {
            $opauth = new Opauth\Opauth($this->getConfig($type, $socialProfile->getService()));
            $response = $opauth->run();

            if(isset($socialProfile) && isset($response->credentials) && isset($response->credentials['token'])) {

                if($type == 'facebook') {
                    $socialProfile->setFacebookId($response->uid);
                    $socialProfile->setFacebookData(serialize($response->raw));
                    $socialProfile->setFacebookAccessToken($response->credentials['token']);
                    if(isset($response->credentials['expires'])) {
                        $socialProfile->setFacebookAccessTokenExpire(date_create_from_format(\DateTime::ISO8601, $response->credentials['expires']));
                    }
                } elseif ($type == 'twitter') {
                    $socialProfile->setTwitterId($response->uid);
                    $socialProfile->setTwitterData(serialize($response->raw));
                    $socialProfile->setTwitterAccessToken($response->credentials['token']);
                    $socialProfile->setTwitterAccessSecret($response->credentials['secret']);
                }

                $em->flush();
            }
        } catch(\Exception $e) { }
        

        //$this->get('session')->set('opauth_redirect', null);
        //$this->get('session')->set('opauth_social_profile_id', null);

        if(!$redirect) {
            throw $this->createNotFoundException();
        }

        return $this->redirect($redirect);
    }

    public function getConfig($type, $service = 'clickeat')
    {
        if($type == 'twitter') {
            $twitter = array(
                'key' => $this->getParameter('twitter_pro_app_key'),
                'secret' => $this->getParameter('twitter_pro_app_secret')
            );


            $strategy = array(
                'Twitter' => $twitter
            );
        } else {
            if($service == 'tttruck') {
                $appId = $this->getParameter('facebook_pro_app_id');
                $appSecret = $this->getParameter('facebook_pro_app_secret');
            } else {
                $appId = $this->getParameter('facebook_pro_app_id');
                $appSecret = $this->getParameter('facebook_pro_app_secret');
            }

            $facebook = array(
              'app_id' => $appId,
              'app_secret' => $appSecret,
              'scope' => 'manage_pages, publish_actions, user_photos',
            );

            $strategy = array(
                'Facebook' => $facebook
            );
        }

        return array(
            'path' => '/opauth/',
            'debug' => true,
            'security_salt' => 'LDFmiilY8Fyw5W1rx4W1KfsVrieQCnpBzzpTBWA5vJideQKDx8pMJbmw28R1C4m',
            'Strategy' => $strategy
        );
    }
}
