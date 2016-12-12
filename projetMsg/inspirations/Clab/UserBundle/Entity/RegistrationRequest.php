<?php

namespace Clab\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_user_registration_request")
 * @ORM\Entity(repositoryClass="Clab\UserBundle\Repository\RegistrationRequestRepository")
 */
class RegistrationRequest
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
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(name="roles", type="text", nullable=true)
     */
    private $roles;

    /**
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    private $message;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=true)
     */
    protected $author;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinTable(name="clab_user_registration_request_restaurant",
     *                joinColumns={@ORM\JoinColumn(name="registration_request_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")})
     */
    protected $restaurants;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\BoardBundle\Entity\Client")
     * @ORM\JoinTable(name="clab_user_registration_request_client",
     *                joinColumns={@ORM\JoinColumn(name="registration_request_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="client_id", referencedColumnName="id")})
     */
    protected $clients;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->restaurants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->clients = new \Doctrine\Common\Collections\ArrayCollection();
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
        return $this->getProxy()->isAllowed($user);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setRoles($roles)
    {
        $this->roles = serialize($roles);
        return $this;
    }

    public function getRoles()
    {
        return unserialize($this->roles);
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setAuthor(\Clab\UserBundle\Entity\User $author = null)
    {
        $this->author = $author;
        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
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

    public function addClient(\Clab\BoardBundle\Entity\Client $clients)
    {
        $this->clients[] = $clients;
        return $this;
    }

    public function removeClient(\Clab\BoardBundle\Entity\Client $clients)
    {
        $this->clients->removeElement($clients);
    }

    public function getClients()
    {
        return $this->clients;
    }
}
