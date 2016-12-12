<?php

namespace Clab\TaxonomyBundle\Twig;

use \Twig_Extension;

class TermExtension extends Twig_Extension
{
    public function getFilters()
    {
        return array(
            'tagsToLabel' => new \Twig_Filter_Method($this, 'verboseTerm'),
            'tagsToList' => new \Twig_Filter_Method($this, 'listTerm'),
            'tagsToListComma' => new \Twig_Filter_Method($this, 'listTermComma'),
        );
    }

    public function verboseTerm($terms)
    {
        $string = '';
        foreach ($terms as $term) {
            $string .= '<span class="label label-info">' . ucfirst($term->getName()) . '</span>&nbsp;';
        }

        return $string;
    }

    public function listTerm($terms)
    {
        $string = '';
        foreach ($terms as $term) {
            $string .= ucfirst($term->getName()) . ' ';
        }

        return $string;
    }

    public function listTermComma($terms)
    {
        $string = '';
        foreach ($terms as $term) {
            $string .= ucfirst($term->getName()) . ', ';
        }

        $string = substr($string, 0, -2);

        return $string;
    }

    public function getName()
    {
        return 'term_extention';
    }
}