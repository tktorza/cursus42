<?php

namespace Clab\BoardBundle\Service;

use Clab\BoardBundle\Entity\AdditionalSale;
use Clab\RestaurantBundle\Entity\Product;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\Count;

class AdditionalSaleManager
{
    protected $em;
    protected $formFactory;
    protected $translator;
    protected $repository;
    protected $addSaleRepository;

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
        $this->repository = $this->em->getRepository('ClabRestaurantBundle:Product');
        $this->addSaleRepository = $this->em->getRepository('ClabBoardBundle:AdditionalSale');
    }

    /**
     * @param AdditionalSale $additionalSale
     *
     * @return \Symfony\Component\Form\Form
     *                                      Get the OptionForm for a product
     */
    public function getAdditionalSaleForm(AdditionalSale $additionalSale)
    {
        $builder = $this->formFactory->createNamedBuilder('addSale');

        $products = $additionalSale->getAdditionalSaleProducts();

        if (!empty($products)) {
            $min = $max = null;
            if ($additionalSale->getMultiple() && $additionalSale->getMinimum() && $additionalSale->getMinimum() > 0) {
                $min = $additionalSale->getMinimum();
            }

            if ($additionalSale->getMultiple() && $additionalSale->getMaximum() && $additionalSale->getMaximum() > 0) {
                $max = $additionalSale->getMaximum();
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
                'class' => 'ClabBoardBundle:AdditionalSaleProduct',
                'data_class' => $additionalSale->getMultiple() ? null : 'Clab\BoardBundle\Entity\AdditionalSaleProduct',
                'choices' => $products,
                'expanded' => true,
                'label' => $additionalSale->getName(),
                'multiple' => $additionalSale->getMultiple(),
                'attr' => array('min' => $min, 'max' => $max, 'product' => $additionalSale->getProduct(), 'meal' => $additionalSale->getMeal()),
            );
            $builder->add((string) $additionalSale->getId(), 'entity', $params);
        }
        $form = $builder->getForm();

        return $form;
    }

    /**
     * @param Product $product
     *
     * @return \Symfony\Component\Form\Form
     *                                      Get the OptionForm for a product
     */
    public function getAdditionalSaleFormForProduct(Product $product)
    {
        $builder = $this->formFactory->createNamedBuilder('addSale');

        $additionalSale = $product->getAdditionalSale();

        $choices = $additionalSale->getAdditionalSaleProducts();

        if (!empty($choices)) {
            $min = $max = null;
            if ($additionalSale->getMultiple() && $additionalSale->getMinimum() && $additionalSale->getMinimum() > 0) {
                $min = $additionalSale->getMinimum();
            }

            if ($additionalSale->getMultiple() && $additionalSale->getMaximum() && $additionalSale->getMaximum() > 0) {
                $max = $additionalSale->getMaximum();
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
                    'class' => 'ClabBoardBundle:AdditionalSaleProduct',
                    'data_class' => $additionalSale->getMultiple() ? null : 'Clab\BoardBundle\Entity\AdditionalSaleProduct',
                    'choices' => $choices,
                    'expanded' => true,
                    'label' => $additionalSale->getName(),
                    'multiple' => $additionalSale->getMultiple(),
                    'attr' => array('min' => $min, 'max' => $max, 'product' => $additionalSale->getProduct()),
                );
            $builder->add((string) $additionalSale->getId(), 'entity', $params);
        }

        $form = $builder->getForm();

        return $form;
    }
}
