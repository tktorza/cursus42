<?php

namespace Clab\ApiBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Persistence\ObjectManager;

class JsonFieldTransformer implements DataTransformerInterface
{
    public function transform($array)
    {
        return json_encode($array);
    }
    public function reverseTransform($string)
    {
        $modelData = json_decode($string, true);
        if ($modelData == null) {
            throw new TransformationFailedException('String is not a valid JSON.');
        }
    }
}