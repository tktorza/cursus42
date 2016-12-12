<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Clab\MediaBundle\Entity\GalleryOwnerInterface;

/**
 * @ORM\Table(name="clickeat_restaurant_productcategory")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\ProductCategoryRepository")
 */
class ProductCategory implements GalleryOwnerInterface
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
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="childrens")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="ProductCategory", mappedBy="parent")
     */
    protected $childrens;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    protected $type;

    /**
     * @ORM\Column(name="categoryGroup", type="string", length=255, nullable=true)
     */
    protected $categoryGroup;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="position", type="float")
     */
    protected $position;

    /**
     * @ORM\ManyToOne(targetEntity="Restaurant", inversedBy="productCategories")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\Client", inversedBy="productCategories")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    protected $client;

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="category")
     * @ORM\OrderBy({"position" = "asc"})
     */
    protected $products;

    /**
     * @ORM\ManyToMany(targetEntity="MealSlot", mappedBy="productCategories", cascade={"all"})
     * @ORM\OrderBy({"position" = "asc"})
     */
    protected $slots;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected $gallery;

    /**
     * @ORM\Column(name="suggestion_category_ratios", type="array", nullable=true)
     */
    protected $suggestionCategoryRatios;

    /**
     * @ORM\Column(name="suggestion_products_ratios", type="array", nullable=true)
     */
    protected $suggestionProductsRatios;

    protected $coverSmall;
    protected $cover;
    protected $coverFull;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->setPosition(9999);

        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();

        $this->suggestionCategoryRatios = array();
        $this->suggestionProductsRatios = array();
    }
    
    public function setId($id){
         $this->id=$id;
         return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function isOnline()
    {
        return $this->getIsOnline();
    }
    public function isDeleted()
    {
        return $this->getIsDeleted();
    }

    public function getCoverSmall()
    {
        return $this->coverSmall;
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
     * @param mixed $slots
     *
     * @return $this
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;

        return $this;
    }

    public function setCoverSmall($coverSmall)
    {
        $this->coverSmall = $coverSmall;

        return $this;
    }

    public function getCover()
    {
        return $this->cover;
    }

    public function setCover($cover)
    {
        $this->cover = $cover;

        return $this;
    }

    public function getCoverFull()
    {
        return $this->coverFull;
    }

    public function setCoverFull($coverFull)
    {
        $this->coverFull = $coverFull;

        return $this;
    }

    public function getCoverDefault()
    {
        return $this->coverDefault;
    }

    public function setCoverDefault($coverDefault)
    {
        $this->coverDefault = $coverDefault;

        return $this;
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
     * Set isOnline.
     *
     * @param bool $isOnline
     *
     * @return ProductCategory
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
     * @return ProductCategory
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
     * @return ProductCategory
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
     * @return ProductCategory
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
     * @return ProductCategory
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
     * Set slug.
     *
     * @param string $slug
     *
     * @return ProductCategory
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return ProductCategory
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set categoryGroup.
     *
     * @param string $categoryGroup
     *
     * @return ProductCategory
     */
    public function setCategoryGroup($categoryGroup)
    {
        $this->categoryGroup = $categoryGroup;

        return $this;
    }

    /**
     * Get categoryGroup.
     *
     * @return string
     */
    public function getCategoryGroup()
    {
        return $this->categoryGroup;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return ProductCategory
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set position.
     *
     * @param float $position
     *
     * @return ProductCategory
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
     * @param \Clab\RestaurantBundle\Entity\ProductCategory $parent
     *
     * @return ProductCategory
     */
    public function setParent(\Clab\RestaurantBundle\Entity\ProductCategory $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \Clab\RestaurantBundle\Entity\ProductCategory
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductCategory $children
     *
     * @return ProductCategory
     */
    public function addChildren(\Clab\RestaurantBundle\Entity\ProductCategory $children)
    {
        $this->childrens[] = $children;

        return $this;
    }

    /**
     * Remove children.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductCategory $children
     */
    public function removeChildren(\Clab\RestaurantBundle\Entity\ProductCategory $children)
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
     * @return ProductCategory
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
     * @return ProductCategory
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
     * Add slot.
     *
     * @param \Clab\RestaurantBundle\Entity\MealSlot $slot
     *
     * @return ProductCategory
     */
    public function addSlot(\Clab\RestaurantBundle\Entity\MealSlot $slot)
    {
        $this->slots[] = $slot;

        return $this;
    }

    /**
     * Remove slot.
     *
     * @param \Clab\RestaurantBundle\Entity\MealSlot $slot
     */
    public function removeSlot(\Clab\RestaurantBundle\Entity\MealSlot $slot)
    {
        $this->slots->removeElement($slot);
    }

    /**
     * Get slots.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Set gallery.
     *
     * @param \Clab\MediaBundle\Entity\Gallery $gallery
     *
     * @return ProductCategory
     */
    public function setGallery(\Clab\MediaBundle\Entity\Gallery $gallery = null)
    {
        $this->gallery = $gallery;

        return $this;
    }

    /**
     * Get gallery.
     *
     * @return \Clab\MediaBundle\Entity\Gallery
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * Set client.
     *
     * @param \Clab\BoardBundle\Entity\Client $client
     *
     * @return ProductCategory
     */
    public function setClient(\Clab\BoardBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \Clab\BoardBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    public function getSuggestionCategoryRatios()
    {
        return $this->suggestionCategoryRatios;
    }

    public function setSuggestionCategoryRatios($suggestionCategoryRatios)
    {
        $this->suggestionCategoryRatios = $suggestionCategoryRatios;
    }

    public function getSuggestionCategoryRatioForCategoryId($categoryId)
    {
        if ($this->suggestionCategoryRatios && !empty($this->suggestionCategoryRatios)) {
           foreach ($this->suggestionCategoryRatios as $ratio) {
                if($ratio['category'] == $categoryId) {
                    return $ratio['weight'];
                }
           }
        }

        return 0;
    }

    public function getSuggestionProductsRatios()
    {
        return $this->suggestionProductsRatios;
    }

    public function setSuggestionProductsRatios($suggestionProductsRatios)
    {
        $this->suggestionProductsRatios = $suggestionProductsRatios;
    }

    public function getSuggestionCategoryRatioForProductId($productId)
    {
        if ($this->suggestionProductsRatios && !empty($this->suggestionProductsRatios)) {
            foreach ($this->suggestionProductsRatios as $ratio) {
                if($ratio['product'] == $productId && isset($ratio['weight'])) {
                    return $ratio['weight'];
                }
            }
        }

        return 0;
    }
}
