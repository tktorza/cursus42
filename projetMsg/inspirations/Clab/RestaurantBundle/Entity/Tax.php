<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tax.
 *
 * @ORM\Table(name="clickeat_restaurant_tax")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\TaxRepository")
 */
class Tax
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float")
     */
    protected $value;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    protected $is_online;

    /**
     * @ORM\Column(name="rank", type="integer")
     */
    protected $rank;

    public function getRawPrice($price)
    {
        $price = $price - (($price * $this->getValue()) / 100);

        return $price;
    }

    public function __toString()
    {
        return $this->name;
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
     * Set name.
     *
     * @param string $name
     *
     * @return Tax
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set value.
     *
     * @param float $value
     *
     * @return Tax
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set is_online.
     *
     * @param bool $isOnline
     *
     * @return Tax
     */
    public function setIsOnline($isOnline)
    {
        $this->is_online = $isOnline;

        return $this;
    }

    /**
     * Get is_online.
     *
     * @return bool
     */
    public function getIsOnline()
    {
        return $this->is_online;
    }

    /**
     * Set rank.
     *
     * @param int $rank
     *
     * @return Tax
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank.
     *
     * @return int
     */
    public function getRank()
    {
        return $this->rank;
    }
}
