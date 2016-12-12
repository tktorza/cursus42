<?php

namespace Clab\SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Table(name="clab_social_post")
 * @ORM\Entity(repositoryClass="Clab\SocialBundle\Entity\SocialPostRepository")
 * @Vich\Uploadable
 */
class SocialPost
{
    const SOCIAL_POST_TYPE_PRODUCT = 10;
    const SOCIAL_POST_TYPE_MEAL = 20;
    const SOCIAL_POST_TYPE_DISCOUNT = 30;
    const SOCIAL_POST_TYPE_LOCATION = 40;
    const SOCIAL_POST_TYPE_PHOTO = 50;
    const SOCIAL_POST_TYPE_MESSAGE = 60;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

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
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Restaurant", inversedBy="socialPosts")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    /**
     * @ORM\Column(name="title", type="text", nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(name="message", type="text", nullable=true)
     */
    protected $message;

    /**
     * @ORM\Column(name="link", type="text", nullable=true)
     */
    protected $link;

    /**
     * @Assert\File(
     *     maxSize="2M",
     *     mimeTypes={"image/png", "image/jpeg", "image/pjpeg"}
     * )
     * @Vich\UploadableField(mapping="clab_socialpost_image", fileNameProperty="imageName")
     */
    private $image;

    /**
     * @ORM\Column(type="string", length=255, name="image_name", nullable=true)
     */
    protected $imageName;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true)
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Meal")
     * @ORM\JoinColumn(name="meal_id", referencedColumnName="id", nullable=true)
     */
    protected $meal;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\ShopBundle\Entity\Discount")
     * @ORM\JoinColumn(name="discount_id", referencedColumnName="id", nullable=true)
     */
    protected $discount;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\LocationBundle\Entity\Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     */
    protected $address;

    protected $type;
    protected $target;

    protected $coverSmall;
    protected $cover;
    protected $coverFull;

    protected $apiCover;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        if($this->getProxyCover()) {
            $this->setApiCover($this->getProxyCover());
        }
    }

    public function __sleep()
    {
        $ref   = new \ReflectionClass(__CLASS__);
        $props = $ref->getProperties(\ReflectionProperty::IS_PROTECTED);

        $serialize_fields = array();

        foreach ($props as $prop) {
            $serialize_fields[] = $prop->name;
        }

        return $serialize_fields;
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
        return $this->getRestaurant()->isAllowed($user);
    }

    public function getProxy()
    {
        if($this->getProduct()) { return $this->getProduct(); }
        if($this->getMeal()) { return $this->getMeal(); }
        if($this->getDiscount()) { return $this->getDiscount(); }
        return null;
    }

    public function getProxyCover()
    {
        if($this->getProduct()) { return $this->getProduct()->getGallery()->getCover(); }
        if($this->getMeal()) { return $this->getMeal()->getGallery()->getCover(); }

        return null;
    }

    public function getType()
    {
        if($this->getProduct()) {
            return self::SOCIAL_POST_TYPE_PRODUCT;
        } elseif($this->getMeal()) {
            return self::SOCIAL_POST_TYPE_MEAL;
        } elseif($this->getDiscount()) {
            return self::SOCIAL_POST_TYPE_DISCOUNT;
        } elseif($this->getAddress()) {
            return self::SOCIAL_POST_TYPE_LOCATION;
        } elseif($this->getImageName()) {
            return self::SOCIAL_POST_TYPE_PHOTO;
        } else {
            return self::SOCIAL_POST_TYPE_MESSAGE;
        }
    }

    public function getTarget()
    {
        if($this->getProduct()) {
            return $this->getProduct();
        } elseif($this->getMeal()) {
            return $this->getMeal();
        } elseif($this->getDiscount()) {
            return $this->getDiscount();
        } elseif($this->getAddress()) {
            return $this->getAddress();
        } else {
            return null;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
        return $this;
    }

    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
        return $this;
    }

    public function getImageName()
    {
        return $this->imageName;
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

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function setProduct(\Clab\RestaurantBundle\Entity\Product $product = null)
    {
        $this->product = $product;
        return $this;
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function setMeal(\Clab\RestaurantBundle\Entity\Meal $meal = null)
    {
        $this->meal = $meal;
        return $this;
    }

    public function getMeal()
    {
        return $this->meal;
    }

    public function setDiscount(\Clab\ShopBundle\Entity\Discount $discount = null)
    {
        $this->discount = $discount;
        return $this;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getApiCover()
    {
        return $this->apiCover;
    }

    public function setApiCover($apiCover)
    {
        $this->apiCover = $apiCover;
        return $this;
    }

    public function getCover()
    {
        return $this->cover;
    }

    public function setCover($cover)
    {
        $this->cover = $cover;
        return $this;
    }

    public function setAddress(\Clab\LocationBundle\Entity\Address $address = null)
    {
        $this->address = $address;
        return $this;
    }

    public function getAddress()
    {
        return $this->address;
    }
}
