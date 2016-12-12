<?php

namespace Clab\WhiteBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Clab\MediaBundle\Entity\GalleryOwnerInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="user_database_bagelstein")
 * @ORM\Entity()
 * @Vich\Uploadable
 */
class Client
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
    protected $isOnline;

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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @ORM\Column(name="first_name", type="string", nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(name="gender", type="integer", nullable=true)
     */
    protected $gender;

    /**
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(name="phone_number", type="string", nullable=true)
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(name="street", type="string", nullable=true)
     */
    protected $street;

    /**
     * @ORM\Column(name="zip", type="string", nullable=true)
     */
    protected $zip;

    /**
     * @ORM\Column(name="city", type="string", nullable=true)
     */
    protected $city;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    protected $comment;


    /**
     * @ORM\Column(name="entreprise", type="string", nullable=true)
     */
    protected $entreprise;


    /**
     * @ORM\Column(name="total_order", type="float", nullable=true)
     */
    protected $totalOrder;

    /**
     * @ORM\Column(name="number_of_order", type="float", nullable=true)
     */
    protected $numberOfOrder;


    public function __construct()
    {
        $this->setIsOnline(true);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->setTotalOrder(0);
        $this->setNumberOfOrder(0);
        $this->setGender(0);
    }

    /**
     * @return mixed
     */
    public function getGender() {

        return $this->gender;
    }

    /**
     * @param mixed $gender
     * @return $this
     */
    public function setGender($gender) {

        $this->gender = $gender;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getId() {

        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id) {

        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsOnline() {

        return $this->isOnline;
    }

    /**
     * @param mixed $isOnline
     * @return $this
     */
    public function setIsOnline($isOnline) {

        $this->isOnline = $isOnline;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated() {

        return $this->created;
    }

    /**
     * @param mixed $created
     * @return $this
     */
    public function setCreated($created) {

        $this->created = $created;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdated() {

        return $this->updated;
    }

    /**
     * @param mixed $updated
     * @return $this
     */
    public function setUpdated($updated) {

        $this->updated = $updated;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName() {

        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name) {

        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFirstName() {

        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     * @return $this
     */
    public function setFirstName($firstName) {

        $this->firstName = $firstName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail() {

        return $this->email;
    }

    /**
     * @param mixed $email
     * @return $this
     */
    public function setEmail($email) {

        $this->email = $email;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber() {

        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     * @return $this
     */
    public function setPhoneNumber($phoneNumber) {

        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStreet() {

        return $this->street;
    }

    /**
     * @param mixed $street
     * @return $this
     */
    public function setStreet($street) {

        $this->street = $street;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getZip() {

        return $this->zip;
    }

    /**
     * @param mixed $zip
     * @return $this
     */
    public function setZip($zip) {

        $this->zip = $zip;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity() {

        return $this->city;
    }

    /**
     * @param mixed $city
     * @return $this
     */
    public function setCity($city) {

        $this->city = $city;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getComment() {

        return $this->comment;
    }

    /**
     * @param mixed $comment
     * @return $this
     */
    public function setComment($comment) {

        $this->comment = $comment;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEntreprise() {

        return $this->entreprise;
    }

    /**
     * @param mixed $entreprise
     * @return $this
     */
    public function setEntreprise($entreprise) {

        $this->entreprise = $entreprise;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalOrder() {

        return $this->totalOrder;
    }

    /**
     * @param mixed $totalOrder
     * @return $this
     */
    public function setTotalOrder($totalOrder) {

        $this->totalOrder = $totalOrder;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNumberOfOrder() {

        return $this->numberOfOrder;
    }

    /**
     * @param mixed $numberOfOrder
     * @return $this
     */
    public function setNumberOfOrder($numberOfOrder) {

        $this->numberOfOrder = $numberOfOrder;
        return $this;
    }


}
