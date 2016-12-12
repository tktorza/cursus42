<?php

namespace Clab\LocationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="clickeat_location_address")
 * @ORM\Entity(repositoryClass="Clab\LocationBundle\Repository\AddressRepository")
 */
class Address
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(name="street", type="string", length=255, nullable=true)
     */
    private $street;

    /**
     * @ORM\Column(name="zip", type="string", length=255, nullable=true)
     */
    private $zip;

    /**
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(name="company", type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @ORM\Column(name="building", type="string", length=255, nullable=true)
     */
    private $building;

    /**
     * @ORM\Column(name="door_code", type="string", length=255, nullable=true)
     */
    private $doorCode;

    /**
     * @ORM\Column(name="second_door_code", type="string", length=255, nullable=true)
     */
    private $secondDoorCode;

    /**
     * @ORM\Column(name="intercom", type="string", length=255, nullable=true)
     */
    private $intercom;

    /**
     * @ORM\Column(name="floor", type="string", length=255, nullable=true)
     */
    private $floor;

    /**
     * @ORM\Column(name="door", type="string", length=255, nullable=true)
     */
    private $door;

    /**
     * @ORM\Column(name="staircase", type="string", nullable=true)
     */
    private $staircase;

    /**
     * @ORM\Column(name="elevator", type="string", nullable=true)
     */
    private $elevator;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(name="latitude", type="string", nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(name="longitude", type="string", nullable=true)
     */
    private $longitude;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User", inversedBy="addresses")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    protected $user;

    protected $description;

    public function verbose()
    {
        $string = '';

        if ($this->getName()) {
            $string = $string . $this->getName() . ' : ';
        }

        $string = $string . $this->getStreet() . ' ' . $this->getZip() . ' ' . $this->getCity();

        return $string;
    }

    public function fullStringDescription()
    {
        $concatenate = 'Société:' . $this->company . '\nImmeuble: ' .  $this->building . '\nCode de porte: ' . $this->doorCode . '\nCode de seconde porte: ' . $this->secondDoorCode .
            '\nInterphone: ' . $this->intercom . '\nEtage: ' . $this->floor . '\nPorte: ' . $this->door . '\nEscalier: ' . $this->staircase . '\nAscenceur: ' . $this->elevator . '\nCommentaire: ' . $this->comment;

        return $concatenate;
    }

    public function isEmpty()
    {
        $concat = $this->getStreet() . $this->getZip() . $this->getCity();

        return empty($concat);
    }

    public function __toString()
    {
        return $this->verbose();
    }

    public static function getUnwantedArray()
    {
        return array(
            'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
            'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
            'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
            'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
        );
    }

    public function getStreetNoAccents()
    {
        return strtr($this->getStreet(), self::getUnwantedArray());
    }

    public function getCityNoAccents()
    {
        return strtr($this->getCity(), self::getUnwantedArray());
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setStreet($street)
    {
        $this->street = $street;
        return $this;
    }

    public function getStreet()
    {
        return $this->street;
    }

    public function setZip($zip)
    {
        $this->zip = $zip;
        return $this;
    }

    public function getZip()
    {
        return $this->zip;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment()
    {
        return $this->comment;
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
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param mixed $building
     */
    public function setBuilding($building)
    {
        $this->building = $building;
    }

    /**
     * @return mixed
     */
    public function getDoorCode()
    {
        return $this->doorCode;
    }

    /**
     * @param mixed $doorCode
     */
    public function setDoorCode($doorCode)
    {
        $this->doorCode = $doorCode;
    }

    /**
     * @return mixed
     */
    public function getSecondDoorCode()
    {
        return $this->secondDoorCode;
    }

    /**
     * @param mixed $secondDoorCode
     */
    public function setSecondDoorCode($secondDoorCode)
    {
        $this->secondDoorCode = $secondDoorCode;
    }

    /**
     * @return mixed
     */
    public function getIntercom()
    {
        return $this->intercom;
    }

    /**
     * @param mixed $intercom
     */
    public function setIntercom($intercom)
    {
        $this->intercom = $intercom;
    }

    /**
     * @return mixed
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @param mixed $floor
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;
    }

    /**
     * @return mixed
     */
    public function getDoor()
    {
        return $this->door;
    }

    /**
     * @param mixed $door
     */
    public function setDoor($door)
    {
        $this->door = $door;
    }

    /**
     * @return mixed
     */
    public function getStaircase()
    {
        return $this->staircase;
    }

    /**
     * @param mixed $staircase
     */
    public function setStaircase($staircase)
    {
        $this->staircase = $staircase;
    }

    /**
     * @return mixed
     */
    public function getElevator()
    {
        return $this->elevator;
    }

    /**
     * @param mixed $elevator
     */
    public function setElevator($elevator)
    {
        $this->elevator = $elevator;
    }
}
