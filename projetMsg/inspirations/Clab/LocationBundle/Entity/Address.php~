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

    public function verbose()
    {
        $string = '';
        if($this->getName()) { $string = $string . $this->getName() . ' : '; }
        $string = $string . $this->getStreet() . ' ' . $this->getZip() . ' ' . $this->getCity();
        return $string;
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
}
