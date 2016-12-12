<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_restaurant_productoption")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\ProductOptionRepository")
 */
class ProductOption
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    protected $isOnline;

    /**
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    protected $isDeleted;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\ManyToOne(targetEntity="ProductOption", inversedBy="childrens")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="ProductOption", mappedBy="parent")
     */
    protected $childrens;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(name="required", type="boolean")
     */
    protected $required;

    /**
     * @ORM\Column(name="multiple", type="boolean")
     */
    protected $multiple;

    /**
     * @ORM\Column(name="minimum", type="integer", nullable=true)
     */
    protected $minimum;

    /**
     * @ORM\Column(name="maximum", type="integer", nullable=true)
     */
    protected $maximum;

    /**
     * @ORM\Column(name="position", type="float")
     */
    protected $position;

    /**
     * @ORM\ManyToOne(targetEntity="Restaurant", inversedBy="options")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\Client", inversedBy="options")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    protected $client;

    /**
     * @ORM\ManyToMany(targetEntity="Product", inversedBy="options", cascade={"persist"})
     * @ORM\JoinTable(name="clickeat_restaurant_products_productoptions",
     *                joinColumns={@ORM\JoinColumn(name="option_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")})
     */
    protected $products;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\OptionChoice", mappedBy="option")
     * @ORM\OrderBy({"position" = "asc"})
     */
    protected $choices;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->setRequired(false);
        $this->setMultiple(false);
        $this->setMinimum(null);
        $this->setMaximum(null);
        $this->setPosition(999);

        $this->choices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function setId($id){
        $this->id=$id;
        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function isAvailable()
    {
        return ($this->isOnline() && !$this->isDeleted()) ? true : false;
    }

    public function isOnline()
    {
        return $this->getIsOnline();
    }
    public function isDeleted()
    {
        return $this->getIsDeleted();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $childrens
     *
     * @return $this
     */
    public function setChildrens($childrens)
    {
        $this->childrens = $childrens;

        return $this;
    }

    /**
     * @param mixed $products
     *
     * @return $this
     */
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @param mixed $choices
     *
     * @return $this
     */
    public function setChoices($choices)
    {
        $this->choices = $choices;

        return $this;
    }

    /**
     * Set isOnline.
     *
     * @param bool $isOnline
     *
     * @return ProductOption
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return bool
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set isDeleted.
     *
     * @param bool $isDeleted
     *
     * @return ProductOption
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted.
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return ProductOption
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return ProductOption
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return ProductOption
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set required.
     *
     * @param bool $required
     *
     * @return ProductOption
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get required.
     *
     * @return bool
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set multiple.
     *
     * @param bool $multiple
     *
     * @return ProductOption
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Get multiple.
     *
     * @return bool
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set minimum.
     *
     * @param int $minimum
     *
     * @return ProductOption
     */
    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;

        return $this;
    }

    /**
     * Get minimum.
     *
     * @return int
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * Set maximum.
     *
     * @param int $maximum
     *
     * @return ProductOption
     */
    public function setMaximum($maximum)
    {
        $this->maximum = $maximum;

        return $this;
    }

    /**
     * Get maximum.
     *
     * @return int
     */
    public function getMaximum()
    {
        return $this->maximum;
    }

    /**
     * Set position.
     *
     * @param float $position
     *
     * @return ProductOption
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return float
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set parent.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductOption $parent
     *
     * @return ProductOption
     */
    public function setParent(\Clab\RestaurantBundle\Entity\ProductOption $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \Clab\RestaurantBundle\Entity\ProductOption
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductOption $children
     *
     * @return ProductOption
     */
    public function addChildren(\Clab\RestaurantBundle\Entity\ProductOption $children)
    {
        $this->childrens[] = $children;

        return $this;
    }

    /**
     * Remove children.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductOption $children
     */
    public function removeChildren(\Clab\RestaurantBundle\Entity\ProductOption $children)
    {
        $this->childrens->removeElement($children);
    }

    /**
     * Get childrens.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildrens()
    {
        return $this->childrens;
    }

    /**
     * Set restaurant.
     *
     * @param \Clab\RestaurantBundle\Entity\Restaurant $restaurant
     *
     * @return ProductOption
     */
    public function setRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * Get restaurant.
     *
     * @return \Clab\RestaurantBundle\Entity\Restaurant
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }

    /**
     * Add product.
     *
     * @param \Clab\RestaurantBundle\Entity\Product $product
     *
     * @return ProductOption
     */
    public function addProduct(\Clab\RestaurantBundle\Entity\Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product.
     *
     * @param \Clab\RestaurantBundle\Entity\Product $product
     */
    public function removeProduct(\Clab\RestaurantBundle\Entity\Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add choice.
     *
     * @param \Clab\RestaurantBundle\Entity\OptionChoice $choice
     *
     * @return ProductOption
     */
    public function addChoice(\Clab\RestaurantBundle\Entity\OptionChoice $choice)
    {
        $this->choices[] = $choice;

        return $this;
    }

    /**
     * Remove choice.
     *
     * @param \Clab\RestaurantBundle\Entity\OptionChoice $choice
     */
    public function removeChoice(\Clab\RestaurantBundle\Entity\OptionChoice $choice)
    {
        $this->choices->removeElement($choice);
    }

    /**
     * Get choices.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Set client
     *
     * @param \Clab\BoardBundle\Entity\Client $client
     *
     * @return ProductOption
     */
    public function setClient(\Clab\BoardBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \Clab\BoardBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
