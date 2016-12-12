<?php
namespace Clab\BoardBundle\Service;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Router;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\ProductCategory;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\ProductOption;
use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\MealSlot;
class CatalogManager
{
    protected $em;
    protected $container;
    protected $router;
    public function __construct(ContainerInterface $container, EntityManager $em, Router $router)
    {
        $this->container = $container;
        $this->em = $em;
        $this->router = $router;
    }
    public function findChildren($entity, $proxy)
    {
        if (!$entity || !$proxy) {
            return false;
        }
        foreach ($entity->getChildrens() as $children) {
            if ($children->getProxy() == $proxy && !$children->isDeleted()) {
                return $children;
            }
        }
        return;
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
                $childrenCategory = new ProductCategory();
                $childrenCategory->setParent($category);
                $childrenCategory->setProxy($restaurant);
                $childrenCategory->setName($category->getName());
                $childrenCategory->setDescription($category->getDescription());
                $childrenCategory->setPositionCategory($category->getPositionCategory());
                $childrenCategory->setPosition($category->getPosition());
                $this->em->persist($childrenCategory);
            }
        }
        $this->em->flush();
    }
    public function createFromExcel($context, $restaurant, $objPHPExcel)
    {
        if ($context == 'client') {
            $restaurants = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findBy(array('client' => $restaurant));
            $client = $restaurant;
            //Cart chaine
            set_time_limit(0);
            $catalog = array();
            // categories
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $categories = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name
                if (!empty($data[0])) {
                    $categories[] = $data;
                }
            }
            $catalog['categories'] = $categories;
            // choices
            $sheet = $objPHPExcel->getSheet(1);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $choices = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name
                if (!empty($data[0])) {
                    $choices[] = $data;
                }
            }
            $catalog['choices'] = $choices;
            // options
            $sheet = $objPHPExcel->getSheet(2);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $options = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name
                if (!empty($data[0])) {
                    $options[] = $data;
                }
            }
            $catalog['options'] = $options;
            // produits
            $sheet = $objPHPExcel->getSheet(3);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $products = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name, price and tax
                if (!empty($data[0]) && isset($data[3]) && isset($data[5])) {
                    $products[] = $data;
                }
            }
            $catalog['products'] = $products;
            // slots
            $sheet = $objPHPExcel->getSheet(4);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $slots = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name
                if (!empty($data[0])) {
                    $slots[] = $data;
                }
            }
            $catalog['slots'] = $slots;
            // meals
            $sheet = $objPHPExcel->getSheet(5);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $meals = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name, price and tax
                if (!empty($data[0]) && isset($data[2]) && isset($data[4])) {
                    $meals[] = $data;
                }
            }
            $catalog['meals'] = $meals;
            $references = array('categories' => array(), 'products' => array(), 'taxes' => array(), 'choices' => array(), 'options' => array(), 'slots' => array(), 'meals' => array());
            $taxes = $this->em->getRepository('ClabRestaurantBundle:Tax')->findBy(array('is_online' => true));
            foreach ($taxes as $tax) {
                $references['taxes'][$tax->getValue()] = $tax;
            }
            $tags = array('Entrées' => 'Entrées', 'Plats' => 'Plats', 'Accompagnements' => 'Accompagnements', 'Desserts' => 'Desserts', 'Boissons' => 'Boissons', 'Autre' => 'Autre');
            $groups = array("Matsuri à la carte" => "Matsuri à la carte", "Plateaux assortis" => "Plateaux assortis", "Spécialités et plats chauds" => "Spécialités et plats chauds", "Formules déjeuner" => "Formules déjeuner", "Boissons et accompagnements" => "Boissons et accompagnements");

            foreach ($catalog['categories'] as $rawCategory) {
                $category = new ProductCategory();
                $category->setName($rawCategory[0]);
                $category->setDescription($rawCategory[1]);
                if (isset($tags[$rawCategory[2]])) {
                    $category->setType($rawCategory[2]);
                }
                if (isset($groups[$rawCategory[3]])) {
                    $category->setCategoryGroup($rawCategory[3]);
                }
                $category->setClient($client);
                $this->em->persist($category);
                $references['categories'][$category->getName()] = $category;
            }
            $this->em->flush();
            foreach ($catalog['choices'] as $rawChoice) {
                $choice = new OptionChoice();
                $choice->setValue($rawChoice[0]);
                $choice->setClient($restaurant);
                $choice->setIsOnline(true);
                $this->em->persist($choice);
                $references['choices'][$choice->getValue()] = $choice;
            }
            foreach ($catalog['options'] as $rawOption) {
                $option = new ProductOption();
                $option->setName($rawOption[0]);
                if (!empty($rawOption[1])) {
                    $option->setRequired(true);
                }
                if (!empty($rawOption[2])) {
                    $option->setMultiple(true);
                }
                if (!empty($rawOption[3])) {
                    $option->setMinimum($rawOption[3]);
                }
                if (!empty($rawOption[4])) {
                    $option->setMaximum($rawOption[4]);
                }
                $option->setClient($client);
                $this->em->persist($option);
                $references['options'][$option->getName()] = $option;
            }
            foreach ($catalog['products'] as $rawProduct) {
                $menuClassic = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->getDefaultMenuForChainStore($client);
                $menuDelivery = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')
                    ->getDeliveryMenuForChainStore($client);

                $product = new Product();

                $product->setName($rawProduct[0]);
                $product->setDescription($rawProduct[1]);

                $category = $references['categories'][$rawProduct[2]];

                if(!is_null($category)) {
                    $product->setCategory($category);
                    $category->addProduct($product);
                    $this->em->persist($category);
                }

                $product->setExtraMakingTime(0);
                $product->addRestaurantMenu($menuDelivery);
                $product->addRestaurantMenu($menuClassic);

                $product->setPrice($rawProduct[3]);

                if (isset($references['taxes'][$rawProduct[4]])) {
                    $product->setTax($references['taxes'][$rawProduct[4]]);
                }

                $product->setDeliveryPrice($rawProduct[5]);

                if (isset($references['taxes'][$rawProduct[6]])) {
                    $product->setTaxDelivery($references['taxes'][$rawProduct[6]]);
                }

                $product->setPriceOnSite($rawProduct[7]);

                if (isset($references['taxes'][$rawProduct[8]])) {
                    $product->setTaxOnSite($references['taxes'][$rawProduct[8]]);
                }

                $extraFields = array(
                    "allergies" => $rawProduct[10],
                    "nutrition" => $rawProduct[9],
                    "regime" => $rawProduct[13],
                    "nbpieces"=>$rawProduct[12],
                    "calories" => $rawProduct[11] );

                $product->setExtraFields($extraFields);

                $this->em->persist($product);
                $this->em->persist($category);
                $references['products'][$product->getName()] = $product;
                $this->em->flush();
            }
            foreach ($catalog['slots'] as $rawSlot) {
                $slot = new MealSlot();
                $slot->setName($rawSlot[0]);
                $slot->setClient($client);
                $this->em->persist($slot);
                $references['slots'][$slot->getName()] = $slot;
            }
            foreach ($catalog['meals'] as $rawMeal) {
                $menuClassic = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                    'chainStore' => $client,
                    'type' => 100,
                ));
                $menuDelivery = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                    'chainStore' => $client,
                    'type' => 200,
                ));
                $meal = new Meal();
                $meal->setName($rawMeal[0]);
                $meal->setDescription($rawMeal[1]);
                $meal->setPrice($rawMeal[2]);
                if ($rawMeal[3] == 1) {
                    $meal->addRestaurantMenu($menuDelivery);
                }
                if ($rawMeal[4] == 1) {
                    $meal->addRestaurantMenu($menuClassic);
                }
                if (isset($references['taxes'][$rawMeal[5]])) {
                    $tax = $this->em->getRepository('ClabRestaurantBundle:Tax')->findBy(array(
                        'value' => $rawMeal[5],
                    ));
                    if (!empty($tax)) {
                        $meal->setTax($tax[0]);
                    }
                }
                $this->em->persist($meal);
                $references['meals'][$meal->getName()] = $meal;
            }
            $this->em->flush();
            //Fin carte chaine
            /* foreach ($restaurants as $res) {
                 set_time_limit(0);
                 foreach ($catalog['categories'] as $rawCategory) {
                     $category = new ProductCategory();
                     $category->setName($rawCategory[0]);
                     $category->setDescription($rawCategory[1]);
                     if (in_array($rawCategory[2], $tags)) {
                         $category->setType($rawCategory[2]);
                     }
                     $category->setRestaurant($res);
                     $category->setParent($references['categories'][$category->getName()]);
                     $this->em->persist($category);
                 }
                 $this->em->flush();
                 foreach ($catalog['choices'] as $rawChoice) {
                     $choice = new OptionChoice();
                     $choice->setValue($rawChoice[0]);
                     $choice->setRestaurant($res);
                     $choice->setIsOnline(true);
                     $choice->setParent($references['choices'][$rawChoice[0]]);
                     $this->em->persist($choice);
                 }
                 foreach ($catalog['options'] as $rawOption) {
                     $option = new ProductOption();
                     $option->setName($rawOption[0]);
                     if (!empty($rawOption[1])) {
                         $option->setRequired(true);
                     }
                     if (!empty($rawOption[2])) {
                         $option->setMultiple(true);
                     }
                     if (!empty($rawOption[3])) {
                         $option->setMinimum($rawOption[3]);
                     }
                     if (!empty($rawOption[4])) {
                         $option->setMaximum($rawOption[4]);
                     }
                     $option->setRestaurant($res);
                     $option->setParent($references['options'][$rawOption[0]]);
                     $this->em->persist($option);
                 }
                 foreach ($catalog['products'] as $rawProduct) {
                     $menuClassic = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                         'restaurant' => $res,
                         'type' => 100,
                     ));
                     $menuDelivery = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                         'restaurant' => $res,
                         'type' => 200,
                     ));
                     $product = new Product();
                     $product->setName($rawProduct[0]);
                     $product->setDescription($rawProduct[1]);
                     $product->setPrice($rawProduct[3]);
                     $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array(
                         'restaurant' => $res,
                         'name' => $rawProduct[2],
                     ));
                     $product->setCategory($category);
                     $category->addProduct($product);
                     $product->setExtraMakingTime(0);
                     if ($rawProduct[4] == 1) {
                         $product->addRestaurantMenu($menuDelivery);
                     }
                     if ($rawProduct[5] == 1) {
                         $product->addRestaurantMenu($menuClassic);
                     }
                     if (isset($references['taxes'][$rawProduct[6]])) {
                         $product->setTax($references['taxes'][$rawProduct[6]]);
                     }
                     $product->setParent($references['products'][$rawProduct[0]]);
                     $this->em->persist($product);
                     $this->em->persist($category);
                     $this->em->flush();
                 }
                 foreach ($catalog['slots'] as $rawSlot) {
                     $slot = new MealSlot();
                     $slot->setName($rawSlot[0]);
                     $slot->setRestaurant($res);
                     $slot->setParent($references['slots'][$slot->getName()]);
                     $this->em->persist($slot);
                 }
                 foreach ($catalog['meals'] as $rawMeal) {
                     $menuClassic = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                         'restaurant' => $res,
                         'type' => 100,
                     ));
                     $menuDelivery = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                         'restaurant' => $res,
                         'type' => 200,
                     ));
                     $meal = new Meal();
                     $meal->setName($rawMeal[0]);
                     $meal->setDescription($rawMeal[1]);
                     $meal->setPrice($rawMeal[2]);
                     $meal->setParent($references['meals'][$meal->getName()]);
                     if ($rawMeal[3] == 1) {
                         $meal->addRestaurantMenu($menuDelivery);
                     }
                     if ($rawMeal[4] == 1) {
                         $meal->addRestaurantMenu($menuClassic);
                     }
                     if (isset($references['taxes'][$rawMeal[5]])) {
                         $tax = $this->em->getRepository('ClabRestaurantBundle:Tax')->findBy(array(
                             'value' => $rawMeal[5],
                         ));
                         if (!empty($tax)) {
                             $meal->setTax($tax[0]);
                         }
                     }
                     $this->em->persist($meal);
                 }
                 $this->em->flush();
             }*/
            return $catalog;
        } else {
            set_time_limit(0);
            $catalog = array();
            // categories
            $sheet = $objPHPExcel->getSheet(0);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $categories = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name
                if (!empty($data[0])) {
                    $categories[] = $data;
                }
            }
            $catalog['categories'] = $categories;
            // choices
            $sheet = $objPHPExcel->getSheet(1);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $choices = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name
                if (!empty($data[0])) {
                    $choices[] = $data;
                }
            }
            $catalog['choices'] = $choices;
            // options
            $sheet = $objPHPExcel->getSheet(2);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $options = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name
                if (!empty($data[0])) {
                    $options[] = $data;
                }
            }
            $catalog['options'] = $options;
            // produits
            $sheet = $objPHPExcel->getSheet(3);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $products = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name, price and tax
                if (!empty($data[0]) && isset($data[3]) && isset($data[5])) {
                    $products[] = $data;
                }
            }
            $catalog['products'] = $products;
            // slots
            $sheet = $objPHPExcel->getSheet(4);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $slots = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name
                if (!empty($data[0])) {
                    $slots[] = $data;
                }
            }
            $catalog['slots'] = $slots;
            // meals
            $sheet = $objPHPExcel->getSheet(5);
            $highestRow = $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();
            $meals = array();
            for ($row = 6; $row <= $highestRow; ++$row) {
                $data = $sheet->rangeToArray('B'.$row.':'.$highestColumn.$row, null, true, false)[0];
                // need name, price and tax
                if (!empty($data[0]) && isset($data[2]) && isset($data[4])) {
                    $meals[] = $data;
                }
            }
            $catalog['meals'] = $meals;
            $references = array('categories' => array(), 'products' => array(), 'taxes' => array(), 'choices' => array(), 'options' => array(), 'slots' => array(), 'meals' => array());
            $taxes = $this->em->getRepository('ClabRestaurantBundle:Tax')->findBy(array('is_online' => true));
            foreach ($taxes as $tax) {
                $references['taxes'][$tax->getValue()] = $tax;
            }
            $tags = array('Entrées' => 'Entrées', 'Plats' => 'Plats', 'Accompagnements' => 'Accompagnements', 'Desserts' => 'Desserts', 'Boissons' => 'Boissons', 'Autre' => 'Autre');
            $groups = array("Matsuri à la carte" => "Matsuri à la carte", "Plateaux assortis" => "Plateaux assortis", "Spécialités et plats chauds" => "Spécialités et plats chauds", "Formules déjeuner" => "Formules déjeuner", "Boissons et accompagnements" => "Boissons et accompagnements");

            foreach ($catalog['categories'] as $rawCategory) {
                $category = new ProductCategory();
                $category->setName($rawCategory[0]);
                $category->setDescription($rawCategory[1]);
                if (isset($tags[$rawCategory[2]])) {
                    $category->setType($rawCategory[2]);
                }
                if (isset($groups[$rawCategory[3]])) {
                    $category->setCategoryGroup($rawCategory[3]);
                }
                $category->setRestaurant($restaurant);
                $this->em->persist($category);
                $references['categories'][$rawCategory[0]] = $category;
            }
            $this->em->flush();
            foreach ($catalog['choices'] as $rawChoice) {
                $choice = new OptionChoice();
                $choice->setValue($rawChoice[0]);
                $choice->setRestaurant($restaurant);
                $choice->setIsOnline(true);
                $this->em->persist($choice);
                $references['choices'][$choice->getValue()] = $choice;
            }
            foreach ($catalog['options'] as $rawOption) {
                $option = new ProductOption();
                $option->setName($rawOption[0]);
                if (!empty($rawOption[1])) {
                    $option->setRequired(true);
                }
                if (!empty($rawOption[2])) {
                    $option->setMultiple(true);
                }
                if (!empty($rawOption[3])) {
                    $option->setMinimum($rawOption[3]);
                }
                if (!empty($rawOption[4])) {
                    $option->setMaximum($rawOption[4]);
                }
                $option->setRestaurant($restaurant);
                $this->em->persist($option);
                $references['options'][$option->getName()] = $option;
            }
            foreach ($catalog['products'] as $rawProduct) {
                $menuClassic = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                    'restaurant' => $restaurant,
                    'type' => 100,
                ));
                $menuDelivery = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                    'restaurant' => $restaurant,
                    'type' => 200,
                ));
                $product = new Product();
                $product->setName($rawProduct[0]);
                $product->setDescription($rawProduct[1]);

                $category = $references['categories'][$rawProduct[2]];
                if(!is_null($category)) {
                    $product->setCategory($category);
                    $category->addProduct($product);
                    $this->em->persist($category);
                }

                $product->setExtraMakingTime(0);
                $product->addRestaurantMenu($menuDelivery);
                $product->addRestaurantMenu($menuClassic);

                $product->setPrice($rawProduct[3]);

                if (isset($references['taxes'][$rawProduct[4]])) {
                    $product->setTax($references['taxes'][$rawProduct[4]]);
                }

                $product->setDeliveryPrice($rawProduct[5]);

                if (isset($references['taxes'][$rawProduct[6]])) {
                    $product->setTaxDelivery($references['taxes'][$rawProduct[6]]);
                }

                $product->setPriceOnSite($rawProduct[7]);

                if (isset($references['taxes'][$rawProduct[8]])) {
                    $product->setTaxOnSite($references['taxes'][$rawProduct[8]]);
                }

                $extraFields = array(
                    "allergies" => $rawProduct[10],
                    "nutrition" => $rawProduct[9],
                    "regime" => $rawProduct[13],
                    "nbpieces"=>$rawProduct[12],
                    "calories" => $rawProduct[11] );

                $product->setExtraFields($extraFields);

                $this->em->persist($product);
                $references['products'][$product->getName()] = $product;
            }
            foreach ($catalog['slots'] as $rawSlot) {
                $slot = new MealSlot();
                $slot->setName($rawSlot[0]);
                $slot->setRestaurant($restaurant);
                $this->em->persist($slot);
                $references['slots'][$slot->getName()] = $slot;
            }
            foreach ($catalog['meals'] as $rawMeal) {
                $menuClassic = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                    'restaurant' => $restaurant,
                    'type' => 100,
                ));
                $menuDelivery = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array(
                    'restaurant' => $restaurant,
                    'type' => 200,
                ));
                $meal = new Meal();
                $meal->setName($rawMeal[0]);
                $meal->setDescription($rawMeal[1]);
                $meal->setPrice($rawMeal[2]);
                if ($rawMeal[3] == 1) {
                    $meal->addRestaurantMenu($menuDelivery);
                }
                if ($rawMeal[4] == 1) {
                    $meal->addRestaurantMenu($menuClassic);
                }
                if (isset($references['taxes'][$rawMeal[5]])) {
                    $tax = $this->em->getRepository('ClabRestaurantBundle:Tax')->findBy(array(
                        'value' => $rawMeal[5],
                    ));
                    if (!empty($tax)) {
                        $meal->setTax($tax[0]);
                    }
                }
                $this->em->persist($meal);
                $references['meals'][$meal->getName()] = $meal;
            }
            $this->em->flush();
            return $catalog;
        }
    }
}