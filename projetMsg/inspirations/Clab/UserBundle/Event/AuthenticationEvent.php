<?php

namespace Clab\UserBundle\Event;

use Symfony\Component\EventDispatcher\Event;

class AuthenticationEvent extends Event
{
    protected $request;
    protected $user;

    public function __construct($request, $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    public function getRequest()
    {
        return $this->request;
    }

    public function getUser()
    {
        return $this->user;
    }
}
