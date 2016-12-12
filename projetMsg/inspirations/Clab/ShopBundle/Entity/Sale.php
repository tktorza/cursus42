<?php

namespace Clab\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="clickeat_shop_sale")
 * @ORM\Entity(repositoryClass="Clab\ShopBundle\Repository\SaleRepository")
 */
class Sale
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="day", type="date")
     */
    private $day;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\Product", mappedBy="sale", cascade={"all"})
     */
    protected $products;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\Meal", mappedBy="sale", cascade={"all"})
     */
    protected $meals;

    /**
     * @ORM\Column(name="start", type="time")
     */
    private $start;

    /**
     * @ORM\Column(name="end", type="time")
     */
    private $end;

    /**
     * @ORM\Column(name="value", type="float")
     */
    private $value;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $is_active;

    public function __construct()
    {
        $this->setStart(date_create_from_format('G:i:s', '00:00:00'));
        $this->setEnd(date_create_from_format('G:i:s', '23:59:59'));
        $this->setDay(date_create('now'));
        $this->setIsActive(true);

        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->meals = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getProxy()
    {
        if($this->getProduct()) {
            return $this->getProduct();
        } elseif ($this->getMeal()) {
            return $this->getMeal();
        }

        return null;
    }

    public function setProxy($proxy)
    {
        if($proxy->getSales()) {
            foreach ($proxy->getSales() as $sale) {
                $sale->setIsActive(false);
            }
        }

        if($proxy instanceof \Clab\RestaurantBundle\Entity\Product) {
            $proxy->addSale($this);
            $this->setProduct($proxy);
        } elseif($proxy instanceof \Clab\RestaurantBundle\Entity\Meal) {
            $proxy->addSale($this);
            $this->setMeal($proxy);
        } elseif ($proxy == null) {
            $this->setProduct(null);
            $this->setMeal(null);
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function setDay($day)
    {
        $this->day = $day;
        return $this;
    }

    public function getDay()
    {
        return $this->day;
    }

    public function setValue($value)
    {
        if($value >= 0 && $value <= 100) {
            $this->value = $value;
        }

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;
        return $this;
    }

    public function getIsActive()
    {
        return $this->is_active;
    }

    public function setStart($start)
    {
        $this->start = $start;
        return $this;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setEnd($end)
    {
        $this->end = $end;
        return $this;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function addProduct(\Clab\RestaurantBundle\Entity\Product $products)
    {
        $this->products[] = $products;
        return $this;
    }

    public function removeProduct(\Clab\RestaurantBundle\Entity\Product $products)
    {
        $this->products->removeElement($products);
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function addMeal(\Clab\RestaurantBundle\Entity\Meal $meals)
    {
        $this->meals[] = $meals;
        return $this;
    }

    public function removeMeal(\Clab\RestaurantBundle\Entity\Meal $meals)
    {
        $this->meals->removeElement($meals);
    }

    public function getMeals()
    {
        return $this->meals;
    }
}
