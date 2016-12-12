<?php

namespace Clab\SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_social_facebook_page")
 * @ORM\Entity(repositoryClass="Clab\SocialBundle\Entity\SocialFacebookPageRepository")
 */
class SocialFacebookPage
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="SocialProfile", inversedBy="facebook_pages")
     * @ORM\JoinColumn(name="social_profile_id", referencedColumnName="id")
     */
    protected $social_profile;

    /**
     * @ORM\Column(name="facebook_id", type="string")
     */
    private $facebook_id;

    /**
     * @ORM\Column(name="access_token", type="string", length=500, nullable=true)
     */
    private $access_token;

    /**
     * @ORM\Column(name="defaultAlbumId", type="string", nullable=true)
     */
    private $defaultAlbumId;

    /**
     * @ORM\Column(name="productAlbumId", type="string", nullable=true)
     */
    private $productAlbumId;

    /**
     * @ORM\OneToMany(targetEntity="\Clab\MultisiteBundle\Entity\Multisite", mappedBy="facebookPage")
     */
    protected $multisites;

    /**
     * @ORM\OneToMany(targetEntity="\Clab\RestaurantBundle\Entity\Restaurant", mappedBy="facebookPage")
     */
    protected $restaurants;

    /**
     * @ORM\OneToMany(targetEntity="\Clab\BoardBundle\Entity\Client", mappedBy="facebookPage")
     */
    protected $clients;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setIsOnline(false);
    }

    public function __toString()
    {
        return $this->getName();
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
        if ($user->hasRole('ROLE_SUPER_ADMIN') || $user->hasRole('ROLE_ADMIN')) {
            return true;
        }

        foreach ($user->getAllowedRestaurants() as $restaurant) {
            if ($restaurant->getSocialProfile() == $this->getSocialProfile()) {
                return true;
            }
        }

        return false;
    }

    public function getCover($size = 40)
    {
        return 'https://graph.facebook.com/' . $this->getFacebookId() . '/picture?width=40&height=' . $size;
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

    public function setAccessToken($accessToken)
    {
        $this->access_token = $accessToken;
        return $this;
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function setSocialProfile(\Clab\SocialBundle\Entity\SocialProfile $socialProfile = null)
    {
        $this->social_profile = $socialProfile;
        return $this;
    }

    public function getSocialProfile()
    {
        return $this->social_profile;
    }

    public function setFacebookId($facebookId)
    {
        $this->facebook_id = $facebookId;
        return $this;
    }

    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    public function setProductAlbumId($productAlbumId)
    {
        $this->productAlbumId = $productAlbumId;
        return $this;
    }

    public function getProductAlbumId()
    {
        return $this->productAlbumId;
    }

    public function setDefaultAlbumId($defaultAlbumId)
    {
        $this->defaultAlbumId = $defaultAlbumId;
        return $this;
    }

    public function getDefaultAlbumId()
    {
        return $this->defaultAlbumId;
    }

    public function addMultisite(\Clab\MultisiteBundle\Entity\Multisite $multisite)
    {
        $this->multisites[] = $multisite;
        return $this;
    }

    public function removeMultisite(\Clab\MultisiteBundle\Entity\Multisite $multisite)
    {
        if ($this->multisites->contains($multisite)) {
            $multisite->setFacebookPage(null);
            $this->multisites->removeElement($multisite);
        }
        return $this;
    }

    public function getMultisites()
    {
        return $this->multisites;
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
