<?php

namespace Clab\ShopBundle\Entity;

use Clab\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Loyalty
 *
 * @ORM\Table(name="clickeat_shop_loyalty")
 * @ORM\Entity(repositoryClass="Clab\ShopBundle\Repository\LoyaltyRepository")
 */
class Loyalty
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="fastmag_id", type="integer", nullable=true)
     */
    protected $fastmagId;

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
     * @var \DateTime
     *
     * @ORM\Column(name="validUntil", type="datetime")
     */
    private $validUntil;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isUsed", type="boolean")
     */
    private $isUsed;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float", nullable=true)
     */
    private $value;

    /**
     * @ORM\Column(name="orderType", type="integer", nullable=true)
     */
    protected $orderType;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isCombinable", type="boolean")
     */
    private $isCombinable;

    /**
     * @var boolean
     *
     * @ORM\Column(name="isRefreshed", type="boolean")
     */
    private $isRefreshed;

    /**
     * @var float
     *
     * @ORM\Column(name="minimumOrder", type="float")
     */
    private $minimumOrder;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User", inversedBy="loyalties", fetch="EAGER", cascade={"persist"})
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="loyalties", cascade={"all"})
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id")
     */
    protected $cart;

    /**
     * @var string
     *
     * @ORM\Column(name="barCode", type="string", nullable=true, length=255)
     */
    private $barCode;

    public function __construct()
    {
        $this->setOrderType(null);
        $this->setIsRefreshed(false);
        $this->setIsUsed(false);
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function getFastmagId()
    {
        return $this->fastmagId;
    }

    public function setFastmagId($fastmagId)
    {
        $this->fastmagId = $fastmagId;

        return $this;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Loyalty
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Loyalty
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set isUsed
     *
     * @param boolean $isUsed
     *
     * @return Loyalty
     */
    public function setIsUsed($isUsed)
    {
        $this->isUsed = $isUsed;

        return $this;
    }

    /**
     * Get isUsed
     *
     * @return boolean
     */
    public function getIsUsed()
    {
        return $this->isUsed;
    }

    /**
     * Set value
     *
     * @param float $value
     *
     * @return Loyalty
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set orderType.
     *
     * IF null => GIFTCARDSURVENTE
     * const ORDERTYPE_PREORDER = 1 => FID_EMP
     * const ORDERTYPE_DELIVERY = 3 => FID_LIV
     * const ORDERTYPE_ONSITE = 4 => FID_SPL
     *
     * @param integer
     *
     * @return Loyalty
     */
    public function setOrderType($orderType = null)
    {
        $this->orderType = $orderType;

        return $this;
    }

    /**
     * Get orderType.
     *
     * @return integer
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * Set isCombinable
     *
     * @param boolean $isCombinable
     *
     * @return Loyalty
     */
    public function setIsCombinable($isCombinable)
    {
        $this->isCombinable = $isCombinable;

        return $this;
    }

    /**
     * Get isCombinable
     *
     * @return boolean
     */
    public function getIsCombinable()
    {
        return $this->isCombinable;
    }

    /**
     * Set isRefreshed
     *
     * @param boolean $isRefreshed
     *
     * @return Loyalty
     */
    public function setIsRefreshed($isRefreshed)
    {
        $this->isRefreshed = $isRefreshed;

        return $this;
    }

    /**
     * Get isRefreshed
     *
     * @return boolean
     */
    public function getIsRefreshed()
    {
        return $this->isRefreshed;
    }

    /**
     * Set minimumOrder
     *
     * @param float $minimumOrder
     *
     * @return Loyalty
     */
    public function setMinimumOrder($minimumOrder)
    {
        $this->minimumOrder = $minimumOrder;

        return $this;
    }

    /**
     * Get minimumOrder
     *
     * @return float
     */
    public function getMinimumOrder()
    {
        return $this->minimumOrder;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser( User $user)
    {
        $this->user = $user;

        $user->addLoyalty($this);

        return $this;
    }

    public function getValidUntil()
    {
        return $this->validUntil;
    }

    public function setValidUntil(\DateTime $validUntil)
    {
        $this->validUntil = $validUntil;

        return $this;
    }

    public function updateValidity($time)
    {
        $this->validUntil->modify($time);

        return $this;
    }

    /**
     * Set cart.
     *
     * @param \Clab\ShopBundle\Entity\Cart $cart
     *
     * @return CartElement
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
     * @return string
     */
    public function getBarCode()
    {
        return $this->barCode;
    }

    /**
     * @param string $barCode
     */
    public function setBarCode($barCode)
    {
        $this->barCode = $barCode;
    }
}