<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Clab\MediaBundle\Entity\GalleryOwnerInterface;
use Clab\ShopBundle\Entity\OrderType;

/**
 * @ORM\Table(name="clickeat_restaurant_product")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\ProductRepository")
 */
class Product implements GalleryOwnerInterface
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
     * @ORM\Column(name="is_online_caisse", type="boolean")
     */
    protected $isOnlineCaisse;

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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="id_pdj", type="boolean",nullable=true)
     */
    protected $isPDJ;

    /**
     * @ORM\Column(name="startDate", type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(name="endDate", type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="extra_fields", type="array", nullable=true)
     */
    protected $extraFields;

    /**
     * @ORM\Column(name="extraMakingTime", type="float", nullable=true)
     */
    protected $extraMakingTime;

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
     * @ORM\Column(name="stock", type="integer")
     */
    protected $stock;

    /**
     * @ORM\Column(name="unlimited_stock", type="boolean")
     */
    protected $unlimitedStock;

    /**
     * @ORM\Column(name="default_stock", type="integer", nullable=true)
     */
    protected $defaultStock;

    /**
     * @ORM\Column(name="position", type="float")
     */
    protected $position;

    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="childrens")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Product", mappedBy="parent")
     */
    protected $childrens;

    /**
     * @ORM\ManyToMany(targetEntity="RestaurantMenu", mappedBy="products",cascade={"all"})
     */
    protected $restaurantMenus;

    /**
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="products")
     * @ORM\JoinColumn(name="product_category_id", referencedColumnName="id", nullable=true)
     */
    protected $category;

    /**
     * @ORM\ManyToMany(targetEntity="ProductOption", mappedBy="products")
     * @ORM\OrderBy({"position" = "asc"})
     */
    protected $options;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\ShopBundle\Entity\Sale", inversedBy="products", cascade={"persist"})
     * @ORM\JoinColumn(name="sale_id", referencedColumnName="id", nullable=true)
     */
    protected $sale;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected $gallery;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Tax", fetch="EAGER")
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
     * @ORM\Column(name="subwaySandwich", type="boolean", nullable=true)
     */
    protected $subwaySandwich;

    /**
     * @ORM\OneToOne(targetEntity="Clab\BoardBundle\Entity\AdditionalSale",cascade={"remove"})
     * @ORM\JoinColumn(name="additional_sale_id", referencedColumnName="id", nullable=true ,onDelete="SET NULL")
     */
    private $additionalSale;

    /**
     * @ORM\OneToMany(targetEntity="Clab\BoardBundle\Entity\AdditionalSaleProduct", mappedBy="product",cascade={"remove"})
     */
    protected $additionalSaleProducts;

    /**
     * @ORM\Column(name="meal_only", type="boolean")
     */
    protected $mealOnly;

    /**
     * @ORM\Column(name="printers", type="array", nullable=true)
     */
    protected $printers;

    protected $coverSmall;
    protected $cover;
    protected $coverFull;
    protected $coverDefault;

    public function __toString()
    {
        return $this->getName();
    }

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setIsOnline(true);
        $this->setIsOnlineCaisse(false);
        $this->setIsDeleted(false);
        $this->setUnlimitedStock(true);
        $this->setStock(10);
        $this->setDefaultStock(0);
        $this->setPosition(9999);
        $this->setIsPDJ(false);
        $this->setPrice(0);
        $this->setDeliveryPrice(0);
        $this->setPriceOnSite(0);
        $this->setMealOnly(false);
        $this->printers = array();

        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->options = new \Doctrine\Common\Collections\ArrayCollection();
        $this->mealChoices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->restaurantMenus = new \Doctrine\Common\Collections\ArrayCollection();
        $this->extraFields = array();
    }

    /**
     * @return mixed
     */
    public function getIsPDJ()
    {
        return $this->isPDJ;
    }

    /**
     * @param mixed $isPDJ
     *
     * @return $this
     */
    public function setIsPDJ($isPDJ)
    {
        $this->isPDJ = $isPDJ;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param mixed $startDate
     *
     * @return $this
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param mixed $endDate
     *
     * @return $this
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getRestaurant()
    {
        return $this->getRestaurantMenus()->first()->getRestaurant();
    }

    /**
     * @return mixed
     */
    public function getIsOnlineCaisse()
    {
        return $this->isOnlineCaisse;
    }

    /**
     * @param mixed $isOnlineCaisse
     *
     * @return $this
     */
    public function setIsOnlineCaisse($isOnlineCaisse)
    {
        $this->isOnlineCaisse = $isOnlineCaisse;

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
     * @param mixed $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    public function isAllowed(\Clab\UserBundle\Entity\User $user)
    {
        return $this->getProxy()->isAllowed($user);
    }

    public function isOnline()
    {
        return $this->getIsOnline();
    }
    public function isDeleted()
    {
        return $this->getIsDeleted();
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


    public function isAvailable($orderType = 1)
    {
        if ($this->isOnline() && !$this->isDeleted()
            && ($this->stock > 0 || $this->getUnlimitedStock() == true)
            && $this->getCategory() !== null
            && $this->getCategory()->getIsOnline()
            && $this->getCurrentPrice($orderType) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getSubwayName()
    {
        $name = $this->getName();
        $name = preg_replace('#\((.*?)\)#', '', $name);

        return $name;
    }

    public function getSubwaySandwichSize()
    {
        if (strpos($this->getName(), '15') !== false) {
            return 15;
        }

        return 30;
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
     * @return Product
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
     * @return Product
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
     * @return Product
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
     * @return Product
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
     * @return Product
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
     * @return Product
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
     * @return Product
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
     * Set extraFields.
     *
     * @param string $extraFields
     *
     * @return Product
     */
    public function setExtraFields($extraFields = array())
    {
        $this->extraFields = $extraFields;

        return $this;
    }

    /**
     * Get extraFields.
     *
     * @return string
     */
    public function getExtraFields()
    {
        return $this->extraFields;
    }

    public function addExtraFields($key, $field)
    {
        $this->extraFields[$key] = $field;

        return $this;
    }

    public function removeExtraField($key)
    {
        unset ($this->extraFields[$key]);

        return $this;
    }

    /**
     * Set extraMakingTime.
     *
     * @param float $extraMakingTime
     *
     * @return Product
     */
    public function setExtraMakingTime($extraMakingTime)
    {
        $this->extraMakingTime = $extraMakingTime;

        return $this;
    }

    /**
     * Get extraMakingTime.
     *
     * @return float
     */
    public function getExtraMakingTime()
    {
        return $this->extraMakingTime;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return Product
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
     * Set stock.
     *
     * @param int $stock
     *
     * @return Product
     */
    public function setStock($stock)
    {
        $this->stock = $stock;

        return $this;
    }

    /**
     * Get stock.
     *
     * @return int
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * Set unlimitedStock.
     *
     * @param bool $unlimitedStock
     *
     * @return Product
     */
    public function setUnlimitedStock($unlimitedStock)
    {
        $this->unlimitedStock = $unlimitedStock;

        return $this;
    }

    /**
     * Get unlimitedStock.
     *
     * @return bool
     */
    public function getUnlimitedStock()
    {
        return $this->unlimitedStock;
    }

    /**
     * Set defaultStock.
     *
     * @param int $defaultStock
     *
     * @return Product
     */
    public function setDefaultStock($defaultStock)
    {
        $this->defaultStock = $defaultStock;

        return $this;
    }

    /**
     * Get defaultStock.
     *
     * @return int
     */
    public function getDefaultStock()
    {
        return $this->defaultStock;
    }

    /**
     * Set position.
     *
     * @param float $position
     *
     * @return Product
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
     * Set subwaySandwich.
     *
     * @param bool $subwaySandwich
     *
     * @return Product
     */
    public function setSubwaySandwich($subwaySandwich)
    {
        $this->subwaySandwich = $subwaySandwich;

        return $this;
    }

    /**
     * Get subwaySandwich.
     *
     * @return bool
     */
    public function getSubwaySandwich()
    {
        return $this->subwaySandwich;
    }

    /**
     * Set parent.
     *
     * @param \Clab\RestaurantBundle\Entity\Product $parent
     *
     * @return Product
     */
    public function setParent(\Clab\RestaurantBundle\Entity\Product $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \Clab\RestaurantBundle\Entity\Product
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children.
     *
     * @param \Clab\RestaurantBundle\Entity\Product $children
     *
     * @return Product
     */
    public function addChildren(\Clab\RestaurantBundle\Entity\Product $children)
    {
        $this->childrens[] = $children;

        return $this;
    }

    /**
     * Remove children.
     *
     * @param \Clab\RestaurantBundle\Entity\Product $children
     */
    public function removeChildren(\Clab\RestaurantBundle\Entity\Product $children)
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
     * Set category.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductCategory $category
     *
     * @return Product
     */
    public function setCategory(\Clab\RestaurantBundle\Entity\ProductCategory $category = null)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return \Clab\RestaurantBundle\Entity\ProductCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Add option.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductOption $option
     *
     * @return Product
     */
    public function addOption(\Clab\RestaurantBundle\Entity\ProductOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Remove option.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductOption $option
     */
    public function removeOption(\Clab\RestaurantBundle\Entity\ProductOption $option)
    {
        $this->options->removeElement($option);
    }

    /**
     * Get options.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set sale.
     *
     * @param \Clab\ShopBundle\Entity\Sale $sale
     *
     * @return Product
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
     * Set gallery.
     *
     * @param \Clab\MediaBundle\Entity\Gallery $gallery
     *
     * @return Product
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
     * @return Product
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
     * @return Product
     */
    public function addRestaurantMenu(\Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu)
    {
        $this->restaurantMenus[] = $restaurantMenu;
        $restaurantMenu->addProduct($this);

        return $this;
    }

    /**
     * Remove restaurantMenu.
     *
     * @param \Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu
     */
    public function removeRestaurantMenu(\Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu)
    {
        $restaurantMenu->removeProduct($this);
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
     * @return Product
     */
    public function setAdditionalSale(\Clab\BoardBundle\Entity\AdditionalSale $additionalSale = null)
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

    /**
     * Add additionalSaleProduct.
     *
     * @param \Clab\BoardBundle\Entity\AdditionalSaleProduct $additionalSaleProduct
     *
     * @return Product
     */
    public function addAdditionalSaleProduct(\Clab\BoardBundle\Entity\AdditionalSaleProduct $additionalSaleProduct)
    {
        $this->additionalSaleProducts[] = $additionalSaleProduct;

        return $this;
    }

    /**
     * Remove additionalSaleProduct.
     *
     * @param \Clab\BoardBundle\Entity\AdditionalSaleProduct $additionalSaleProduct
     */
    public function removeAdditionalSaleProduct(\Clab\BoardBundle\Entity\AdditionalSaleProduct $additionalSaleProduct)
    {
        $this->additionalSaleProducts->removeElement($additionalSaleProduct);
    }

    /**
     * Get additionalSaleProducts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdditionalSaleProducts()
    {
        return $this->additionalSaleProducts;
    }

    public function isMealOnly()
    {
        return $this->mealOnly;
    }

    public function setMealOnly($mealOnly)
    {
        $this->mealOnly = $mealOnly;

        return $this;
    }

    public function getPrinters()
    {
        if($this->printers) {
            return empty($this->printers) ? null : $this->printers;
        }

        return null;
    }

    public function setPrinters(array $printers = array())
    {
        $this->printers = $printers;

        return $this;
    }

    public function addPrinter($printer)
    {
        if (!is_array($this->printers)) {
            $this->printers = array();
        }

        if (!in_array($printer,$this->printers)) {
            $this->printers[] = $printer;
        }

        return $this;
    }

    public function removePrinter($printer)
    {
        if(!is_array($this->printers)) {
            return $this;
        }

        if (in_array($printer,$this->printers)) {
            unset($this->printers[array_search($printer,$this->printers)]);

            $this->printers = array_values($this->printers);
        }

        return $this;
    }
}
