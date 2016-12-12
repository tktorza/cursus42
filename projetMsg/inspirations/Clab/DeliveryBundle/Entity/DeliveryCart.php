<?php

namespace Clab\DeliveryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_delivery_cart")
 * @ORM\Entity(repositoryClass="Clab\DeliveryBundle\Entity\Repository\DeliveryCartRepository")
 */
class DeliveryCart
{
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
     * @ORM\Column(name="min", type="float", nullable=true)
     */
    private $min;

    /**
     * @ORM\Column(name="max", type="float", nullable=true)
     */
    private $max;

    /**
     * @ORM\Column(name="delay", type="integer")
     */
    private $delay;

    /**
     * @ORM\Column(name="extra", type="float", nullable=true)
     */
    private $extra;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    private $restaurant;

    public function __construct()
    {
        $this->setDelay(15);
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
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

    public function isOnline() { return $this->getIsOnline(); }

    public function setIsDeleted($isDeleted)
    {
        $this->is_deleted = $isDeleted;
        return $this;
    }

    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    public function isDeleted() { return $this->getIsDeleted(); }

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

    public function getId()
    {
        return $this->id;
    }

    public function setMin($min)
    {
        $this->min = $min;

        return $this;
    }

    public function getMin()
    {
        return $this->min;
    }

    public function setMax($max)
    {
        $this->max = $max;

        return $this;
    }

    public function getMax()
    {
        return $this->max;
    }

    public function setDelay($delay)
    {
        $this->delay = $delay;
        return $this;
    }

    public function getDelay()
    {
        return $this->delay;
    }

    public function setExtra($extra)
    {
        $this->extra = $extra;
        return $this;
    }

    public function getExtra()
    {
        return $this->extra;
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
}
