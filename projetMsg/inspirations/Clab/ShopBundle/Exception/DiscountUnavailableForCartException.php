<?php

namespace Clab\ShopBundle\Exception;
use \Exception;

class DiscountUnavailableForCartException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
