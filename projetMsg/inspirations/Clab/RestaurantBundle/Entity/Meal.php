<?php

namespace Clab\RestaurantBundle\Entity;

use Clab\BoardBundle\Entity\AdditionalSale;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Clab\MediaBundle\Entity\GalleryOwnerInterface;
use Clab\ShopBundle\Entity\OrderType;

/**
 * @ORM\Table(name="clickeat_restaurant_meal")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\MealRepository")
 */
class Meal implements GalleryOwnerInterface
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    private $isOnline;

    /**
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    private $isDeleted;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity="Meal", inversedBy="childrens")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Meal", mappedBy="parent")
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
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="price", type="float")
     */
    protected $price;

    protected $currentPrice;

    /**
     * @ORM\Column(name="delivery_price", type="float")
     */
    protected $deliveryPrice;

    /**
     * @ORM\Column(name="price_on_site", type="float")
     */
    protected $priceOnSite;


    /**
     * @ORM\Column(name="position", type="float")
     */
    protected $position;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\ShopBundle\Entity\Sale", inversedBy="meals", cascade={"all"})
     * @ORM\JoinColumn(name="sale_id", referencedColumnName="id", nullable=true)
     */
    protected $sale;

    /**
     * @ORM\ManyToMany(targetEntity="RestaurantMenu", mappedBy="meals")
     */
    protected $restaurantMenus;

    /**
     * @ORM\ManyToMany(targetEntity="MealSlot", mappedBy="meals", cascade={"all"})
     * @ORM\OrderBy({"position" = "asc"})
     */
    protected $slots;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected $gallery;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Tax")
     * @ORM\JoinColumn(name="tax_id", referencedColumnName="id")
     */
    protected $tax;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Tax", fetch="EAGER")
     * @ORM\JoinColumn(name="tax_delivery_id", referencedColumnName="id")
     */
    protected $taxDelivery;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Tax", fetch="EAGER")
     * @ORM\JoinColumn(name="tax_onsite_id", referencedColumnName="id")
     */
    protected $taxOnSite;


    /**
     * @ORM\OneToOne(targetEntity="Clab\BoardBundle\Entity\AdditionalSale",cascade={"remove"})
     * @ORM\JoinColumn(name="additional_sale_id", referencedColumnName="id", nullable=true ,onDelete="SET NULL")
     */
    private $additionalSale;

    protected $coverSmall;
    protected $cover;
    protected $coverFull;
    protected $coverDefault;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->setPosition(999);
        $this->setPrice(0);
        $this->setDeliveryPrice(0);
        $this->setPriceOnSite(0);

        $this->slots = new \Doctrine\Common\Collections\ArrayCollection();
        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->restaurantMenus = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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

    public function getRestaurant()
    {
        return $this->getRestaurantMenus()->first()->getRestaurant();
    }

    public function remove()
    {
        $this->setIsOnline(false);
        $this->setIsDeleted(true);
    }

    public function isOnline()
    {
        return $this->getIsOnline();
    }
    public function isDeleted()
    {
        return $this->getIsDeleted();
    }

    public function isAvailable($orderType = 1)
    {
        if (!$this->isOnline() || $this->isDeleted() || count($this->getSlots()) == 0 || $this->getCurrentPrice($orderType) == 0) {
            return false;
        }

        return true;
    }

    public function getCurrentPrice($orderType = OrderType::ORDERTYPE_PREORDER)
    {
        if(OrderType::ORDERTYPE_PREORDER == $orderType) {
            $price = $this->getPrice();
        } else if( OrderType::ORDERTYPE_DELIVERY == $orderType) {
            $price = $this->getDeliveryPrice();
        } else {
            $price = $this->getPriceOnSite();
        }

        if ($this->getSale() && $this->getSale()->getValue() >= 0 && $this->getSale()->getValue() <= 100) {
            $price = $price - $price * $this->getSale()->getValue() / 100;
        }

        $price = round($price, 2);

        return $price;
    }

    public function _getCurrentPrice()
    {
        $price = $this->getPrice();

        if ($this->getSale() && $this->getSale()->getValue() >= 0 && $this->getSale()->getValue() <= 100) {
            $price = $price - $price * $this->getSale()->getValue() / 100;
        }

        $price = round($price, 2);

        return $price;
    }

    public function getCoverSmall()
    {
        return $this->coverSmall;
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
     * @param mixed $restaurantMenu
     *
     * @return $this
     */
    public function setRestaurantMenus($restaurantMenus)
    {
        $this->restaurantMenus = $restaurantMenus;

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
     * @return Meal
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
     * @return Meal
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
     * @return Meal
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
     * @return Meal
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
     * @return Meal
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
     * @return Meal
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
     * Set description.
     *
     * @param string $description
     *
     * @return Meal
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
     * Set price.
     *
     * @param float $price
     *
     * @return Meal
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return Product
     */
    public function setDeliveryPrice($deliveryPrice)
    {
        $this->deliveryPrice = $deliveryPrice;

        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getDeliveryPrice()
    {
        return $this->deliveryPrice;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return Product
     */
    public function setPriceOnSite($priceOnSite)
    {
        $this->priceOnSite = $priceOnSite;

        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPriceOnSite()
    {
        return $this->priceOnSite;
    }

    /**
     * Set position.
     *
     * @param float $position
     *
     * @return Meal
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
     * @param \Clab\RestaurantBundle\Entity\Meal $parent
     *
     * @return Meal
     */
    public function setParent(\Clab\RestaurantBundle\Entity\Meal $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \Clab\RestaurantBundle\Entity\Meal
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children.
     *
     * @param \Clab\RestaurantBundle\Entity\Meal $children
     *
     * @return Meal
     */
    public function addChildren(\Clab\RestaurantBundle\Entity\Meal $children)
    {
        $this->childrens[] = $children;

        return $this;
    }

    /**
     * Remove children.
     *
     * @param \Clab\RestaurantBundle\Entity\Meal $children
     */
    public function removeChildren(\Clab\RestaurantBundle\Entity\Meal $children)
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
     * Set sale.
     *
     * @param \Clab\ShopBundle\Entity\Sale $sale
     *
     * @return Meal
     */
    public function setSale(\Clab\ShopBundle\Entity\Sale $sale = null)
    {
        $this->sale = $sale;

        return $this;
    }

    /**
     * Get sale.
     *
     * @return \Clab\ShopBundle\Entity\Sale
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * Add slot.
     *
     * @param \Clab\RestaurantBundle\Entity\MealSlot $slot
     *
     * @return Meal
     */
    public function addSlot(\Clab\RestaurantBundle\Entity\MealSlot $slot)
    {
        $this->slots[] = $slot;
        $slot->addMeal($this);

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
     * @return Meal
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
     * Set tax.
     *
     * @param \Clab\RestaurantBundle\Entity\Tax $tax
     *
     * @return Meal
     */
    public function setTax(\Clab\RestaurantBundle\Entity\Tax $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get tax.
     *
     * @return \Clab\RestaurantBundle\Entity\Tax
     */
    public function getTax($orderType = OrderType::ORDERTYPE_PREORDER)
    {
        if(OrderType::ORDERTYPE_PREORDER == $orderType) {
            return $this->tax;
        } else if( OrderType::ORDERTYPE_DELIVERY == $orderType) {
            return $this->taxDelivery;
        } else {
            return $this->taxOnSite;
        }
    }

    /**
     * Set tax.
     *
     * @param \Clab\RestaurantBundle\Entity\Tax $tax
     *
     * @return Product
     */
    public function setTaxDelivery(\Clab\RestaurantBundle\Entity\Tax $taxDelivery = null)
    {
        $this->taxDelivery = $taxDelivery;

        return $this;
    }

    /**
     * Get tax.
     *
     * @return \Clab\RestaurantBundle\Entity\Tax
     */
    public function getTaxDelivery()
    {
        return $this->taxDelivery;
    }

    /**
     * @return mixed
     */
    public function getTaxOnSite()
    {
        return $this->taxOnSite;
    }

    /**
     * @param mixed $taxOnSite
     *
     * @return $this
     */
    public function setTaxOnSite($taxOnSite)
    {
        $this->taxOnSite = $taxOnSite;

        return $this;
    }

    /**
     * Add restaurantMenu.
     *
     * @param \Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu
     *
     * @return Meal
     */
    public function addRestaurantMenu(\Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu)
    {
        $this->restaurantMenus[] = $restaurantMenu;
        $restaurantMenu->addMeal($this);

        return $this;
    }

    /**
     * Remove restaurantMenu.
     *
     * @param \Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu
     */
    public function removeRestaurantMenu(\Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu)
    {
        $this->restaurantMenus->removeElement($restaurantMenu);
    }

    /**
     * Get restaurantMenus.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRestaurantMenus()
    {
        return $this->restaurantMenus;
    }

    /**
     * Set additionalSale.
     *
     * @param \Clab\BoardBundle\Entity\AdditionalSale $additionalSale
     *
     * @return Meal
     */
    public function setAdditionalSale(AdditionalSale $additionalSale = null)
    {
        $this->additionalSale = $additionalSale;

        return $this;
    }

    /**
     * Get additionalSale.
     *
     * @return \Clab\BoardBundle\Entity\AdditionalSale
     */
    public function getAdditionalSale()
    {
        return $this->additionalSale;
    }
}
