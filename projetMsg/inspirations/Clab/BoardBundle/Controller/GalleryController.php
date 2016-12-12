<?php

namespace Clab\BoardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Clab\MediaBundle\Entity\Gallery;
use Clab\MediaBundle\Entity\Image;
use Clab\MediaBundle\Entity\Album;
use Clab\BoardBundle\Form\Type\Media\AlbumType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class GalleryController extends Controller
{
    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function getAction($context, $contextPk, $entityType, $entityId, $type = null)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $imageManager = $this->get('app_media.image_manager');

        list($success, $gallery) = $imageManager->getGallery($entityType, $entityId, $this->getUser(), $type);

        $params = array(
            'gallery' => $gallery,
            'entityType' => $entityType,
            'entityId' => $entityId,
            'proDomain' => $this->getParameter('boarddomain'),
            'type' => $type,
        );

        if ($this->getRequest()->get('noCover')) {
            $params = array(
                'noCover' => true,
                'gallery' => $gallery,
                'entityType' => $entityType,
                'entityId' => $entityId,
                'proDomain' => $this->getParameter('boarddomain'),
                'type' => $type,
            );
        }

        return $this->render('ClabBoardBundle:Gallery:get.html.twig', array_merge($this->get('board.helper')->getParams(), $params));
    }

    public function uploadAction($context, $contextPk, $entityType, $entityId, $type = null)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $imageManager = $this->get('app_media.image_manager');

        list($success, $image) = $imageManager->upload($entityType, $entityId, $this->getUser(), $type);

        if ($success) {
            $cacheManager = $this->get('liip_imagine.cache.manager');
            $url = $cacheManager->getBrowserPath($image->getWebPath(), 'square_180');

            $data = $this->container->get('jms_serializer')
            ->serialize(array(
                'success' => $success,
                'url' => $url,
                'urlFull' => $url,
                'id' => $image->getId(),
            ), 'json');

            return new Response($data, 200);
        }

        return new Response('', 400);
    }

    public function deleteAction($context, $contextPk, $entityType, $entityId, $type = null)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST' && $imageId = $this->getRequest()->request->get('imageId')) {
            $imageManager = $this->get('app_media.image_manager');

            list($success, $gallery) = $imageManager->remove($entityType, $entityId, $imageId, $this->getUser(), $type);

            $cacheManager = $this->get('liip_imagine.cache.manager');
            if ($gallery->getImages()->first()) {
                $url = $cacheManager->getBrowserPath($gallery->getImages()->first()->getWebPath(), 'square_180');
            }

            $data = $this->container->get('jms_serializer')
            ->serialize(array(
                'success' => $success,
                'url' => isset($url) ? $url : null,
                'urlFull' => isset($url) ? $url : null,
            ), 'json');

            if ($success) {
                return new Response($data, 200);
            }
        }

        return new Response('', 400);
    }

    public function coverAction($context, $contextPk, $entityType, $entityId, $type = null)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $request = $this->get('request');
        if ($request->getMethod() == 'POST' && $imageId = $this->getRequest()->request->get('imageId')) {
            $imageManager = $this->get('app_media.image_manager');
            list($success, $gallery) = $imageManager->setCover($entityType, $entityId, $imageId, $this->getUser(), $type);

            $cacheManager = $this->get('liip_imagine.cache.manager');
            $url = $cacheManager->getBrowserPath($gallery->getCover()->getWebPath(), 'square_180');

            $data = $this->container->get('jms_serializer')
            ->serialize(array(
                'success' => $success,
                'url' => $url,
                'urlFull' => $url,
            ), 'json');

            if ($success) {
                return new Response($data, 200);
            }
        }

        return new Response('', 400);
    }

    public function replaceAction($context, $contextPk, $entityType, $entityId, $image, $type = null)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $imageManager = $this->get('app_media.image_manager');

        $image = str_replace('image-', '', $image);
        $media = $this->getMediaFromUrl($this->getRequest()->get('url'));

        list($success, $image) = $imageManager->replace($entityType, $entityId, $this->getUser(), $media, $image, $type);

        if ($success) {
            $cacheManager = $this->get('liip_imagine.cache.manager');
            $url = $cacheManager->getBrowserPath($image->getWebPath(), 'square_180');

            $data = $this->container->get('jms_serializer')
            ->serialize(array(
                'success' => $success,
                'promoted' => $image->isPromoted(),
                'url' => $url,
                'urlFull' => $url,
                'id' => $image->getId(),
            ), 'json');

            return new Response($data, 200);
        }

        return new Response('', 400);
    }

    public function selectAction($context, $contextPk, $entityType, $entityId, $field)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $imageManager = $this->get('app_media.image_manager');
        list($success, $gallery, $entity) = $imageManager->getGallery($entityType, $entityId, $this->getUser());

        $backUrl = $this->getRequest()->get('backUrl') ? $this->getRequest()->get('backUrl') : null;

        if ($field == 'cover') {
            $image = $gallery->getCover();
        } elseif (method_exists($entity, 'get'.ucfirst($field)) && $entity->{'get'.ucfirst($field)}()) {
            $image = $entity->{'get'.ucfirst($field)}();
        }

        $form = $this->createFormBuilder()
            ->add('image', 'entity', array(
                'class' => 'ClabMediaBundle:Image',
                'choices' => $gallery->getImages(),
                'expanded' => true,
                'data' => isset($image) && $image ? $image : $gallery->getImages()->first(),
            ))
        ->getForm();

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            if ($field == 'cover') {
                list($success, $gallery) = $imageManager->setCover($entityType, $entityId, $form->get('image')->getData()->getId(), $this->getUser());
            } elseif (method_exists($entity, 'set'.ucfirst($field))) {
                $entity->{'set'.ucfirst($field)}($form->get('image')->getData());
                $em->flush();
            }

            if ($backUrl) {
                return $this->redirect($backUrl);
            }

            return $this->redirectToRoute('board_dashboard', array('contextPk' => $contextPk, 'context' => $context));
        }

        return $this->render('ClabBoardBundle:Gallery:select.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'form' => $form->createView(),
            'entityType' => $entityType, 'entityId' => $entityId,
            'field' => $field,
            'backUrl' => $backUrl,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function albumLibraryAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $albums = $this->get('board.helper')->getProxy()->getAlbums();

        return $this->render('ClabBoardBundle:Gallery:albumLibrary.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'albums' => $albums,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function albumLibraryListAction($context, $contextPk)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        $albums = $this->get('board.helper')->getProxy()->getAlbums();

        return $this->render('ClabBoardBundle:Gallery:albumLibrary.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'albums' => $albums,
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function albumEditAction($context, $contextPk, $slug)
    {
        $this->get('board.helper')->initContext($context, $contextPk);

        if ($slug) {
            $em = $this->getDoctrine()->getManager();
            $album = $em->getRepository('ClabMediaBundle:Album')->findOneBy(array('slug' => $slug));

            if (!$album) {
                throw $this->createNotFoundException();
            }

            if (!$this->get('board.helper')->getProxy()->getAlbums()->contains($album)) {
                throw new AccessDeniedException();
            }
        } else {
            $album = new Album();
        }

        if (!$this->getRequest()->isXmlHttpRequest()) {
            return $this->redirectToRoute('board_album_library', array('context' => $context, 'contextPk' => $contextPk));
        }

        $form = $this->createForm(new AlbumType(), $album);
        $request = $this->get('request');
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') {
            $form->bind($this->getRequest());

            if ($form->isValid()) {
                $this->get('board.helper')->getProxy()->addAlbum($album);
                $em->persist($album);
                $em->flush();

                $this->get('session')->getFlashBag()->add('formSuccess', 'L\'album a bien été sauvegardé');

                return $this->redirectToRoute('board_album_edit', array('context' => $context, 'contextPk' => $contextPk, 'slug' => $album->getSlug()));
            } else {
                $this->get('session')->getFlashBag()->add('formError', 'Erreur dans le formulaire');
            }
        }

        return $this->render('ClabBoardBundle:Gallery:albumEdit.html.twig', array_merge($this->get('board.helper')->getParams(), array(
            'album' => $album,
            'form' => $form->createView(),
        )));
    }

    /**
     * @Secure(roles="ROLE_MANAGER_2")
     */
    public function albumDeleteAction($context, $contextPk, $slug)
    {
        $this->get('board.helper')->initContext($context, $contextPk);
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository('ClabMediaBundle:Album')->findOneBy(array('slug' => $slug));

        if (!$album) {
            throw $this->createNotFoundException();
        }

        if (!$this->get('board.helper')->getProxy()->getAlbums()->contains($album)) {
            throw new AccessDeniedException();
        }

        $this->get('board.helper')->getProxy()->removeAlbum($album);
        $em->remove($album);
        $em->flush();

        return $this->redirectToRoute('board_album_library', array('context' => $context, 'contextPk' => $contextPk));
    }

    public function getMediaFromUrl($mediaUrl)
    {
        $path = tempnam(sys_get_temp_dir(), 'kuma_');
        $saveFile = fopen($path, 'w');
        $this->path = $path;

        $ch = curl_init($mediaUrl);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FILE, $saveFile);
        curl_exec($ch);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        fclose($saveFile);
        chmod($path, 0777);

        $url = parse_url($effectiveUrl);
        $info = pathinfo($url['path']);
        $filename = md5(time().$info['filename']).'.'.$info['extension'];

        // activate test mode in order to be able to move file
        // @todo make something different...
        $upload = new UploadedFile($path, $filename, null, null, null, true);

        return $upload;
    }
}
