<?php

namespace Clab\BoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_subscription")
 * @ORM\Entity()
 */
class Subscription
{
    const SUBSCRIPTION_TYPE_PLAN = 0;
    const SUBSCRIPTION_TYPE_APP = 10;

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
     * @ORM\Column(name="next_due_date",type="datetime")
     */
    private $nextDueDate;

    /**
     * @ORM\Column(name="commission", type="float")
     */
    private $commission;

    /**
     * @ORM\Column(name="external_commission", type="float")
     */
    private $commissionExternal;

    /**
     * @ORM\Column(name="transaction_commission", type="float")
     */
    private $transactionCommission;

    /**
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", inversedBy="subscriptions", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    /**
     * @ORM\Column(name="stripe_subscription_id", type="string", nullable=true)
     */
    protected $stripeSubscriptionId;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Plan", inversedBy="subscriptions")
     * @ORM\JoinColumn(name="plan_id", referencedColumnName="id")
     */
    private $plan;



    public function __toString()
    {
        return (string) $this->getId();
    }

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->setTransactionCommission(2);
        $this->setCommission(9);
        $this->setCommissionExternal(9);
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNextDueDate()
    {
        return $this->nextDueDate;
    }

    /**
     * @param mixed $nextDueDate
     *
     * @return $this
     */
    public function setNextDueDate($nextDueDate)
    {
        $this->nextDueDate = $nextDueDate;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * @param mixed $plan
     *
     * @return $this
     */
    public function setPlan($plan)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
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
    public function getIsOnline()
    {
        return $this->is_online;
    }

    /**
     * @param mixed $is_online
     *
     * @return $this
     */
    public function setIsOnline($is_online)
    {
        $this->is_online = $is_online;

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
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * @param mixed $commission
     *
     * @return $this
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommissionExternal()
    {
        return $this->commissionExternal;
    }

    /**
     * @param mixed $commissionExternal
     *
     * @return $this
     */
    public function setCommissionExternal($commissionExternal)
    {
        $this->commissionExternal = $commissionExternal;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTransactionCommission()
    {
        return $this->transactionCommission;
    }

    /**
     * @param mixed $transactionCommission
     *
     * @return $this
     */
    public function setTransactionCommission($transactionCommission)
    {
        $this->transactionCommission = $transactionCommission;

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
    public function getStripeSubscriptionId()
    {
        return $this->stripeSubscriptionId;
    }

    /**
     * @param mixed $stripeSubscriptionId
     *
     * @return $this
     */
    public function setStripeSubscriptionId($stripeSubscriptionId)
    {
        $this->stripeSubscriptionId = $stripeSubscriptionId;

        return $this;
    }
}
