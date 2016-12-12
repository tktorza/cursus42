<?php

namespace Clab\MultisiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_multisite_site")
 * @ORM\Entity(repositoryClass="Clab\MultisiteBundle\Repository\MultisiteRepository")
 */
class Multisite
{
    const MULTISITE_TYPE_CLASSIC = 1.0;
    const MULTISITE_TYPE_EMBED = 10.0;

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
     * @ORM\Column(name="type", type="float")
     */
    private $type;

    /**
     * @ORM\Column(name="domain", type="string", length=255)
     */
    private $domain;

    /**
     * @ORM\Column(name="primaryColor", type="string", length=255, nullable=true)
     */
    private $primaryColor;

    /**
     * @ORM\Column(name="secondaryColor", type="string", length=255, nullable=true)
     */
    private $secondaryColor;

    /**
     * @ORM\Column(name="darkBackground", type="string", length=255, nullable=true)
     */
    private $darkBackground;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Image")
     * @ORM\JoinColumn(name="cover_picture_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $coverPicture;

    /**
     * @ORM\Column(name="sectionMenu", type="boolean")
     */
    private $sectionMenu;

    /**
     * @ORM\Column(name="sectionGallery", type="boolean")
     */
    private $sectionGallery;

    /**
     * @ORM\Column(name="sectionSocial", type="boolean")
     */
    private $sectionSocial;

    /**
     * @ORM\Column(name="sectionContact", type="boolean")
     */
    private $sectionContact;

    /**
     * @ORM\Column(name="sectionReview", type="boolean")
     */
    private $sectionReview;

    /**
     * @ORM\Column(name="orderButton", type="boolean")
     */
    private $orderButton;

    /**
     * @ORM\Column(name="logo", type="boolean")
     */
    private $logo;

    /**
     * @ORM\Column(name="aboutTitle", type="string", length=255, nullable=true)
     */
    private $aboutTitle;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id", nullable=true)
     */
    private $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\BoardBundle\Entity\Client")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true)
     */
    private $client;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\SocialBundle\Entity\SocialFacebookPage", inversedBy="multisites")
     * @ORM\JoinColumn(name="facebook_page_id", referencedColumnName="id", nullable=true)
     */
    private $facebookPage;

    /**
     * @ORM\OneToMany(targetEntity="\Clab\ShopBundle\Entity\OrderDetail", mappedBy="multisite")
     */
    protected $orders;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setType(self::MULTISITE_TYPE_CLASSIC);
        $this->setIsOnline(false);

        $this->setSectionMenu(true);
        $this->setSectionSocial(true);
        $this->setSectionContact(true);
        $this->setSectionReview(true);
        $this->setSectionGallery(false);

        $this->setOrderButton(false);
        $this->setLogo(false);
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
        return $this->getRestaurant()->isAllowed($user);
    }

    public function getId()
    {
        return $this->id;
    }

    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setPrimaryColor($primaryColor)
    {
        $this->primaryColor = $primaryColor;

        return $this;
    }

    public function getPrimaryColor()
    {
        return $this->primaryColor;
    }

    public function setSecondaryColor($secondaryColor)
    {
        $this->secondaryColor = $secondaryColor;

        return $this;
    }

    public function getSecondaryColor()
    {
        return $this->secondaryColor;
    }

    public function setSectionMenu($sectionMenu)
    {
        $this->sectionMenu = $sectionMenu;

        return $this;
    }

    public function getSectionMenu()
    {
        return $this->sectionMenu;
    }

    public function setSectionSocial($sectionSocial)
    {
        $this->sectionSocial = $sectionSocial;

        return $this;
    }

    public function getSectionSocial()
    {
        return $this->sectionSocial;
    }

    public function setSectionContact($sectionContact)
    {
        $this->sectionContact = $sectionContact;

        return $this;
    }

    public function getSectionContact()
    {
        return $this->sectionContact;
    }

    public function setRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getRestaurant()
    {
        return $this->restaurant;
    }

    public function setClient(\Clab\BoardBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    public function getClient()
    {
        return $this->client;
    }

    public function addOrder(\Clab\ShopBundle\Entity\OrderDetail $orders)
    {
        $this->orders[] = $orders;

        return $this;
    }

    public function removeOrder(\Clab\ShopBundle\Entity\OrderDetail $orders)
    {
        $this->orders->removeElement($orders);
    }

    public function getOrders()
    {
        return $this->orders;
    }

    public function setFacebookPage(\Clab\SocialBundle\Entity\SocialFacebookPage $facebookPage = null)
    {
        $this->facebookPage = $facebookPage;

        return $this;
    }

    public function getFacebookPage()
    {
        return $this->facebookPage;
    }

    public function setSectionGallery($sectionGallery)
    {
        $this->sectionGallery = $sectionGallery;

        return $this;
    }

    public function getSectionGallery()
    {
        return $this->sectionGallery;
    }

    public function setSectionReview($sectionReview)
    {
        $this->sectionReview = $sectionReview;

        return $this;
    }

    public function getSectionReview()
    {
        return $this->sectionReview;
    }

    public function setOrderButton($orderButton)
    {
        $this->orderButton = $orderButton;

        return $this;
    }

    public function getOrderButton()
    {
        return $this->orderButton;
    }

    public function setAboutTitle($aboutTitle)
    {
        $this->aboutTitle = $aboutTitle;

        return $this;
    }

    public function getAboutTitle()
    {
        return $this->aboutTitle;
    }

    public function setCoverPicture(\Clab\MediaBundle\Entity\Image $coverPicture = null)
    {
        $this->coverPicture = $coverPicture;

        return $this;
    }

    public function getCoverPicture()
    {
        return $this->coverPicture;
    }

    /**
     * Set logo.
     *
     * @param bool $logo
     *
     * @return Multisite
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * Get logo.
     *
     * @return bool
     */
    public function getLogo()
    {
        return $this->logo;
    }

    public function setDarkBackground($darkBackground)
    {
        $this->darkBackground = $darkBackground;

        return $this;
    }

    public function getDarkBackground()
    {
        return $this->darkBackground;
    }
}
