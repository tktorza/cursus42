<?php

namespace Clab\LocationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_location_event_schedule")
 * @ORM\Entity(repositoryClass="Clab\LocationBundle\Repository\EventScheduleRepository")
 */
class EventSchedule
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
     * @ORM\Column(name="startTime", type="time")
     */
    private $startTime;

    /**
     * @ORM\Column(name="endTime", type="time")
     */
    private $endTime;

    /**
     * @ORM\Column(name="startDate", type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(name="endDate", type="datetime")
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="Event", cascade={"all"}, inversedBy="schedules")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true)
     */
    protected $event;

    public function __construct()
    {
        parent::__construct();

        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setStartDate(date_create('now'));
        $this->setEndDate(date_create('now'));
        $this->setStartTime(date_create_from_format('G:i', '10:00'));
        $this->setEndTime(date_create_from_format('G:i', '20:00'));
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
        if($user->hasRole('ROLE_SUPER_ADMIN')) {
            return true;
        }

        return false;
    }

    public function getDays()
    {
        $days = array();
        $start = clone($this->getStartDate());

        while ($start <= $this->getEndDate()) {
            $days[] = clone($start);
            $start->modify('+1 day');
        }

        return $days;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
        return $this;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
        return $this;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }

    public function setEvent(\Clab\LocationBundle\Entity\Event $event = null)
    {
        $this->event = $event;
        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }
}
