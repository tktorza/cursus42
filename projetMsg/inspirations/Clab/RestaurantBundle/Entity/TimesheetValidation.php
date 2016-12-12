<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_store_timesheet_validation")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\TimesheetValidationRepository")
 */
class TimesheetValidation
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    protected $is_online;

    /**
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    protected $is_deleted;

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
     * @ORM\ManyToOne(targetEntity="Restaurant", inversedBy="timesheetValidations")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id")
     */
    protected $address;

    /**
     * @ORM\Column(name="start", type="time")
     */
    protected $start;

    /**
     * @ORM\Column(name="end", type="time")
     */
    protected $end;

    /**
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    protected $date;

    /**
     * @ORM\Column(name="isPosted", type="boolean", nullable=true)
     */
    protected $isPosted;

    /**
     * @ORM\Column(name="is_private", type="boolean", nullable=true)
     */
    protected $isPrivate;

    /**
     * @ORM\Column(name="isPublished", type="boolean", nullable=true)
     */
    protected $isPublished;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setIsPosted(false);
        $this->setIsPublished(false);
        $this->setIsPrivate(false);
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

    public function isOnline()
    {
        return $this->getIsOnline();
    }

    public function setIsDeleted($isDeleted)
    {
        $this->is_deleted = $isDeleted;

        return $this;
    }

    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    public function isDeleted()
    {
        return $this->getIsDeleted();
    }

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
        return true;
    }

    public function getId()
    {
        return $this->id;
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

    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;
        $restaurant->addTimesheetValidation($this);

        return $this;
    }

    public function getRestaurant()
    {
        return $this->restaurant;
    }

    public function setIsPublished($isPublished)
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function isPublished()
    {
        return $this->getIsPublished();
    }
    public function getIsPublished()
    {
        return $this->isPublished;
    }

    public function setIsPosted($isPosted)
    {
        $this->isPosted = $isPosted;

        return $this;
    }

    public function isPosted()
    {
        return $this->getIsPosted();
    }
    public function getIsPosted()
    {
        return $this->isPosted;
    }

    public function setIsPrivate($isPrivate)
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function isPrivate()
    {
        return $this->getIsPrivate();
    }
    public function getIsPrivate()
    {
        return $this->isPrivate;
    }

    public function setAddress(\Clab\LocationBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }
}
