<?php

namespace Clab\BoardBundle\Service;

use Clab\RestaurantBundle\Entity\Restaurant;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MailManager
{
    protected $em;
    protected $container;
    protected $mailer;
    protected $templateEngine;
    protected $domain;

    public function __construct(EntityManager $em, ContainerInterface $container, $mailer, $templateEngine)
    {
        $this->em = $em;
        $this->container = $container;
        $this->mailer = $mailer;
        $this->templateEngine = $templateEngine;
        $this->domain = 'http://'.$this->container->getParameter('prodomain');
        //$this->domain = 'http://pro.click-eat.fr';

        $this->container->enterScope('request');
        $this->container->set('request', new Request(), 'request');
    }

    public function testMail()
    {
        $message = \Swift_Message::newInstance()
        ->setSubject('Clickeat Pro - Salut toi')
        ->setFrom(array('team@click-eat.fr' => 'Clickeat'))
        ->setTo('tom.piard@gmail.com')
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:test.html.twig'), 'text/html');

        $this->mailer->send($message);
    }

    public function paymentFailedMail(Restaurant $restaurant)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('CLICKLAB x '.$restaurant->getName().' - échec de paiement')
            ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
            ->setTo($restaurant->getManagerEmail())
            ->setBcc(array('compta@click-eat.fr', 'antoine@click-eat.fr'))
            ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:payment-failed.html.twig', array('restaurant' => $restaurant)), 'text/html');

        $this->mailer->send($message);
    }

    public function paymentSucceededMail(Restaurant $restaurant)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('CLICKLAB x '.$restaurant->getName().' - paiement bien reçu')
            ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
            ->setTo($restaurant->getManagerEmail())
            ->setBcc(array('compta@click-eat.fr', 'antoine@click-eat.fr'))
            ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:payment-succeeded.html.twig', array('restaurant' => $restaurant)), 'text/html');

        $this->mailer->send($message);
    }

    public function subscriptionCancelMail(Restaurant $restaurant)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('CLICKLAB x '.$restaurant->getName().' - 3ème échec de paiement')
            ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
            ->setTo($restaurant->getManagerEmail())
            ->setBcc(array('compta@click-eat.fr', 'antoine@click-eat.fr'))
            ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:subscription-cancel.html.twig', array('restaurant' => $restaurant)), 'text/html');

        $this->mailer->send($message);
    }

    public function infos($restaurant)
    {
        if ($restaurant->isMobile()) {
            $title = 'Track The Truck au Sandwich & Snack Show, l\'outil des food trucks connectés';
            $template = 'ClabBoardBundle:Mail:sss/infos-ttt.html.twig';
        } else {
            $title = 'Clickeat au Sandwich & Snack Show, découvrez notre solution de commande en ligne';
            $template = 'ClabBoardBundle:Mail:sss/infos-clickeat.html.twig';
        }

        $message = \Swift_Message::newInstance()
        ->setSubject($title)
        ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
        ->setTo($restaurant->getManagerEmail())
        ->setBody($this->templateEngine->render($template, array(
            'restaurant' => $restaurant,
            'domain' => $this->domain,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function sssGame($restaurant)
    {
        if ($restaurant->isMobile()) {
            $title = 'Votre participation au jeu Track the Truck, au Sandwich & Snack Show';
            $template = 'ClabBoardBundle:Mail:sss/game-ttt.html.twig';
        } else {
            $title = 'Votre participation au jeu Clickeat, au Sandwich & Snack Show';
            $template = 'ClabBoardBundle:Mail:sss/game-clickeat.html.twig';
        }

        $message = \Swift_Message::newInstance()
        ->setSubject($title)
        ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
        ->setTo($restaurant->getManagerEmail())
        ->setBody($this->templateEngine->render($template, array(
            'restaurant' => $restaurant,
            'domain' => $this->domain,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function newManager($restaurant, $user)
    {
        if ($restaurant->isMobile()) {
            $title = $user->getFirstname().', bienvenue sur myClickeat - l’interface de gestion Track the Truck !';
        } else {
            $title = $user->getFirstname().', bienvenue sur myClickeat - l’interface de gestion Clickeat !';
        }

        $message = \Swift_Message::newInstance()
        ->setSubject($title)
        ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
        ->setTo($user->getEmail())
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:newManager.html.twig', array(
            'restaurant' => $restaurant,
            'user' => $user,
            'domain' => $this->domain,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function invite($restaurant, $user)
    {
        if ($restaurant->isMobile()) {
            $title = $user->getFirstname().', bienvenue sur myClickeat - l’interface de gestion Track the Truck !';
        } else {
            $title = $user->getFirstname().', bienvenue sur myClickeat - l’interface de gestion Clickeat !';
        }

        $message = \Swift_Message::newInstance()
        ->setSubject($title)
        ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
        ->setTo($user->getEmail())
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:invite.html.twig', array(
            'restaurant' => $restaurant,
            'user' => $user,
            'domain' => $this->domain,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function inviteNew($restaurant, $email)
    {
        if ($restaurant->isMobile()) {
            $title = 'Bienvenue sur myClickeat - l’interface de gestion Track the Truck !';
        } else {
            $title = 'Bienvenue sur myClickeat - l’interface de gestion Clickeat !';
        }

        $message = \Swift_Message::newInstance()
        ->setSubject($title)
        ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
        ->setTo($email)
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:inviteNew.html.twig', array(
            'restaurant' => $restaurant,
            'email' => $email,
            'domain' => $this->domain,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function launchBoard($restaurant)
    {
        $user = $restaurant->getOwner();

        if (!$user) {
            return;
        }

        if ($restaurant->isMobile()) {
            $title = $user->getFirstname().', votre food truck est bientôt accessible à tous !';
        } else {
            $title = $user->getFirstname().', vous êtes à quelques clics de la commande en ligne !';
        }

        $message = \Swift_Message::newInstance()
        ->setSubject($title)
        ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
        ->setTo($user->getEmail())
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:launchBoard.html.twig', array(
            'restaurant' => $restaurant,
            'user' => $user,
            'domain' => $this->domain,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function online($restaurant)
    {
        $user = $restaurant->getOwner();

        if (!$user) {
            return;
        }

        $message = \Swift_Message::newInstance()
        ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
        ->setTo($user->getEmail());

        if ($restaurant->isMobile()) {
            $title = $user->getFirstname().', vous êtes en ligne sur Track The Truck !';
            $message
            ->setSubject($title)
            ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:online-foodtruck.html.twig', array(
                'restaurant' => $restaurant,
                'user' => $user,
                'domain' => $this->domain,
            )), 'text/html');
        } else {
            $title = $user->getFirstname().', félicitations votre restaurant est ouvert aux commandes en ligne !';
            $message
            ->setSubject($title)
            ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:online.html.twig', array(
                'restaurant' => $restaurant,
                'user' => $user,
                'domain' => $this->domain,
            )), 'text/html');
        }

        $this->mailer->send($message);
    }

    public function testReminder($restaurant, $days)
    {
        if (!$restaurant->getOwner()) {
            return false;
        }

        list($todo, $percent) = $this->container->get('clab_board.dashboard_manager')->getTodoList($restaurant);

        if ($restaurant->isMobile()) {
            $title = $restaurant->getOwner()->getFullname().', les affamés de streetfood vous attendent sur Track The Truck';
            $template = 'ClabBoardBundle:Mail:testReminderFoodtruck.html.twig';
        } else {
            $title = 'Votre période d’essai Clickeat est bientôt terminée';
            $template = 'ClabBoardBundle:Mail:testReminder.html.twig';
        }

        $message = \Swift_Message::newInstance()
        ->setSubject($title)
        ->setFrom(array('team@click-eat.fr' => 'Clickeat'))
        ->setTo($restaurant->getOwner()->getEmail())
        ->setBody($this->templateEngine->render($template, array(
            'restaurant' => $restaurant,
            'todo' => $todo,
            'percent' => $percent,
            'days' => $days,
            'domain' => $this->domain,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function testEnd($restaurant)
    {
        if (!$restaurant->getOwner()) {
            return false;
        }

        if ($restaurant->isMobile()) {
            $title = $restaurant->getOwner()->getFullname().', votre période d’essai Track the Truck vient de prendre fin';
        } else {
            $title = $restaurant->getOwner()->getFullname().', votre période d’essai Clickeat vient de prendre fin';
        }

        $message = \Swift_Message::newInstance()
        ->setSubject($title)
        ->setFrom(array('team@click-eat.fr' => 'Clickeat'))
        ->setTo($restaurant->getOwner()->getEmail())
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:testEnd.html.twig', array(
            'restaurant' => $restaurant,
            'domain' => $this->domain,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function adminSubscriptionNotification($restaurant)
    {
        $message = \Swift_Message::newInstance()
        ->setSubject('B2O - Nouvel inscrit')
        ->setFrom(array('team@click-eat.fr' => 'Clickeat'))
        ->setTo('antoine@click-eat.fr')
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:admin/subscription.html.twig', array(
            'restaurant' => $restaurant,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function adminOnlineNotification($restaurant)
    {
        $message = \Swift_Message::newInstance()
        ->setSubject('[WELL DONE] - NOUVEAU CLIENT !')
        ->setFrom(array('team@click-eat.fr' => 'Clickeat'))
        ->setTo('gang@click-eat.fr')
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:admin/online.html.twig', array(
            'restaurant' => $restaurant,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function adminDuplicateNotification($restaurant, $type, $email, $phone, $message)
    {
        $message = \Swift_Message::newInstance()
        ->setSubject('B2O - Problème d\'inscription')
        ->setFrom(array('team@click-eat.fr' => 'Clickeat'))
        ->setTo('antoine@click-eat.fr')
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:admin/duplicate.html.twig', array(
            'restaurant' => $restaurant,
            'type' => $type,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
        )), 'text/html');

        $this->mailer->send($message);
    }

    public function adminContact($source, $name, $store, $email, $phone, $message)
    {
        $mail = \Swift_Message::newInstance()
        ->setSubject('Prise de contact')
        ->setFrom(array('team@click-eat.fr' => 'Clickeat'))
        ->setTo('team@click-eat.fr');

        if ($source == 'Track the Truck') {
            $mail->setBcc('tttruckpro@gmail.com');
        }

        $mail->setBody($this->templateEngine->render('ClabBoardBundle:Mail:admin/contact.html.twig', array(
            'source' => $source,
            'name' => $name,
            'store' => $store,
            'email' => $email,
            'phone' => $phone,
            'message' => $message,
        )), 'text/html');

        $this->mailer->send($mail);
    }

    public function adminGuide($name, $email, $phone)
    {
        $mail = \Swift_Message::newInstance()
        ->setSubject('Téléchargement du guide')
        ->setFrom(array('team@click-eat.fr' => 'Clickeat'))
        ->setTo('commercial@click-eat.fr');

        $mail->setBody($this->templateEngine->render('ClabBoardBundle:Mail:admin/guide.html.twig', array(
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
        )), 'text/html');

        $this->mailer->send($mail);
    }

    public function guide($name, $email)
    {
        $mail = \Swift_Message::newInstance()
        ->setSubject('Clickeat - Guide pour tout savoir sur la commande en ligne')
        ->setFrom(array('commercial@click-eat.fr' => 'Clickeat'))
        ->setTo(array($email => $name))
        ->attach(\Swift_Attachment::fromPath($this->container->get('kernel')->getRootDir().'/../web/downloads/Guide-Vente_en_ligne.pdf'));

        $mail->setBody($this->templateEngine->render('ClabBoardBundle:Mail:guide.html.twig', array(
            'name' => $name,
            'email' => $email,
        )), 'text/html');

        $this->mailer->send($mail);
    }

    public function adminReadyNotification($restaurant)
    {
        $message = \Swift_Message::newInstance()
        ->setSubject('B2O - Restaurant ready')
        ->setFrom(array('team@click-eat.fr' => 'Clickeat'))
        ->setTo('commercial@click-eat.fr')
        ->setBcc('support@click-eat.fr')
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:admin/ready.html.twig', array(
            'restaurant' => $restaurant,
        )), 'text/html');

        if ($restaurant->getCommercial()) {
            $message->setBcc($restaurant->getCommercial()->getEmail());
        }

        $this->mailer->send($message);
    }

    public function adminAppNotification($restaurant)
    {
        $message = \Swift_Message::newInstance()
        ->setSubject('B2O - Intérêt pour une app')
        ->setFrom(array('team@click-eat.fr' => 'Clickeat'))
        ->setTo('commercial@click-eat.fr')
        ->setBcc('support@click-eat.fr')
        ->setBody($this->templateEngine->render('ClabBoardBundle:Mail:admin/app.html.twig', array(
            'restaurant' => $restaurant,
        )), 'text/html');

        $this->mailer->send($message);
    }
}
