<?php

namespace Clab\ApiOldBundle\Manager;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;

class Mixpanel
{
    protected $em;
    protected $router;
    protected $mp;

    public function __construct(EntityManager $em, Router $router, $mixpanelToken)
    {
        $this->em = $em;
        $this->router = $router;
        $this->mp = \Mixpanel::getInstance($mixpanelToken);
    }

    public function track($event, array $properties, SessionManager $sessionManager)
    {
        try {
            if($user = $sessionManager->getUser()) {
                $this->mp->people->set($user->getId(), array(
                    '$first_name' => $user->getFirstName(),
                    '$last_name' => $user->getLastName(),
                    '$email' => $user->getEmail(),
                    '$phone' => $user->getPhone() ? $user->getPhone()->__toString() : null,
                    'Zipcode' => $user->getZipcode(),
                    'Newsletter Clickeat' => $user->getNewsletterClickeat()
                ));
            }

            $properties['API'] = true;

            switch ($sessionManager->getService()) {
                case 'clickeat':
                    $properties['Platform'] = 'Clickeat iOS';
                    break;
                case 'clickeat android':
                    $properties['Platform'] = 'Clickeat Android';
                    break;
                default:
                    return;
                    break;
            }
            

            $this->mp->track($event, $properties);
        } catch(\Exception $e) { }
    }

    public function flush()
    {
        $this->mp->flush();
    }
}
