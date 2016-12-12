<?php

namespace Clab\DeliveryBundle\Entity;

use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_delivery_schedule")
 * @ORM\Entity(repositoryClass="Clab\DeliveryBundle\Entity\Repository\DeliveryScheduleRepository")
 */
class DeliverySchedule
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
    private $isOnline;

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
     * @ORM\Column(name="color", type="string", length=255, nullable=true)
     */
    protected $color;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\DeliveryBundle\Entity\AreaDelivery" , inversedBy="deliverySchedules", fetch="EAGER")
     * @ORM\JoinTable(name="delivery_area_schedule",
     *      joinColumns={@ORM\JoinColumn(name="schedule_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="area_id", referencedColumnName="id")}
     *      )
     */
    protected $areas;

    /**
     * @ORM\ManyToOne(targetEntity="DeliveryPeriod", inversedBy="deliverySchedules")
     * @ORM\JoinColumn(name="period_id", referencedColumnName="id")
     */
    protected $deliveryPeriod;

    /**
     * @ORM\OneToMany(targetEntity="DeliveryDay", mappedBy="deliverySchedule", cascade={"persist", "remove"})
     */
    protected $deliveryDays;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", inversedBy="deliverySchedules")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setColor('992b2b');
        $this->areas = new ArrayCollection();
        $this->deliveryDays = new ArrayCollection();
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
    public function getAreas()
    {
        return $this->areas;
    }

    /**
     * @param mixed $areas
     *
     * @return $this
     */
    public function setAreas($areas)
    {
        $this->areas = $areas;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryPeriod()
    {
        return $this->deliveryPeriod;
    }

    /**
     * @param mixed $deliveryPeriod
     *
     * @return $this
     */
    public function setDeliveryPeriod($deliveryPeriod)
    {
        $this->deliveryPeriod = $deliveryPeriod;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeliveryDays()
    {
        return $this->deliveryDays;
    }

    /**
     * @param mixed $deliveryDays
     *
     * @return $this
     */
    public function setDeliveryDays($deliveryDays)
    {
        $this->deliveryDays = $deliveryDays;

        return $this;
    }

    public function getRestaurant()
    {
        return $this->restaurant;
    }

    public function setRestaurant(Restaurant $restaurant)
    {
        $this->restaurant = $restaurant;
        return $this;
    }
}
