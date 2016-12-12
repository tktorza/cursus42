<?php


/* Deprecated */


namespace Clab\UserBundle\Security\User\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use \BaseFacebook;
use \FacebookApiException;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Clab\UserBundle\Event\RegistrationEvent;

class FacebookProvider implements UserProviderInterface
{
    /**
     * @var \Facebook
     */
    protected $facebook;
    protected $userManager;
    protected $userProvider;
    protected $validator;
    protected $em;
    protected $container;

    public function __construct(BaseFacebook $facebook, $userManager, $validator, EntityManager $em, ContainerInterface $container, $userProvider)
    {
        $this->facebook = $facebook;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->em = $em;
        $this->container = $container;
        $this->userProvider = $userProvider;

    }

    public function supportsClass($class)
    {
        //return $this->userManager->supportsClass($class);
        return $this->userProvider->supportsClass($class);
    }

    public function findUserByFbId($fbId)
    {
        return $this->userManager->findUserBy(array('facebookId' => $fbId));
    }

    public function findUserByEmail($email)
    {
        return $this->userManager->findUserBy(array('email' => $email));
    }

    public function loadUserByUsername($username)
    {
        //$user = $this->findUserByFbId($username);

        try {
            $fbdata = $this->facebook->api('/me');
        } catch (FacebookApiException $e) {
            $fbdata = null;
        }

        if(!isset($fbdata['email']))
        {
            throw new UsernameNotFoundException('Nous n\'avons pas eu accès à votre adresse email.');
        }

        $user = null;
        $token = $this->container->get('security.context')->getToken();
        if($token) {
            $user = $token->getUser();
        }

        // si connecté
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

        if (!empty($fbdata)) {
            if (empty($user)) {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->addRole('ROLE_MEMBER');
                $token = new \Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken(
                  $user,
                  null,
                  'main',
                  $user->getRoles()
                );
                $this->container->get('security.context')->setToken($token);

                if(!$user->getPassword()) {
                    $user->setPassword('');
                }

                try {
                    $host = $this->container->get('request')->getHost();
                    if(strpos($host, 'ttt') !== false) {
                        $user->setSource('TTT');
                    }
                } catch(\Exception $e) {}

                $newUser = true;
            } else {
                unset($fbdata['email']);
            }

            // TODO use http://developers.facebook.com/docs/api/realtime
            $user->setFBData($fbdata);
            if($user && !$user->getImageName()) {
                try {
                    $url  = 'https://graph.facebook.com/' . $fbdata['id'] . '/picture?width=400&height=400';
                    $saveto = $this->container->getParameter("kernel.cache_dir");
                    $path = sprintf('%s/%s.%s', $saveto, sha1($url), 'gif');
                    file_put_contents($path, fopen($url, 'r'));

                    $file = new \Symfony\Component\HttpFoundation\File\UploadedFile($path, 'fb');

                    $user->setImage($file);
                } catch(\Exception $e) {}
            }

            if (count($this->validator->validate($user, 'Facebook'))) {
                // TODO: the user was found obviously, but doesnt match our expectations, do something smart
                throw new UsernameNotFoundException('The facebook user could not be stored');
            }
            $this->userManager->updateUser($user);

            if(isset($newUser) && $newUser) {
                $dispatcher = $this->container->get('event_dispatcher');
                $dispatcher->dispatch('app_user.registration_completed', new RegistrationEvent($user));
            }
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on facebook');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getFacebookId()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getFacebookId());
    }
}
