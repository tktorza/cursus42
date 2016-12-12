<?php

namespace Clab\BoardBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class CommaToPointTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (!empty($value)) {
                return preg_replace('/[,]/', '.', $value);
        }

        return 0.;
    }

    public function reverseTransform($value)
    {
        return $value;
    }
}