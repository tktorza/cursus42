<?php

namespace Clab\ShopBundle\Entity;

use Clab\BoardBundle\Entity\Company;
use Clab\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_shop_orderdetails")
 * @ORM\Entity(repositoryClass="Clab\ShopBundle\Repository\OrderDetailRepository")
 */
class OrderDetail
{
    const ORDER_STATE_INITIAL = 0;
    const ORDER_STATE_WAITING_PAYMENT = 100;
    const ORDER_STATE_WAITING_PAYMENT_RESTOFLASH = 120;
    const ORDER_STATE_VALIDATED = 200;
    const ORDER_STATE_READY = 300;
    const ORDER_STATE_READY_PACKING = 310;
    const ORDER_STATE_READY_PACKED = 320;
    const ORDER_STATE_TERMINATED = 400;
    const ORDER_STATE_CANCELLED = 500;

    const ORDER_STATE_TREATED = 0;
    const ORDER_STATE_IN_PREPARATION = 1;
    const ORDER_STATE_IN_DELIVERY = 2;
    const ORDER_STATE_SERVED = 3;
    const ORDER_STATE_DELIVERY_ERROR = 4;
    const ORDER_STATE_SEEN = 5;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="hash", type="string", nullable=true)
     */
    private $hash;

    /**
     * @ORM\Column(name="state", type="float")
     */
    private $state;

    /**
     * @ORM\Column(name="preparationState", type="integer", nullable=true)
     */
    private $preparationState;

    /**
     * @ORM\Column(name="online_payment", type="boolean", nullable=false)
     */
    private $onlinePayment;

    /**
     * @ORM\Column(name="is_paid", type="boolean", nullable=false)
     */
    private $isPaid;

    /**
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    protected $currentPrice;

    /**
     * @ORM\Column(name="tva_20", type="float")
     */
    private $tva20;

    /**
     * @ORM\Column(name="tva_10", type="float")
     */
    private $tva10;

    /**
     * @ORM\Column(name="tva_55", type="float")
     */
    private $tva55;
    /**
     * @ORM\Column(name="tva_7", type="float")
     */
    private $tva7;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updated;

    /**
     * @ORM\Column(type="datetime")
     */
    private $time;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(name="is_test", type="boolean", nullable=true)
     */
    private $isTest;

    /**
     * @ORM\Column(name="source", type="string", nullable=true)
     */
    private $source;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", cascade={"all"})
     */
    protected $cart;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", inversedBy="orders")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id", nullable=true)
     */
    protected $restaurant;


    /**
     * @ORM\ManyToOne(targetEntity="Clab\SocialBundle\Entity\SocialFacebookPage")
     * @ORM\JoinColumn(name="facebook_page_id", referencedColumnName="id", nullable=true)
     */
    protected $facebookPage;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User", inversedBy="orders", fetch="EAGER")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     */
    protected $profile;

    /**
     * @ORM\ManyToOne(targetEntity="OrderType")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="id", nullable=true)
     */
    protected $orderType;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\BoardBundle\Entity\OrderStatement", inversedBy="orders")
     * @ORM\JoinColumn(name="order_statement_id", referencedColumnName="id", nullable=true)
     */
    protected $orderStatement;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestoflashBundle\Entity\RestoflashTransaction")
     * @ORM\JoinColumn(name="restoflash_transaction_id", referencedColumnName="id", nullable=true)
     */
    protected $restoflashTransaction;

    /**
     * @ORM\OneToOne(targetEntity="Clab\DeliveryBundle\Entity\Delivery", mappedBy="order", cascade={"persist", "remove"})
     */
    protected $delivery;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\Company", inversedBy="orders", fetch="EAGER")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id")
     */
    private $company;

    /**
     * @ORM\Column(name="onSitePayments", type="array", nullable=true)
     */
    private $onSitePayments;

    private $covers = null;

    public function __construct()
    {
        $this->setState(self::ORDER_STATE_INITIAL);
        $this->setPreparationState(self::ORDER_STATE_TREATED);
        $this->setOnlinePayment(true);
        $this->setIsPaid(false);
        $this->setIsTest(false);
        $this->setSource('web');

        $this->onSitePayments = ['cash' => 0., 'ticketResto' => 0., 'cbOnSite' => 0.];
    }

    public function __toString()
    {
        return (string) $this->id;
    }

    public function labellizeState()
    {
        switch ($this->getState()) {
            case self::ORDER_STATE_INITIAL:
                return 'Initial';
                break;
            case self::ORDER_STATE_WAITING_PAYMENT:
            case self::ORDER_STATE_WAITING_PAYMENT_RESTOFLASH:
                return '<span class="label label-warning">A payer</span>';
                break;
            case self::ORDER_STATE_VALIDATED:
            case self::ORDER_STATE_READY:
            case self::ORDER_STATE_READY_PACKING:
                return '<span class="label label-success">Validée</span>';
                break;
            case self::ORDER_STATE_READY_PACKED:
                return '<span class="label label-success">Préparée</span>';
                break;
            case self::ORDER_STATE_TERMINATED:
                return '<span class="label label-info">Terminée</span>';
                break;
            case self::ORDER_STATE_CANCELLED:
                return '<span class="label label-important label-danger">Annulée</span>';
                break;
            default:
               return;
                break;
        }
    }

    public function labellizeStateAdmin()
    {
        switch ($this->getState()) {
            case self::ORDER_STATE_INITIAL:
                return 'Initial';
                break;
            case self::ORDER_STATE_WAITING_PAYMENT:
            case self::ORDER_STATE_WAITING_PAYMENT_RESTOFLASH:
                return '<span class="label label-warning">A payer</span>';
                break;
            case self::ORDER_STATE_VALIDATED:
                return '<span class="label label-success">Validée</span>';
                break;
            case self::ORDER_STATE_READY:
            case self::ORDER_STATE_READY_PACKING:
                return '<span class="label label-success">Vue</span>';
                break;
            case self::ORDER_STATE_READY_PACKED:
                return '<span class="label label-success">Préparée</span>';
                break;
            case self::ORDER_STATE_TERMINATED:
                return '<span class="label label-info">Terminée</span>';
                break;
            case self::ORDER_STATE_CANCELLED:
                return '<span class="label label-important label-danger">Annulée</span>';
                break;
            default:
               return;
                break;
        }
    }

    public static function getStateArray()
    {
        $states = array(
            self::ORDER_STATE_WAITING_PAYMENT => 'A payer',
            self::ORDER_STATE_WAITING_PAYMENT_RESTOFLASH => 'A payer',
            self::ORDER_STATE_VALIDATED => 'Validée',
            self::ORDER_STATE_READY => 'Vu',
            self::ORDER_STATE_READY_PACKING => 'À préparer',
            self::ORDER_STATE_READY_PACKED => 'Préparé',
            self::ORDER_STATE_TERMINATED => 'Terminée',
            self::ORDER_STATE_CANCELLED => 'Annulée',
        );

        return $states;
    }

    public function getBasePrice()
    {
        if ($this->getCart()) {
            return $this->getCart()->getBasePrice();
        }

        return 0;
    }

    public function getReduction()
    {
        $reduction = $this->getBasePrice() - $this->getPrice();

        if ($reduction == 0 || $this->getBasePrice() == 0) {
            return 0;
        }

        return round($reduction * 100 / $this->getBasePrice(), 2);
    }

    /**
     * @return mixed
     */
    public function getTva20() {

        return $this->tva20;
    }

    /**
     * @param mixed $tva20
     * @return $this
     */
    public function setTva20($tva20) {

        $this->tva20 = $tva20;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTva10() {

        return $this->tva10;
    }

    /**
     * @param mixed $tva10
     * @return $this
     */
    public function setTva10($tva10) {

        $this->tva10 = $tva10;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTva55() {

        return $this->tva55;
    }

    /**
     * @param mixed $tva55
     * @return $this
     */
    public function setTva55($tva55) {

        $this->tva55 = $tva55;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTva7() {

        return $this->tva7;
    }

    /**
     * @param mixed $tva7
     * @return $this
     */
    public function setTva7($tva7) {

        $this->tva7 = $tva7;
        return $this;
    }



    public function getCommission()
    {
        if ($this->getMultisite()) {
            $commission = $this->getSubscription()->getCommissionExternal();
        } elseif ($this->getOrderType()->getId() == 3) {
            $commission = $this->getSubscription()->getCommissionDelivery();
        } else {
            $commission = $this->getSubscription()->getCommission();
        }

        return $commission;
    }

    public function getTotalCommission()
    {
        if ($this->getSubscription() == null) {
            return;
        }

        $subCom = $this->getCommission();

        if ($this->getOnlinePayment() && !$this->getRestoflashTransaction()) {
            $commission = $subCom + $this->getSubscription()->getTransactionCommission();
        } else {
            $commission = $subCom;
        }

        return $commission;
    }

    public function getRestaurantDiscountPrice()
    {
        return $this->getCart()->getDiscountAmount();
    }

    public function getRestaurantCouponPrice()
    {
        $coupon = $this->getCart()->getCoupon();

        if ($coupon && $coupon->getIsBilledToClient()) {
            return $this->getCart()->getCouponAmount();
        }

        return 0;
    }

    public function getClickEatCouponPrice()
    {
        $coupon = $this->getCart()->getCoupon();

        if ($coupon && !$coupon->getIsBilledToClient()) {
            return $this->getCart()->getCouponAmount();
        }

        return 0;
    }

    public function getClickEatCommission()
    {
        $coupon = $this->getCart()->getCoupon();

        if ($coupon && !$coupon->getIsBilledToClient()) {
            $price = $this->getCart()->getDiscountPrice();
        } else {
            $price = $this->getCart()->getTotalPrice();
        }

        $gain = $price * ($this->getTotalCommission() / 100);

        return round($gain, 2);
    }

    public function getClickEatGain()
    {
        $gain = $this->getClickEatCommission() - $this->getClickEatCouponPrice();

        return round($gain, 2);
    }

    public function getRestaurantGain()
    {
        $gain = $this->getCart()->getTotalPrice() - $this->getClickEatCommission() + $this->getClickEatCouponPrice();
            //- $this->getRestaurantDiscountPrice() - $this->getRestaurantCouponPrice();

        return round($gain, 2);
    }

    public function getBalance()
    {
        if ($this->getOnlinePayment() && !$this->getRestoflashTransaction()) {
            return round($this->getRestaurantGain(), 2);
        } else {
            return round(-$this->getClickEatGain(), 2);
        }
    }

    public function getTaxesAmount()
    {
        $taxs = array();
        foreach ($this->getCart()->getElements() as $element) {
            if ($element->getTax()) {
                $tax = $element->getTax();
                if (isset($taxs[$tax->getName()])) {
                    $taxs[$tax->getName()] = round($taxs[$tax->getName()] + $tax->getValue() * $element->getPrice() * $element->getQuantity() / 100, 2);
                } else {
                    $taxs[$tax->getName()] = round($tax->getValue() * $element->getPrice() * $element->getQuantity() / 100, 2);
                }
            }

            //@todo change number_format => round
            foreach ($element->getChildrens() as $children) {
                if ($children->getTax()) {
                    $tax = $children->getTax();
                    if (isset($taxs[$tax->getName()])) {
                        $taxs[$tax->getName()] = number_format($taxs[$tax->getName()] + $tax->getValue() * $children->getPrice() * $children->getQuantity() / 100, 2);
                    } else {
                        $taxs[$tax->getName()] = number_format($tax->getValue() * $children->getPrice() * $children->getQuantity() / 100, 2);
                    }
                }
            }
        }

        $reduction = $this->getReduction() / 100;
        if ($reduction > 0) {
            foreach ($taxs as $key => $tax) {
                $taxs[$key] = round($tax - $tax * $reduction, 2);
            }
        }

        return $taxs;
    }

    public function getTotalTax()
    {
        $totalTax = 0;
        foreach ($this->getTaxesAmount() as $tax) {
            $totalTax += $tax;
        }

        return $totalTax;
    }

    public function directPaymentEnabled()
    {
        if ($this->getRestaurant() && $this->getCart()) {
            foreach ($this->getRestaurant()->getPaymentMethods() as $method) {
                if (!$method->getIsOnline()) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isTest()
    {
        return $this->getIsTest();
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
     * Set hash.
     *
     * @param string $hash
     *
     * @return OrderDetail
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set state.
     *
     * @param float $state
     *
     * @return OrderDetail
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state.
     *
     * @return float
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set onlinePayment.
     *
     * @param bool $onlinePayment
     *
     * @return OrderDetail
     */
    public function setOnlinePayment($onlinePayment)
    {
        $this->onlinePayment = $onlinePayment;

        return $this;
    }

    /**
     * Get onlinePayment.
     *
     * @return bool
     */
    public function getOnlinePayment()
    {
        return $this->onlinePayment;
    }

    /**
     * Set isPaid.
     *
     * @param bool $isPaid
     *
     * @return OrderDetail
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    /**
     * Get isPaid.
     *
     * @return bool
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return OrderDetail
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
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return OrderDetail
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
     * @return OrderDetail
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
     * Set time.
     *
     * @param \DateTime $time
     *
     * @return OrderDetail
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    /**
     * Get time.
     *
     * @return \DateTime
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return OrderDetail
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set isTest.
     *
     * @param bool $isTest
     *
     * @return OrderDetail
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;

        return $this;
    }

    /**
     * Get isTest.
     *
     * @return bool
     */
    public function getIsTest()
    {
        return $this->isTest;
    }

    /**
     * Set source.
     *
     * @param string $source
     *
     * @return OrderDetail
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set cart.
     *
     * @param \Clab\ShopBundle\Entity\Cart $cart
     *
     * @return OrderDetail
     */
    public function setCart(\Clab\ShopBundle\Entity\Cart $cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart.
     *
     * @return \Clab\ShopBundle\Entity\Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Set restaurant.
     *
     * @param \Clab\RestaurantBundle\Entity\Restaurant $restaurant
     *
     * @return OrderDetail
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
     * Set facebookPage.
     *
     * @param \Clab\SocialBundle\Entity\SocialFacebookPage $facebookPage
     *
     * @return OrderDetail
     */
    public function setFacebookPage(\Clab\SocialBundle\Entity\SocialFacebookPage $facebookPage = null)
    {
        $this->facebookPage = $facebookPage;

        return $this;
    }

    /**
     * Get facebookPage.
     *
     * @return \Clab\SocialBundle\Entity\SocialFacebookPage
     */
    public function getFacebookPage()
    {
        return $this->facebookPage;
    }

    /**
     * Set profile.
     *
     * @param \Clab\ShopBundle\Entity\Profile $profile
     *
     * @return OrderDetail
     */
    public function setProfile(User $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile.
     *
     * @return \Clab\UserBundle\Entity\User
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set orderType.
     *
     * @param \Clab\ShopBundle\Entity\OrderType $orderType
     *
     * @return OrderDetail
     */
    public function setOrderType(\Clab\ShopBundle\Entity\OrderType $orderType = null)
    {
        $this->orderType = $orderType;

        return $this;
    }

    /**
     * Get orderType.
     *
     * @return \Clab\ShopBundle\Entity\OrderType
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * Set orderStatement.
     *
     * @param \Clab\BoardBundle\Entity\OrderStatement $orderStatement
     *
     * @return OrderDetail
     */
    public function setOrderStatement(\Clab\BoardBundle\Entity\OrderStatement $orderStatement = null)
    {
        $this->orderStatement = $orderStatement;

        return $this;
    }

    /**
     * Get orderStatement.
     *
     * @return \Clab\BoardBundle\Entity\OrderStatement
     */
    public function getOrderStatement()
    {
        return $this->orderStatement;
    }

    /**
     * Set restoflashTransaction.
     *
     * @param \Clab\RestoflashBundle\Entity\RestoflashTransaction $restoflashTransaction
     *
     * @return OrderDetail
     */
    public function setRestoflashTransaction(\Clab\RestoflashBundle\Entity\RestoflashTransaction $restoflashTransaction = null)
    {
        $this->restoflashTransaction = $restoflashTransaction;

        return $this;
    }

    /**
     * Get restoflashTransaction.
     *
     * @return \Clab\RestoflashBundle\Entity\RestoflashTransaction
     */
    public function getRestoflashTransaction()
    {
        return $this->restoflashTransaction;
    }

    /**
     * Set delivery.
     *
     * @param \Clab\DeliveryBundle\Entity\Delivery $delivery
     *
     * @return OrderDetail
     */
    public function setDelivery(\Clab\DeliveryBundle\Entity\Delivery $delivery = null)
    {
        $this->delivery = $delivery;

        return $this;
    }

    /**
     * Get delivery.
     *
     * @return \Clab\DeliveryBundle\Entity\Delivery
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * Set profile.
     *
     * @param \Clab\BoardBundle\Entity\Company $company
     *
     * @return OrderDetail
     */
    public function setCompany(Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get Company.
     *
     * @return \Clab\BoardBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    public function getOnSitePayments()
    {
        return $this->onSitePayments;
    }

    public function setOnSitePayments(array $onSitePayments)
    {
        $this->onSitePayments = $onSitePayments;
    }

    public function addOnSitePayment($type, $amount)
    {
        if (array_key_exists($type, $this->onSitePayments)) {
           $this->onSitePayments[$type] = intval($amount);
        }

        return $this;
    }

    public function getPreparationState()
    {
        return $this->preparationState;
    }

    public function setPreparationState($preparationState)
    {
        $this->preparationState = $preparationState;

        return $this;
    }

    public function setCovers($covers)
    {
        $this->covers = $covers;

        return $this;
    }

    public function getCovers()
    {
        return $this->covers;
    }
}
