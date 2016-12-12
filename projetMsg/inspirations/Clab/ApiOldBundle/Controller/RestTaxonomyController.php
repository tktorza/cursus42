<?php

namespace Clab\ApiOldBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;

class RestTaxonomyController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Taxonomy",
     *      resource=true,
     *      description="List of terms by vocabulary",
     * )
     */
    public function vocabularyTermsAction($slug)
    {
        $terms = $this->get('clab_taxonomy.manager')->getTermsByVocabulary($slug);

        $serializer = $this->container->get('serializer');
        $response = $serializer->serialize($terms, 'json');

        return new Response($response);
    }
}
