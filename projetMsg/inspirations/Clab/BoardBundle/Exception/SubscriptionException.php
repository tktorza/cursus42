<?php

namespace Clab\BoardBundle\Exception;

use \Exception;

class SubscriptionException extends Exception
{
    protected $redirectUrl;

    public function __construct($redirectUrl)
    {
        parent::__construct('Votre inscription est incomplète');

        $this->redirectUrl = $redirectUrl;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }
}