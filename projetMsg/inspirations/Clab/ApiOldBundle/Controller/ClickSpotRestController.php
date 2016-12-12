<?php

namespace Clab\ApiOldBundle\Controller;

use Clab\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ClickSpotRestController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Search",
     *      resource=true,
     *      description="Get restaurants near an address",
     *      requirements={
     *          {"name"="street", "dataType"="string", "required"=true, "description"="Rue autours de laquelle
     on recherche"}
     *      }
     * )
     */
    public function searchNearbByAction($street)
    {
        $restaurantNearBy = $this->get('clab.gmap.service')->nearByAction($street);

        return new JsonResponse($restaurantNearBy);
    }

    /**
     * @ApiDoc(
     *      section="Search",
     *      resource=true,
     *      description="Upload a file",
     *      requirements={
     *          {"name"="address", "dataType"="string", "required"=true, "description"="Afficher la Gmap par rapport
     * à une addresse"}
     *      }
     * )
     */
    public function mapAction($address)
    {
        $map = $this->container->get('clab.gmap.service')->markerAction($address);

        return new JsonResponse($map);
    }

    protected function loginUser(User $user)
    {
        $security = $this->get('security.context');
        $providerKey = $this->container->getParameter('fos_user.firewall_name');
        $roles = $user->getRoles();
        $token = new UsernamePasswordToken($user, null, $providerKey, $roles);
        $security->setToken($token);
    }
    protected function logoutUser()
    {
        $security = $this->get('security.context');
        $token = new AnonymousToken(null, new User());
        $security->setToken($token);
        $this->get('session')->invalidate();
    }

    protected function checkUserPassword(User $user, $password)
    {
        $factory = $this->get('security.encoder_factory');
        $encoder = $factory->getEncoder($user);
        if (!$encoder) {
            return false;
        }

        return $encoder->isPasswordValid($user->getPassword(), $password, $user->getSalt());
    }
    protected function getUserManager()
    {
        return $this->get('fos_user.user_manager');
    }

    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="Login",
     *      requirements={
     *          {"name"="email", "dataType"="string", "required"=true, "description"="email"},
     *          {"name"="password", "dataType"="string", "required"=true, "description"="password"}
     *      }
     * )
     */
    public function loginAction($email, $password)
    {
        $um = $this->getUserManager();
        $user = $um->findUserByEmail($email);

        if (!$user instanceof User) {
            throw new NotFoundHttpException('Utilisateur non trouvé');
        }
        if (!$this->checkUserPassword($user, $password)) {
            throw new AccessDeniedException('Mot de passe erroné');
        }

        $this->loginUser($user);

        return new JsonResponse($user);
    }
    /**
     * @ApiDoc(
     *      section="User",
     *      resource=true,
     *      description="Logout",
     *      requirements={
     *      }
     * )
     */
    public function logoutAction()
    {
        $this->logoutUser();

        return  new JsonResponse('200');
    }
}
