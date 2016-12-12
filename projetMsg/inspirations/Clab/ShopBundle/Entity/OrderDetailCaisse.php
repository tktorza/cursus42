<?php

namespace Clab\ShopBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="caisse_shop_orderdetails")
 * @ORM\Entity(repositoryClass="Clab\ShopBundle\Repository\OrderDetailRepository")
 */
class OrderDetailCaisse
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

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="reference", type="string", nullable=true)
     */
    private $reference;

    /**
     * @ORM\Column(name="state", type="float")
     */
    private $state;

    /**
     * @ORM\Column(name="is_paid", type="boolean", nullable=false)
     */
    private $isPaid;

    /**
     * @ORM\Column(name="price", type="float")
     */
    private $price;

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
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(name="cancel_comment", type="text", nullable=true)
     */
    private $cancelComment;

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
     * @ORM\Column(name="is_test", type="boolean", nullable=true)
     */
    private $isTest;

    /**
     * @ORM\Column(name="source", type="string", nullable=true)
     */
    private $source = "caisse";

    /**
     * @ORM\Column(name="order_type", type="string", nullable=true)
     */
    protected $orderType;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", cascade={"all"})
     */
    protected $cart;

    /**
     * @ORM\OneToOne(targetEntity="Clab\DeliveryBundle\Entity\Delivery", mappedBy="orderCaisse", cascade={"persist", "remove"})
     */
    protected $delivery;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\Pincode")
     * @ORM\JoinColumn(name="pin_id", referencedColumnName="id")
     */
    private $pin;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", inversedBy="orders")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id", nullable=true)
     */
    protected $restaurant;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\ShopBundle\Entity\Payment")
     * @ORM\JoinTable(name="payments_order",
     *      joinColumns={@ORM\JoinColumn(name="orderdetail_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="payment_id", referencedColumnName="id")}
     *      )
     */
    private $payments;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User", inversedBy="orders", fetch="EAGER")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     */
    protected $profile;

    /**
     * @ORM\Column(type="datetime")
     */
    private $time;

    /**
     * number of covers served
     * @ORM\Column(name="covers", type="integer", nullable=true)
     */
    private $covers;

    public function __construct()
    {
        $this->setState(self::ORDER_STATE_INITIAL);
        $this->setIsPaid(false);
        $this->setIsTest(false);
        $this->setSource('caisse');
        $this->setTva10(0);
        $this->setTva55(0);
        $this->setTva7(0);
        $this->setTva20(0);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->payments = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getTva20()
    {
        return $this->tva20;
    }

    /**
     * @param mixed $tva20
     *
     * @return $this
     */
    public function setTva20($tva20)
    {
        $this->tva20 = $tva20;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTva10()
    {
        return $this->tva10;
    }

    /**
     * @param mixed $tva10
     *
     * @return $this
     */
    public function setTva10($tva10)
    {
        $this->tva10 = $tva10;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTva55()
    {
        return $this->tva55;
    }

    /**
     * @param mixed $tva55
     *
     * @return $this
     */
    public function setTva55($tva55)
    {
        $this->tva55 = $tva55;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTva7()
    {
        return $this->tva7;
    }

    /**
     * @param mixed $tva7
     *
     * @return $this
     */
    public function setTva7($tva7)
    {
        $this->tva7 = $tva7;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPin()
    {
        return $this->pin;
    }

    /**
     * @param mixed $pin
     *
     * @return $this
     */
    public function setPin($pin)
    {
        $this->pin = $pin;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCancelComment()
    {
        return $this->cancelComment;
    }

    /**
     * @param mixed $cancelComment
     *
     * @return $this
     */
    public function setCancelComment($cancelComment)
    {
        $this->cancelComment = $cancelComment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param mixed $payments
     *
     * @return $this
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;

        return $this;
    }

    public function __toString()
    {
        return (string) $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param mixed $time
     *
     * @return $this
     */
    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    public function addPayment(Payment $payment)
    {
        $this->payments[] = $payment;
    }

    public function removePayment(Payment $payment)
    {
        $this->payments->removeElement($payment);
    }

    /**
     * @return mixed
     */
    public function getPayment()
    {
        return $this->payments;
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
     * @return mixed
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * @param mixed $reference
     *
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     *
     * @return $this
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsPaid()
    {
        return $this->isPaid;
    }

    /**
     * @param mixed $isPaid
     *
     * @return $this
     */
    public function setIsPaid($isPaid)
    {
        $this->isPaid = $isPaid;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     *
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     *
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param mixed $comment
     *
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsTest()
    {
        return $this->isTest;
    }

    /**
     * @param mixed $isTest
     *
     * @return $this
     */
    public function setIsTest($isTest)
    {
        $this->isTest = $isTest;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     *
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * @param mixed $orderType
     *
     * @return $this
     */
    public function setOrderType($orderType)
    {
        $this->orderType = $orderType;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @param mixed $cart
     *
     * @return $this
     */
    public function setCart($cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }

    /**
     * @param mixed $restaurant
     *
     * @return $this
     */
    public function setRestaurant($restaurant)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     *
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }

    public function getDelivery()
    {
        return $this->delivery;
    }

    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
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
