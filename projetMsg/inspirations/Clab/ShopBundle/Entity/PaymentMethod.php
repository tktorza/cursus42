<?php

namespace Clab\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="clickeat_shop_payment_method")
 * @ORM\Entity(repositoryClass="Clab\ShopBundle\Repository\PaymentMethodRepository")
 */
class PaymentMethod
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;


    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    private $is_online;

    /**
     * @ORM\Column(name="availableForOrder", type="boolean")
     */
    private $availableForOrder;

    /**
     * @ORM\Column(name="minimum", type="float")
     */
    private $minimum;
        
    /**
     * @ORM\Column(name="icon", type="string")
     */
    private $icon;

    /**
     * @ORM\Column(name="code", type="integer")
     */
    private $code;

    public function __construct()
    {
        $this->setIsOnline(false);
        $this->setMinimum(0);
        $this->setAvailableForOrder(0);
    }

    public function __toString()
    {
        return $this->name;
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

    public function setIsOnline($isOnline)
    {
        $this->is_online = $isOnline;
        return $this;
    }

    public function getIsOnline()
    {
        return $this->is_online;
    }

    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;
        return $this;
    }

    public function getMinimum()
    {
        return $this->minimum;
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

    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    public function getIcon()
    {
        return $this->icon;
    }

    public function setAvailableForOrder($availableForOrder)
    {
        $this->availableForOrder = $availableForOrder;
        return $this;
    }

    public function getAvailableForOrder()
    {
        return $this->availableForOrder;
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
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}
