<?php

namespace Clab\BoardBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\BoardBundle\Entity\Client;

use Clab\RestaurantBundle\Entity\ProductCategory;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\ProductOption;
use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\MealSlot;
use Clab\RestaurantBundle\Entity\MealChoice;

class ChainManager
{
    protected $container;
    protected $em;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function updateProductCategory($category, $restaurants = null)
    {
        if (!($category->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $category->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($category, $restaurant);

            if ($children) {
                //update
            } else {
                $this->container->get('app_admin.import_manager')->importProductCategory($restaurant, $category);
            }
        }
    }

    public function updateProduct($product, $restaurants = null)
    {
        if (!($product->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $product->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($product, $restaurant);
            $childrenCategory = $this->findChildren($product->getCategory(), $restaurant);

            if ($children) {
                if ($childrenCategory && $product->getCategory() != $childrenCategory) {
                    $children->setCategory($childrenCategory);
                } elseif ($product->getCategory() == null) {
                    $children->setCategory(null);
                }
                $this->em->flush();
            } else {
                $this->container->get('app_admin.import_manager')->importProducts($restaurant, array($product), $childrenCategory);

                $this->container->get('app_admin.chain_manager')->updateOptionsImport($product, $product->getOptions(), array($restaurant));
            }
        }
    }

    public function updateProductOption($option, $restaurants = null)
    {
        if (!($option->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $option->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($option, $restaurant);

            if ($children) {
            } else {
                $this->container->get('app_admin.import_manager')->importOptions($restaurant, array($option));

                foreach ($option->getChoices() as $choice) {
                    $this->container->get('app_admin.chain_manager')->updateChoicesImport($option, array($choice->getParent()));
                }
            }
        }
    }

    public function updateOptionsImport($product, $options, $restaurants = null)
    {
        if (!($product->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $product->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($product, $restaurant);

            if ($children) {
                foreach ($options as $option) {
                    $childrenOption = $this->findChildren($option, $restaurant);

                    //@todo create option if not exists
                    if ($childrenOption) {
                        $this->container->get('app_admin.import_manager')->importOptions($children, array($childrenOption));
                    }
                }
            }
        }
    }

    public function updateOptionsRemove($product, $options, $restaurants = null)
    {
        if (!($product->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $product->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($product, $restaurant);

            if ($children) {
                foreach ($options as $option) {
                    $childrenOption = $this->findChildren($option, $restaurant);

                    if ($childrenOption) {
                        $childrenOption->removeProduct($children);
                    }
                }
            }
        }
    }

    public function updateChoicesImport($option, $choices, $restaurants = null)
    {
        if (!($option->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $option->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($option, $restaurant);

            if ($children) {
                foreach ($choices as $choice) {
                    $childrenChoice = $this->findChildren($choice, $restaurant);

                    //@todo create option if not exists
                    if ($childrenChoice) {
                        $this->container->get('app_admin.import_manager')->importChoices($children, array($childrenChoice));
                    }
                }
            }
        }
    }

    public function updateChoice($choice, $restaurants = null)
    {
        if (!($choice->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $choice->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($choice, $restaurant);

            if (!$children) {
                $newChoice = new OptionChoice();
                $newChoice->setValue($choice->getValue());
                $newChoice->setPrice($choice->getPrice());
                $newChoice->setParent($choice);
                $newChoice->setProxy($restaurant);
                $this->em->persist($newChoice);

                $this->em->flush();
            }
        }
    }

    public function updateMeal($meal, $restaurants = null)
    {
        if (!($meal->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $meal->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($meal, $restaurant);

            if ($children) {
                foreach ($meal->getSlots() as $slot) {
                    foreach ($children->getSlots() as $childrenSlot) {
                        if ($childrenSlot->getParent()->getParent() == $slot->getParent()) {
                            $childrenSlot->setPosition($slot->getPosition());
                        }
                    }
                }
            } else {
                $this->container->get('app_admin.import_manager')->importMeals($restaurant, array($meal));

                $this->container->get('app_admin.chain_manager')->updateMealSlotImport($meal, $meal->getSlots(), array($restaurant));
            }
        }
    }

    public function updateMealSlot($slot, $restaurants = null)
    {
        if (!($slot->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $slot->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($slot, $restaurant);

            if ($children) {
            } else {
                $this->container->get('app_admin.import_manager')->importSlots($restaurant, array($slot));

                $this->container->get('app_admin.chain_manager')->updateSlotCategories($slot);
            }
        }
    }

    public function updateMealSlotChildrenPrice($slot, $restaurants = null)
    {
        if (!($slot->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $slot->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($slot, $restaurant);

            if ($children) {
                $disabledProducts = array();
                $customPrices = array();

                foreach ($slot->getDisabledProducts() as $productId) {
                    $product = $this->em->getRepository('ClabRestaurantBundle:Product')->find($productId);
                    $childrenProduct = $this->findChildren($product, $restaurant);
                    $disabledProducts[] = $childrenProduct->getId();
                }
                $children->setDisabledProducts($disabledProducts);

                foreach ($slot->getCustomPrices() as $productId => $price) {
                    $product = $this->em->getRepository('ClabRestaurantBundle:Product')->find($productId);
                    $childrenProduct = $this->findChildren($product, $restaurant);
                    $customPrices[$childrenProduct->getId()] = $price;
                }
                $children->setCustomPrices($customPrices);

                $this->em->flush();
            }
        }
    }

    public function updateSlotCategories($slot, $restaurants = null)
    {
        if (!($slot->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $slot->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($slot, $restaurant);


            $children->resetProductCategories();
            foreach ($slot->getProductCategories() as $category) {
                $childrenCategory = $this->findChildren($category, $restaurant);

                if ($childrenCategory) {
                    $children->addProductCategory($childrenCategory);
                }
            }
        }

        $this->em->flush();
    }

    public function addProductToSlot($slot, $product)
    {
        $choice = new MealChoice();
        $choice->setProduct($product);
        $choice->setMealSlot($slot);
        $this->em->persist($choice);

        if ($slot->getProxy() && $slot->getProxy() instanceof Client) {
            foreach ($slot->getChildrens() as $children) {
                if ($children->getProxy() && $children->getProxy() instanceof Restaurant) {
                    $childrenProduct = $this->findChildren($product, $children->getProxy());

                    if ($childrenProduct) {
                        $newChoice = new MealChoice();
                        $newChoice->setMealSlot($children);
                        $newChoice->setParent($choice);
                        $newChoice->setIsOnline(true);
                        $newChoice->setProduct($childrenProduct);
                        $this->em->persist($newChoice);

                        foreach ($children->getChildrens() as $greatChildren) {
                            $newChoice2 = new MealChoice();
                            $newChoice2->setProduct($childrenProduct);
                            $newChoice2->setMealSlot($greatChildren);
                            $newChoice2->setParent($newChoice);
                            $newChoice2->setIsOnline(true);
                            $this->em->persist($newChoice2);
                        }
                    }
                } else {
                    $newChoice = new MealChoice();
                    $newChoice->setMealSlot($children);
                    $newChoice->setParent($choice);
                    $newChoice->setIsOnline(true);
                    $newChoice->setProduct($product);
                    $this->em->persist($newChoice);
                }
            }
        } else {
            foreach ($slot->getChildrens() as $children) {
                $newChoice = new MealChoice();
                $newChoice->setMealSlot($children);
                $newChoice->setParent($choice);
                $newChoice->setIsOnline(true);
                $newChoice->setProduct($product);
                $this->em->persist($newChoice);
            }
        }

        $this->em->flush();
    }

    public function updateMealSlotImport($meal, $slots, $restaurants = null)
    {
        if (!($meal->getProxy() instanceof Client)) {
            return;
        }

        $push = $restaurants ? $restaurants : $meal->getProxy()->getRestaurants();
        foreach ($push as $restaurant) {
            $children = $this->findChildren($meal, $restaurant);

            if ($children) {
                foreach ($slots as $slot) {
                    $childrenSlot = $this->findChildren($slot, $restaurant);
                    $this->container->get('app_admin.import_manager')->importSlots($children, array($childrenSlot));
                }
            }
        }
    }

    public function findChildren($entity, $proxy)
    {
        if (!$entity || !$proxy) {
            return;
        }

        foreach ($entity->getChildrens() as $children) {
            if ($children->getProxy() == $proxy && !$children->isDeleted()) {
                return $children;
            }
        }

        return null;
    }

    public function findChildrenChoice($choice, $option)
    {
        if (!$choice || !$option) {
            return;
        }

        foreach ($option->getChoices() as $childrenChoice) {
            if ($childrenChoice->getParent() == $choice) {
                return $childrenChoice;
            }
        }

        return null;
    }
}
