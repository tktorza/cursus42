<?php

namespace Clab\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
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

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($terms, 'json');

        return new Response($response);
    }

    /**
     * Get tags for categories searched.
     *
     * ### Response format ###
     *
     *     {
     *       "Catégorie restaurant": [
     *         {
     *         "id": 4,
     *         "name": "Kebab",
     *         "slug": "kebab",
     *         "icons":[
     *              "on":"https://www.click-eat.fr/iconOnUrl",
     *              "off":"https://www.click-eat.fr/iconOffUrl"
     *          ]
     *         },
     *         ...
     *       ],
     *       ...
     *     }
     *
     *
     * @ApiDoc(
     *      section="Taxonomy",
     *      resource=true,
     *      requirements={
     *          {"name"="categories", "dataType"="string", "required"=true, "description"="slug for the categories in wich we will search the tags separated by a comma ex:\'?categories=categories-extra,categories-restaurant\'"},
     *      },
     *      description="List of tags (categories + regimes)"
     * )
     */
    public function getTagsAction(Request $request)
    {
        $categories = explode(',', $request->get('categories'));

        $terms = $this->get('clab_taxonomy.manager')->getAllWithVocabulary(true, $categories);

        $formatedTerms = array();

        foreach ($terms as $term) {
            if (isset($term['cat_name'])) {
                $formatedTerms[$term['cat_name']][] =
                    array(
                        'id' => $term['id'],
                        'name' => $term['name'],
                        'slug' => $term['slug'],
                        'icons' => array(
                            'on' => sprintf('%s/images/terms/%s', $request->getSchemeAndHttpHost(), $term['iconOnName'] ?: 'blank.png'),
                            'off' => sprintf('%s/images/terms/%s', $request->getSchemeAndHttpHost(),  $term['iconOffName'] ?: 'blank.png'),
                        )
                    );
            }
        }

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($formatedTerms, 'json');

        return new Response($response);
    }
}
