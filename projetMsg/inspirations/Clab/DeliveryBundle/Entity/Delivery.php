<?php

namespace Clab\DeliveryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_delivery_delivery")
 * @ORM\Entity(repositoryClass="Clab\DeliveryBundle\Entity\Repository\DeliveryRepository")
 */
class Delivery
{
    const DELIVERY_STATE_INITIAL = 0;
    const DELIVERY_STATE_WAITING_DELIVERYMAN = 10;
    const DELIVERY_STATE_IN_PROGRESS = 20;
    const DELIVERY_STATE_DONE = 30;
    const DELIVERY_STATE_CANCELLED = 50;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    private $is_online;

    /**
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    private $is_deleted;

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
     * @ORM\Column(name="state", type="integer")
     */
    private $state;

    /**
     * @ORM\Column(name="start", type="datetime", nullable=true)
     */
    private $start;

    /**
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    private $end;

    /**
     * @ORM\Column(name="latitude", type="string", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(name="longitude", type="string", nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(name="code", type="string", nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(name="codeCustomer", type="string", nullable=true)
     */
    private $codeCustomer;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(name="customerGrade", type="float", nullable=true)
     */
    private $customerGrade;

    /**
     * @ORM\Column(name="customerComment", type="text", nullable=true)
     */
    private $customerComment;

    /**
     * @ORM\Column(name="entry_code", type="string", nullable=true)
     */
    private $entryCode;

    /**
     * @ORM\Column(name="appartment_number", type="string", nullable=true)
     */
    private $appartmentNumber;

    /**
     * @ORM\OneToOne(targetEntity="\Clab\ShopBundle\Entity\OrderDetail", inversedBy="delivery")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", nullable=true)
     */
    private $order;

    /**
     * @ORM\OneToOne(targetEntity="\Clab\ShopBundle\Entity\OrderDetailCaisse", inversedBy="delivery")
     * @ORM\JoinColumn(name="order_caisse_id", referencedColumnName="id", nullable=true)
     */
    private $orderCaisse;

    /**
     * @ORM\ManyToOne(targetEntity="DeliveryMan", inversedBy="deliveries")
     * @ORM\JoinColumn(name="delivery_man_id", referencedColumnName="id")
     */
    private $deliveryMan;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    private $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address", cascade={"persist"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     */
    protected $address;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address")
     * @ORM\JoinColumn(name="pickup_address_id", referencedColumnName="id", nullable=true)
     */
    protected $pickupAddress;
    protected $customer;
    protected $deliveryTime;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->setState(self::DELIVERY_STATE_INITIAL);
    }

    public function isAvailable()
    {
        return ($this->isOnline() && !$this->isDeleted()) ? true : false;
    }

    public function remove()
    {
        $this->setIsOnline(false);
        $this->setIsDeleted(true);
    }

    public function setIsOnline($isOnline)
    {
        $this->is_online = $isOnline;

        return $this;
    }

    public function getIsOnline()
    {
        return $this->is_online;
    }

    public function isOnline()
    {
        return $this->getIsOnline();
    }

    public function setIsDeleted($isDeleted)
    {
        $this->is_deleted = $isDeleted;

        return $this;
    }

    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    public function isDeleted()
    {
        return $this->getIsDeleted();
    }

    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated()
    {
        return $this->updated;
    }

    public function isAllowed(\Clab\UserBundle\Entity\User $user)
    {
        return $this->getRestaurant()->isAllowed($user);
    }

    public function getCustomer()
    {
        if ($this->getOrder() && $this->getOrder()->getProfile()) {
            return array(
                'cover' => $this->getOrder()->getProfile()->getCover(),
                'name' => $this->getOrder()->getProfile()->getFullname(),
                'phone' => $this->getOrder()->getProfile()->getPhone(),
            );
        }

        return;
    }

    public function getDeliveryTime()
    {
        if ($this->getStart() && $this->getEnd()) {
            $diff = date_diff($this->getEnd(), $this->getStart());
            $time = floor(($diff->i > 0) ? $diff->i / 2 : 0);

            $date = clone($this->getStart());

            return $date->modify('+'.$time.' minute');
        }

        return $this->getEnd();
    }

    /**
     * @return mixed
     */
    public function getEntryCode()
    {
        return $this->entryCode;
    }

    /**
     * @param mixed $entryCode
     *
     * @return $this
     */
    public function setEntryCode($entryCode)
    {
        $this->entryCode = $entryCode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAppartmentNumber()
    {
        return $this->appartmentNumber;
    }

    /**
     * @param mixed $appartmentNumber
     *
     * @return $this
     */
    public function setAppartmentNumber($appartmentNumber)
    {
        $this->appartmentNumber = $appartmentNumber;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function getDeliveryMan()
    {
        return $this->deliveryMan;
    }

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCodeCustomer($codeCustomer)
    {
        $this->codeCustomer = $codeCustomer;

        return $this;
    }

    public function getCodeCustomer()
    {
        return $this->codeCustomer;
    }

    public function setCustomerGrade($customerGrade)
    {
        $this->customerGrade = $customerGrade;

        return $this;
    }

    public function getCustomerGrade()
    {
        return $this->customerGrade;
    }

    public function setCustomerComment($customerComment)
    {
        $this->customerComment = $customerComment;

        return $this;
    }

    public function getCustomerComment()
    {
        return $this->customerComment;
    }

    public function setOrder(\Clab\ShopBundle\Entity\OrderDetail $order = null)
    {
        $this->order = $order;

        return $this;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getRestaurant()
    {
        return $this->restaurant;
    }

    public function setDeliveryMan(\Clab\DeliveryBundle\Entity\DeliveryMan $deliveryMan = null)
    {
        $this->deliveryMan = $deliveryMan;

        return $this;
    }

    public function setAddress(\Clab\LocationBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function setPickupAddress(\Clab\LocationBundle\Entity\Address $pickupAddress = null)
    {
        $this->pickupAddress = $pickupAddress;

        return $this;
    }

    public function getPickupAddress()
    {
        return $this->pickupAddress;
    }

    public function getOrderCaisse()
    {
        return $this->orderCaisse;
    }

    public function setOrderCaisse($orderCaisse)
    {
        $this->orderCaisse = $orderCaisse;
    }
}
