<?php

namespace Clab\UserBundle\Service;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Clab\UserBundle\Entity\RegistrationRequest;
use Clab\DeliveryBundle\Entity\DeliveryMan;
use Clab\RestaurantBundle\Entity\Restaurant;

class RegistrationRequestManager
{
    protected $em;
    protected $container;
    protected $domain;

    public function __construct(EntityManager $em, ContainerInterface $container, $mailer, $templateEngine)
    {
        $this->em = $em;
        $this->container = $container;
        $this->mailer = $mailer;
        $this->templateEngine = $templateEngine;
        $this->domain = 'http://' . $this->container->getParameter('prodomain');
    }

    public function createRequest($email, $entities = array(), $roles = array(), $author = null, $message = null, $mail = true)
    {
        $request = new RegistrationRequest();
        $request->setEmail($email);
        $request->setRoles($roles);
        $request->setAuthor($author);

        foreach ($entities as $entity) {
            if($entity instanceof \Clab\RestaurantBundle\Entity\Restaurant) {
                $request->addRestaurant($entity);
            } elseif ($entity instanceof \Clab\BoardBundle\Entity\Client) {
                $request->addClient($entity);
            }
        }

        $request->setMessage($message);

        $this->em->persist($request);
        $this->em->flush();

        if($mail) {
            $this->sendMail($request);
        }
    }

    public function sendMail($request)
    {
        $mail = \Swift_Message::newInstance()
            ->setSubject('myClickeat - Demande d\'inscription')
            ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
            ->setTo($request->getEmail())
            ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:addUser.html.twig', array(
                'request' => $request,
                'domain' => $this->domain
            )), 'text/html');

        $result = $this->mailer->send($mail);
    }

    public function checkRequestsForUser($user)
    {
        $requests = $this->em->getRepository('ClabUserBundle:RegistrationRequest')
            ->findBy(array('email' => $user->getEmail()));

        foreach ($requests as $request) {
            foreach ($request->getRoles() as $role) {
                if(!$user->hasRole($role)) {
                    $user->addRole($role);
                }
            }

            foreach ($request->getRestaurants() as $restaurant) {
                if(!$restaurant->getManagers()->contains($user)) {

                    // first onboard
                    if(!$restaurant->getOwner()) {
                        $restaurant->setOwner($user);
                        $restaurant->setManagerFirstName($user->getFirstName());
                        $restaurant->setManagerName($user->getLastName());
                        $restaurant->setManagerEmail($user->getEmail());
                        $restaurant->setTttEventValidationMail($user->getEmail());
                        $restaurant->setNotificationMails($user->getEmail());

                        if(!$restaurant->getPhone() && $restaurant->getManagerPhone()) {
                            $restaurant->setPhone($restaurant->getManagerPhone());
                        }

                        if(!$restaurant->getEmail() && $restaurant->getManagerEmail()) {
                            $restaurant->setEmail($restaurant->getManagerEmail());
                        }

                         try {
                            $pipedrive = $this->container->get('clab.pipedrive');
                            $pipedrive->updateRestaurantManager($restaurant);

                            if($restaurant->getSource() == 'self') {
                                $this->container->get('clab_board.mail_manager')->newManager($restaurant, $user);
                            }
                        } catch(\Exception $e) { }
                    }
                    
                    $restaurant->addManager($user);
                }
            }

            foreach ($request->getClients() as $client) {
                if(!$client->getManagers()->contains($user)) {
                    $client->addManager($user);
                }
            }
            $this->em->remove($request);
        }

        if(count($requests) > 0) {

            if($user->isDeliveryMan()) {
                $deliveryMan = $this->em->getRepository('ClabDeliveryBundle:DeliveryMan')
                    ->findOneBy(array('user' => $user));

                if(!$deliveryMan) {
                    $deliveryMan = new DeliveryMan();
                    $deliveryMan->setUser($user);
                    $this->em->persist($deliveryMan);
                }
            }

            $this->em->flush();
            return true;
        }

        return false;
    }
}
