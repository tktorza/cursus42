<?php
namespace Clab\UserBundle\Security\User\Provider;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FilterUserResponseEvent;

use \Facebook;
use \FacebookApiException;

use Clab\UserBundle\Event\AuthenticationEvent;
use Clab\UserBundle\Event\RegistrationEvent;

class FOSUBUserProvider extends BaseClass
{
    protected $container;

    public function __construct(UserManagerInterface $userManager, array $properties, ContainerInterface $container, EntityManager $em)
    {
        parent::__construct($userManager, $properties);
        $this->container = $container;
        $this->em = $em;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();

        //on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();

        if($service == 'facebook_ttt') {
            $service = 'facebook';
        }

        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';

        //we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }

        //we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $service = $response->getResourceOwner()->getName();

        $user = null;
        // check if already logged in
        $token = $this->container->get('security.context')->getToken();
        if($token) {
            $user = $token->getUser();
        }

        if($service == 'facebook' || $service == 'facebook_ttt') {
            $fbdata = $response->getResponse();

            if($user) {
                $fbUser = $this->findUserByFbId($fbdata['id']);
                // si un compte différent est déjà connecté avec ce fb
                if($fbUser && $fbUser != $user) {
                    $user = $fbUser;
                }
                // sinon update les infos du compte en question
            } else {
                $fbUser = $this->findUserByFbId($fbdata['id']);
                // si un compte existe avec ce fbId
                if($fbUser) {
                    $user = $fbUser;
                } else {
                    $fbUser = $this->findUserByEmail($fbdata['email']);
                    // si un compte existe avec l'adresse mail fb, relier à ce compte
                    if($fbUser) {
                        $user = $fbUser;
                    }
                }
            }

            if(empty($user)) {
                $user = $this->userManager->createUser();
                $user->setEmail($fbdata['email']);
                $user->setUsername($fbdata['email']);
                $user->addRole('ROLE_MEMBER');
                $user->setPassword('');
                $user->setEnabled(true);

                //@todo set source
                $session = $this->container->get('session');
                if($session->get('facebook_connect_source')) {
                    $source = $session->get('facebook_connect_source');
                    $facebookPageId = $session->get('facebook_connect_source_fb_page');

                    $user->setSource($source);

                    if($facebookPageId) {
                        $facebookPage = $this->em->getRepository('ClabSocialBundle:SocialFacebookPage')->findOneBy(array('id' => $facebookPageId, 'is_online' => true));

                        if($facebookPage) {
                            $user->setSourceFacebookPage($facebookPage);
                        }
                    }
                }

                $newUser = true;
            } else {
                unset($fbdata['email']);
            }

            $user->setFBData($fbdata);

            if($user && !$user->getImage()) {
                //@todo
                /*try {
                    $url  = 'https://graph.facebook.com/' . $fbdata['id'] . '/picture?width=400&height=400';
                    $saveto = $this->container->getParameter("kernel.cache_dir");
                    $path = sprintf('%s/%s.%s', $saveto, sha1($url), 'gif');
                    file_put_contents($path, fopen($url, 'r'));

                    $file = new \Symfony\Component\HttpFoundation\File\UploadedFile($path, 'fb');

                    $user->setFile($file);
                } catch(\Exception $e) {}*/
            }

            $this->userManager->updateUser($user);

            if(isset($newUser) && $newUser) {
                $dispatcher = $this->container->get('event_dispatcher');
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, new Request(), new Response()));
            } else {
                $dispatcher = $this->container->get('event_dispatcher');
                $dispatcher->dispatch('app_user.authentication.success', new AuthenticationEvent($this->container->get('request'), $user));
            }
        }

        //if user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);

        $serviceName = $response->getResourceOwner()->getName();

        if($serviceName == 'facebook_ttt') {
            $serviceName = 'facebook';
        }

        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';

        //update access token
        $user->$setter($response->getAccessToken());

        return $user;
    }

    public function findUserByFbId($fbId)
    {
        return $this->userManager->findUserBy(array('facebookId' => $fbId));
    }

    public function findUserByEmail($email)
    {
        return $this->userManager->findUserBy(array('email' => $email));
    }
}
