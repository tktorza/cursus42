<?php

namespace Clab\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_caisse_licence")
 * @ORM\Entity()
 * @ORM\Entity(repositoryClass="Clab\ApiBundle\Repository\LicenceRepository")
 */
class Licence
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
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $resetAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $pingedAt;

    /**
     * @ORM\Column(name="licence", type="text")
     */
    protected $licence;

    /**
     * @ORM\Column(name="serial", type="text", nullable=true)
     */
    protected $serial;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id", nullable=true)
     */
    private $restaurant;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
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
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @param mixed $licence
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;
    }

    /**
     * @return mixed
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * @param mixed $serial
     */
    public function setSerial($serial)
    {
        $this->serial = $serial;
    }

    public function resetSerial() {
        $this->serial = null;
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
     */
    public function setRestaurant($restaurant)
    {
        $this->restaurant = $restaurant;
    }

    /**
     * @return mixed
     */
    public function getResetAt()
    {
        return $this->resetAt;
    }

    public function setResetDate() {
        $this->resetAt = new \DateTime(null, new \DateTimeZone('Europe/Paris'));
    }

    /**
     * @param mixed $resetAt
     */
    public function setResetAt($resetAt)
    {
        $this->resetAt = $resetAt;
    }

    /**
     * @return mixed
     */
    public function getPingedAt()
    {
        return $this->pingedAt;
    }

    /**
     * @param mixed $pingedAt
     */
    public function setPingedAt($pingedAt)
    {
        $this->pingedAt = $pingedAt;
    }

    public function setPingDate() {
        $this->pingedAt = new \DateTime(null, new \DateTimeZone('Europe/Paris'));
    }
}