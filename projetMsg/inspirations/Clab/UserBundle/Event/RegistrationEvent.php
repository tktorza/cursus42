<?php

namespace Clab\UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class RegistrationEvent extends Event
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}
