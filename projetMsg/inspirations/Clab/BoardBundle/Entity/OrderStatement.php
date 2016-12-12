<?php

namespace Clab\BoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="admin_order_statement")
 * @ORM\Entity(repositoryClass="Clab\BoardBundle\Entity\OrderStatementRepository")
 */
class OrderStatement
{
    const ORDERSTATEMENT_STATUS_NEW = 10;
    const ORDERSTATEMENT_STATUS_HOLD = 20;
    const ORDERSTATEMENT_STATUS_LOCKED = 30;
    const ORDERSTATEMENT_STATUS_CLOSED = 100;

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
     * @ORM\Column(type="date")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date")
     */
    private $endDate;

    /**
     * @ORM\Column(name="tax", type="float")
     */
    private $tax;

    /**
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @ORM\Column(name="price", type="float", nullable=true)
     */
    private $price;

    /**
     * @ORM\Column(name="balance", type="float", nullable=true)
     */
    private $balance;

    /**
     * @ORM\Column(name="cumulatedBalance", type="float", nullable=true)
     */
    private $cumulatedBalance;

    /**
     * @ORM\Column(name="adminValidation", type="boolean")
     */
    private $adminValidation;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Restaurant", inversedBy="orderStatements")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id", nullable=true)
     */
    protected $restaurant;

    /**
     * @ORM\ManyToMany(targetEntity="OrderStatement", inversedBy="childrens")
     * @ORM\JoinTable(name="clickeat_admin_order_statement_parents",
     *                joinColumns={@ORM\JoinColumn(name="children_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="parent_id", referencedColumnName="id")})
     */
    protected $parents;

    /**
     * @ORM\ManyToMany(targetEntity="OrderStatement", mappedBy="parents")
     */
    protected $childrens;

    /**
     * @ORM\OneToMany(targetEntity="Clab\ShopBundle\Entity\OrderDetail", mappedBy="orderStatement")
     */
    protected $orders;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->setTax(0.20);
        $this->setAdminValidation(false);
        $this->setStatus(self::ORDERSTATEMENT_STATUS_NEW);

        $this->orders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->parents = new \Doctrine\Common\Collections\ArrayCollection();
        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
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

    // ??
    public function getTotalPrice()
    {
        $price = 0;

        foreach ($this->getOrders() as $order) {
            $price = $price + $order->getPrice();
        }

        return $price;
    }

    public function getInfos()
    {
        $totalOnline = 0;
        $countOnline = 0;
        $totalOffline = 0;
        $countOffline = 0;
        $commissions = array();
        $taxs = array();
        $coupons = array();
        $discounts = array();
        $totalClick = 0;

        foreach ($this->getOrders() as $order) {
            // orders
            if ($order->getOnlinePayment() && !$order->getRestoflashTransaction()) {
                ++$countOnline;
                $totalOnline += $order->getCart()->getTotalPrice();
            } else {
                ++$countOffline;
                $totalOffline += $order->getCart()->getTotalPrice();
            }

            $totalClick += $order->getClickEatCommission();

            // commision
            if (isset($commissions[$order->getTotalCommission()])) {
                $commissions[$order->getTotalCommission()] = $commissions[$order->getTotalCommission()] + $order->getClickEatCommission();
            } else {
                $commissions[$order->getTotalCommission()] = $order->getClickEatCommission();
            }

            // taxs
            $orderTaxs = $order->getTaxesAmount();

            foreach ($orderTaxs as $key => $tax) {
                if (isset($taxs[$key])) {
                    $taxs[$key] = $taxs[$key] + $tax;
                } else {
                    $taxs[$key] = $tax;
                }
            }

            // coupons
            $restaurantCouponPrice = $order->getRestaurantCouponPrice();
            if ($restaurantCouponPrice > 0) {
                $coupon = $order->getCart()->getCoupon();
                if (isset($coupons[$coupon->getName()])) {
                    $coupons[$coupon->getName()] = $coupons[$coupon->getName()] + $restaurantCouponPrice;
                } else {
                    $coupons[$coupon->getName()] = $restaurantCouponPrice;
                }
            }

            // discounts
            $restaurantDiscountPrice = $order->getRestaurantDiscountPrice();
            if ($restaurantDiscountPrice > 0) {
                $discount = $order->getCart()->getDiscount();
                if (isset($discounts[$discount->getName()])) {
                    $discounts[$discount->getName()] = $discounts[$discount->getName()] + $restaurantDiscountPrice;
                } else {
                    $discounts[$discount->getName()] = $restaurantDiscountPrice;
                }
            }
        }

        return array(
            'totalOnline' => round($totalOnline, 2), 'countOnline' => $countOnline,
            'totalOffline' => round($totalOffline, 2), 'countOffline' => $countOffline,
            'commissions' => $commissions, 'totalClick' => round($totalClick, 2),
            'taxes' => $taxs, 'coupons' => $coupons, 'discounts' => $discounts,
        );
    }

    /**
     * @param mixed $balance
     *
     * @return $this
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    /**
     * @param mixed $cumulatedBalance
     *
     * @return $this
     */
    public function setCumulatedBalance($cumulatedBalance)
    {
        $this->cumulatedBalance = $cumulatedBalance;

        return $this;
    }

    public function getBalance()
    {
        if (!$this->getPrice()) {
            $infos = $this->getInfos();
            $balance = $infos['totalClick'] + $infos['totalClick'] * $this->getTax() - $infos['totalOnline'];
            $this->setPrice(round($balance, 2));
        }

        return $this->getPrice();
    }

    public function getParentBalance()
    {
        $balance = 0;

        foreach ($this->getParents() as $parent) {
            $balance = $balance + $parent->getBalance();
        }

        return $balance;
    }

    public function getCumulatedBalance()
    {
        return $this->getParentBalance() + $this->getBalance();
    }

    public function verboseStatus()
    {
        switch ($this->getStatus()) {
            case self::ORDERSTATEMENT_STATUS_NEW:
                return 'Nouvelle';
                break;
            case self::ORDERSTATEMENT_STATUS_HOLD:
                return 'Cagnotte';
                break;
            case self::ORDERSTATEMENT_STATUS_LOCKED:
                return 'Lockée';
                break;
            case self::ORDERSTATEMENT_STATUS_CLOSED:
                return 'Cloturée';
                break;
            default:
                break;
        }
    }

    public static function getStatusChoices()
    {
        return array(
            self::ORDERSTATEMENT_STATUS_NEW => 'Nouvelle',
            self::ORDERSTATEMENT_STATUS_HOLD => 'Cagnotte',
            self::ORDERSTATEMENT_STATUS_LOCKED => 'Lockée',
            self::ORDERSTATEMENT_STATUS_CLOSED => 'Cloturée',
        );
    }

    public function getId()
    {
        return $this->id;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    public function getPrice()
    {
        return $this->price;
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

    public function addOrder(\Clab\ShopBundle\Entity\OrderDetail $orders)
    {
        $this->orders[] = $orders;

        return $this;
    }

    public function removeOrder(\Clab\ShopBundle\Entity\OrderDetail $orders)
    {
        $this->orders->removeElement($orders);
    }

    public function getOrders()
    {
        return $this->orders;
    }

    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    public function getTax()
    {
        return $this->tax;
    }

    public function setAdminValidation($adminValidation)
    {
        $this->adminValidation = $adminValidation;

        return $this;
    }

    public function getAdminValidation()
    {
        return $this->adminValidation;
    }

    public function addParent(\Clab\BoardBundle\Entity\OrderStatement $parent)
    {
        if (!$this->getParents()->contains($parent)) {
            $this->parents[] = $parent;
        }

        return $this;
    }

    public function removeParent(\Clab\BoardBundle\Entity\OrderStatement $parent)
    {
        if ($this->getParents()->contains($parent)) {
            $this->parents->removeElement($parent);
        }
    }

    public function getParents()
    {
        return $this->parents;
    }

    public function addChildren(\Clab\BoardBundle\Entity\OrderStatement $children)
    {
        if (!$this->getChildrens()->contains($children)) {
            $this->childrens[] = $children;
        }

        return $this;
    }

    public function removeChildren(\Clab\BoardBundle\Entity\OrderStatement $children)
    {
        if ($this->getChildrens()->contains($children)) {
            $this->childrens->removeElement($children);
        }
    }

    public function getChildrens()
    {
        return $this->childrens;
    }
}
