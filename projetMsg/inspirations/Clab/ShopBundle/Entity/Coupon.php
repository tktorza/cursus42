<?php

namespace Clab\ShopBundle\Entity;

use Clab\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_shop_coupon")
 * @ORM\Entity(repositoryClass="Clab\ShopBundle\Repository\CouponRepository")
 */
class Coupon
{
    const COUPON_PLATFORM_CLICKEAT = 0;
    const COUPON_PLATFORM_CAISSE = 10;
    const COUPON_PLATFORM_BOTH = 20;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @ORM\Column(name="is_online", type="boolean")
     */
    protected $isOnline;

    /**
     * @ORM\Column(name="is_billed_to_client", type="boolean")
     */
    protected $isBilledToClient;

    /**
     * @ORM\Column(name="is_unique_usage", type="boolean")
     */
    protected $isUniqueUsage;

    /**
     * @ORM\Column(name="startDay", type="date", nullable=true)
     */
    protected $startDay;

    /**
     * @ORM\Column(name="endDay", type="date", nullable=true)
     */
    protected $endDay;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Restaurant", inversedBy="coupons")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id", nullable=true)
     */
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id", nullable=true)
     */
    protected $profile;

    /**
     * @ORM\Column(name="amount", type="float", nullable=true)
     */
    protected $amount;

    /**
     * @ORM\Column(name="percent", type="float", nullable=true)
     */
    protected $percent;

    /**
     * @ORM\Column(name="maxForPercent", type="float", nullable=true)
     */
    protected $maxForPercent;

    /**
     * @ORM\Column(name="quantity", type="float", nullable=true)
     */
    protected $quantity;

    /**
     * @ORM\Column(name="unlimited", type="boolean")
     */
    protected $unlimited;

    /**
     * @ORM\Column(name="platform", type="integer", nullable=true)
     */
    protected $platform;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\UserBundle\Entity\User")
     * @ORM\JoinTable(name="users_coupons",
     *      joinColumns={@ORM\JoinColumn(name="coupon_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     *      )
     */
    private $usedBy;

    protected $verbose;
    protected $managerVerbose;

    public function isAllowed(\Clab\UserBundle\Entity\User $user)
    {
        return true;
    }

    public function __construct()
    {
        $this->setQuantity(0);
        $this->setUnlimited(true);
        $this->setIsBilledToClient(false);
        $this->setIsOnline(true);
        $this->usedBy = new ArrayCollection();
    }

    public function isAvailable()
    {
        if (!$this->getIsOnline()) {
            return false;
        }

        if (!$this->getUnlimited() && !$this->getQuantity() > 0) {
            return false;
        }

        $now = new \DateTime('now');
        if (!$this->getStartDay() && $this->getStartDay() > $now) {
            return false;
        }

        if (!$this->getEndDay() && $this->getEndDay() < $now) {
            return false;
        }

        if (!$this->getAmount() && !$this->getPercent()) {
            return false;
        }

        return true;
    }

    public function isAvailableForCart(Cart $cart, $multisite = false)
    {
        if (!$this->isAvailable()) {
            return false;
        }

        if ($this->getRestaurant() && $this->getRestaurant()->getSlug() !== $cart->getRestaurant()->getSlug()) {
            return false;
        }

        if ($multisite && (!$this->getIsBilledToClient() || !$this->getRestaurant())) {
            return false;
        }

        if ($this->getIsUniqueUsage() == true) {
        }

        return true;
    }

    public function isAvailableForUser(User $user)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        if ($this->getIsUniqueUsage() == true && $this->getUsedBy()->contains($user)) {
            return false;
        }

        return true;
    }

    public function getDiscount($price)
    {
        if ($this->getAmount()) {
            if ($this->getAmount() > $price) {
                return $price;
            }

            return $this->getAmount();
        } elseif ($this->getPercent()) {
            if ($this->getMaxForPercent() && $price > $this->getMaxForPercent()) {
                return $this->getMaxForPercent() * $this->getPercent() / 100;
            } else {
                return $price * $this->getPercent() / 100;
            }
        }

        return 0;
    }

    public function verbose()
    {
        if ($this->getAmount()) {
            return '- '.$this->getAmount().'€';
        } elseif ($this->getPercent()) {
            return '- '.$this->getPercent().'%';
        } else {
            return '';
        }
    }

    public function getManagerVerbose()
    {
        if ($this->getIsBilledToClient()) {
            $billedBy = 'le restaurant';
        } else {
            $billedBy = 'Clickeat';
        }

        return 'Coupon de '.$this->verbose().' (offert par '.$billedBy.')';
    }

    /**
     * @return mixed
     */
    public function getIsUniqueUsage()
    {
        return $this->isUniqueUsage;
    }

    /**
     * @param mixed $isUniqueUsage
     *
     * @return $this
     */
    public function setIsUniqueUsage($isUniqueUsage)
    {
        $this->isUniqueUsage = $isUniqueUsage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsedBy()
    {
        return $this->usedBy;
    }

    /**
     * Add friendsWithMe.
     *
     * @return User
     */
    public function addUsedBy(User $usedby)
    {
        $this->usedBy[] = $usedby;

        return $this;
    }
    /**
     * Remove friendsWithMe.
     */
    public function removeUsedBy(User $usedby)
    {
        $this->usedBy->removeElement($usedby);
    }

    public function getId()
    {
        return $this->id;
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

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * @return mixed
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param mixed $platform
     *
     * @return $this
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    public function setStartDay($startDay)
    {
        $this->startDay = $startDay;

        return $this;
    }

    public function getStartDay()
    {
        return $this->startDay;
    }

    public function setEndDay($endDay)
    {
        $this->endDay = $endDay;

        return $this;
    }

    public function getEndDay()
    {
        return $this->endDay;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    public function getPercent()
    {
        return $this->percent;
    }

    public function setMaxForPercent($maxForPercent)
    {
        $this->maxForPercent = $maxForPercent;

        return $this;
    }

    public function getMaxForPercent()
    {
        return $this->maxForPercent;
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

    public function setProfile(User $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    public function getProfile()
    {
        return $this->profile;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setUnlimited($unlimited)
    {
        $this->unlimited = $unlimited;

        return $this;
    }

    public function getUnlimited()
    {
        return $this->unlimited;
    }

    public function setIsBilledToClient($isBilledToClient)
    {
        $this->isBilledToClient = $isBilledToClient;

        return $this;
    }

    public function getIsBilledToClient()
    {
        return $this->isBilledToClient;
    }
}
