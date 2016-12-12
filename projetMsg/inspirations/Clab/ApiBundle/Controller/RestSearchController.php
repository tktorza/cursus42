<?php
namespace Clab\ApiBundle\Controller;

use Clab\BoardBundle\Entity\Client;
use Clab\BoardBundle\Entity\UserDataBase;
use Clab\UserBundle\Entity\User;
use FOS\RestBundle\Controller\FOSRestController;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Clab\RestaurantBundle\Entity\Restaurant;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RestSearchController extends FOSRestController
{
    /**
     * Search all restaurants in 2km range.
     *
     * @ApiDoc(
     *   section="Restaurant",
     *   resource = "/api/v1/search",
     *   description = "Search all restaurants, clients and tags that contains the words you type",
     *      parameters={
     *          {"name"="search", "dataType"="string", "required"=true, "description"="the word you want to search)"},
     *      }
     * )
     *
     * @return Response
     */
    public function searchMultipleAction(Request $request) {
        $search = $request->get('search');

        $results['restaurants'] = $this->get('app_restaurant.restaurant_manager')->findBySearch($search);
        $results['tags'] = $this->get('clab_taxonomy.manager')->findBySearch($search);
        $results['clients'] = $this->getDoctrine()->getEntityManager()->getRepository('ClabBoardBundle:Client')->findBySearch($search);

        $serializer = $this->get('serializer');
        $response = $serializer->serialize($results, 'json');
        $response = new Response($response);

        return $response;
    }
}