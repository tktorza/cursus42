<?php

namespace Clab\BoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="client_configuration")
 * @ORM\Entity(repositoryClass="Clab\BoardBundle\Entity\ClientConfigurationRepository")
 */
class ClientConfiguration
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\Client", inversedBy="configurations")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    protected $client;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", mappedBy="clientConfiguration")
     */
    protected $restaurants;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="productCategoryCreation", type="boolean")
     */
    private $productCategoryCreation;

    /**
     * @ORM\Column(name="productCategoryEdition", type="boolean")
     */
    private $productCategoryEdition;

    /**
     * @ORM\Column(name="productCreation", type="boolean")
     */
    private $productCreation;

    /**
     * @ORM\Column(name="productEdition", type="boolean")
     */
    private $productEdition;

    /**
     * @ORM\Column(name="productOptionManage", type="boolean")
     */
    private $productOptionManage;

    /**
     * @ORM\Column(name="productOptionCreation", type="boolean")
     */
    private $productOptionCreation;

    /**
     * @ORM\Column(name="productOptionEdition", type="boolean")
     */
    private $productOptionEdition;

    /**
     * @ORM\Column(name="optionChoiceCreation", type="boolean")
     */
    private $optionChoiceCreation;

    /**
     * @ORM\Column(name="optionChoiceEdition", type="boolean")
     */
    private $optionChoiceEdition;

    /**
     * @ORM\Column(name="mealCreation", type="boolean")
     */
    private $mealCreation;

    /**
     * @ORM\Column(name="mealEdition", type="boolean")
     */
    private $mealEdition;

    /**
     * @ORM\Column(name="mealSlotEdition", type="boolean")
     */
    private $mealSlotEdition;

    /**
     * @ORM\Column(name="mealChoiceEdition", type="boolean")
     */
    private $mealChoiceEdition;

    public function __construct()
    {
        $this->restaurants = new \Doctrine\Common\Collections\ArrayCollection();

        $this->setProductCategoryCreation(false);
        $this->setProductCategoryEdition(false);

        $this->setProductCreation(false);
        $this->setProductEdition(false);

        $this->setProductOptionManage(false);

        $this->setProductOptionCreation(false);
        $this->setProductOptionEdition(false);

        $this->setOptionChoiceCreation(false);
        $this->setOptionChoiceEdition(false);

        $this->setMealCreation(false);
        $this->setMealEdition(false);

        $this->setMealSlotEdition(false);
        $this->setMealChoiceEdition(false);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setClient(\Clab\BoardBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function addRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurants)
    {
        $this->restaurants[] = $restaurants;

        return $this;
    }

    public function removeRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurants)
    {
        $this->restaurants->removeElement($restaurants);
    }

    public function getRestaurants()
    {
        return $this->restaurants;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setProductCategoryCreation($productCategoryCreation)
    {
        $this->productCategoryCreation = $productCategoryCreation;

        return $this;
    }

    public function getProductCategoryCreation()
    {
        return $this->productCategoryCreation;
    }

    public function setProductCategoryEdition($productCategoryEdition)
    {
        $this->productCategoryEdition = $productCategoryEdition;
        return $this;
    }

    public function getProductCategoryEdition()
    {
        return $this->productCategoryEdition;
    }

    public function setProductCreation($productCreation)
    {
        $this->productCreation = $productCreation;
        return $this;
    }

    public function getProductCreation()
    {
        return $this->productCreation;
    }

    public function setProductEdition($productEdition)
    {
        $this->productEdition = $productEdition;
        return $this;
    }

    public function getProductEdition()
    {
        return $this->productEdition;
    }

    public function setProductOptionManage($productOptionManage)
    {
        $this->productOptionManage = $productOptionManage;
        return $this;
    }

    public function getProductOptionManage()
    {
        return $this->productOptionManage;
    }

    public function setProductOptionCreation($productOptionCreation)
    {
        $this->productOptionCreation = $productOptionCreation;
        return $this;
    }

    public function getProductOptionCreation()
    {
        return $this->productOptionCreation;
    }

    public function setProductOptionEdition($productOptionEdition)
    {
        $this->productOptionEdition = $productOptionEdition;
        return $this;
    }

    public function getProductOptionEdition()
    {
        return $this->productOptionEdition;
    }

    public function setOptionChoiceCreation($optionChoiceCreation)
    {
        $this->optionChoiceCreation = $optionChoiceCreation;
        return $this;
    }

    public function getOptionChoiceCreation()
    {
        return $this->optionChoiceCreation;
    }

    public function setOptionChoiceEdition($optionChoiceEdition)
    {
        $this->optionChoiceEdition = $optionChoiceEdition;
        return $this;
    }

    public function getOptionChoiceEdition()
    {
        return $this->optionChoiceEdition;
    }

    public function setMealCreation($mealCreation)
    {
        $this->mealCreation = $mealCreation;

        return $this;
    }

    public function getMealCreation()
    {
        return $this->mealCreation;
    }

    public function setMealEdition($mealEdition)
    {
        $this->mealEdition = $mealEdition;
        return $this;
    }

    public function getMealEdition()
    {
        return $this->mealEdition;
    }

    public function setMealSlotEdition($mealSlotEdition)
    {
        $this->mealSlotEdition = $mealSlotEdition;
        return $this;
    }

    public function getMealSlotEdition()
    {
        return $this->mealSlotEdition;
    }

    public function setMealChoiceEdition($mealChoiceEdition)
    {
        $this->mealChoiceEdition = $mealChoiceEdition;
        return $this;
    }

    public function getMealChoiceEdition()
    {
        return $this->mealChoiceEdition;
    }
}
