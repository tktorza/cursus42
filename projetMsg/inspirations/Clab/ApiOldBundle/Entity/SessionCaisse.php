<?php

namespace Clab\ApiOldBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_old_caisse_session")
 * @ORM\Entity()
 */
class SessionCaisse
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @ORM\Column(name="date_start", type="datetime", nullable = true)
     */
    protected $dateStart;

    /**
     * @ORM\Column(name="date_end", type="datetime", nullable = true)
     */
    protected $dateEnd;

    /**
     * @ORM\Column(name="cash_flow_end", type="float", nullable = true)
     */
    protected $cashFlowEnd;

    /**
     * @ORM\Column(name="cash_flow_diff", type="float", nullable = true)
     */
    protected $cashFlowDiff;

    /**
     * @ORM\Column(name="cash_flow_end_theoric", type="float", nullable = true)
     */
    protected $cashFlowEndTheoric;

    /**
     * @ORM\Column(name="in_out", type="float", nullable = true)
     */
    protected $inOut;

    /**
     * @ORM\Column(name="cash_refund", type="float", nullable = true)
     */
    protected $cashRefund;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    private $restaurant;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param mixed $dateStart
     *
     * @return $this
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param mixed $dateEnd
     *
     * @return $this
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

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
    public function getCashFlowStart()
    {
        return $this->cashFlowStart;
    }

    /**
     * @param mixed $cashFlowStart
     *
     * @return $this
     */
    public function setCashFlowStart($cashFlowStart)
    {
        $this->cashFlowStart = $cashFlowStart;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCashFlowEnd()
    {
        return $this->cashFlowEnd;
    }

    /**
     * @param mixed $cashFlowEnd
     *
     * @return $this
     */
    public function setCashFlowEnd($cashFlowEnd)
    {
        $this->cashFlowEnd = $cashFlowEnd;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCashFlowDiff()
    {
        return $this->cashFlowDiff;
    }

    /**
     * @param mixed $cashFlowDiff
     *
     * @return $this
     */
    public function setCashFlowDiff($cashFlowDiff)
    {
        $this->cashFlowDiff = $cashFlowDiff;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCashFlowEndTheoric()
    {
        return $this->cashFlowEndTheoric;
    }

    /**
     * @param mixed $cashFlowEndTheoric
     *
     * @return $this
     */
    public function setCashFlowEndTheoric($cashFlowEndTheoric)
    {
        $this->cashFlowEndTheoric = $cashFlowEndTheoric;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInOut()
    {
        return $this->inOut;
    }

    /**
     * @param mixed $inOut
     *
     * @return $this
     */
    public function setInOut($inOut)
    {
        $this->inOut = $inOut;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCashRefund()
    {
        return $this->cashRefund;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setCashRefund($cashRefund)
    {
        $this->cashRefund = $cashRefund;

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
}
