<?php

namespace Clab\BoardBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class BooleanIntegerTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if(is_int($value) && $value > 0) {
            return true;
        }

        return false;
    }

    public function reverseTransform($value)
    {
        if($value) {
            return 1;
        }

        return 0;
    }
}