<?php

namespace Clab\BoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="clab_user_database")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Clab\BoardBundle\Repository\UserDataBaseRepository")
 */
class UserDataBase
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
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
     * @ORM\Column(name="is_deleted", type="boolean", length=255, nullable=false, options={"default" : false})
     */
    protected $isDeleted;
    /**
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Regex(pattern="#^0[1-68]([-. ]?[0-9]{2}){4}$#",message="Numero de telephone non valide")
     */
    protected $phone;

    /**
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    protected $birthday;

    /**
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    protected $note;

    /**
     * @ORM\Column(name="company", type="string", nullable=true)
     */
    protected $company;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="home_address_id", referencedColumnName="id", nullable=true)
     */
    protected $homeAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="job_address_id", referencedColumnName="id", nullable=true)
     */
    protected $jobAddress;

    /**
     * @ORM\Column(name="subscribed_newsletter", type="boolean", nullable=true)
     */
    protected $subscribed_newsletter;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    private $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    public function __construct()
    {
        $this->updated = new \DateTime();
        $this->created = new \DateTime();
        $this->isDeleted = false;
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
    public function getRestaurant()
    {
        return $this->restaurant;
    }

    /**
     * @param mixed $restaurant
     *
     * @return $this
     */
    public function setRestaurant($restaurant)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     *
     * @return $this
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     *
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param mixed $birthday
     *
     * @return $this
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param mixed $note
     *
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param mixed $company
     *
     * @return $this
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHomeAddress()
    {
        return $this->homeAddress;
    }

    /**
     * @param mixed $homeAddress
     *
     * @return $this
     */
    public function setHomeAddress($homeAddress)
    {
        $this->homeAddress = $homeAddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getJobAddress()
    {
        return $this->jobAddress;
    }

    /**
     * @param mixed $jobAddress
     *
     * @return $this
     */
    public function setJobAddress($jobAddress)
    {
        $this->jobAddress = $jobAddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubscribedNewsletter()
    {
        return $this->subscribed_newsletter;
    }

    /**
     * @param mixed $subscribed_newsletter
     *
     * @return $this
     */
    public function setSubscribedNewsletter($subscribed_newsletter)
    {
        $this->subscribed_newsletter = $subscribed_newsletter;

        return $this;
    }

    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }
}
