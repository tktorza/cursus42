<?php

namespace Clab\LocationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use Clab\MediaBundle\Entity\GalleryOwnerInterface;
use Clab\ReviewBundle\Entity\ReviewObservableInterface;

/**
 * @ORM\Table(name="clab_location_event")
 * @ORM\Entity(repositoryClass="Clab\LocationBundle\Repository\EventRepository")
 */
class Event implements GalleryOwnerInterface, ReviewObservableInterface
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\OneToMany(targetEntity="EventSchedule", mappedBy="event", cascade={"all"})
     * @ORM\OrderBy({"startDate" = "asc"})
     */
    protected $schedules;

    /**
     * @ORM\ManyToOne(targetEntity="Place", cascade={"all"}, inversedBy="events")
     * @ORM\JoinColumn(name="place_id", referencedColumnName="id", nullable=true)
     */
    protected $place;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected $gallery;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="public_gallery_id", referencedColumnName="id")
     */
    protected $publicGallery;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Image")
     * @ORM\JoinColumn(name="cover_picture_id", referencedColumnName="id", nullable=true)
     */
    protected $cover_picture;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Image")
     * @ORM\JoinColumn(name="profile_picture_id", referencedColumnName="id", nullable=true)
     */
    protected $profile_picture;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\ReviewBundle\Entity\Review")
     * @ORM\JoinTable(name="clab_location_events_reviews",
     *                joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="review_id", referencedColumnName="id")})
     * @ORM\OrderBy({"created" = "desc"})
     */
    protected $reviews;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\SocialBundle\Entity\SocialPost")
     * @ORM\JoinTable(name="clab_location_events_social_posts",
     *                joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="social_post_id", referencedColumnName="id")})
     * @ORM\OrderBy({"created" = "desc"})
     */
    protected $socialPosts;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\UserBundle\Entity\User", inversedBy="events")
     * @ORM\JoinTable(name="clab_location_event_managers",
     *                joinColumns={@ORM\JoinColumn(name="event_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")})
     */
    protected $managers;

    /**
     * @ORM\OneToOne(targetEntity="Clab\SocialBundle\Entity\SocialProfile", cascade={"all"})
     * @ORM\JoinColumn(name="social_profile_id", referencedColumnName="id", nullable=true)
     */
    private $socialProfile;

    protected $coverPicturePathSmall;
    protected $coverPicturePath;
    protected $coverPicturePathFull;

    protected $profilePicturePathSmall;
    protected $profilePicturePath;
    protected $profilePicturePathFull;

    public function __construct()
    {
        parent::__construct();

        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->managers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->schedules = new \Doctrine\Common\Collections\ArrayCollection();
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
        return $this->hasManager($user);
    }

    public function hasManager(\Clab\UserBundle\Entity\User $user)
    {
        if($user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')) {
            return true;
        }

        if($this->getManagers()->contains($user)) {
            return true;
        }

        return false;
    }

    public function getCoverPicturePathSmall()
    {
        return $this->coverPicturePathSmall;
    }

    public function setCoverPicturePathSmall($path)
    {
        $this->coverPicturePathSmall = $path;
        return $this;
    }

    public function getCoverPicturePath()
    {
        return $this->coverPicturePath;
    }

    public function setCoverPicturePath($path)
    {
        $this->coverPicturePath = $path;
        return $this;
    }

    public function getCoverPicturePathFull()
    {
        return $this->coverPicturePathFull;
    }

    public function setCoverPicturePathFull($path)
    {
        $this->coverPicturePathFull = $path;
        return $this;
    }

    public function getProfilePicturePathSmall()
    {
        return $this->profilePicturePathSmall;
    }

    public function setProfilePicturePathSmall($path)
    {
        $this->profilePicturePathSmall = $path;
        return $this;
    }

    public function getProfilePicturePath()
    {
        return $this->profilePicturePath;
    }

    public function setProfilePicturePath($path)
    {
        $this->profilePicturePath = $path;
        return $this;
    }

    public function getProfilePicturePathFull()
    {
        return $this->profilePicturePathFull;
    }

    public function setProfilePicturePathFull($path)
    {
        $this->profilePicturePathFull = $path;
        return $this;
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

    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function addSchedule(\Clab\LocationBundle\Entity\EventSchedule $schedules)
    {
        $this->schedules[] = $schedules;
        return $this;
    }

    public function removeSchedule(\Clab\LocationBundle\Entity\EventSchedule $schedules)
    {
        $this->schedules->removeElement($schedules);
    }

    public function getSchedules()
    {
        return $this->schedules;
    }

     public function setGallery(\Clab\MediaBundle\Entity\Gallery $gallery = null)
    {
        $this->gallery = $gallery;
        return $this;
    }

    public function getGallery()
    {
        return $this->gallery;
    }

    public function setPublicGallery(\Clab\MediaBundle\Entity\Gallery $publicGallery = null)
    {
        $this->publicGallery = $publicGallery;
        return $this;
    }

    public function getPublicGallery()
    {
        return $this->publicGallery;
    }

    public function setPlace(\Clab\LocationBundle\Entity\Place $place = null)
    {
        $this->place = $place;
        return $this;
    }

    public function getPlace()
    {
        return $this->place;
    }

    public function setCoverPicture(\Clab\MediaBundle\Entity\Image $coverPicture = null)
    {
        $this->cover_picture = $coverPicture;
        return $this;
    }

    public function getCoverPicture()
    {
        return $this->cover_picture;
    }

    public function setProfilePicture(\Clab\MediaBundle\Entity\Image $profilePicture = null)
    {
        $this->profile_picture = $profilePicture;
        return $this;
    }

    public function getProfilePicture()
    {
        return $this->profile_picture;
    }

    public function addReview(\Clab\ReviewBundle\Entity\Review $reviews)
    {
        $this->reviews[] = $reviews;
        return $this;
    }

    public function removeReview(\Clab\ReviewBundle\Entity\Review $reviews)
    {
        $this->reviews->removeElement($reviews);
    }

    public function getReviews()
    {
        return $this->reviews;
    }

    public function addSocialPost(\Clab\SocialBundle\Entity\SocialPost $socialPosts)
    {
        $this->socialPosts[] = $socialPosts;
        return $this;
    }

    public function removeSocialPost(\Clab\SocialBundle\Entity\SocialPost $socialPosts)
    {
        $this->socialPosts->removeElement($socialPosts);
    }

    public function getSocialPosts()
    {
        return $this->socialPosts;
    }

    public function addManager(\Clab\UserBundle\Entity\User $managers)
    {
        $this->managers[] = $managers;
        return $this;
    }

    public function removeManager(\Clab\UserBundle\Entity\User $managers)
    {
        $this->managers->removeElement($managers);
    }

    public function getManagers()
    {
        return $this->managers;
    }

    public function setSocialProfile(\Clab\SocialBundle\Entity\SocialProfile $socialProfile = null)
    {
        $this->socialProfile = $socialProfile;
        return $this;
    }

    public function getSocialProfile()
    {
        return $this->socialProfile;
    }
}
