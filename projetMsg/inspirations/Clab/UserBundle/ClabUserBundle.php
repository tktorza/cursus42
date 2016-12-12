<?php

namespace Clab\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClabUserBundle extends Bundle
{
	public function getParent()
    {
        return 'FOSUserBundle';
    }
}
