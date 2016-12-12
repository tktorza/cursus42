<?php

namespace Clab\BoardBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;

use Clab\RestaurantBundle\Entity\ProductCategory;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\ProductOption;
use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\MealSlot;
use Clab\RestaurantBundle\Entity\MealChoice;

class ImportManager
{
    protected $container;
    protected $em;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;
    }

    public function importProductCategory($proxy, $category)
    {
        $childrenCategory = new ProductCategory();
        $childrenCategory->setParent($category);
        $childrenCategory->setProxy($proxy);
        $childrenCategory->setName($category->getName());
        $childrenCategory->setDescription($category->getDescription());
        //$childrenCategory->setGallery($category->getGallery());
        $childrenCategory->setPositionCategory($category->getPositionCategory());
        $childrenCategory->setPosition($category->getPosition());

        $this->em->persist($childrenCategory);
        $this->em->flush();
    }

    public function importProducts($proxy, $products, $category = null)
    {
        foreach ($products as $product) {
            $newProduct = new Product();
            $newProduct->setParent($product);
            $product->addChildren($newProduct);
            $newProduct->setName($product->getName());
            $newProduct->setDescription($product->getDescription());
            $newProduct->setPrice($product->getPrice());
            $newProduct->setProxy($proxy);
            $newProduct->setTax($product->getTax());

            if ($category) {
                $newProduct->setCategory($category);
                $category->addProduct($newProduct);
            }

            $this->em->persist($newProduct);
        }

        $this->em->flush();
    }

    public function importOptions($proxy, $options)
    {
        foreach ($options as $option) {
            if ($proxy instanceof Product) {
                $option->addProduct($proxy);
            } else {
                $newOption = new ProductOption();
                $newOption->setName($option->getName());
                $newOption->setRequired($option->getRequired());
                $newOption->setMultiple($option->getMultiple());
                $newOption->setMinimum($option->getMinimum());
                $newOption->setMaximum($option->getMaximum());
                $newOption->setPosition($option->getPosition());
                $newOption->setParent($option);
                $newOption->setProxy($proxy);
                $this->em->persist($newOption);
            }
        }

        $this->em->flush();
    }

    public function importChoices($option, $choices)
    {
        foreach ($choices as $choice) {
            $newChoice = new OptionChoice();
            $newChoice->setParent($choice);
            $newChoice->setValue($choice->getValue());
            $newChoice->setPrice($choice->getPrice());
            $newChoice->setOption($option);
            $option->addChoice($newChoice);
            $this->em->persist($newChoice);
        }

        $this->em->flush();
    }

    public function importMeals($proxy, $meals)
    {
        foreach ($meals as $meal) {
            $newMeal = new Meal();
            $newMeal->setParent($meal);
            $meal->addChildren($newMeal);
            $newMeal->setProxy($proxy);
            $newMeal->setName($meal->getName());
            $newMeal->setDescription($meal->getDescription());
            $newMeal->setPrice($meal->getPrice());
            $newMeal->setTax($meal->getTax());

            $this->em->persist($newMeal);
        }

        $this->em->flush();
    }

    public function importSlots($proxy, $slots)
    {
        foreach ($slots as $slot) {
            if ($proxy instanceof Meal) {
                $slot->addMeal($proxy);
            } else {
                $newSlot = new MealSlot();
                $newSlot->setName($slot->getName());
                $newSlot->setParent($slot);
                $newSlot->setProxy($proxy);
                $this->em->persist($newSlot);
            }
        }

        $this->em->flush();

        return isset($newSlot) ? $newSlot : null;
    }
}
