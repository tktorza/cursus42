<?php

namespace Clab\BoardBundle\Service;

use Doctrine\ORM\EntityManager;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Facebook;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;

class FacebookManager
{
    protected $em;
    protected $container;
    protected $router;

    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
    }

    public function checkPageTab($page, $app)
    {
        $appId = $this->getAppId($app);

        $url = '/'.$page->getFacebookId().'/tabs';

        try {
            $fb = new Facebook(array(
                'app_id' => $this->container->getParameter('facebook_pro_app_id'),
                'app_secret' => $this->container->getParameter('facebook_pro_app_secret'),
                'fileUpload' => true,
                'cookie' => true,
            ));

            $url = '/'.$page->getFacebookId().'/feed';
            $request = $fb->request('GET', $url);
            $response = $fb->getClient()->sendRequest($request);

            foreach ($response->getResponse()->data as $tab) {
                if (isset($tab->application) && $tab->application && $tab->application->id == $appId) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    public function addPageTab($page, $app)
    {
        $appId = $this->getAppId($app);

        if (!$appId) {
            return false;
        }

        $urlPublish = '/'.$page->getFacebookId().'/tabs';
        $dataPublish = array(
            'app_id' => $appId,
        );

        $urlUpdate = '/'.$page->getFacebookId().'/tabs/app_'.$appId;
        $dataUpdate = array(
            'position' => 1,
        );

        try {
            $options['access_token'] = $page->getAccessToken();
            $fb = new Facebook(array(
                'app_id' => $this->container->getParameter('facebook_pro_app_id'),
                'app_secret' => $this->container->getParameter('facebook_pro_app_secret'),
                'fileUpload' => true,
                'cookie' => true,
            ));
            $publish = $fb->request('POST', $urlPublish, $dataPublish);
            $fb->getClient()->sendRequest($publish);
            $update = $fb->request('POST', $urlUpdate, $dataUpdate);
            $fb->getClient()->sendRequest($update);
        } catch (FacebookResponseException $e) {
            return false;
        }

        return true;
    }

    public function getRatings($page)
    {
        $url = '/'.$page->getFacebookId().'/ratings';

        try {
            $options['access_token'] = $page->getAccessToken();
            $fb = new Facebook(array(
                'app_id' => $this->container->getParameter('facebook_pro_app_id'),
                'app_secret' => $this->container->getParameter('facebook_pro_app_secret'),
                'fileUpload' => true,
                'cookie' => true,
            ));
            $request = $fb->request('GET', $url);
            $response = $fb->getClient()->sendRequest($request);

            return $response->getResponse()->data;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function updateFacebookInfos($restaurant)
    {
        if ($page = $restaurant->getFacebookPage()) {
            $timesheetManager = $this->container->get('app_restaurant.timesheet_manager');
            $planning = $timesheetManager->getPlanning($restaurant, '6 days');

            $hours = array(
                'mon_1_open' => 'none', 'mon_1_close' => 'none', 'mon_2_open' => 'none', 'mon_2_close' => 'none',
                'tue_1_open' => 'none', 'tue_1_close' => 'none', 'tue_2_open' => 'none', 'tue_2_close' => 'none',
                'wed_1_open' => 'none', 'wed_1_close' => 'none', 'wed_2_open' => 'none', 'wed_2_close' => 'none',
                'thu_1_open' => 'none', 'thu_1_close' => 'none', 'thu_2_open' => 'none', 'thu_2_close' => 'none',
                'fri_1_open' => 'none', 'fri_1_close' => 'none', 'fri_2_open' => 'none', 'fri_2_close' => 'none',
                'sat_1_open' => 'none', 'sat_1_close' => 'none', 'sat_2_open' => 'none', 'sat_2_close' => 'none',
                'sun_1_open' => 'none', 'sun_1_close' => 'none', 'sun_2_open' => 'none', 'sun_2_close' => 'none',
            );

            foreach ($planning as $day) {
                $count = 1;
                foreach ($day as $event) {
                    if ($event['type'] !== 0 && $count < 3) {
                        $keyStart = strtolower($event['start']->format('D')).'_'.$count.'_'.'open';
                        $valueStart = $event['start']->format('H:i');
                        $keyEnd = strtolower($event['start']->format('D')).'_'.$count.'_'.'close';
                        $valueEnd = $event['end']->format('H:i');

                        $hours[$keyStart] = $valueStart;
                        $hours[$keyEnd] = $valueEnd;

                        ++$count;
                    }
                }
            }

            $url = '/'.$page->getFacebookId();
            $data = array(
                'access_token' => $page->getAccessToken(),
                'hours' => json_encode($hours),
                'general_info' => strip_tags($restaurant->getDescription()),
            );

            try {
                $fb = new Facebook(array(
                    'app_id' => $this->container->getParameter('facebook_pro_app_id'),
                    'app_secret' => $this->container->getParameter('facebook_pro_app_secret'),
                ));

                $request = $fb->request('POST', $url, $data);
                $response = $fb->getClient()->sendRequest($request);
            } catch (FacebookResponseException $e) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function getAppId($app)
    {
        switch ($app) {
            case 'ttt_menu':
                $appId = $this->container->getParameter('app_facebook_ttt_menu_id');
                break;
            case 'ttt_planning':
                $appId = $this->container->getParameter('app_facebook_ttt_planning_id');
                break;
            case 'iframe':
                $appId = $this->container->getParameter('facebook_byclickeat_app_id');
                break;
            default:
                return false;
                break;
        }

        return $appId;
    }
}
