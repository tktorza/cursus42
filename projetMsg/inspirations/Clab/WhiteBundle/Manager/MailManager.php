<?php

namespace Clab\WhiteBundle\Manager;

use Clab\ShopBundle\Entity\OrderDetail;
use Clab\ShopBundle\Entity\OrderType;
use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Zend\Mail\Message;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class MailManager implements MailerInterface
{
    protected $em;
    protected $mailer;
    protected $templateEngine;
    protected $container;

    public function __construct(EntityManager $em, $mailer, $templateEngine, ContainerInterface $container)
    {
        $this->em = $em;
        $this->mailer = $mailer;
        $this->templateEngine = $templateEngine;
        $this->container = $container;
    }


    public function registerMail($user)
    {

        $message = \Swift_Message::newInstance()
            ->setSubject('Bienvenue '.$user->getFirstname().', votre inscription a bien été prise en compte !')
            ->setFrom('matsuri@click-eat.fr')
            ->setTo($user->getEmail())
            ->setBody($this->templateEngine->render('ClabWhiteBundle:Mail:welcome.html.twig', array(
                'user' => $user,
            )), 'text/html');

        $this->mailer->send($message);
    }

    public function passwordResetMail($user, $url)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Réinitialisation de mot de passe Matsuri')
            ->setFrom('matsuri@click-eat.fr')
            ->setTo($user->getEmail())
            ->setBody($this->templateEngine->render('ClabWhiteBundle:Mail:passwordReset.html.twig', array(
                'user' => $user,
                'url' => $url,
            )), 'text/html');

        $this->mailer->send($message);
    }

    public function confirmOrder($restaurant, $order)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($order->getProfile()->getFirstname().', votre commande chez '.$restaurant->getName().' a bien été prise en compte !')
            ->setFrom('matsuri@click-eat.fr')
            ->setTo($order->getProfile()->getEmail());

            $template = ($order->getOrderType()->getId() == OrderType::ORDERTYPE_DELIVERY) ? 'ClabWhiteBundle:Mail:confirmOrderDelivery.html.twig' : 'ClabWhiteBundle:Mail:confirmOrder.html.twig' ;

            $message->setBody($this->templateEngine->render($template, array('restaurant' => $restaurant, 'order' => $order)), 'text/html');


        $this->mailer->send($message);
    }

    public function mailNotification($order)
    {
        $mails = explode(',', $order->getRestaurant()->getNotificationMails());
        $confirm = array();
        foreach ($mails as $mail) {
            $mail = trim($mail);
            if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                $confirm[] = $mail;
            }
        }

        if ($this->container->get('kernel')->getEnvironment() == 'prod') {
            $to = 'matsuri@click-eat.fr';
            $confirm = array(
                'camille@click-eat.fr',
                'luiz@click-eat.fr',
                $order->getRestaurant()->getEmail()
            );
        } else {
            $to = 'sacha@click-eat.fr';
        }

        $content = 'Clickeat : Nouvelle commande de '.$order->getProfile()->getFullName().' à '.$order->getTime()->format('H:i').' chez '.$order->getRestaurant()->getName();
        $message = \Swift_Message::newInstance()
            ->setSubject($content)
            ->setFrom('matsuri@click-eat.fr')
            ->setTo($to)
            ->setBody($this->templateEngine->render('ClabWhiteBundle:Mail:orderNotification.html.twig', array('order' => $order)), 'text/html');

        if (count($confirm) > 0) {
            $message->setBcc($confirm);
        }

        $this->mailer->send($message);
    }

    /**
     * Send an email to a user to confirm the account creation
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Bienvenue '.$user->getFirstname().', votre inscription a bien été prise en compte !')
            ->setFrom('matsuri@click-eat.fr')
            ->setTo($user->getEmail())
            ->setBody($this->templateEngine->render('ClabWhiteBundle:Mail:welcome.html.twig', array(
                'user' => $user,
            )), 'text/html');

        $this->mailer->send($message);
    }

    /**
     * Send an email to a user to confirm the password reset
     *
     * @param UserInterface $user
     *
     * @return void
     */
    public function sendResettingEmailMessage(UserInterface $user)
    {
        $url = $this->container->get('router')->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        $message = \Swift_Message::newInstance()
            ->setSubject('Réinitialisation de mot de passe Matsuri')
            ->setFrom('matsuri@click-eat.fr')
            ->setTo($user->getEmail())
            ->setBody($this->templateEngine->render('ClabWhiteBundle:Mail:passwordReset.html.twig', array(
                'user' => $user,
                'url'  => $url,
            )), 'text/html');

        $this->mailer->send($message);
    }
}
