<?php

namespace Clab\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * LoyaltyConfig
 *
 * @ORM\Table(name="clickeat_shop_loyalty_config")
 * @ORM\Entity(repositoryClass="Clab\ShopBundle\Repository\LoyaltyConfigRepository")
 */
class LoyaltyConfig
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
     * @var float
     *
     * @ORM\Column(name="minimumOrder", type="float", nullable=true)
     */
    private $minimumOrder;

    /**
     * @var float
     *
     * @ORM\Column(name="percentageOfOrder", type="float", nullable=true)
     */
    private $percentageOfOrder;

    /**
     * @var integer
     *
     * @ORM\Column(name="validityPeriod", type="integer", nullable=true)
     */
    private $validityPeriod;

    /**
     * @var integer
     *
     * @ORM\Column(name="firstValidityPeriod", type="integer", nullable=true)
     */
    private $firstValidityPeriod;

    /**
     * @var integer
     *
     * @ORM\Column(name="refreshPeriod", type="integer", nullable=true)
     */
    private $refreshPeriod;

    /**
     * @var float
     *
     * @ORM\Column(name="minimumValue", type="float")
     */
    private $minValue;

    /**
     * @var float
     *
     * @ORM\Column(name="maximumValue", type="float", nullable=true)
     */
    private $maxValue;

    /**
     * @var float
     *
     * @ORM\Column(name="roundRatio", type="float")
     */
    private $roundRatio;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return LoyaltyConfig
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
     * @return LoyaltyConfig
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
     * Set minimumOrder
     *
     * @param float $minimumOrder
     *
     * @return LoyaltyConfig
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

    /**
     * Set percentageOfOrder
     *
     * @param float $percentageOfOrder
     *
     * @return LoyaltyConfig
     */
    public function setPercentageOfOrder($percentageOfOrder)
    {
        $this->percentageOfOrder = $percentageOfOrder;

        return $this;
    }

    /**
     * Get percentageOfOrder
     *
     * @return float
     */
    public function getPercentageOfOrder()
    {
        return $this->percentageOfOrder;
    }

    /**
     * Set validityPeriod
     *
     * @param integer $validityPeriod
     *
     * @return LoyaltyConfig
     */
    public function setValidityPeriod($validityPeriod)
    {
        $this->validityPeriod = $validityPeriod;

        return $this;
    }

    /**
     * Get validityPeriod
     *
     * @return integer
     */
    public function getValidityPeriod()
    {
        return $this->validityPeriod;
    }

    /**
     * Set firstvalidityPeriod
     *
     * @param integer $firstValidityPeriod
     *
     * @return LoyaltyConfig
     */
    public function setFirstValidityPeriod($firstValidityPeriod)
    {
        $this->firstValidityPeriod = $firstValidityPeriod;

        return $this;
    }

    /**
     * Get firstValidityPeriod
     *
     * @return integer
     */
    public function getFirstValidityPeriod()
    {
        return $this->firstValidityPeriod;
    }

    /**
     * Set minValue
     *
     * @param float $minValue
     *
     * @return LoyaltyConfig
     */
    public function setMinValue($minValue)
    {
        $this->minValue = $minValue;

        return $this;
    }

    /**
     * Get minValue
     *
     * @return float
     */
    public function getMinValue()
    {
        return $this->minValue;
    }

    /**
     * Set maxValue
     *
     * @param float $maxValue
     *
     * @return LoyaltyConfig
     */
    public function setMaxValue($maxValue)
    {
        $this->maxValue = $maxValue;

        return $this;
    }

    /**
     * Get maxValue
     *
     * @return float
     */
    public function getMaxValue()
    {
        return $this->maxValue;
    }

    /**
     * Set roundRatio
     *
     * @param float $roundRatio
     *
     * @return LoyaltyConfig
     */
    public function setRoundRatio($roundRatio)
    {
        $this->roundRatio = $roundRatio;

        return $this;
    }

    /**
     * Get roundRatio
     *
     * @return float
     */
    public function getRoundRatio()
    {
        return $this->roundRatio;
    }

    public function setRefreshPeriod($refreshPeriod)
    {
        $this->refreshPeriod = $refreshPeriod;

        return $this;
    }

    public function getRefreshPeriod()
    {
        return $this->refreshPeriod;
    }
}

