<?php

namespace Clab\DeliveryBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_delivery_day")
 * @ORM\Entity(repositoryClass="Clab\DeliveryBundle\Entity\Repository\DeliveryDayRepository")
 */
class DeliveryDay
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
     * @ORM\Column(name="day", type="date", nullable=true)
     */
    private $day;

    /**
     * @ORM\Column(name="weekDay", type="integer", nullable=true)
     */
    private $weekDay;

    /**
     * @ORM\Column(name="cancelDays", type="text")
     */
    private $cancelDays;

    /**
     * @ORM\Column(name="start", type="time")
     */
    private $start;

    /**
     * @ORM\Column(name="end", type="time")
     */
    private $end;

    /**
     * @ORM\Column(name="fullSlots", type="text")
     */
    private $fullSlots;

    /**
     * @ORM\ManyToOne(targetEntity="DeliverySchedule", inversedBy="deliveryDays")
     * @ORM\JoinColumn(name="schedule_id", referencedColumnName="id")
     */
    protected $deliverySchedule;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    private $restaurant;

    /**
     * @ORM\ManyToMany(targetEntity="DeliveryMan")
     * @ORM\JoinTable(name="clab_delivery_day_deliveryman",
     *                joinColumns={@ORM\JoinColumn(name="delivery_day_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="deliveryman_id", referencedColumnName="id")})
     */
    protected $deliveryMen;

    protected $apiFullSlots;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setCancelDays(array());
        $this->setFullSlots(array());

        $this->setStart(date_create_from_format('G:i', '8:00'));
        $this->setEnd(date_create_from_format('G:i', '20:00'));

        $this->deliveryMen = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function getTimestampStart($timestamp)
    {
        $date = new \Datetime();
        $date->setTimestamp($timestamp);
        return date_create_from_format('d/m/Y H:i', $date->format('d/m/Y') . ' ' . $this->getStart()->format('H:i'));
    }

    public function getTimestampEnd($timestamp)
    {
        $date = new \Datetime();
        $date->setTimestamp($timestamp);
        return date_create_from_format('d/m/Y H:i', $date->format('d/m/Y') . ' ' . $this->getEnd()->format('H:i'));
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

    public function setWeekDay($weekDay)
    {
        $this->weekDay = $weekDay;
        return $this;
    }

    public function getWeekDay()
    {
        return $this->weekDay;
    }

    public function getCancelDays()
    {
        if($this->cancelDays) {
            return unserialize($this->cancelDays);
        } else {
            return array();
        }
    }

    public function setCancelDays(array $cancelDays)
    {
        $this->cancelDays = serialize($cancelDays);
        return $this;
    }

    public function addCancelDay($timestamp)
    {
        $days = $this->getCancelDays();
        if(!in_array($timestamp, $days)) {
            $days[] = $timestamp;
        }
        $this->setCancelDays($days);
    }

    public function getFullSlots()
    {
        if($this->fullSlots) {
            return unserialize($this->fullSlots);
        } else {
            return array();
        }
    }

    public function setFullSlots(array $fullSlots)
    {
        $this->fullSlots = serialize($fullSlots);
        return $this;
    }

    public function addFullSlot($timestamp, $deliveryMan, $delivery)
    {
        $slots = $this->getFullSlots();
        $slots[$timestamp][$delivery->getId()] = $deliveryMan->getId();
        $this->setFullSlots($slots);
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

    public function setDeliverySchedule(\Clab\DeliveryBundle\Entity\deliverySchedule $deliverySchedule = null)
    {
        $this->deliverySchedule = $deliverySchedule;
        return $this;
    }

    public function getDeliverySchedule()
    {
        return $this->deliverySchedule;
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

    public function addDeliveryMan(\Clab\DeliveryBundle\Entity\DeliveryMan $deliveryMen)
    {
        $this->deliveryMen[] = $deliveryMen;
        return $this;
    }

    public function removeDeliveryMan(\Clab\DeliveryBundle\Entity\DeliveryMan $deliveryMen)
    {
        $this->deliveryMen->removeElement($deliveryMen);
    }

    public function getDeliveryMen()
    {
        return $this->deliveryMen;
    }

    public function getDeliveryMenForSlot($timestamp)
    {
        $deliveryMen = array();

        if(isset($this->getFullSlots()[$timestamp]) && is_array($this->getFullSlots()[$timestamp])) {
            foreach ($this->getDeliveryMen() as $deliveryMan) {
                if(!in_array($deliveryMan->getId(), $this->getFullSlots()[$timestamp])) {
                    $deliveryMen[] = $deliveryMan;
                }
            }
        } else {
            $deliveryMen = $this->getDeliveryMen()->toArray();
        }

        return $deliveryMen;
    }

    public function getDeliveryMan($timestamp)
    {
        $deliveryMen = $this->getDeliveryMenForSlot($timestamp);
        shuffle($deliveryMen);

        if(count($deliveryMen) > 0) {
            shuffle($deliveryMen);

            return $deliveryMen[0];
        }

        return null;
    }
}
