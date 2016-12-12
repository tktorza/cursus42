<?php

namespace Clab\RestaurantBundle\Entity;

use Clab\BoardBundle\Entity\Subscription;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_plans")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\PlanRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Plan
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(name="price", type="float", length=255)
     */
    protected $price;

    /**
     * @ORM\Column(name="stripe_id_plan", type="string", length=255)
     */
    protected $stripePlanId;

    /**
     * @ORM\OneToMany(targetEntity="Clab\BoardBundle\Entity\Subscription", mappedBy="plan")
     */
    private $subscriptions;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->setIsOnline(true);
        $this->subscriptions = new ArrayCollection();
    }
    /**
     * @return mixed
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    public function addSubscriptions(Subscription $subscription)
    {
        $this->subscriptions[] = $subscription;
    }

    /**
     * Remove app.
     */
    public function removeApp(Subscription $subscription)
    {
        $this->subscriptions->removeElement($subscription);
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

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function getStripePlanId()
    {
        return $this->stripePlanId;
    }

    /**
     * @param mixed $stripePlanId
     *
     * @return $this
     */
    public function setStripePlanId($stripePlanId)
    {
        $this->stripePlanId = $stripePlanId;

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
}
