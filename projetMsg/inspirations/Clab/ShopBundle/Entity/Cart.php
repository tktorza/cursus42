<?php

namespace Clab\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_shop_cart")
 * @ORM\Entity(repositoryClass="Clab\ShopBundle\Repository\CartRepository")
 */
class Cart
{

    const SAUCE_PRICE = 0.2;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="orderType", type="integer", nullable=true)
     */
    protected $orderType;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="date")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    protected $updated;

    /**
     * @ORM\OneToMany(targetEntity="CartElement", mappedBy="cart", cascade={"persist", "remove"})
     */
    protected $elements;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant")
     */
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\ShopBundle\Entity\Coupon")
     * @ORM\JoinColumn(name="coupon_id", referencedColumnName="id", nullable=true)
     */
    protected $coupon;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\ShopBundle\Entity\Discount", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="discount_id", referencedColumnName="id", nullable=true)
     */
    protected $discount;

    /**
     * @ORM\OneToMany(targetEntity="Clab\ShopBundle\Entity\Loyalty", mappedBy="cart", cascade={"persist", "remove"})
     */
    protected $loyalties;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\DeliveryBundle\Entity\DeliveryCart")
     * @ORM\JoinColumn(name="delivery_cart_id", referencedColumnName="id", nullable=true)
     */
    protected $deliveryCart;

    /**
     * @ORM\Column(name="oldSchoolDiscount", type="boolean", nullable=true)
     */
    protected $oldSchoolDiscount;

    /**
     * @ORM\Column(name="oldSchoolDiscountAmount", type="float", nullable=true)
     */
    protected $oldSchoolDiscountAmount;

    /**
     * @ORM\Column(name="woodSticks", type="integer", nullable=true)
     */
    protected $woodSticks;

    private $saltySoja;

    private $sugarySoja;

    private $wasabi;

    private $ginger;

    private $freeSaltySoja;

    private $freeSugarySoja;

    private $freeWasabi;

    private $freeGinger;
    /**
     * @ORM\Column(name="free_sauces", type="integer", nullable=true)
     */
    private $freeSauces;

    protected $discountAmount;
    protected $discountPrice;
    protected $couponAmount;
    protected $totalPrice;

    public function __construct()
    {
        $this->setOrderType(1);
        $this->setOldSchoolDiscount(false);

        $this->elements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->loyalties = new \Doctrine\Common\Collections\ArrayCollection();
        $this->woodSticks = 0;
    }

    public function getBasePrice()
    {
        $total = 0;

        foreach ($this->getElements() as $element) {
            $total = $total + $element->getPrice() * $element->getQuantity();

            foreach ($element->getChildrens() as $children) {
                $total = $total + $children->getPrice() * $children->getQuantity() * $element->getQuantity();
            }
        }

        if ($this->getDeliveryCart() && $this->getDeliveryCart()->getExtra()) {
            $total = $total + $this->getDeliveryCart()->getExtra();
        }

        return $total;
    }

    public function getDiscountAmount()
    {
        //legacy
        if ($this->getOldSchoolDiscount()) {
            return $this->getOldSchoolDiscountAmount();
        }

        if ($this->getDiscount()) {
            $amount = $this->getDiscount()->getCartDiscountAmount($this);
        } else {
            $amount = 0;
        }

        return round($amount, 2);
    }

    public function getDiscountPrice()
    {
        $price = $this->getBasePrice() - $this->getDiscountAmount();

        if ($price < 0) {
            $price = 0;
        }

        return round($price, 2);
    }

    public function getCouponAmount()
    {
        if ($this->getCoupon()) {
            $amount = $this->getCoupon()->getDiscount($this->getDiscountPrice());
        } else {
            $amount = 0;
        }

        return round($amount, 2);
    }

    public function getTotalPrice()
    {
        $price = $this->getDiscountPrice() - $this->getCouponAmount();

        if (count($this->loyalties)) {
            foreach ($this->loyalties as $loyalty) {
                $price -= $loyalty->getValue();
            }
        }

        if ($price < 0) {
            $price = 0;
        }

        return round($price, 2);
    }

    public function getExtraMakingTime()
    {
        $time = 0;
        foreach ($this->getElements() as $element) {
            if ($element->getProduct() && $element->getProduct()->getExtraMakingTime() && $element->getProduct()->getExtraMakingTime() > $time) {
                $time = $element->getProduct()->getExtraMakingTime();
            }
        }

        return $time;
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
     * Set orderType.
     *
     * @param int $orderType
     *
     * @return Cart
     */
    public function setOrderType($orderType)
    {
        $this->orderType = $orderType;

        return $this;
    }

    /**
     * Get orderType.
     *
     * @return int
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Cart
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
     * @return Cart
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
     * Set oldSchoolDiscount.
     *
     * @param bool $oldSchoolDiscount
     *
     * @return Cart
     */
    public function setOldSchoolDiscount($oldSchoolDiscount)
    {
        $this->oldSchoolDiscount = $oldSchoolDiscount;

        return $this;
    }

    /**
     * Get oldSchoolDiscount.
     *
     * @return bool
     */
    public function getOldSchoolDiscount()
    {
        return $this->oldSchoolDiscount;
    }

    /**
     * Set oldSchoolDiscountAmount.
     *
     * @param float $oldSchoolDiscountAmount
     *
     * @return Cart
     */
    public function setOldSchoolDiscountAmount($oldSchoolDiscountAmount)
    {
        $this->oldSchoolDiscountAmount = $oldSchoolDiscountAmount;

        return $this;
    }

    /**
     * Get oldSchoolDiscountAmount.
     *
     * @return float
     */
    public function getOldSchoolDiscountAmount()
    {
        return $this->oldSchoolDiscountAmount;
    }

    /**
     * Set woodSticks.
     *
     * @param integer $woodSticks
     *
     * @return Cart
     */
    public function setWoodSticks($woodSticks)
    {
        $this->woodSticks = $woodSticks;

        return $this;
    }

    /**
     * Get woodSticks.
     */
    public function getWoodSticks()
    {
        return $this->woodSticks;
    }

    /**
     * Add element.
     *
     * @param \Clab\ShopBundle\Entity\CartElement $element
     *
     * @return Cart
     */
    public function addElement(\Clab\ShopBundle\Entity\CartElement $element)
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * Remove element.
     *
     * @param \Clab\ShopBundle\Entity\CartElement $element
     */
    public function removeElement(\Clab\ShopBundle\Entity\CartElement $element)
    {
        $this->elements->removeElement($element);
    }

    /**
     * Get elements.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Set restaurant.
     *
     * @param \Clab\RestaurantBundle\Entity\Restaurant $restaurant
     *
     * @return Cart
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
     * Set coupon.
     *
     * @param \Clab\ShopBundle\Entity\Coupon $coupon
     *
     * @return Cart
     */
    public function setCoupon(\Clab\ShopBundle\Entity\Coupon $coupon = null)
    {
        $this->coupon = $coupon;

        return $this;
    }

    /**
     * Get coupon.
     *
     * @return \Clab\ShopBundle\Entity\Coupon
     */
    public function getCoupon()
    {
        return $this->coupon;
    }

    /**
     * Set discount.
     *
     * @param \Clab\ShopBundle\Entity\Discount $discount
     *
     * @return Cart
     */
    public function setDiscount(\Clab\ShopBundle\Entity\Discount $discount = null)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount.
     *
     * @return \Clab\ShopBundle\Entity\Discount
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Add loyalty.
     *
     * @param \Clab\ShopBundle\Entity\Loyalty $loyalty
     *
     * @return Cart
     */
    public function addLoyalty(\Clab\ShopBundle\Entity\Loyalty $loyalty)
    {
        $this->loyalties[] = $loyalty;

        return $this;
    }

    /**
     * Remove loyalty.
     *
     * @param \Clab\ShopBundle\Entity\Loyalty $loyalty
     */
    public function removeLoyalty(\Clab\ShopBundle\Entity\loyalty $loyalty)
    {
        $this->loyalties->remove($loyalty);

        return $this;
    }

    /**
     * Get loyalties.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLoyalties()
    {
        return $this->loyalties;
    }

    /**
     * set loyalties.
     */
    public function setLoyalties($loyalties = null)
    {
        $this->loyalties = $loyalties;

        return $this;
    }

    /**
     * Set deliveryCart.
     *
     * @param \Clab\DeliveryBundle\Entity\DeliveryCart $deliveryCart
     *
     * @return Cart
     */
    public function setDeliveryCart(\Clab\DeliveryBundle\Entity\DeliveryCart $deliveryCart = null)
    {
        $this->deliveryCart = $deliveryCart;

        return $this;
    }

    /**
     * Get deliveryCart.
     *
     * @return \Clab\DeliveryBundle\Entity\DeliveryCart
     */
    public function getDeliveryCart()
    {
        return $this->deliveryCart;
    }

    public function getSaltySoja()
    {
        return $this->saltySoja;
    }

    public function setSaltySoja($saltySoja)
    {
        $this->saltySoja = $saltySoja;

        return $this;
    }

    public function getSugarySoja()
    {
        return $this->sugarySoja;
    }

    public function setSugarySoja($sugarySoja)
    {
        $this->sugarySoja = $sugarySoja;

        return $this;
    }

    public function getWasabi()
    {
        return $this->wasabi;
    }

    public function setWasabi($wasabi)
    {
        $this->wasabi = $wasabi;

        return $this;
    }

    public function getGinger()
    {
        return $this->ginger;
    }

    public function setGinger($ginger)
    {
        $this->ginger = $ginger;

        return $this;
    }

    public function getFreeSaltySoja()
    {
        return $this->freeSaltySoja;
    }

    public function setFreeSaltySoja($freeSaltySoja)
    {
        $this->freeSaltySoja = $freeSaltySoja;

        return $this;
    }

    public function getFreeSugarySoja()
    {
        return $this->freeSugarySoja;
    }

    public function setFreeSugarySoja($freeSugarySoja)
    {
        $this->freeSugarySoja = $freeSugarySoja;

        return $this;
    }

    public function getFreeWasabi()
    {
        return $this->freeWasabi;
    }

    public function setFreeWasabi($freeWasabi)
    {
        $this->freeWasabi = $freeWasabi;

        return $this;
    }

    public function getFreeGinger()
    {
        return $this->freeGinger;
    }

    public function setFreeGinger($freeGinger)
    {
        $this->freeGinger = $freeGinger;

        return $this;
    }

    public function getFreeSauces()
    {
        return $this->freeSauces;
    }

    public function setFreeSauces($freeSauces)
    {
        $this->freeSauces = $freeSauces;

        return $this;
    }
}
