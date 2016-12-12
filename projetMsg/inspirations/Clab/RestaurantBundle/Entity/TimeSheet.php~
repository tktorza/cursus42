<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_store_timesheet")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\TimeSheetRepository")
 */
class TimeSheet
{
    const TIMESHEET_TYPE_CLOSED = 0;
    const TIMESHEET_TYPE_CLASSIC = 1;
    const TIMESHEET_TYPE_EVENT = 2;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @ORM\Column(name="type", type="integer")
     */
    protected $type;

    /**
     * @ORM\Column(name="days", type="array", nullable=true)
     */
    protected $days;

    /**
     * @ORM\Column(name="start", type="time")
     */
    protected $start;

    /**
     * @ORM\Column(name="end", type="time")
     */
    protected $end;

    /**
     * @ORM\Column(name="startDate", type="datetime", nullable=true)
     */
    protected $startDate;

    /**
     * @ORM\Column(name="endDate", type="datetime", nullable=true)
     */
    protected $endDate;

    /**
     * @ORM\Column(name="monday", type="boolean")
     */
    protected $monday;

    /**
     * @ORM\Column(name="tuesday", type="boolean")
     */
    protected $tuesday;

    /**
     * @ORM\Column(name="wednesday", type="boolean")
     */
    protected $wednesday;

    /**
     * @ORM\Column(name="thursday", type="boolean")
     */
    protected $thursday;

    /**
     * @ORM\Column(name="friday", type="boolean")
     */
    protected $friday;

    /**
     * @ORM\Column(name="saturday", type="boolean")
     */
    protected $saturday;

    /**
     * @ORM\Column(name="sunday", type="boolean")
     */
    protected $sunday;

    /**
     * @ORM\Column(name="is_private", type="boolean")
     */
    protected $isPrivate;

    /**
     * @ORM\Column(name="maxPreorderTime", type="time", nullable=true)
     */
    protected $maxPreorderTime;

    /**
     * @ORM\ManyToOne(targetEntity="Restaurant", inversedBy="timesheets")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address", cascade={"persist"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     */
    protected $address;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Place", cascade={"persist"})
     * @ORM\JoinColumn(name="place_id", referencedColumnName="id", nullable=true)
     */
    protected $place;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Event", cascade={"persist"})
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true)
     */
    protected $event;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setType(self::TIMESHEET_TYPE_CLASSIC);

        $this->setStart(date_create_from_format('G:i', '10:00'));
        $this->setEnd(date_create_from_format('G:i', '20:00'));
        $this->setStartDate(null);
        $this->setEndDate(null);
        $this->setIsPrivate(false);
        $this->setDays($this->getDayChoices());

        // @todo remove
        $this->setMonday(true);
        $this->setTuesday(true);
        $this->setWednesday(true);
        $this->setThursday(true);
        $this->setFriday(true);
        $this->setSaturday(true);
        $this->setSunday(true);
    }

    public static function getDayChoices()
    {
        return array('MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY');
    }

    public function isPrivate()
    {
        return $this->getIsPrivate();
    }

    public function setStart($start)
    {
        if ($start && substr($start->format('i'), -1) != '0' && substr($start->format('i'), -1) != '5') {
            $start = date_create_from_format('G:i', $start->format('G:').substr($start->format('i'), 0, 1).'5');
        }

        $this->start = $start;

        return $this;
    }

    public function setEnd($end)
    {
        if ($end && substr($end->format('i'), -1) != '0' && substr($end->format('i'), -1) != '5') {
            $end = date_create_from_format('G:i', $end->format('G:').substr($end->format('i'), 0, 1).'5');
        }

        $this->end = $end;

        return $this;
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
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return TimeSheet
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return TimeSheet
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set type.
     *
     * @param int $type
     *
     * @return TimeSheet
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    public function addDay($day)
    {
        $this->days[] = $day;
    }

    public function setDays(array $days)
    {
        $this->days = $days;

        return $this;
    }

    public function removeDay($day)
    {
        $this->days->removeElement($day);
    }


    /**
     * Get days.
     *
     * @return array
     */
    public function getDays()
    {
        //legacy
        if(is_null($this->days)) {
            return array();
        }

        return $this->days;
    }

    /**
     * Get start.
     *
     * @return \DateTime
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Get end.
     *
     * @return \DateTime
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set startDate.
     *
     * @param \DateTime $startDate
     *
     * @return TimeSheet
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate.
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate.
     *
     * @param \DateTime $endDate
     *
     * @return TimeSheet
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate.
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set monday.
     *
     * @param bool $monday
     *
     * @return TimeSheet
     */
    public function setMonday($monday)
    {
        $this->monday = $monday;

        return $this;
    }

    /**
     * Get monday.
     *
     * @return bool
     */
    public function getMonday()
    {
        return $this->monday;
    }

    /**
     * Set tuesday.
     *
     * @param bool $tuesday
     *
     * @return TimeSheet
     */
    public function setTuesday($tuesday)
    {
        $this->tuesday = $tuesday;

        return $this;
    }

    /**
     * Get tuesday.
     *
     * @return bool
     */
    public function getTuesday()
    {
        return $this->tuesday;
    }

    /**
     * Set wednesday.
     *
     * @param bool $wednesday
     *
     * @return TimeSheet
     */
    public function setWednesday($wednesday)
    {
        $this->wednesday = $wednesday;

        return $this;
    }

    /**
     * Get wednesday.
     *
     * @return bool
     */
    public function getWednesday()
    {
        return $this->wednesday;
    }

    /**
     * Set thursday.
     *
     * @param bool $thursday
     *
     * @return TimeSheet
     */
    public function setThursday($thursday)
    {
        $this->thursday = $thursday;

        return $this;
    }

    /**
     * Get thursday.
     *
     * @return bool
     */
    public function getThursday()
    {
        return $this->thursday;
    }

    /**
     * Set friday.
     *
     * @param bool $friday
     *
     * @return TimeSheet
     */
    public function setFriday($friday)
    {
        $this->friday = $friday;

        return $this;
    }

    /**
     * Get friday.
     *
     * @return bool
     */
    public function getFriday()
    {
        return $this->friday;
    }

    /**
     * Set saturday.
     *
     * @param bool $saturday
     *
     * @return TimeSheet
     */
    public function setSaturday($saturday)
    {
        $this->saturday = $saturday;

        return $this;
    }

    /**
     * Get saturday.
     *
     * @return bool
     */
    public function getSaturday()
    {
        return $this->saturday;
    }

    /**
     * Set sunday.
     *
     * @param bool $sunday
     *
     * @return TimeSheet
     */
    public function setSunday($sunday)
    {
        $this->sunday = $sunday;

        return $this;
    }

    /**
     * Get sunday.
     *
     * @return bool
     */
    public function getSunday()
    {
        return $this->sunday;
    }

    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getIsPrivate()
    {
        return $this->isPrivate;
    }

    /**
     * Set maxPreorderTime.
     *
     * @param \DateTime $maxPreorderTime
     *
     * @return TimeSheet
     */
    public function setMaxPreorderTime($maxPreorderTime)
    {
        $this->maxPreorderTime = $maxPreorderTime;

        return $this;
    }

    /**
     * Get maxPreorderTime.
     *
     * @return \DateTime
     */
    public function getMaxPreorderTime()
    {
        return $this->maxPreorderTime;
    }

    /**
     * Set restaurant.
     *
     * @param \Clab\RestaurantBundle\Entity\Restaurant $restaurant
     *
     * @return TimeSheet
     */
    public function setRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * Get restaurant.
     *
     * @return \Clab\RestaurantBundle\Entity\Restaurant
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }

    /**
     * Set address.
     *
     * @param \Clab\LocationBundle\Entity\Address $address
     *
     * @return TimeSheet
     */
    public function setAddress(\Clab\LocationBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return \Clab\LocationBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set place.
     *
     * @param \Clab\LocationBundle\Entity\Place $place
     *
     * @return TimeSheet
     */
    public function setPlace(\Clab\LocationBundle\Entity\Place $place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place.
     *
     * @return \Clab\LocationBundle\Entity\Place
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set event.
     *
     * @param \Clab\LocationBundle\Entity\Event $event
     *
     * @return TimeSheet
     */
    public function setEvent(\Clab\LocationBundle\Entity\Event $event = null)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Get event.
     *
     * @return \Clab\LocationBundle\Entity\Event
     */
    public function getEvent()
    {
        return $this->event;
    }
}
