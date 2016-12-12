<?php

namespace Clab\CallCenterBundle\Controller;

use Clab\CallCenterBundle\Form\Type\User\EditType;
use Clab\ShopBundle\Entity\OrderDetail;
use Clab\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Widop\HttpAdapter\CurlHttpAdapter;


class UserController extends Controller
{
    public function loginAction(Request $request)
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('clab_call_center_homepage');
        }

        $session = $request->getSession();

        if ($request->attributes->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContextInterface::AUTHENTICATION_ERROR);
        } elseif (null !== $session && $session->has(SecurityContextInterface::AUTHENTICATION_ERROR)) {
            $error = $session->get(SecurityContextInterface::AUTHENTICATION_ERROR);
            $session->remove(SecurityContextInterface::AUTHENTICATION_ERROR);
        } else {
            $error = null;
        }

        if (!$error instanceof AuthenticationException) {
            $error = null;
        }

        $lastUsername = (null === $session) ? '' : $session->get(SecurityContextInterface::LAST_USERNAME);

        $csrfToken = $this->has('form.csrf_provider')
            ? $this->get('form.csrf_provider')->generateCsrfToken('authenticate')
            : null;

        return $this->render('ClabCallCenterBundle:Default:login.html.twig', array(
            'last_username' => $lastUsername,
            'error' => $error,
            'csrf_token' => $csrfToken,
        ));
    }

    /**
     * @ParamConverter("user", class="ClabUserBundle:User", options={"id" = "userId"})
     */
    public function userInfoAction(Request $request, User $user)
    {

        $form = $this->createForm(new EditType(), $user);

        if ($request->isXmlHttpRequest()) {
            if($form->handleRequest($request)->isValid()) {

                foreach($user->getAddresses() as $address) {
                    $address->setUser($user);
                    $this->getDoctrine()->getManager()->persist($address);
                }

                $this->getDoctrine()->getManager()->flush();

                $response = array(
                    $user->getEmail(),
                    $user->getLastName(),
                    $user->getFirstName(),
                    $user->getCompany() ? $user->getCompany()->getName() : "",
                    $user->getPhone(),
                    "<a href=".$this->get('router')->generate('clab_call_center_choose_customer',array('id'=>$user->getId())).">
                        Commander  <i class=\"fa fa-arrow-circle-right fa-2x\" aria-hidden=\"true\"></i>
                    </a>"
                    )
                ;

                return new JsonResponse($response);
            }

            //$orders = $this->getDoctrine()->getRepository(OrderDetail::class)->findBy(array('profile'=>$user), array('created' => 'DESC'));

            return $this->render('ClabCallCenterBundle:Default:modalUser.html.twig', array(
                'user' => $user,
                //'orders' => $orders,
                'form' => $form->createView(),
            ));
        }

        return $this->redirectToRoute('clab_call_center_homepage');
    }

    public function searchUsersAction(Request $request)
    {
        $search = $request->get('search');

        $users = $this->getDoctrine()->getRepository(User::class)->searchUsers($search, 500);

        if(!$users) {
            return new Response('error',400);
        }

        $ret = [];

        foreach($users as $user) {
            $ret[] = [
                'id'=> $user->getId(),
                'first_name' => $user->getFirstName() ? $user->getFirstName() : '' ,
                'name' => $user->getLastName() ? $user->getLastName() : '',
                'society' => $user->getCompany() ? $user->getCompany()->getName(): '',
                'email' => $user->getEmail(),
                'phone' => $user->getPhone() ? $user->getPhone() : ''
            ];
        }

        return new JsonResponse($ret);
    }
}
