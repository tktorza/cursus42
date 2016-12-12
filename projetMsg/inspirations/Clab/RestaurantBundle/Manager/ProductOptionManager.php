<?php

namespace Clab\RestaurantBundle\Manager;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotNull;
use Clab\BoardBundle\Entity\Client;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\ProductOption;
use Clab\RestaurantBundle\Entity\OptionChoice;
use Clab\RestaurantBundle\Event\ProductOptionEvent;
use Clab\RestaurantBundle\Event\OptionChoiceEvent;

class ProductOptionManager
{
    protected $em;
    protected $formFactory;
    protected $translator;
    protected $repository;
    protected $choiceRepository;

    /**
     * @param EntityManager        $em
     * @param FormFactoryInterface $formFactory
     * @param TranslatorInterface  $translator
     *                                          Constructor
     */
    public function __construct(EntityManager $em, FormFactoryInterface $formFactory, TranslatorInterface $translator)
    {
        $this->em = $em;
        $this->formFactory = $formFactory;
        $this->translator = $translator;
        $this->repository = $this->em->getRepository('ClabRestaurantBundle:ProductOption');
        $this->choiceRepository = $this->em->getRepository('ClabRestaurantBundle:OptionChoice');
    }

    public function getRepository()
    {
        return $this->repository;
    }

    public function getChoiceRepository()
    {
        return $this->choiceRepository;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return ProductOption
     *                       Create an option for a restaurant
     */
    public function createForRestaurant(Restaurant $restaurant)
    {
        $option = new ProductOption();
        $option->setRestaurant($restaurant);

        return $option;
    }

    /**
     * @param Client $chainStore
     *
     * @return ProductOption
     *                       Create an option for a chainstore
     */
    public function createForChainStore(Client $chainStore)
    {
        $option = new ProductOption();
        $option->setClient($chainStore);

        return $option;
    }

    /**
     * @param Product $product
     *
     * @return bool
     *              Remove an option for a product
     */
    public function remove(ProductOption $productOption)
    {
        $productOption->setIsOnline(false);
        $productOption->setIsDeleted(true);

        $this->em->flush();

        foreach ($productOption->getChildrens() as $children) {
            $this->remove($children);
        }

        return true;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return array|\Clab\RestaurantBundle\Entity\ProductOption[]
     *                                                             Get all options available for a restaurant
     */
    public function getForRestaurant(Restaurant $restaurant)
    {
        return $this->repository->findBy(array(
            'restaurant' => $restaurant,
            'isDeleted' => false,
        ), array(
            'position' => 'asc',
        ));
    }

    /**
     * @param Client $chainStore
     *
     * @return array|\Clab\RestaurantBundle\Entity\ProductOption[]
     *                                                             Get all options available for a chainstore
     */
    public function getForChainStore(Client $chainStore)
    {
        return $this->repository->findBy(array(
            'client' => $chainStore,
            'isDeleted' => false,
        ), array(
            'position' => 'asc',
        ));
    }

    /**
     * @param Product $product
     *
     * @return array|\Clab\RestaurantBundle\Entity\ProductOption[]
     *                                                             all options for a product
     */
    public function getForProduct(Product $product)
    {
        return $this->repository->findBy(array(
            'products' => $product,
            'isDeleted' => false,
        ), array(
            'position' => 'asc',
        ));
    }

    /**
     * @param Product $product
     *
     * @return array
     *               all options available for a product
     */
    public function getAvailableForProduct(Product $product)
    {
        return $this->repository->getAvailableForProduct($product);
    }

    /**
     * @param array $products
     *
     * @return array
     *               Get all available options for multiple products
     */
    public function getAvailableForProducts(array $products)
    {
        $options = array();
        foreach ($products as $product) {
            $productOptions = $this->getAvailableForProduct($product);
            if (!empty($productOptions)) {
                $options[$product->getId()] = $productOptions;
            }
        }

        return $options;
    }

    /**
     * @param ArrayCollection $options
     *
     * @return bool
     *              Reorder options in the BackOffice
     */
    public function reorder(ArrayCollection $options)
    {
        foreach ($options as $key => $option) {
            $option->setPosition($key);
        }

        $this->em->flush();

        return true;
    }

    /**
     * @return OptionChoice
     *                      Create an option
     */
    public function createChoiceForRestaurant(Restaurant $restaurant)
    {
        $choice = new OptionChoice();
        $choice->setRestaurant($restaurant);

        return $choice;
    }

    public function createChoiceForChainStore(Client $chainStore)
    {
        $choice = new OptionChoice();
        $choice->setClient($chainStore);

        return $choice;
    }

    /**
     * @param Restaurant $restaurant
     *
     * @return array|\Clab\RestaurantBundle\Entity\OptionChoice[]
     *                                                            Get choices available for a restaurant
     */
    public function getChoicesForRestaurant(Restaurant $restaurant)
    {
        return $this->choiceRepository->findBy(array(
            'restaurant' => $restaurant,
            'isDeleted' => false,
        ), array(
            'position' => 'asc',
        ));
    }

    /**
     * @param Client $chainStore
     *
     * @return array|\Clab\RestaurantBundle\Entity\OptionChoice[]
     *                                                            Get choices available for a chainstore
     */
    public function getChoicesForChainStore(Client $chainStore)
    {
        return $this->choiceRepository->findBy(array(
            'client' => $chainStore,
            'isDeleted' => false,
        ), array(
            'position' => 'asc',
        ));
    }

    /**
     * @param OptionChoice $choice
     *
     * @return array
     *               Get options available for a choice
     */
    public function getOptionsForChoice(OptionChoice $choice)
    {
        return $this->repository->getOptionsForChoice($choice);
    }

    /**
     * @param OptionChoice $choice
     *
     * @return OptionChoice
     *                      Soft delete an option
     */
    public function removeChoice(OptionChoice $choice)
    {
        $choice->setIsOnline(false);
        $choice->setIsDeleted(true);
        $choice->setOption(null);

        $this->em->flush();

        foreach ($choice->getChildrens() as $children) {
            $this->removeChoice($children);
        }

        return true;
    }

    /**
     * @param ProductOption $option
     * @param OptionChoice  $choice
     *                              Add a choice to an option
     */
    public function addChoiceToOption(ProductOption $option, OptionChoice $choice)
    {
        //check if choice is already in option
        $alreadyInOption = false;
        foreach ($option->getChoices() as $optionChoice) {
            if ($optionChoice->getParent() == $choice) {
                $alreadyInOption = true;
            }
        }

        if (!$alreadyInOption) {
            $newChoice = new OptionChoice();
            $newChoice->setParent($choice);
            $newChoice->setValue($choice->getValue());
            $newChoice->setPrice($choice->getPrice());
            $newChoice->setOption($option);
            $this->em->persist($newChoice);
        }

        if ($chainStore = $option->getClient()) {
            foreach ($chainStore->getRestaurants() as $restaurant) {
                $childOption = $this->repository->findOneBy(array('restaurant' => $restaurant, 'parent' => $option));
                $childChoice = $this->choiceRepository->findOneBy(array('restaurant' => $restaurant, 'parent' => $choice));

                $this->addChoiceToOption($childOption, $childChoice);
            }
        }
    }

    /**
     * @param ProductOption $option
     * @param OptionChoice  $choice
     *                              Remove a choice from an option
     */
    public function removeChoiceFromOption(ProductOption $option, OptionChoice $choice)
    {
        foreach ($option->getChoices() as $optionChoice) {
            if ($optionChoice->getParent() == $choice) {
                // soft delete
                $optionChoice->setOption(null);
                $this->removeChoice($optionChoice);
            }
        }

        // chainStore
        foreach ($option->getChildrens() as $childrenOption) {
            foreach ($childrenOption->getChoices() as $optionChoice) {
                if ($optionChoice->getParent() && $optionChoice->getParent()->getParent() && $optionChoice->getParent()->getParent() == $choice) {
                    // soft delete
                    $optionChoice->setOption(null);
                    $this->removeChoice($optionChoice);
                }
            }
        }
    }

    /**
     * @param Product $product
     *
     * @return \Symfony\Component\Form\Form
     *                                      Get the OptionForm for a product
     */
    public function getOptionFormForProduct(Product $product, $token = null)
    {

        if ($token) {
            $builder = $this->formFactory->createNamedBuilder('optionsProductMeal-'.$token);
        } else {
            $builder = $this->formFactory->createBuilder();
        }

        foreach ($this->getAvailableForProduct($product) as $option) {
            $choices = $option->getChoices()->toArray();

            if (is_array($choices) && !empty($choices)) {
                if (!$option->getRequired()) {
                    $params['empty_value'] = $this->translator->trans('Aucun(e)');
                } else {
                    $params['constraints'] = array(new NotNull());

                    if (!$option->getMultiple()) {
                        $params['data'] = $option->getChoices()->first();
                    }
                }

                $min = $max = null;
                if ($option->getMultiple() && $option->getMinimum() && $option->getMinimum() > 0) {
                    $min = $option->getMinimum();
                }

                if ($option->getMultiple() && $option->getMaximum() && $option->getMaximum() > 0) {
                    $max = $option->getMaximum();
                }

                $minMessage = $this->translator->trans('Vous devez choisir au moins {{ limit }} élements.');
                $maxMessage = $this->translator->trans('Vous devez choisir au plus {{ limit }} élements.');
                $exactMessage = $this->translator->trans('Vous devez choisir {{ limit }} élements.');

                if ($min && $max) {
                    $constraint = new Count(array(
                        'min' => $min,
                        'max' => $max,
                        'minMessage' => $minMessage,
                        'maxMessage' => $maxMessage,
                        'exactMessage' => $exactMessage,
                    ));
                } elseif ($min) {
                    $constraint = new Count(array(
                        'min' => $min,
                        'minMessage' => $minMessage,
                    ));
                } elseif ($max) {
                    $constraint = new Count(array(
                        'max' => $max,
                        'maxMessage' => $maxMessage,
                    ));
                }

                if (isset($constraint) && $constraint) {
                    $params['constraints'][] = $constraint;
                }
                $params = array(
                    'class' => 'ClabRestaurantBundle:OptionChoice',
                    'data_class' => $option->getMultiple() ? null : 'Clab\RestaurantBundle\Entity\OptionChoice',
                    'choices' => $choices,
                    'expanded' => true,
                    'label' => $this->getOptionLabel($option),
                    'multiple' => $option->getMultiple(),
                    'required' => $option->getRequired(),
                    'attr' => array('min' => $min,'max' => $max),
                );
                $builder->add((string) $option->getId(), 'entity', $params);
            }
        }
        $form = $builder->getForm();

        return $form;
    }

    /**
     * @param ProductOption $option
     *
     * @return string
     *                Get an option label
     */
    public function getOptionLabel(ProductOption $option)
    {
        $str = $option->getName();

        return $str;
    }

    public function createdFromChainStore(ProductOptionEvent $event)
    {
        $productOption = $event->getProductOption();
        $chainStore = $productOption->getClient();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $child = $this->createForRestaurant($restaurant);
            $child->setParent($productOption);
            $child->setName($productOption->getName());
            $child->setRequired($productOption->getRequired());
            $child->setMultiple($productOption->getMultiple());
            $child->setMinimum($productOption->getMinimum());
            $child->setMaximum($productOption->getMaximum());
            $child->setPosition($productOption->getPosition());
            $this->em->persist($child);
        }

        $this->em->flush();
    }

    public function updatedFromChainStore(ProductOptionEvent $event)
    {
        $productOption = $event->getProductOption();
        $chainStore = $productOption->getClient();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $child = $this->repository->findOneBy(array('restaurant' => $restaurant, 'parent' => $productOption));

            if ($child) {
                $child->setName($productOption->getName());
                $child->setRequired($productOption->getRequired());
                $child->setMultiple($productOption->getMultiple());
                $child->setMinimum($productOption->getMinimum());
                $child->setMaximum($productOption->getMaximum());
                $child->setPosition($productOption->getPosition());
            }
        }

        $this->em->flush();
    }

    public function updatedFromChainStoreForOption(ProductOption $productOption, Client $chainStore)
    {
        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $child = $this->repository->findOneBy(array('restaurant' => $restaurant, 'parent' => $productOption));

            if ($child) {
                $child->setName($productOption->getName());
                $child->setRequired($productOption->getRequired());
                $child->setMultiple($productOption->getMultiple());
                $child->setMinimum($productOption->getMinimum());
                $child->setMaximum($productOption->getMaximum());
                $child->setPosition($productOption->getPosition());
            }
        }

        $this->em->flush();
    }

    public function choiceCreatedFromChainStore(OptionChoiceEvent $event)
    {
        $optionChoice = $event->getOptionChoice();
        $chainStore = $optionChoice->getClient();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            $child = $this->createChoiceForRestaurant($restaurant);
            $child->setParent($optionChoice);
            $child->setValue($optionChoice->getValue());
            $child->setPrice($optionChoice->getPrice());
            $child->setGallery($optionChoice->getGallery());
            $child->setPosition($optionChoice->getPosition());
            $this->em->persist($child);
        }

        $this->em->flush();
    }

    public function choiceUpdatedFromChainStore(OptionChoiceEvent $event)
    {
        $optionChoice = $event->getOptionChoice();
        $chainStore = $optionChoice->getOption() ? $optionChoice->getOption()->getClient() : $optionChoice->getClient();

        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            if ($productOption = $optionChoice->getOption()) {
                $childOption = $this->repository->findOneBy(array('restaurant' => $restaurant, 'parent' => $productOption));
                $parentChoice = $this->choiceRepository->findOneBy(array('restaurant' => $restaurant, 'parent' => $optionChoice->getParent()));
                $child = $this->choiceRepository->findOneBy(array('option' => $childOption, 'parent' => $parentChoice));
            } else {
                $child = $this->choiceRepository->findOneBy(array('restaurant' => $restaurant, 'parent' => $optionChoice));
            }

            if ($child) {
                $child->setValue($optionChoice->getValue());
                $child->setPrice($optionChoice->getPrice());
                $child->setGallery($optionChoice->getGallery());
                $child->setPosition($optionChoice->getPosition());
            }
        }

        $this->em->flush();
    }
    public function choiceUpdatedFromChainStoreForChoice(OptionChoice $optionChoice, Client $chainStore)
    {
        if (is_null($chainStore)) {
            return false;
        }

        foreach ($chainStore->getRestaurants() as $restaurant) {
            if ($productOption = $optionChoice->getOption()) {
                $childOption = $this->repository->findOneBy(array('restaurant' => $restaurant, 'parent' => $productOption));
                $parentChoice = $this->choiceRepository->findOneBy(array('restaurant' => $restaurant, 'parent' => $optionChoice->getParent()));
                $child = $this->choiceRepository->findOneBy(array('option' => $childOption, 'parent' => $parentChoice));
            } else {
                $child = $this->choiceRepository->findOneBy(array('restaurant' => $restaurant, 'parent' => $optionChoice));
            }

            if ($child) {
                $child->setValue($optionChoice->getValue());
                $child->setPrice($optionChoice->getPrice());
                $child->setGallery($optionChoice->getGallery());
                $child->setPosition($optionChoice->getPosition());
            }
        }

        $this->em->flush();
    }
}
