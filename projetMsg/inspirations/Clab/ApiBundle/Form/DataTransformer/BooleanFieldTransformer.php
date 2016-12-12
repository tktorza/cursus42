<?php

namespace Clab\ApiBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class BooleanFieldTransformer implements DataTransformerInterface
{
    public function transform($boolean)
    {
        return $boolean;
    }

    public function reverseTransform($value)
    {
        if ($value == null) {
            return null;
        }

        if($value == '1' || $value == 1 || $value == 'true') {
            return true;
        }

        if($value == '0' || $value == 0 || $value == 'false') {
            return false;
        }

        return null;
    }
}