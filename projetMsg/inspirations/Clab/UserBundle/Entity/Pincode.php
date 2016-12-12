<?php

namespace Clab\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="pincode")
 * @UniqueEntity({"restaurant", "code"})
 */
class Pincode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="code", type="string", nullable=true)
     */
    protected $code;

    /**
     * @ORM\Column(name="has_right_on_bo", type="boolean")
     */
    protected $hasRightOnBo;

    /**
     * @ORM\Column(name="has_right_on_logs", type="boolean")
     */
    protected $hasRightOnLogs;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", inversedBy="pincodes")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     *
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHasRightOnBo()
    {
        return $this->hasRightOnBo;
    }

    /**
     * @param mixed $hasRightOnBo
     *
     * @return $this
     */
    public function setHasRightOnBo($hasRightOnBo)
    {
        $this->hasRightOnBo = $hasRightOnBo;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHasRightOnLogs()
    {
        return $this->hasRightOnLogs;
    }

    /**
     * @param mixed $hasRightOnLogs
     *
     * @return $this
     */
    public function setHasRightOnLogs($hasRightOnLogs)
    {
        $this->hasRightOnLogs = $hasRightOnLogs;

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
