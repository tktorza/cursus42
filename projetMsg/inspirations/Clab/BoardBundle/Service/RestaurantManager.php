<?php
/**
 * Created by PhpStorm.
 * User: lfbarreto
 * Date: 17/04/16
 * Time: 19:33.
 */

namespace Clab\BoardBundle\Service;

use Clab\BoardBundle\Entity\Client;
use Clab\LocationBundle\Entity\Address;
use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\MealSlot;
use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\ProductCategory;
use Clab\RestaurantBundle\Entity\ProductOption;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Clab\RestaurantBundle\Entity\TimeSheet;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Clab\RestaurantBundle\Entity\Restaurant;
use Symfony\Component\VarDumper\VarDumper;

class RestaurantManager
{
    private $libphonenumber;
    private $em;

    public function __construct($libphonenumber, EntityManager $em)
    {
        $this->libphonenumber = $libphonenumber;
        $this->em = $em;
    }

    public function createRestaurantFromExcel($context, Client $client, \PHPExcel $objPHPExcel)
    {
        if ($context == 'client') {
            $categories = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findBy(array('client' => $client));
            $choices = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->findBy(array('client' => $client));
            $options = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findBy(array('client' => $client));
            $menuClassic = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('chainStore' => $client, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT));
            $menuDelivery = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('chainStore' => $client, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY));

            $products = $this->em->getRepository('ClabRestaurantBundle:Product')->getForChainStore($client);
            $slots = $this->em->getRepository('ClabRestaurantBundle:MealSlot')->findBy(array('client' => $client));
            $meals = $this->em->getRepository('ClabRestaurantBundle:Meal')->getForChainStore($client);

            $listRestaurant = array();
            $errors = array();
            $PHPExcel_Worksheet = $objPHPExcel->getAllSheets();
            $sheet_info = $PHPExcel_Worksheet[0]; //feuille 1 info
            $sheet_schedule = $PHPExcel_Worksheet[1]; //feuille 2 horaires

            $duplicate_lines = $this->checkDuplicate($sheet_info);
            $rows = $sheet_info->getRowDimensions();
            foreach ($rows as $key => $row) {
                if ($key < 5) {
                    continue;
                }

                if (!in_array($key, $duplicate_lines)) {
                    $restaurant = new Restaurant();
                    $address = new Address();

                    $name = $sheet_info->getCell('A'.$key)->getValue();
                    $restaurant->setName($name);

                    $street = $sheet_info->getCell('B'.$key)->getValue();
                    $city = $sheet_info->getCell('D'.$key)->getValue();
                    $zip = $sheet_info->getCell('C'.$key)->getValue();

                    $address->setStreet($street);
                    $address->setCity($city);
                    $address->setZip($zip);

                    $restaurant->setAddress($address);

                    $mail = $sheet_info->getCell('E'.$key)->getValue();

                    if (filter_var($mail, FILTER_VALIDATE_EMAIL)) {
                        //verification format mail

                        $restaurant->setEmail($mail);
                    } else {
                        $errors[$key]['phone'] = 'format email invalide ligne:'.$key;
                    }

                    $phone = $sheet_info->getCell('F'.$key)->getValue();

                    if (preg_match('#^0[1-68]([-. ]?[0-9]{2}){4}$#', $phone)) {
                        $lib_phone = $this->libphonenumber->parse($phone, 'FR');

                        $restaurant->setPhone($lib_phone);
                    } else {
                        $errors[$key]['phone'] = ' telephone invalide ligne:'.$key;
                    }

                    if (empty($errors[$key])) {
                        $r = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('name' => $restaurant->getName(), 'address' => $address));
                        if (empty($r)) {
                            $listRestaurant[$key] = $restaurant;
                            $restaurant->setClient($client);
                            $client->addRestaurant($restaurant);

                            $menuC = new RestaurantMenu();
                            $menuC->setRestaurant($restaurant);
                            $menuC->setIsOnline($menuClassic->getIsOnline());
                            $menuC->setName($menuClassic->getName());
                            $menuC->setType($menuClassic->getType());
                            $this->em->persist($menuC);

                            $menuD = new RestaurantMenu();
                            $menuD->setRestaurant($restaurant);
                            $menuD->setIsOnline($menuDelivery->getIsOnline());
                            $menuD->setName($menuDelivery->getName());
                            $menuD->setType($menuDelivery->getType());
                            $this->em->persist($menuD);

                            $this->em->persist($client);
                            $this->em->persist($restaurant);
                        } else {
                            $duplicate_lines[] = $key;
                        }
                    }
                }
            }
            $this->em->flush();
            
            $this->setTimeSheets($sheet_schedule);

            $result = array(
                'listRestaurant' => $listRestaurant,
                'duplicate_lines' => $duplicate_lines,
                'errors' => $errors,
            );
            return $result;
        }
    }

    public function checkDuplicate(\PHPExcel_Worksheet $sheet)
    {
        $data = array();
        $duplicate_lines = array();
        $rows = $sheet->getRowDimensions();
        foreach ($rows as $row) {
            $current_row_data = array(
                'name' => $sheet->getCell('A'.$row->getRowIndex())->getValue(),
                'street' => $sheet->getCell('B'.$row->getRowIndex())->getValue(),
                'city' => $sheet->getCell('C'.$row->getRowIndex())->getValue(),
                'zip' => $sheet->getCell('D'.$row->getRowIndex())->getValue(),
            );

            if (in_array($current_row_data, $data)) {
                $duplicate_lines[] = $row->getRowIndex();
            } else {
                $data[$row->getRowIndex()] = $current_row_data;
            }
        }

        return $duplicate_lines;
    }

    public function setTimeSheets($sheet_schedule)
    {
        $rows = $sheet_schedule->getRowDimensions();

        foreach ($rows as $key => $row) {
            if ($key < 5) {
                continue;
            }

            $name_sheet_schedule = $sheet_schedule->getCell('A'.$key)->getValue();
            $restaurant = $this->em->getRepository('ClabRestaurantBundle:Restaurant')->findOneBy(array('name' => $name_sheet_schedule));

            $times = explode(',', $sheet_schedule->getCell('B'.$key)->getValue()); //format 12:30-17:30,19:40-22:40
            if (!empty($restaurant)) {
                foreach ($times as $time) {
                    //recuperation des plages horaires

                    $time_sheet = new TimeSheet();
                    if (isset($time) || $time !== '') {
                        $t = explode('-', $time);
                        $start = $t[0];
                        if (array_key_exists(1, $t)) {
                            $end = $t[1];
                            $time_sheet->setStart(date_create_from_format('G:i', $start));
                            $time_sheet->setEnd(date_create_from_format('G:i', $end));

                            $d = strtoupper($sheet_schedule->getCell('C'.$key)->getValue()); // format lundi,mardi,jeudi,vendredi

                            $days = explode(',', $d);

                            $formated_days = array();

                            if (!in_array('LUNDI', $days)) {
                                $time_sheet->setMonday(false);
                            } else {
                                $formated_days[] = 'MONDAY';
                            }

                            if (!in_array('MARDI', $days)) {
                                $time_sheet->setTuesday(false);
                            } else {
                                $formated_days[] = 'TUESDAY';
                            }

                            if (!in_array('MERCREDI', $days)) {
                                $time_sheet->setWednesday(false);
                            } else {
                                $formated_days[] = 'WEDNESDAY';
                            }

                            if (!in_array('JEUDI', $days)) {
                                $time_sheet->setThursday(false);
                            } else {
                                $formated_days[] = 'THURSDAY';
                            }

                            if (!in_array('VENDREDI', $days)) {
                                $time_sheet->setFriday(false);
                            } else {
                                $formated_days[] = 'FRIDAY';
                            }

                            if (!in_array('SAMEDI', $days)) {
                                $time_sheet->setSaturday(false);
                            } else {
                                $formated_days[] = 'SATURDAY';
                            }

                            if (!in_array('DIMANCHE', $days)) {
                                $time_sheet->setSunday(false);
                            } else {
                                $formated_days[] = 'SUNDAY';
                            }
                            $time_sheet->setDays($formated_days);
                            $time_sheet->setRestaurant($restaurant);
                            $time_sheet->setType(TimeSheet::TIMESHEET_TYPE_CLASSIC);

                            $restaurant->addTimesheet($time_sheet);

                            $this->em->persist($time_sheet);
                            $this->em->flush();
                        }
                    }
                }
            }
        }
    }

    public function setCatalog(Restaurant $res, $categories, $choices, $options, RestaurantMenu $menuClassic, RestaurantMenu $menuDelivery, $products, $slots, $meals)
    {
        set_time_limit(-1);

        $this->setCategories($res, $categories);
        $this->setChoices($res, $choices);
        $this->setProducts($res, $menuClassic, $menuDelivery, $products);
        $this->setOptions($res, $options);
        $this->setslots($res, $slots);
        $this->setMeals($res, $menuClassic, $menuDelivery, $meals);
    }

    public function setCategories(Restaurant $res, $categories)
    {
        foreach ($categories as $parentCategory) {
            $category = new ProductCategory();
            $category->setName($parentCategory->getName());
            $category->setDescription($parentCategory->getDescription());
            $category->setParent($parentCategory);
            $category->setType($parentCategory->getType());
            $category->setRestaurant($res);
            $this->em->persist($res);
            $this->em->persist($category);
        }
        $this->em->flush();
    }

    public function synchroCategories(Restaurant $res, $categories){
        foreach ($categories as $parentCategory) {
            $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array('restaurant'=>$res,'parent'=>$parentCategory));
            if(is_null($category)) {
                $category = new ProductCategory();
            }
            $category->setName($parentCategory->getName());
            $category->setDescription($parentCategory->getDescription());
            $category->setParent($parentCategory);
            $category->setType($parentCategory->getType());
            $category->setRestaurant($res);
            $category->setCategoryGroup($parentCategory->getCategoryGroup());
            $this->em->persist($res);
            $this->em->persist($category);
        }
        $this->em->flush();
    }

    public function synchroOneCategory(Client $client, ProductCategory $parentCategory){
        foreach( $client->getRestaurants() as $restaurant) {
            $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array('restaurant'=>$restaurant, 'parent' => $parentCategory));
            if(is_null($category)) {
                $category = new ProductCategory();
            }
            $category->setName($parentCategory->getName());
            $category->setDescription($parentCategory->getDescription());
            $category->setParent($parentCategory);
            $category->setIsOnline($parentCategory->getIsOnline());
            $parentCategory->addChildren($category);
            $category->setCategoryGroup($parentCategory->getCategoryGroup());
            $category->setPosition($parentCategory->getPosition());

            foreach ($parentCategory->getProducts() as $parentProduct) {
                $this->synchroOneProductPosition($restaurant, $parentProduct);
            }

            $category->setType($parentCategory->getType());
            $category->setRestaurant($restaurant);

            $this->em->persist($restaurant);
            $this->em->persist($category);
        }
        $this->em->persist($parentCategory);
        $this->em->flush();
    }

    public function synchroOneCategoryProducts($restaurants, $parentCategory) {
        foreach( $restaurants as $restaurant ) {
            $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array('restaurant'=>$restaurant,'parent'=>$parentCategory));

            foreach($parentCategory->getProducts() as $parentProduct) {
                $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($restaurant,$parentProduct);
                if(!is_null($product) && !is_null($category)) {
                    $product->setCategory($category);
                    if(!$category->getProducts()->contains($product)) {
                        $category->addProduct($product);
                    }
                    $this->em->flush();
                }
            }
        }
    }

    public function synchroOneProductPosition($res, Product $parentProduct) {
        $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res,$parentProduct);
        if(!is_null($product)) {
            $product->setPosition($parentProduct->getPosition());
            $this->em->flush();
        }
    }

    public function synchroOneMealPosition(Client $client, Meal $parentMeal) {
        $meals = $this->em->getRepository('ClabRestaurantBundle:Meal')->findBy(array('parent'=>$parentMeal));
        if(count($meals)>0) {
            foreach($meals as $meal) {
                $meal->setPosition($parentMeal->getPosition());
            }
            $this->em->flush();
        }
    }

    public function synchroOneCategoryPosition($res, ProductCategory $parentCategory) {
        $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array('restaurant'=>$res,'parent'=>$parentCategory));
        if(!is_null($category)) {
            $category->setPosition($parentCategory->getPosition());
            $this->em->flush();
        }
    }


    public function setChoices(Restaurant $res, $choices)
    {
        foreach ($choices as $parentChoice) {
            $choice = new OptionChoice();

            $choice = new OptionChoice();
            $choice->setCreated(new \DateTime());
            $choice->setRestaurant($res);
            $choice->setParent($parentChoice);

            $parentOption = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findOneBy(array('restaurant'=>$res,'parent'=>$parentChoice->getOption()));
            if(!is_null($parentOption)) {
                $choice->setOption($parentOption);
            }
            $choice->setPosition($parentChoice->getPosition());
            $choice->setSubwayType($parentChoice->getSubwayType());
            $choice->setValue($parentChoice->getValue());
            if(!is_null($parentChoice->getPrice())) {
                $choice->setPrice($parentChoice->getPrice());
            }
            $choice->setGallery($parentChoice->getGallery());
            $choice->setCover($parentChoice->getCover());
            $choice->setCoverFull($parentChoice->getCoverFull());
            $choice->setCoverSmall($parentChoice->getCoverSmall());
            $choice->setUpdated(new \DateTime());
            $choice->setIsOnline($parentChoice->getIsOnline());
            $this->em->persist($choice);
        }
        $this->em->flush();
    }

    public function synchroChoices(Restaurant $res, $choices)
    {
        foreach ($choices as $parentChoice) {
            $choice = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->findOneBy(array('restaurant'=>$res,'parent'=>$parentChoice));
            if(is_null($choice)) {
                $choice = new OptionChoice();
                $choice->setCreated(new \DateTime());
                $choice->setRestaurant($res);
                $choice->setParent($parentChoice);
            }
            $parentOption = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findOneBy(array('restaurant'=>$res,'parent'=>$parentChoice->getOption()));
            if(!is_null($parentOption)) {
                $choice->setOption($parentOption);
            }
            $choice->setPosition($parentChoice->getPosition());
            $choice->setSubwayType($parentChoice->getSubwayType());
            $choice->setValue($parentChoice->getValue());
            if(!is_null($parentChoice->getPrice())) {
                $choice->setPrice($parentChoice->getPrice());
            }
            $choice->setGallery($parentChoice->getGallery());
            $choice->setCover($parentChoice->getCover());
            $choice->setCoverFull($parentChoice->getCoverFull());
            $choice->setCoverSmall($parentChoice->getCoverSmall());
            $choice->setUpdated(new \DateTime());
            $choice->setIsOnline($parentChoice->getIsOnline());
            $this->em->persist($choice);
        }
        $this->em->flush();
    }

    public function synchroOneChoice(Restaurant $res,OptionChoice $parentChoice)
    {
        $choice = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->findOneBy(array('restaurant'=>$res,'parent'=>$parentChoice));
        if(is_null($choice)) {
            $choice = new OptionChoice();
            $choice->setCreated(new \DateTime());
            $choice->setRestaurant($res);
            $choice->setParent($parentChoice);
        }
        $parentOption = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findOneBy(array('restaurant'=>$res,'parent'=>$parentChoice->getOption()));
        if(!is_null($parentOption)) {
            $choice->setOption($parentOption);
        }
        $choice->setPosition($parentChoice->getPosition());
        $choice->setSubwayType($parentChoice->getSubwayType());
        $choice->setValue($parentChoice->getValue());
        if(!is_null($parentChoice->getPrice())) {
            $choice->setPrice($parentChoice->getPrice());
        }
        $choice->setGallery($parentChoice->getGallery());
        $choice->setCover($parentChoice->getCover());
        $choice->setCoverFull($parentChoice->getCoverFull());
        $choice->setCoverSmall($parentChoice->getCoverSmall());
        $choice->setUpdated(new \DateTime());
        $choice->setIsOnline($parentChoice->getIsOnline());
        $this->em->persist($choice);
        $this->em->flush();
    }

    public function deleteChoices(OptionChoice $parentChoice)
    {
        $choices = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->findBy(array('parent'=>$parentChoice));

        foreach($choices as $choice) {
            $choice->setIsOnline(false);
            $choice->setIsDeleted(true);
            $choice->setOption(null);
            $this->em->persist($choice);
        }
        $this->em->flush();
    }

    public function setOptions(Restaurant $res, $options)
    {
        foreach ($options as $parentOption) {
            $option = new ProductOption();

            foreach($parentOption->getProducts() as $parentProduct) {
                $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res,$parentProduct);
                if(!is_null($product)) {
                    $option->addProduct($product);
                    $product->addOption($option);
                    $this->em->persist($product);
                }
            }
            foreach($parentOption->getChoices() as $parentChoice) {
                $choice = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->findOneBy(array('restaurant'=>$res,'parent'=>$parentChoice));
                if(is_null($choice)) {
                    $choice = new OptionChoice();
                    $choice->setRestaurant($res);
                    $choice->setParent($parentChoice);
                    $choice->setCreated(new \DateTime());
                    $choice->setOption($option);
                }
                $choice->setPosition($parentChoice->getPosition());
                $choice->setSubwayType($parentChoice->getSubwayType());
                $choice->setValue($parentChoice->getValue());
                if(!is_null($parentChoice->getPrice())) {
                    $choice->setPrice($parentChoice->getPrice());
                }
                $choice->setGallery($parentChoice->getGallery());
                $choice->setCover($parentChoice->getCover());
                $choice->setCoverFull($parentChoice->getCoverFull());
                $choice->setCoverSmall($parentChoice->getCoverSmall());
                $choice->setUpdated(new \DateTime());
                $choice->setIsOnline($parentChoice->getIsOnline());
                $option->addChoice($choice);
                $this->em->persist($choice);
            }
            $option->setName($parentOption->getName());
            $option->setRequired($parentOption->getRequired());
            $option->setMultiple($parentOption->getMultiple());
            $option->setMinimum($parentOption->getMinimum());
            $option->setMaximum($parentOption->getMaximum());
            $option->setRestaurant($res);


            $option->setParent($parentOption);
            $this->em->persist($option);
        }
        $this->em->flush();
    }

    public function synchroOptions(Restaurant $res, $options)
    {
        foreach ($options as $parentOption) {
            $option = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findOneBy(array('restaurant'=>$res,'parent'=>$parentOption));
            if(is_null($option)) {
                $option = new ProductOption();
            }else{
                $option->setProducts(null);
                $option->setChoices(null);
            }
            foreach($parentOption->getProducts() as $parentProduct) {
                $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res,$parentProduct);
                if(!is_null($product)) {
                    $option->addProduct($product);
                    $product->addOption($option);
                    $this->em->persist($product);
                }
            }
            $option->setChoices(null);
            foreach($parentOption->getChoices() as $parentChoice) {
                $choice = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->findOneBy(array('restaurant'=>$res,'parent'=>$parentChoice));
                if(is_null($choice)) {
                    $choice = new OptionChoice();
                    $choice->setRestaurant($res);
                    $choice->setParent($parentChoice);
                    $choice->setCreated(new \DateTime());
                    $choice->setOption($option);
                }
                $choice->setPosition($parentChoice->getPosition());
                $choice->setSubwayType($parentChoice->getSubwayType());
                $choice->setValue($parentChoice->getValue());
                if(!is_null($parentChoice->getPrice())) {
                    $choice->setPrice($parentChoice->getPrice());
                }
                $choice->setGallery($parentChoice->getGallery());
                $choice->setCover($parentChoice->getCover());
                $choice->setCoverFull($parentChoice->getCoverFull());
                $choice->setCoverSmall($parentChoice->getCoverSmall());
                $choice->setUpdated(new \DateTime());
                $choice->setIsOnline($parentChoice->getIsOnline());
                $option->addChoice($choice);
                $this->em->persist($choice);
            }
            $option->setName($parentOption->getName());
            $option->setRequired($parentOption->getRequired());
            $option->setMultiple($parentOption->getMultiple());
            $option->setMinimum($parentOption->getMinimum());
            $option->setMaximum($parentOption->getMaximum());
            $option->setRestaurant($res);
            $option->setPosition($parentOption->getPosition());


            $option->setParent($parentOption);
            $this->em->persist($option);
        }
        $this->em->flush();
    }

    public function synchroOneOption(Restaurant $res, ProductOption $parentOption)
    {
        $option = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findOneBy(array('restaurant'=>$res,'parent'=>$parentOption));
        if(is_null($option)) {
            $option = new ProductOption();
        }else{
            $option->setProducts(null);
            $option->setChoices(null);
        }
        foreach($parentOption->getProducts() as $parentProduct) {
            $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res,$parentProduct);
            if(!is_null($product)) {
                $option->addProduct($product);
                $product->addOption($option);
                $this->em->persist($product);
            }
        }
        $option->setChoices(null);
        foreach($parentOption->getChoices() as $parentChoice) {
            $choice = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->findOneBy(array('restaurant'=>$res,'parent'=>$parentChoice));
            if(is_null($choice)) {
                $choice = new OptionChoice();
                $choice->setCreated(new \DateTime());
                $choice->setOption($option);
            }
            $choice->setRestaurant($res);
            $choice->setParent($parentChoice);
            $choice->setPosition($parentChoice->getPosition());
            $choice->setSubwayType($parentChoice->getSubwayType());
            $choice->setValue($parentChoice->getValue());
            if(!is_null($parentChoice->getPrice())) {
                $choice->setPrice($parentChoice->getPrice());
            }
            $choice->setGallery($parentChoice->getGallery());
            $choice->setCover($parentChoice->getCover());
            $choice->setCoverFull($parentChoice->getCoverFull());
            $choice->setCoverSmall($parentChoice->getCoverSmall());
            $choice->setUpdated(new \DateTime());
            $choice->setIsOnline($parentChoice->getIsOnline());
            $option->addChoice($choice);
            $this->em->persist($choice);
        }
        $option->setName($parentOption->getName());
        $option->setRequired($parentOption->getRequired());
        $option->setMultiple($parentOption->getMultiple());
        $option->setMinimum($parentOption->getMinimum());
        $option->setMaximum($parentOption->getMaximum());
        $option->setRestaurant($res);


        $option->setParent($parentOption);
        $this->em->persist($option);
        $this->em->flush();
    }

    public function deleteOptions(ProductOption $parentOption)
    {
        $options = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findBy(array('parent'=>$parentOption));
        foreach($options as $option) {
            $option->setIsOnline(false);
            $option->setIsDeleted(true);
            $this->em->persist($option);
        }
        $this->em->flush();
    }

    public function createMenuClassic(Restaurant $res, RestaurantMenu $parentMenuClassic)
    {
        $menuClassic = new RestaurantMenu();
        $menuClassic->setRestaurant($res);
        $menuClassic->setIsOnline($parentMenuClassic->getIsOnline());
        $menuClassic->setName($parentMenuClassic->getName());
        $menuClassic->setType($parentMenuClassic->getType());
        $this->em->persist($menuClassic);
    }

    public function createMenuDelivery(Restaurant $res, RestaurantMenu $parentMenuDelivery)
    {
        $menuDelivery = new RestaurantMenu();
        $menuDelivery->setRestaurant($res);
        $menuDelivery->setIsOnline($parentMenuDelivery->getIsOnline());
        $menuDelivery->setName($parentMenuDelivery->getName());
        $menuDelivery->setType($parentMenuDelivery->getType());
        $this->em->persist($menuDelivery);
    }

    public function setProducts(Restaurant $res, RestaurantMenu $menuClassic, RestaurantMenu $menuDelivery, $products)
    {
        foreach ($products as $parentProduct) {
            $product = new Product();
            $product->setName($parentProduct->getName());
            $product->setDescription($parentProduct->getDescription());
            $product->setPrice($parentProduct->getPrice());
            $product->setPriceOnSite($parentProduct->getPriceOnSite());
            $product->setDeliveryPrice($parentProduct->getDeliveryPrice());
            $product->setGallery($parentProduct->getGallery());
            $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array(
                'restaurant' => $res,
                'parent' => $parentProduct->getCategory(),
            ));
            if (!is_null($category)) {
                $product->setCategory($category);
                $category->addProduct($product);
                $this->em->persist($category);
            }
            $product->setExtraMakingTime(0);
            foreach ($parentProduct->getRestaurantMenus() as $restaurantMenu) {
                if ($restaurantMenu->getType() == 100) {
                    $product->addRestaurantMenu($menuClassic);
                } elseif ($restaurantMenu->getType() == 200) {
                    $product->addRestaurantMenu($menuDelivery);
                }
            }
            $product->setTax($parentProduct->getTax());
            $product->setTaxOnsite($parentProduct->getTaxOnSite());
            $product->setTaxDelivery($parentProduct->getTaxDelivery());
            $product->setExtraFields($parentProduct->getExtraFields());
            $product->setParent($parentProduct);
            $product->setMealOnly($parentProduct->isMealOnly());
            $this->em->persist($product);
        }
    }

    public function synchroProducts(Restaurant $res, RestaurantMenu $menuClassic, RestaurantMenu $menuDelivery, $products)
    {
        foreach ($products as $parentProduct) {

            $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res,$parentProduct);
            $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array(
                'restaurant' => $res,
                'parent' => $parentProduct->getCategory(),
            ));
            if(is_null($product)) {
                $product = new Product();
                if (!is_null($category)) {
                    $product->setCategory($category);
                    if(!$category->getProducts()->contains($product)) {
                        $category->addProduct($product);
                        $this->em->persist($category);
                    }
                }
                $product->setName($parentProduct->getName());
                $product->setDescription($parentProduct->getDescription());
                $product->setMealOnly($parentProduct->isMealOnly());

                $product->setExtraMakingTime($parentProduct->getExtraMakingTime());
                $product->setTax($parentProduct->getTax());
                $product->setTaxOnsite($parentProduct->getTaxOnSite());
                $product->setTaxDelivery($parentProduct->getTaxDelivery());

                $product->setParent($parentProduct);
                $product->setIsPDJ($parentProduct->getIsPDJ());
                $product->setStartDate($parentProduct->getStartDate());
                $product->setEndDate($parentProduct->getEndDate());
                $product->setPrice($parentProduct->getPrice());
                $product->setIsOnline($parentProduct->getIsOnline());
                $product->setGallery($parentProduct->getGallery());

                $product->setRestaurantMenus(null);

                foreach ($parentProduct->getRestaurantMenus() as $restaurantMenu) {
                    if ($restaurantMenu->getType() == 100) {
                        $product->addRestaurantMenu($menuClassic);
                        $this->em->persist($menuClassic);
                    } elseif ($restaurantMenu->getType() == 200) {
                        $product->addRestaurantMenu($menuDelivery);
                        $this->em->persist($menuDelivery);
                    }
                }
            }else {
                if (!is_null($category)) {
                    $product->setCategory($category);
                    if(!$category->getProducts()->contains($product)) {
                        $category->addProduct($product);
                        $this->em->persist($category);
                    }
                }
                $product->setName($parentProduct->getName());
                $product->setDescription($parentProduct->getDescription());
                $product->setIsPDJ($parentProduct->getIsPDJ());
                $product->setStartDate($parentProduct->getStartDate());
                $product->setEndDate($parentProduct->getEndDate());
                $product->setPrice($parentProduct->getPrice());
                $product->setPriceOnSite($parentProduct->getPriceOnSite());
                $product->setDeliveryPrice($parentProduct->getDeliveryPrice());
                $product->setExtraFields($parentProduct->getExtraFields());
                $product->setGallery($parentProduct->getGallery());
                $product->setIsOnline($parentProduct->getIsOnline());
                $product->setExtraMakingTime($parentProduct->getExtraMakingTime());
                $product->setTax($parentProduct->getTax());
                $product->setParent($parentProduct);
            }
            $this->em->persist($product);
        }
    }

    public function synchroOneProduct(Restaurant $res, RestaurantMenu $menuClassic, RestaurantMenu $menuDelivery,Product $parentProduct)
    {


            $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res,$parentProduct);
            $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array(
                'restaurant' => $res,
                'parent' => $parentProduct->getCategory(),
            ));
            if(is_null($product)) {
                $product = new Product();
                if (!is_null($category)) {
                    $product->setCategory($category);
                    $category->addProduct($product);
                    $this->em->persist($category);
                }
                $product->setName($parentProduct->getName());
                $product->setDescription($parentProduct->getDescription());

                $product->setExtraMakingTime($parentProduct->getExtraMakingTime());
                $product->setTax($parentProduct->getTax());
                $product->setParent($parentProduct);
                $product->setMealOnly($parentProduct->isMealOnly());

                $product->setIsPDJ($parentProduct->getIsPDJ());
                $product->setStartDate($parentProduct->getStartDate());
                $product->setEndDate($parentProduct->getEndDate());

                $product->setPrice($parentProduct->getPrice());

                $product->setIsOnline($parentProduct->getIsOnline());
                $product->setPosition($parentProduct->getPosition());

                $product->setGallery($parentProduct->getGallery());

                $product->setRestaurantMenus(new ArrayCollection());

                foreach ($parentProduct->getRestaurantMenus() as $restaurantMenu) {
                    if ($restaurantMenu->getType() == 100) {
                        $product->addRestaurantMenu($menuClassic);
                        $this->em->persist($menuClassic);
                    } elseif ($restaurantMenu->getType() == 200) {
                        $product->addRestaurantMenu($menuDelivery);
                        $this->em->persist($menuDelivery);
                    }
                }
            }else {
                $product->setName($parentProduct->getName());
                $product->setDescription($parentProduct->getDescription());
                $product->setPrice($parentProduct->getPrice());
                $product->setPriceOnSite($parentProduct->getPriceOnSite());
                $product->setDeliveryPrice($parentProduct->getDeliveryPrice());
                $product->setExtraFields($parentProduct->getExtraFields());
                $product->setGallery($parentProduct->getGallery());

                $product->setMealOnly($parentProduct->isMealOnly());

                $product->setIsPDJ($parentProduct->getIsPDJ());
                $product->setStartDate($parentProduct->getStartDate());
                $product->setEndDate($parentProduct->getEndDate());

                if (!is_null($category)) {
                    $product->setCategory($category);
                    if(!$category->getProducts()->contains($product)) {
                        $category->addProduct($product);
                        $this->em->persist($category);
                    }
                } else {
                    if($product->getCategory()) {
                        $cat = $product->getCategory();
                        $cat->removeProduct($product);
                        $this->em->persist($cat);
                    }
                    $product->setCategory(null);
                }

                $menuClassic->removeProduct($product);
                $menuDelivery->removeProduct($product);
                $product->setRestaurantMenus(new ArrayCollection());

                foreach ($parentProduct->getRestaurantMenus() as $restaurantMenu) {
                    if ($restaurantMenu->getType() == 100) {
                        $product->addRestaurantMenu($menuClassic);
                        $this->em->persist($menuClassic);
                    } elseif ($restaurantMenu->getType() == 200) {
                        $product->addRestaurantMenu($menuDelivery);
                        $this->em->persist($menuDelivery);
                    }
                }

                $product->setExtraMakingTime($parentProduct->getExtraMakingTime());
                $product->setTax($parentProduct->getTax());
                $product->setTaxOnsite($parentProduct->getTaxOnSite());
                $product->setTaxDelivery($parentProduct->getTaxDelivery());
                $product->setParent($parentProduct);
                $product->setPosition($parentProduct->getPosition());
            }
            $this->em->persist($product);
            return $product;

    }

    public function deleteProducts(Product $parentProduct)
    {
        $products = $this->em ->getRepository('ClabRestaurantBundle:Product')->findBy(array('parent'=>$parentProduct));
        foreach($products as $product) {
            $product->setIsOnline(false);
            $product->setIsDeleted(true);
            if(!is_null($product->getCategory())){
                $product->getCategory()->removeProduct($product);
                $product->setCategory(null);
            }
            foreach($product->getOptions() as $option) {
                $option->removeProduct($product);
                $this->em->persist($option);
            }
            $this->em->persist($product);
        }
        $this->em->flush();
    }

    public function setslots(Restaurant $res, $slots)
    {
        foreach ($slots as $parentSlot) {
            $slot = new MealSlot();
            $slot->setProductCategories(null);
            foreach($parentSlot->getProductCategories() as $parentCategory){
                $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array(
                    'restaurant' => $res,
                    'parent' => $parentCategory,
                ));
                if(!is_null($category)) {
                    $slot->addProductCategory($category);
                }
            }
            foreach($parentSlot->getMeals() as $parentMeal){
                $meal = $this->em->getRepository('ClabRestaurantBundle:Meal')->getForRestaurantAndParent($res,$parentMeal);
                if(!is_null($meal)) {
                    if(!is_null($slot->getMeals()) && !in_array($meal,$slot->getMeals()->toArray())) {
                        $slot->addMeal($meal);
                    }
                    if(!is_null($meal->getSlots()) && !in_array($slot,$meal->getSlots()->toArray())) {
                        $meal->addSlot($slot);
                        $this->em->persist($meal);
                    }
                }
            }
            $disabledProducts = array();

            foreach($parentSlot->getDisabledProducts() as $parentProductId) {
                $parentProduct = $this->em->getRepository('ClabRestaurantBundle:Product')->findOneBy(array('id'=>$parentProductId));
                $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res, $parentProduct);
                if(!is_null($product)) {
                    $disabledProducts[] = $product->getId();
                }
            }
            $slot->setDisabledProducts($disabledProducts);

            $data = array();
            foreach ($parentSlot->getCustomPrices() as $key=>$customPrice) {
                $parentProduct = $this->em->getRepository('ClabRestaurantBundle:Product')->findOneBy(array('id'=>$key));
                $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res, $parentProduct);
                if(!is_null($product)) {
                    $data[$product->getId()] = $customPrice;
                }
            }
            $slot->setCustomPrices($data);


            $slot->setName($parentSlot->getName());

            $slot->setUpdated(new \DateTime());
            $slot->setParent($parentSlot);


            $this->em->persist($slot);
        }
        $this->em->flush();
    }

    public function synchroSlots(Restaurant $res, $slots)
    {
        foreach ($slots as $parentSlot) {

            $slot = $this->em->getRepository('ClabRestaurantBundle:MealSlot')->findOneBy(array('restaurant' => $res, 'parent' => $parentSlot));
            if(is_null($slot)) {
                $slot = new MealSlot();
                $slot->setRestaurant($res);
                $slot->setCreated(new \DateTime());
            }

            $slot->setProductCategories(null);
            foreach($parentSlot->getProductCategories() as $parentCategory){
                $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array(
                    'restaurant' => $res,
                    'parent' => $parentCategory,
                ));
                $slotCategories = $slot->getProductCategories();
                if($category && !$slotCategories->contains($category)) {
                    $slot->addProductCategory($category);
                }
            }

            foreach($parentSlot->getMeals() as $parentMeal){
                $meal = $this->em->getRepository('ClabRestaurantBundle:Meal')->getForRestaurantAndParent($res,$parentMeal);
                if(!is_null($meal)) {
                    if(!is_null($slot->getMeals()) && !in_array($meal,$slot->getMeals()->toArray())) {
                        $slot->addMeal($meal);
                    }
                    if(!is_null($meal->getSlots()) && !in_array($slot,$meal->getSlots()->toArray())) {
                        $meal->addSlot($slot);
                        $this->em->persist($meal);
                    }
                }
            }
            $disabledProducts = array();

            foreach($parentSlot->getDisabledProducts() as $parentProductId) {
                $parentProduct = $this->em->getRepository('ClabRestaurantBundle:Product')->findOneBy(array('id'=>$parentProductId));
                $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res, $parentProduct);
                if(!is_null($product)) {
                    $disabledProducts[] = $product->getId();
                }
            }
            $slot->setDisabledProducts($disabledProducts);

            $data = array();
            foreach ($parentSlot->getCustomPrices() as $key=>$customPrice) {
                $parentProduct = $this->em->getRepository('ClabRestaurantBundle:Product')->findOneBy(array('id'=>$key));
                $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res, $parentProduct);
                if(!is_null($product)) {
                    $data[$product->getId()] = $customPrice;
                }
            }
            $slot->setCustomPrices($data);


            $slot->setName($parentSlot->getName());

            $slot->setUpdated(new \DateTime());
            $slot->setParent($parentSlot);
            $slot->setPosition($parentSlot->getPosition());

            $this->em->persist($slot);
        }
        $this->em->flush();
    }

    public function synchroOneSlot(Restaurant $res, MealSlot $parentSlot){
        $slot = $this->em->getRepository('ClabRestaurantBundle:MealSlot')->findOneBy(array('restaurant' => $res, 'parent' => $parentSlot));

        if(is_null($slot)) {
            $slot = new MealSlot();
            $slot->setRestaurant($res);
            $slot->setCreated(new \DateTime());
        }

        $slot->setProductCategories( new ArrayCollection());

        foreach($parentSlot->getProductCategories() as $parentCategory){
            $category = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findOneBy(array(
                'restaurant' => $res,
                'parent' => $parentCategory,
            ));

            if (!$category->getSlots()->contains($slot)) {
                $category->addSlot($slot);
            }

            if($category && !$slot->getProductCategories()->contains($category)) {
                $slot->addProductCategory($category);
            }
        }

        foreach($parentSlot->getMeals() as $parentMeal){
            $meal = $this->em->getRepository('ClabRestaurantBundle:Meal')->getForRestaurantAndParent($res,$parentMeal);
            if(!is_null($meal)) {
               if(!is_null($slot->getMeals()) && !in_array($meal,$slot->getMeals()->toArray())) {
                       $slot->addMeal($meal);
                   }
                if(!is_null($meal->getSlots()) && !in_array($slot,$meal->getSlots()->toArray())) {
                    $meal->addSlot($slot);
                    $this->em->persist($meal);
                }
            }
        }
        $disabledProducts = array();

        foreach($parentSlot->getDisabledProducts() as $parentProductId) {
            $parentProduct = $this->em->getRepository('ClabRestaurantBundle:Product')->findOneBy(array('id'=>$parentProductId));
            $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res, $parentProduct);
            if(!is_null($product)) {
                $disabledProducts[] = $product->getId();
            }
        }
        $slot->setDisabledProducts($disabledProducts);

        $data = array();
        foreach ($parentSlot->getCustomPrices() as $key=>$customPrice) {
            $parentProduct = $this->em->getRepository('ClabRestaurantBundle:Product')->findOneBy(array('id'=>$key));
            $product = $this->em->getRepository('ClabRestaurantBundle:Product')->getForRestaurantAndParent($res, $parentProduct);
            if(!is_null($product)) {
                $data[$product->getId()] = $customPrice;
            }
        }
        $slot->setCustomPrices($data);


        $slot->setName($parentSlot->getName());

        $slot->setUpdated(new \DateTime());
        $slot->setParent($parentSlot);
        $slot->setPosition($parentSlot->getPosition());

        $this->em->persist($slot);
        $this->em->flush();
    }

    public function deleteSlots(MealSlot $parentSlot)
    {
        $slots = $this->em->getRepository('ClabRestaurantBundle:MealSlot')->findBy(array('parent'=>$parentSlot));

        foreach($slots as $slot) {
            $this->em->remove($slot);
        }
        $this->em->flush();
    }

    public function setMeals(Restaurant $res, RestaurantMenu $menuClassic, RestaurantMenu $menuDelivery, $meals)
    {
        foreach ($meals as $parentMeal) {
            $meal = new Meal();
            $meal->setName($parentMeal->getName());
            $meal->setDescription($parentMeal->getDescription());
            $meal->setPrice($parentMeal->getPrice());
            $meal->setGallery($parentMeal->getGallery());
            foreach ($parentMeal->getRestaurantMenus() as $restaurantMenu) {
                if ($restaurantMenu->getType() == 100) {
                    $meal->addRestaurantMenu($menuClassic);
                } elseif ($restaurantMenu->getType() == 200) {
                    $meal->addRestaurantMenu($menuDelivery);
                }
            }
            $meal->setTax($parentMeal->getTax());

            $this->em->persist($meal);
        }
        $this->em->flush();
    }

    public function synchroMeals(Restaurant $res, RestaurantMenu $menuClassic, RestaurantMenu $menuDelivery, $meals)
    {
        foreach ($meals as $parentMeal) {
            $meal = $this->em->getRepository('ClabRestaurantBundle:Meal')->getForRestaurantAndParent($res,$parentMeal);

            if(is_null($meal)){
                $meal = new Meal();
            }
            $meal->setName($parentMeal->getName());
            $meal->setDescription($parentMeal->getDescription());
            $meal->setPrice($parentMeal->getPrice());
            $meal->setDeliveryPrice($parentMeal->getDeliveryPrice());
            $meal->setPriceOnSite($parentMeal->getPriceOnSite());
            $meal->setParent($parentMeal);
            $meal->setPosition($parentMeal->getPosition());
            $meal->setGallery($parentMeal->getGallery());
            $meal->setTax($parentMeal->getTax());
            foreach ($parentMeal->getRestaurantMenus() as $restaurantMenu) {
                if ($restaurantMenu->getType() == 100) {
                    if(!$meal->getRestaurantMenus()->contains($menuClassic)) {
                        $meal->addRestaurantMenu($menuClassic);
                        $this->em->persist($menuClassic);
                    }
                } elseif ($restaurantMenu->getType() == 200) {
                    if(!$meal->getRestaurantMenus()->contains($menuDelivery)) {
                        $meal->addRestaurantMenu($menuDelivery);
                        $this->em->persist($menuDelivery);
                    }
                }
            }
            foreach ($parentMeal->getSlots() as $parentSlot){
                $slot = $this->em->getRepository('ClabRestaurantBundle:MealSlot')->findOneBy(array('restaurant'=>$res, 'parent'=>$parentSlot));
                if(!is_null($slot)){
                    if(!$slot->getMeals()->contains($meal)){
                        $slot->addMeal($meal);
                    }
                    $slot->setPosition($parentSlot->getPosition());
                    if(!$meal->getSlots()->contains($slot)) {
                        $meal->addSlot($slot);
                    }
                }
            }

            $this->em->persist($meal);
        }
        $this->em->flush();
    }

    public function synchroOneMeal($restaurants, $parentMeal)
    {
            foreach($restaurants as $res) {
                $meal = $this->em->getRepository('ClabRestaurantBundle:Meal')->getForRestaurantAndParent($res,$parentMeal);

                $menuClassic = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('restaurant' => $res, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT));
                $menuDelivery = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('restaurant' => $res, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY));

                if(is_null($meal)){
                    $meal = new Meal();
                }
                $meal->setName($parentMeal->getName());
                $meal->setDescription($parentMeal->getDescription());
                $meal->setPrice($parentMeal->getPrice());
                $meal->setDeliveryPrice($parentMeal->getDeliveryPrice());
                $meal->setPriceOnSite($parentMeal->getPriceOnSite());
                $meal->setParent($parentMeal);
                $meal->setPosition($parentMeal->getPosition());
                $meal->setGallery($parentMeal->getGallery());
                $meal->setTax($parentMeal->getTax());
                $meal->setTaxOnsite($parentMeal->getTaxOnSite());
                $meal->setTaxDelivery($parentMeal->getTaxDelivery());
                foreach ($parentMeal->getRestaurantMenus() as $restaurantMenu) {
                    if ($restaurantMenu->getType() == 100) {
                        if(!$meal->getRestaurantMenus()->contains($menuClassic)) {
                            $meal->addRestaurantMenu($menuClassic);
                            $this->em->persist($menuClassic);
                        }
                    } elseif ($restaurantMenu->getType() == 200) {
                        if(!$meal->getRestaurantMenus()->contains($menuDelivery)) {
                            $meal->addRestaurantMenu($menuDelivery);
                            $this->em->persist($menuDelivery);
                        }
                    }
                }
                foreach ($parentMeal->getSlots() as $parentSlot){
                    $slot = $this->em->getRepository('ClabRestaurantBundle:MealSlot')->findOneBy(array('restaurant'=>$res, 'parent'=>$parentSlot));
                    if(!is_null($slot)){
                        if(!$slot->getMeals()->contains($meal)){
                            $slot->addMeal($meal);
                        }
                        $slot->setPosition($parentSlot->getPosition());
                        if(!$meal->getSlots()->contains($slot)) {
                            $meal->addSlot($slot);
                        }
                    }
                }

                $this->em->persist($meal);

                $this->em->flush();
            }
    }

    public function synchroOneMealChild(Restaurant $res, RestaurantMenu $menuClassic, RestaurantMenu $menuDelivery, $parentMeal)
    {

        $meal = $this->em->getRepository('ClabRestaurantBundle:Meal')->getForRestaurantAndParent($res,$parentMeal);

        if(is_null($meal)){
            $meal = new Meal();
        }
        $meal->setName($parentMeal->getName());
        $meal->setDescription($parentMeal->getDescription());
        $meal->setPrice($parentMeal->getPrice());
        $meal->setParent($parentMeal);
        $meal->setPosition($parentMeal->getPosition());
        $meal->setGallery($parentMeal->getGallery());
        $meal->setTax($parentMeal->getTax());
        $meal->setTaxOnsite($parentMeal->getTaxOnSite());
        $meal->setTaxDelivery($parentMeal->getTaxDelivery());
        foreach ($parentMeal->getRestaurantMenus() as $restaurantMenu) {
            if ($restaurantMenu->getType() == 100) {
                if(!$meal->getRestaurantMenus()->contains($menuClassic)) {
                    $meal->addRestaurantMenu($menuClassic);
                    $this->em->persist($menuClassic);
                }
            } elseif ($restaurantMenu->getType() == 200) {
                if(!$meal->getRestaurantMenus()->contains($menuDelivery)) {
                    $meal->addRestaurantMenu($menuDelivery);
                    $this->em->persist($menuDelivery);
                }
            }
        }
        foreach ($parentMeal->getSlots() as $parentSlot){
            $slot = $this->em->getRepository('ClabRestaurantBundle:MealSlot')->findOneBy(array('restaurant'=>$res, 'parent'=>$parentSlot));
            if(!is_null($slot)){
                if(!$slot->getMeals()->contains($meal)){
                    $slot->addMeal($meal);
                }
                $slot->setPosition($parentSlot->getPosition());
                if(!$meal->getSlots()->contains($slot)) {
                    $meal->addSlot($slot);
                }
            }
        }

        $this->em->persist($meal);

        $this->em->flush();

        return $meal;
    }


    public function deleteMeals(Meal $parentMeal) {
        $meals = $this->em->getRepository('ClabRestaurantBundle:Meal')->findBy(array('parent'=>$parentMeal));
        foreach($meals as $meal) {
            $meal->setIsOnline(false);
            $meal->setIsDeleted(true);
        }
        $this->em->flush();
    }

    public function synchroniseCatalog(Restaurant $res, Client $client){
        $categories = $this->em->getRepository('ClabRestaurantBundle:ProductCategory')->findBy(array('client' => $client));
        $choices = $this->em->getRepository('ClabRestaurantBundle:OptionChoice')->findBy(array('client' => $client));
        $options = $this->em->getRepository('ClabRestaurantBundle:ProductOption')->findBy(array('client' => $client));
        $menuClassic = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('restaurant' => $res, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT));
        $menuDelivery = $this->em->getRepository('ClabRestaurantBundle:RestaurantMenu')->findOneBy(array('restaurant' => $res, 'type' => RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY));

        $products = $this->em->getRepository('ClabRestaurantBundle:Product')->getForChainStore($client);
        $slots = $this->em->getRepository('ClabRestaurantBundle:MealSlot')->findBy(array('client' => $client));
        $meals = $this->em->getRepository('ClabRestaurantBundle:Meal')->getForChainStore($client);

        $this->synchroCategories($res,$categories);
        $this->synchroChoices($res,$choices);
        $this->synchroProducts($res,$menuClassic,$menuDelivery,$products);
        $this->synchroOptions($res,$options);
        $this->synchroSlots($res,$slots);
        $this->synchroMeals($res,$menuClassic,$menuDelivery,$meals);
    }
}
