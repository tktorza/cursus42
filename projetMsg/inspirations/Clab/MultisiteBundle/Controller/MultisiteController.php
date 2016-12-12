<?php

namespace Clab\MultisiteBundle\Controller;

use Clab\RestaurantBundle\Entity\Restaurant;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Clab\MultisiteBundle\Form\Type\ContactType;
use Clab\ReviewBundle\Entity\Review;
use Clab\ReviewBundle\Form\Type\ReviewType;
use Symfony\Component\HttpFoundation\Request;

class MultisiteController extends Controller
{
    protected $site;
    protected $restaurant;
    protected $orderUrl;

    public function init()
    {
        $multisiteManager = $this->get('clab_multisite.multisite_manager');
        $site = $multisiteManager->getByDomain($this->getRequest()->getHost());

        if (!$site || !$site->getRestaurant()) {
            throw $this->createNotFoundException();
        }

        $this->setSite($site);
        $this->setRestaurant($site->getRestaurant());

        if (!$site->isOnline() || $this->getRestaurant()->getStatus() < Restaurant::STORE_STATUS_ACTIVE || $this->getRestaurant()->getStatus() >= Restaurant::STORE_STATUS_TRASH) {
            throw $this->createNotFoundException();
        }

        $reviewData = $this->container->get('clab_review.review_manager')->getReviewsForEntity($this->getRestaurant());
        $this->getRestaurant()->setReviewData($reviewData);

        $newsData = $this->container->get('clab.social_manager')->getNewsDataForRestaurant($this->getRestaurant());
        $this->getRestaurant()->setNewsData($newsData);

        $this->orderUrl = $multisiteManager->getUrlForRestaurant($this->getRestaurant(), true);
    }

    public function getSite()
    {
        return $this->site;
    }

    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    public function getRestaurant()
    {
        return $this->restaurant;
    }

    public function setRestaurant($restaurant)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getAction()
    {
        $this->init();

        if ($this->getRestaurant()->isMobile()) {
            $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->getRestaurant(), '5 days', false);
            $foodtruck->updateSchedule(date_create('today'), date_create('now'), date_create('now'));
        } else {
            $planning = $this->get('app_restaurant.timesheet_manager')->getWeekDayPlanning($this->getRestaurant());
        }

        return $this->render('ClabMultisiteBundle:Multisite:get.html.twig', array(
            'site' => $this->site,
            'restaurant' => $this->restaurant,
            'orderUrl' => $this->orderUrl,
            'planning' => isset($planning) ? $planning : null,
            'foodtruck' => isset($foodtruck) ? $foodtruck : null,
        ));
    }

    public function styleAction()
    {
        $this->init();

        $response = $this->render('ClabMultisiteBundle:Theme:style.css.twig', array(
            'site' => $this->site,
            'restaurant' => $this->restaurant,
        ));

        $response->headers->set('Content-Type', 'text/css');

        return $response;
    }

    public function menuAction()
    {
        $this->init();

        $menu = $this->get('clab.restaurant_menu_manager')->getDefaultMenuForRestaurant($this->restaurant);
        $categories = $this->get('app_restaurant.product_category_manager')->getAvailableForRestaurant($this->restaurant);
        $products = $this->get('app_restaurant.product_manager')->getAvailableForRestaurantMenu($menu);
        $meals = $this->get('app_restaurant.meal_manager')->getAvailableForRestaurantMenu($menu);

        return $this->render('ClabMultisiteBundle:Multisite:menu.html.twig', array(
            'site' => $this->site,
            'restaurant' => $this->restaurant,
            'orderUrl' => $this->orderUrl,
            'categories' => $categories,
            'products' => $products,
            'meals' => $meals,
        ));
    }

    public function planningAction()
    {
        $this->init();

        $foodtruck = $this->get('clab_ttt.foodtruck_manager')->createFoodtruck($this->getRestaurant(), '5 days', false);
        $foodtruck->updateSchedule(date_create('today'), date_create('now'), date_create('now'));

        return $this->render('ClabMultisiteBundle:Multisite:planning.html.twig', array(
            'site' => $this->site,
            'restaurant' => $this->restaurant,
            'orderUrl' => $this->orderUrl,
            'foodtruck' => $foodtruck,
        ));
    }

    public function socialAction()
    {
        $this->init();

        $posts = $this->getDoctrine()->getManager()->getRepository('ClabSocialBundle:SocialPost')->getForRestaurant($this->getRestaurant(), 1, 30);

        return $this->render('ClabMultisiteBundle:Multisite:social.html.twig', array(
            'site' => $this->site,
            'restaurant' => $this->restaurant,
            'orderUrl' => $this->orderUrl,
            'posts' => $posts,
        ));
    }

    public function galleryAction()
    {
        $this->init();

        return $this->render('ClabMultisiteBundle:Multisite:gallery.html.twig', array(
            'site' => $this->site,
            'restaurant' => $this->restaurant,
            'orderUrl' => $this->orderUrl,
        ));
    }

    public function productAction($slug, Request $request)
    {
        $this->init();
        $id = $this->getRestaurant()->getId();
        $restaurant = $this->getDoctrine()->getRepository('ClabRestaurantBundle:Restaurant')->find($id);
        $products = $this->getDoctrine()->getManager()->getRepository('ClabRestaurantBundle:Product')->findBy(array('slug' => $slug, 'isOnline' => true, 'isDeleted' => false));
        foreach ($products as $result) {
            if ($result->getRestaurant()->getSlug() === $restaurant->getSlug()) {
                $product = $result;
                break;
            }
        }
        if (!$request->isXmlHttpRequest() || !$product) {
            throw $this->createNotFoundException();
        }

        return $this->render('ClabMultisiteBundle:Multisite:product.html.twig', array(
            'site' => $this->site,
            'restaurant' => $this->restaurant,
            'orderUrl' => $this->orderUrl,
            'product' => $product,
        ));
    }

    public function reviewsAction()
    {
        $this->init();

        $reviews = $this->getDoctrine()->getRepository('ClabReviewBundle:Review')->findBy(array('restaurant' => $this->getRestaurant(), 'isOnline' => true));

        return $this->render('ClabMultisiteBundle:Multisite:reviews.html.twig', array(
            'site' => $this->site,
            'restaurant' => $this->restaurant,
            'reviews' => $reviews,
            'orderUrl' => $this->orderUrl,
        ));
    }

    public function writeReviewAction(Request $request)
    {
        $this->init();

        return $this->redirectToRoute('multisite_reviews');

        $reviewManager = $this->get('clab_review.review_manager');

        $review = new Review();
        $form = $this->createForm(new ReviewType(), $review);

        if ($form->handleRequest($request)->isValid()) {
            $review = $reviewManager->writeReview($this->getRestaurant(), null, array(
                'title' => $form->get('title')->getData(),
                'body' => $form->get('body')->getData(),
                'authorName' => $form->get('authorName')->getData(),
                'score' => $form->get('score')->getData(),
                'source' => 'byclickeat',
            ));

            $this->addFlash('success', 'Merci, votre avis a bien été reçu, il sera publié suite à une modération');

            return $this->redirectToRoute('multisite_reviews');
        }

        return $this->render('ClabMultisiteBundle:Multisite:writeReview.html.twig', array(
            'site' => $this->site,
            'restaurant' => $this->restaurant,
            'orderUrl' => $this->orderUrl,
            'form' => $form->createView(),
        ));
    }

    public function contactAction(Request $request)
    {
        $this->init();

        if (!$this->getSite()->getSectionContact()) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm(new ContactType());

        if ($form->handleRequest($request)->isValid()) {
            $message = \Swift_Message::newInstance()
                ->setSubject('Byclickeat '.$this->getRestaurant().' - Prise de contact')
                ->setFrom(array('support@click-eat.fr' => 'Clickeat'))
                ->setTo($this->getRestaurant()->getEmail())
                ->setBcc('support@click-eat.fr')
                ->setBody($this->render('ClabMultisiteBundle:Mail:contact.html.twig', array('form' => $form->getData()))->getContent(), 'text/html');

            $this->get('mailer')->send($message);

            $this->addFlash('success', 'Votre message nous a bien été transmis, nous vous recontactons dès que possible.');

            return $this->redirectToRoute('multisite_contact');
        }

        return $this->render('ClabMultisiteBundle:Multisite:contact.html.twig', array(
            'site' => $this->site,
            'restaurant' => $this->restaurant,
            'orderUrl' => $this->orderUrl,
            'form' => $form->createView(),
        ));
    }

    public function sitemapAction()
    {
        $this->init();

        $urls = array();

        $urls[] = array('loc' => $this->get('router')->generate('multisite_get', array(), true), 'changefreq' => 'weekly', 'priority' => '1.0');
        if ($this->getSite()->getSectionMenu()) {
            $urls[] = array('loc' => $this->get('router')->generate('multisite_menu', array(), true), 'changefreq' => 'weekly', 'priority' => '1.0');
        }
        if ($this->getRestaurant()->isMobile()) {
            $urls[] = array('loc' => $this->get('router')->generate('multisite_planning', array(), true), 'changefreq' => 'weekly', 'priority' => '0.3');
        }
        if ($this->getSite()->getSectionGallery()) {
            $urls[] = array('loc' => $this->get('router')->generate('multisite_gallery', array(), true), 'changefreq' => 'weekly', 'priority' => '0.3');
        }
        if ($this->getSite()->getSectionSocial()) {
            $urls[] = array('loc' => $this->get('router')->generate('multisite_social', array(), true), 'changefreq' => 'weekly', 'priority' => '0.3');
        }
        if ($this->getSite()->getSectionReview()) {
            $urls[] = array('loc' => $this->get('router')->generate('multisite_reviews', array(), true), 'changefreq' => 'weekly', 'priority' => '0.3');
        }
        if ($this->getSite()->getSectionContact()) {
            $urls[] = array('loc' => $this->get('router')->generate('multisite_contact', array(), true), 'changefreq' => 'weekly', 'priority' => '0.3');
        }

        return $this->render('ClabMultisiteBundle:Multisite:sitemap.xml.twig', array('urls' => $urls));
    }
}
