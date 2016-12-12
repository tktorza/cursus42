<?php

namespace Clab\BoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_subscription_terms")
 * @ORM\Entity(repositoryClass="Clab\BoardBundle\Entity\SubscriptionTermsRepository")
 */
class SubscriptionTerms
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
     * @ORM\Column(name="is_signed", type="boolean")
     */
    private $is_signed;

    /**
     * @ORM\Column(name="contractVersion", type="string", length=255, nullable=true)
     */
    private $contractVersion;

    /**
     * @ORM\Column(name="lastEdit", type="datetime", nullable=true)
     */
    private $lastEdit;

    /**
     * @ORM\Column(name="lastSign", type="datetime", nullable=true)
     */
    private $lastSign;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", mappedBy="subscriptionTerms")
     */
    protected $restaurants;

    public function __toString()
    {
        return (string) $this->getId();
    }

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setContractVersion('1.1');

        $this->setLastEdit(date_create('now'));
        $this->setIsSigned(false);
    }

    public function isValid()
    {
        if (!$this->isSigned()) {
            return false;
        }

        return true;
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

    public function setIsSigned($isSigned)
    {
        $this->is_signed = $isSigned;

        return $this;
    }

    public function isSigned()
    {
        return $this->getIsSigned();
    }
    public function getIsSigned()
    {
        return $this->is_signed;
    }

    public function setContractVersion($contractVersion)
    {
        $this->contractVersion = $contractVersion;

        return $this;
    }

    public function getContractVersion()
    {
        return $this->contractVersion;
    }

    public function setLastEdit($lastEdit)
    {
        $this->lastEdit = $lastEdit;

        return $this;
    }

    public function getLastEdit()
    {
        return $this->lastEdit;
    }

    public function setLastSign($lastSign)
    {
        $this->lastSign = $lastSign;

        return $this;
    }

    public function getLastSign()
    {
        return $this->lastSign;
    }

    public function getRestaurant()
    {
        if (isset($this->restaurants[0])) {
            return $this->restaurants[0];
        }

        return;
    }

    public function addRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurants)
    {
        $this->restaurants[] = $restaurants;

        return $this;
    }

    public function removeRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurants)
    {
        $this->restaurants->removeElement($restaurants);
    }

    public function getRestaurants()
    {
        return $this->restaurants;
    }
}
