<?php

namespace Clab\DeliveryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_area_delivery")
 * @ORM\Entity()
 */
class AreaDelivery
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
    private $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @ORM\Column(name="zone",type="string")
     */
    private $zone;

    /**
     * @Gedmo\Slug(fields={"zone"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(name="is_online",type="boolean")
     */
    private $isOnline;

    /**
     * @ORM\Column(name="is_deleted",type="boolean")
     */
    private $isDeleted;

    /**
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price;

    /**
     * @ORM\Column(name="min_panier",type="float")
     */
    private $minPanier;

    /**
     * @ORM\Column(name="slotLength", type="float", nullable=true)
     */
    protected $slotLength;

    /**
     * @ORM\Column(name="color", type="string", nullable=true)
     */
    private $color;

    /**
     * @ORM\Column(name="points", type="array")
     */
    private $points;

    /**
     * @ORM\Column(name="center_lat", type="float")
     */
    private $centerLat;

    /**
     * @ORM\Column(name="center_lng", type="float")
     */
    private $centerLng;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    private $restaurant;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\DeliveryBundle\Entity\DeliverySchedule", mappedBy="areas", fetch="EAGER")
     */
    private $deliverySchedules;

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->setPoints(array());
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
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * @param mixed $zone
     *
     * @return $this
     */
    public function setZone($zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param mixed $color
     *
     * @return $this
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * @param mixed $points
     *
     * @return $this
     */
    public function setPoints($points)
    {
        $this->points = $points;

        return $this;
    }

    public function getMinPanier(){
        return $this->minPanier;
    }

    public function setMinPanier($minPanier)
    {
        $this->minPanier = $minPanier;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlotLength()
    {
        return $this->slotLength;
    }

    /**
     * @param mixed $slotLength
     *
     * @return $this
     */
    public function setSlotLength($slotLength)
    {
        $this->slotLength = $slotLength;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * @param mixed $isOnline
     *
     * @return $this
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * @param mixed $isDeleted
     *
     * @return $this
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
    * @param string $slug
    *
    * @return Restaurant
    */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    public function setCenterLat($centerLat)
    {
        $this->centerLat = $centerLat;
        return $this;
    }

    public function getCenterLat()
    {
        return $this->centerLat;
    }

    public function setCenterLng($centerLng)
    {
        $this->centerLng = $centerLng;
        return $this;
    }

    public function getCenterLng()
    {
        return $this->centerLng;
    }

    public function getDeliverySchedules()
    {
        return $this->deliverySchedules;
    }

    public function setDeliverySchedules($deliverySchedules)
    {
        $this->deliverySchedules = $deliverySchedules;

        return $this;
    }
}
