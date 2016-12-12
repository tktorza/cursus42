<?php

namespace Clab\BoardBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContextManager
{
    protected $container;
    protected $em;
    protected $request;

    protected $proxy;

    protected $productRepository;
    protected $categoryRepository;

    protected $fetchParams;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em        = $em;
        $this->request   = $this->container->get('request');

        $this->productRepository  = $this->em->getRepository('ClabRestaurantBundle:Product');
        $this->categoryRepository = $this->em->getRepository('ClabRestaurantBundle:ProductCategory');
    }

    public function getParams($context, $contextPk)
    {
        $params = array(
            'context' => $context,
            'contextPk' => $contextPk
        );

        if ($context == 'restaurant') {
            $proxy                = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array(
                'slug' => $contextPk
            ));
            $params['restaurant'] = $proxy;
            $this->fetchParams    = array(
                'restaurant' => $proxy
            );
        } elseif ($context == 'client') {
            $proxy             = $this->em->getRepository('ClabBoardBundle:Client')->findOneBy(array(
                'slug' => $contextPk
            ));
            $params['client']  = $proxy;
            $this->fetchParams = array(
                'client' => $proxy,
                'restaurant' => null
            );
        } else {
            throw $this->create404();
        }

        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        if (!$proxy || !$proxy->hasManager($user)) {
            throw $this->create404();
        }

        $this->proxy = $proxy;

        $session = $this->request->getSession();

        $session->set('admin_' . $context, $proxy->getSlug());

        return array(
            $proxy,
            $params
        );
    }

    public function getProductOr404($slug)
    {
        $params               = $this->fetchParams;
        $params['slug']       = $slug;
        $params['is_deleted'] = 0;

        $product = $this->productRepository->findOneBy($params);

        if (!$product) {
            throw $this->create404();
        } else {
            return $product;
        }
    }

    public function getProductsByCategory($category)
    {
        $params             = $this->fetchParams;
        $params['category'] = $category;

        $products = $this->productRepository->findBy($params);

        return $products;
    }

    public function getProductCategoryOr404($slug)
    {
        $params         = $this->fetchParams;
        $params['slug'] = $slug;

        $category = $this->categoryRepository->findOneBy($params);

        if (!$category) {
            throw $this->create404();
        } else {
            return $category;
        }
    }

    public function getProductCategories()
    {
        $params         = $this->fetchParams;

        $categories = $this->categoryRepository->findBy($params);

        return $categories;
    }

    public function getProductOptionOr404($params)
    {
        $option = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findOneBy($params);

        if (!$option) {
            throw $this->create404();
        } else {
            return $option;
        }
    }

    public function getOptionChoiceOr404($id)
    {
        $params['id'] = $id;

        $choice = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->findOneBy($params);

        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        if (!$choice || !$choice->isAllowed($user)) {
            throw $this->create404();
        } else {
            return $choice;
        }
    }

    public function getUserOr404($id)
    {
        $user = $this->em->getRepository('ClabUserBundle:User')
            ->find($id);

        if(!$user || !$this->proxy->hasManager($user)) {
            throw $this->create404();
        } else {
            return $user;
        }
    }

    public function getDiscounts()
    {
        $params         = $this->fetchParams;
        $params['is_deleted'] = 0;

        $discounts = $this->em->getRepository('ClabShopBundle:Discount')->findBy($params);

        return $discounts;
    }

    public function getDiscountOr404($slug)
    {
        $params         = $this->fetchParams;
        $params['slug'] = $slug;
        $params['is_deleted'] = 0;

        $discount = $this->em->getRepository('ClabShopBundle:Discount')->findOneBy($params);

        if (!$discount) {
            throw $this->create404();
        } else {
            return $discount;
        }
    }

    public function getDiscountConditionOr404($id)
    {
        $params         = array();
        $params['id'] = $id;

        $condition = $this->em->getRepository('ClabShopBundle:DiscountCondition')->findOneBy($params);

        if (!$condition || $condition->getDiscount()->getRestaurant() !== $this->proxy) {
            throw $this->create404();
        } else {
            return $condition;
        }
    }

    public function getDiscountResultOr404($id)
    {
        $params         = array();
        $params['id'] = $id;

        $result = $this->em->getRepository('ClabShopBundle:DiscountResult')->findOneBy($params);

        if (!$result || $result->getDiscount()->getRestaurant() !== $this->proxy) {
            throw $this->create404();
        } else {
            return $result;
        }
    }

    public function getCompanyOr404($slug)
    {
        $result = $this->em->getRepository('ClabPeopleBundle:Company')->findOneBy(array('is_online' => true, 'slug' => $slug));

        if(!$result) {
            throw $this->create404();
        } else {
            return $result;
        }
    }

    public function getMeals()
    {
        $params         = $this->fetchParams;
        $params['is_deleted'] = 0;

        $meals = $this->em->getRepository('ClabRestaurantBundle:Meal')->findBy($params);

        return $meals;
    }

    public function create404()
    {
        return new NotFoundHttpException('Not found');
    }
}
