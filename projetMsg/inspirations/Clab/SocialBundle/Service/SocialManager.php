<?php

namespace Clab\SocialBundle\Service;

use Doctrine\ORM\EntityManager;
use Facebook\Facebook;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
use Clab\SocialBundle\Entity\SocialPost;
use Clab\SocialBundle\Entity\SocialProfile;
use Clab\SocialBundle\Entity\SocialFacebookPage;
use Twitter;

class SocialManager
{
    protected $container;
    protected $em;
    protected $router;
    protected $request;

    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
        $this->request = $this->container->get('request');
    }

    public function initSocialProfile($restaurant, $pages = false)
    {
        if (!$restaurant->getSocialProfile()) {
            $pages = true;
            $socialProfile = new SocialProfile();
            $restaurant->setSocialProfile($socialProfile);

            if ($restaurant->isMobile()) {
                $socialProfile->setService('tttruck');
            }

            $this->em->flush();
        }

        if ($pages && $restaurant->getSocialProfile()->getFacebookAccessToken()) {
            $this->fetchFacebookPages($restaurant->getSocialProfile());
        }

        if (!$restaurant->getSocialProfile()->getTttEventValidationMessage()) {
            $message = 'Nous sommes présents à [[nom]]: [[adresse]], [[heure]]';
            $restaurant->getSocialProfile()->setTttEventValidationMessage($message);
        }

        if (!$restaurant->getSocialProfile()->getTttEventAnnulationMessage()) {
            $message = 'Nous sommes fermés aujourd\'hui';
            $restaurant->getSocialProfile()->setTttEventAnnulationMessage($message);
        }

        return true;
    }

    public function getOpauthLink($type, $service, $socialProfile, $callbackRoute, $callbackRouteParameters = array())
    {
        $route = null;
        $backUrl = $this->router->generate($callbackRoute, $callbackRouteParameters, true);

        switch ($type) {
            case 'facebook':
                switch ($service) {
                    case 'clickeat':
                        $root = 'http://'.$this->container->getParameter('domain');
                        $route = $root.$this->router->generate('opauth', array('type' => $type, 'redirect' => $backUrl, 'socialProfileId' => $socialProfile->getId()));
                        break;
                    case 'tttruck':
                        $root = 'http://'.$this->container->getParameter('tttdomain');
                        $route = $root.$this->router->generate('opauth', array('type' => $type, 'redirect' => $backUrl, 'socialProfileId' => $socialProfile->getId()));
                        break;
                }
                break;
        }

        return $route;
    }

    public function fetchFacebookPages(SocialProfile $socialProfile)
    {
        $parameters = array('access_token' => $socialProfile->getFacebookAccessToken());
        $url = 'https://graph.facebook.com/me/accounts';

        $client = new \GuzzleHttp\Client();
        $res = $client->get($url, [
            'query' => $parameters,
        ]);

        $output = json_decode($res->getBody()->getContents(), true);
        $fb = new Facebook(array(
            'app_id' => $this->container->getParameter('facebook_pro_app_id'),
            'app_secret' => $this->container->getParameter('facebook_pro_app_secret'),
            'fileUpload' => true,
            'cookie' => true,
        ));
        $request = $fb->request('GET', '/me/accounts', $parameters);
        $response = $fb->getClient()->sendRequest($request);
        $data = $response->getDecodedBody()['data'];

        if ($response && isset($data) && $data) {
            foreach ($data as $page) {
                if (!$socialProfile->hasPage($page['id'])) {
                    $facebookPage = new SocialFacebookPage();
                    $socialProfile->addFacebookPage($facebookPage);
                    $facebookPage->setAccessToken($page['access_token']);
                    $facebookPage->setName($page['name']);
                    $facebookPage->setFacebookId($page['id']);
                    $this->em->persist($facebookPage);
                } else {
                    $facebookPage = $socialProfile->hasPage($page['id']);
                    $facebookPage->setAccessToken($page['access_token']);
                }
            }

            $this->em->flush();
        }

        return $socialProfile;
    }

    public function pushSocialPost(SocialPost $socialPost, $addLink = false, array $targets = array())
    {
        $imageManager = $this->container->get('app_media.image_manager');
        $imageHelper = $this->container->get('vich_uploader.templating.helper.uploader_helper');

        if ($socialPost->getRestaurant() && $socialPost->getRestaurant()->isMobile()) {
            $home = 'ttt_home';
            $route = 'ttt_profile';
        } else {
            $home = 'clickeat_home';
            $route = 'clickeat_store_profile';
        }

        $link = $this->router->generate($route, array('slug' => $socialPost->getRestaurant()->getSlug()), true);
        $homeLink = $this->router->generate($home, array(), true);

        if ($socialPost->getProduct()) {
            $type = 'photo';
            if ($socialPost->getRestaurant()->getFacebookPage()) {
                $album = $this->getOrCreateFacebookAlbum($socialPost->getRestaurant()->getFacebookPage(), 'product');
            }

            $name = $socialPost->getProduct()->getName();
            $caption = $socialPost->getProduct()->getDescription();

            if ($socialPost->getImage()) {
                $path = $imageHelper->asset($socialPost, 'image');
                $picture = $homeLink.$path;
            } else {
                $picture = $imageManager->getAbsoluteUrl($socialPost->getProduct());
            }
        } elseif ($socialPost->getMeal()) {
            $type = 'photo';
            $album = $this->getOrCreateFacebookAlbum($socialPost->getRestaurant()->getFacebookPage(), 'product');
            $name = $socialPost->getMeal()->getName();
            $caption = $socialPost->getMeal()->getDescription();

            if ($socialPost->getImage()) {
                $path = $imageHelper->asset($socialPost, 'image');
                $picture = $homeLink.$path;
            } else {
                $picture = $imageManager->getAbsoluteUrl($socialPost->getMeal());
            }
        } elseif ($socialPost->getDiscount()) {
            $type = 'post';
            $name = $socialPost->getDiscount()->getName();
            $caption = $socialPost->getDiscount()->verboseName();

            if ($socialPost->getImage()) {
                $path = $imageHelper->asset($socialPost, 'image');
                $picture = $homeLink.$path;
            } else {
                $picture = $imageManager->getAbsoluteUrl($socialPost->getRestaurant());
            }
        } else {
            $name = $socialPost->getRestaurant()->getName();
            if ($socialPost->getImage()) {
                $type = 'photo';
                $albumType = 'default';
                $path = $imageHelper->asset($socialPost, 'image');
                $picture = $homeLink.$path;
            } else {
                $type = 'post';
                $picture = $imageManager->getAbsoluteUrl($socialPost->getRestaurant());
            }
        }

        $options = array(
            'message' => $socialPost->getMessage(),
        );

        if ($type == 'photo') {
            $options['url'] = isset($picture) && $picture ? $picture : null;
            //$options['url'] = 'http://click-eat.fr/files/cache/square_400/files/restaurant/il-midi/8506d5425ed0153a722afb4b2d40754a.png';
        }

        if ($addLink) {
            if ($type == 'photo') {
                $options['name'] = $socialPost->getMessage().'

                '.$link;
            } else {
                $options['link'] = $link;
                $options['name'] = isset($name) && $name ? $name : null;
                $options['caption'] = isset($caption) && $caption ? $caption : null;
                $options['picture'] = isset($picture) && $picture ? $picture : null;
                //$options['picture'] = 'http://click-eat.fr/files/cache/square_400/files/restaurant/il-midi/8506d5425ed0153a722afb4b2d40754a.png';
            }
        }

        if (isset($targets['facebook']) && $targets['facebook']) {
            try {
                if ($page = $socialPost->getRestaurant()->getFacebookPage()) {
                    if ($type == 'photo' && isset($albumType)) {
                        $albumId = $this->getOrCreateFacebookAlbum($socialPost->getRestaurant()->getFacebookPage(), $albumType);
                        $options['album'] = $albumId;
                    }
                    $this->pushFacebookPage($page, $options, $type);
                }
            } catch (\Exception $e) {
            }
        }

        if (isset($targets['twitter']) && $targets['twitter']) {
            try {
                $this->tweet($socialPost->getRestaurant()->getSocialProfile(), $options);
            } catch (\Exception $te) {
            }
        }
    }

    public function pushFacebookPage(SocialFacebookPage $page, $options = array(), $type = 'post')
    {
        $options['access_token'] = $page->getAccessToken();
        $fb = new Facebook(array(
            'app_id' => $this->container->getParameter('facebook_pro_app_id'),
            'app_secret' => $this->container->getParameter('facebook_pro_app_secret'),
            'fileUpload' => true,
            'access_token' => $page->getAccessToken(),
        ));
        $url = '/'.$page->getFacebookId().'/feed';
        $request = $fb->request('POST', $url, $options);
        $response = $fb->getClient()->sendRequest($request);

        return $response;
    }

    public function getOrCreateFacebookAlbum(SocialFacebookPage $page, $type)
    {
        switch ($type) {
            case 'default':
                if ($page->getDefaultAlbumId()) {
                    return $page->getDefaultAlbumId();
                } else {
                    $name = 'Mes photos';
                    $privacy = array('value' => 'SELF');
                }
                break;
            case 'product':
                if ($page->getProductAlbumId()) {
                    return $page->getProductAlbumId();
                } else {
                    $name = 'Mes produits';
                }
                break;
            default:
                return;
                break;
        }

        $url = '/'.$page->getFacebookId().'/albums';
        $options = array(
            'access_token' => $page->getAccessToken(),
            'name' => $name,
        );

        if (isset($privacy)) {
            $options['privacy'] = json_encode($privacy);
        }

        $fb = new Facebook(array(
            'app_id' => $this->container->getParameter('facebook_pro_app_id'),
            'app_secret' => $this->container->getParameter('facebook_pro_app_secret'),
            'fileUpload' => true,
            'cookie' => true,
        ));
        $request = $fb->request('POST', $url, $options);
        $response = $fb->getClient()->sendRequest($request);
        if ($response && isset($response->asArray()['id']) && $id = $response->asArray()['id']) {
            switch ($type) {
                case 'default':
                    $page->setDefaultAlbumId($id);
                    break;
                case 'product':
                    $page->setProductAlbumId($id);
                    break;
            }

            $this->em->flush();

            return $id;
        }

        return;
    }

    public function tweet(SocialProfile $socialProfile, $options = array())
    {
        $url = 'https://api.twitter.com/1.1/statuses/update.json';

        $consumerKey = $this->container->getParameter('twitter_pro_app_key');
        $consumerSecret = $this->container->getParameter('twitter_pro_app_secret');
        $accessToken = $socialProfile->getTwitterAccessToken();
        $accessTokenSecret = $socialProfile->getTwitterAccessSecret();

        $twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

        if (isset($options['link'])) {
            $message = substr($options['message'], 0, 116);
            $message .= ' '.$options['link'];
        } else {
            $message = substr($options['message'], 0, 139);
        }

        try {
            $response = $twitter->send($message);
        } catch (\Exception $e) {
        }

        return $response;
    }

    public function getLatestPost($restaurant = null, array $parameters = array(), $page = 1, $itemPerPage = 10)
    {
        if ($restaurant) {
            $lastestPosts = $this->em->getRepository('ClabSocialBundle:SocialPost')
                ->getForRestaurant($restaurant, $page, $itemPerPage);
        } else {
            $lastestPosts = $this->em->getRepository('ClabSocialBundle:SocialPost')
                ->getLatest($limit, $parameters);
        }

        $helper = $this->container->get('vich_uploader.templating.helper.uploader_helper');
        $cacheManager = $this->container->get('liip_imagine.cache.manager');
        foreach ($lastestPosts as $post) {
            if ($post->getImage()) {
                $path = $helper->asset($post, 'image');
                $post->setApiCover($this->request->getHost().$path);
            } elseif ($post->getProxy()) {
                if (($post->getProxy() instanceof \Clab\RestaurantBundle\Entity\Product ||
                   $post->getProxy() instanceof \Clab\RestaurantBundle\Entity\Meal)
                    && $cover = $post->getProxy()->getGallery()->getCover()) {
                    $post->setApiCover($this->request->getHost().$cacheManager->getBrowserPath($cover->getWebPath(), 'square_200'));
                }
            } elseif ($post->getRestaurant() && $post->getRestaurant()->getGallery() && $post->getRestaurant()->getGallery()->getCover()) {
                $post->setApiCover($this->request->getHost().$cacheManager->getBrowserPath($post->getRestaurant()->getGallery()->getCover()->getWebPath(), 'square_200'));
            }
        }

        return $lastestPosts;
    }

    public function getFeedForEntity($entity)
    {
        $socialPosts = array();

        foreach ($entity->getSocialPosts() as $socialPost) {
            if ($socialPost->isAvailable()) {
                $socialPosts[] = $socialPost;
            }
        }

        return $socialPosts;
    }

    public function getNewsDataForRestaurant($restaurant)
    {
        $news = $this->em->getRepository('ClabSocialBundle:SocialPost')
                ->getLatestByRestaurant($restaurant, 5);

        $data = array('news' => $news);

        return $data;
    }
}
