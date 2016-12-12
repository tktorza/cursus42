<?php

namespace Clab\ApiBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\View\View;
use Clab\ApiBundle\Form\Type\Media\RestGalleryType;

class RestMediaController extends FOSRestController
{
    /**
     * @ApiDoc(
     *      section="Media",
     *      description="Get gallery",
     *      requirements={
     *          {"name"="entityType", "dataType"="string", "required"=true, "description"="Entity type"},
     *          {"name"="entityId", "dataType"="integer", "required"=true, "description"="Entity id"}
     *      }
     * )
     */
    public function getAction($entityType, $entityId)
    {
        $imageManager = $this->get('app_media.image_manager');

        list($success, $content) = $imageManager->getGallery($entityType, $entityId, $this->get('api.session_manager')->getUser(),
            null,
            true);

        if ($success) {
            $result = $this->get('api.rest_manager')->getResponse(array('gallery' => $content));

            return new JsonResponse($result);
        } else {
            return new JsonResponse(400);
        }
    }

    /**
     * @ApiDoc(
     *      section="Media",
     *      description="Patch gallery",
     *      requirements={
     *          {"name"="entityType", "dataType"="string", "required"=true, "description"="Entity type"},
     *          {"name"="entityId", "dataType"="integer", "required"=true, "description"="Entity id"}
     *      }
     * )
     */
    public function editAction($entityType, $entityId, $type, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $imageManager = $this->get('app_media.image_manager');

        list($success, $gallery) = $imageManager->getGallery($entityType, $entityId, $type,
            $this->get('api.session_manager')->getUser());

        if ($success) {
            $form = $this->createForm(new RestGalleryType(array('images' => $gallery->getImages())), $gallery, array('method' => 'PATCH'));
            $form->submit($request, false);

            if ($form->isValid()) {
                $cover = $form->get('cover')->getData();
                $gallery->setCover($cover);

                $em->flush();

                $response = new Response(204, '');

                return $response;
            }

            return $this->get('api.rest_manager')->getFormErrorResponse($form);
        } else {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Not found');
        }
    }

    /**
     * @ApiDoc(
     *      section="Media",
     *      description="Upload picture to entity gallery",
     *      requirements={
     *          {"name"="entityType", "dataType"="string", "required"=true, "description"="Entity type"},
     *          {"name"="entityId", "dataType"="integer", "required"=true, "description"="Entity id"},
     *          {"name"="galleryType", "dataType"="integer", "required"=true, "description"="Gallery type"}
     *      }
     * )
     */
    public function uploadAction($entityType, $entityId, $galleryType)
    {
        $imageManager = $this->get('app_media.image_manager');
        $cacheManager = $this->get('liip_imagine.cache.manager');

        list($success, $content) = $imageManager->upload($entityType, $entityId, $this->getUser(), $galleryType);

        if ($success) {
            return new JsonResponse(array(
                'image' => $cacheManager->getBrowserPath($content->getWebPath(), 'square_180')
            ));
        } else {
            return View::create($content, 400);
        }
    }

    /**
     * @ApiDoc(
     *      section="Media",
     *      resource=true,
     *      description="Delete image for gallery",
     *      requirements={
     *          {"name"="entityType", "dataType"="string", "required"=true, "description"="Entity type"},
     *          {"name"="entityId", "dataType"="integer", "required"=true, "description"="Entity id"},
     *          {"name"="imageId", "dataType"="string", "required"=true, "description"="Image ID"},
     *      },
     * )
     */
    public function deleteAction($entityType, $entityId, $imageId)
    {
        if (true !== $init = $this->init()) {
            return $init;
        }

        $imageManager = $this->get('app_media.image_manager');

        list($success, $content) = $imageManager->remove($entityType, $entityId, $imageId, $this->get('api.session_manager')->getUser());

        if ($success) {
            return $this->get('api.rest_manager')->getResponse(array());
        } else {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Not found');
        }
    }

    /**
     * @ApiDoc(
     *      section="Media",
     *      description="Get public gallery",
     *      requirements={
     *          {"name"="entityType", "dataType"="string", "required"=true, "description"="Entity type"},
     *          {"name"="entityId", "dataType"="integer", "required"=true, "description"="Entity id"}
     *      }
     * )
     */
    public function getPublicAction($entityType, $entityId)
    {
        $imageManager = $this->get('app_media.image_manager');

        list($success, $content) = $imageManager->getPublicGallery($entityType, $entityId, $this->get('api.session_manager')->getUser(), true);

        if ($success) {
            return $this->get('api.rest_manager')->getResponse(array('gallery' => $content));
        } else {
            return $this->get('api.rest_manager')->getErrorResponse('ERROR', 'Not found');
        }
    }

    /**
     * @ApiDoc(
     *      section="Media",
     *      description="Upload picture to entity public gallery",
     *      requirements={
     *          {"name"="entityType", "dataType"="string", "required"=true, "description"="Entity type"},
     *          {"name"="entityId", "dataType"="integer", "required"=true, "description"="Entity id"}
     *      }
     * )
     */
    public function uploadPublicAction($entityType, $entityId)
    {
        $imageManager = $this->get('app_media.image_manager');

        list($success, $content) = $imageManager->upload($entityType, $entityId, $this->get('api.session_manager')->getUser(), 'public');

        if ($success) {
            return new JsonResponse($content);
        } else {
            return View::create($content, 400);
        }
    }
}
