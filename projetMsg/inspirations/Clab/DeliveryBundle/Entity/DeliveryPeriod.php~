<?php

namespace Clab\DeliveryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_delivery_period")
 * @ORM\Entity(repositoryClass="Clab\DeliveryBundle\Entity\Repository\DeliveryPeriodRepository")
 */
class DeliveryPeriod
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
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    private $restaurant;

    /**
     * @ORM\OneToMany(targetEntity="DeliverySchedule", mappedBy="deliveryPeriod")
     */
    protected $deliverySchedules;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->deliverySchedule = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getWeekDay($day)
    {
        foreach ($this->getDeliverySchedules() as $deliverySchedule) {
            if($deliverySchedule->getWeekDay() == $day) {
                return $deliverySchedule;
            }
        }
        return null;
    }

    public function getId()
    {
        return $this->id;
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

    public function addDeliverySchedule(\Clab\DeliveryBundle\Entity\DeliverySchedule $deliverySchedules)
    {
        $this->deliverySchedules[] = $deliverySchedules;
        return $this;
    }

    public function removeDeliverySchedule(\Clab\DeliveryBundle\Entity\DeliverySchedule $deliverySchedules)
    {
        $this->deliverySchedules->removeElement($deliverySchedules);
    }

    public function getDeliverySchedules()
    {
        return $this->deliverySchedules;
    }
}
