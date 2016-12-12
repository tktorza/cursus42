<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Clab\MediaBundle\Entity\GalleryOwnerInterface;

/**
 * @ORM\Table(name="clickeat_restaurant_optionchoice")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\OptionChoiceRepository")
 */
class OptionChoice implements GalleryOwnerInterface
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
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    protected $isDeleted;

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
     * @ORM\ManyToOne(targetEntity="OptionChoice", inversedBy="childrens")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="OptionChoice", mappedBy="parent")
     */
    protected $childrens;

    /**
     * @ORM\Column(name="value", type="string", length=255)
     */
    protected $value;

    /**
     * @ORM\Column(name="price", type="float")
     */
    protected $price;

    protected $currentPrice;

    /**
     * @ORM\Column(name="position", type="float")
     */
    protected $position;

    /**
     * @ORM\Column(name="subwayType", type="string", nullable=true)
     */
    protected $subwayType;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected $gallery;

    /**
     * @ORM\ManyToOne(targetEntity="ProductOption", inversedBy="choices")
     * @ORM\JoinColumn(name="option_id", referencedColumnName="id", nullable=true, onDelete="SET NULL")
     */
    protected $option;

    /**
     * @ORM\ManyToOne(targetEntity="Restaurant", inversedBy="productCategories")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\Client", inversedBy="choices")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    protected $client;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\ShopBundle\Entity\CartElement", mappedBy="choices")
     */
    protected $elements;

    protected $coverSmall;
    protected $cover;
    protected $coverFull;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->setPrice(0);
        $this->setPosition(99999);

        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->elements = new \Doctrine\Common\Collections\ArrayCollection();
        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getValue();
    }

    public function isAvailable()
    {
        return ($this->isOnline() && !$this->isDeleted()) ? true : false;
    }

    public function isOnline()
    {
        return $this->getIsOnline();
    }
    public function isDeleted()
    {
        return $this->getIsDeleted();
    }

    public function getSubwayName()
    {
        $value = $this->getValue();
        $value = preg_replace('#\((.*?)\)#', '', $value);

        return $value;
    }

    public function getCoverSmall()
    {
        return $this->coverSmall;
    }

    public function setCoverSmall($coverSmall)
    {
        $this->coverSmall = $coverSmall;

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

    public function getCoverFull()
    {
        return $this->coverFull;
    }

    public function setCoverFull($coverFull)
    {
        $this->coverFull = $coverFull;

        return $this;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isOnline.
     *
     * @param bool $isOnline
     *
     * @return OptionChoice
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return bool
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set isDeleted.
     *
     * @param bool $isDeleted
     *
     * @return OptionChoice
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted.
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return OptionChoice
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return OptionChoice
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return OptionChoice
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return OptionChoice
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price.
     *
     * @return float
     */
    public function getPrice()
    {
        return floatval($this->price);
    }

    /**
     * Set position.
     *
     * @param float $position
     *
     * @return OptionChoice
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position.
     *
     * @return float
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set subwayType.
     *
     * @param string $subwayType
     *
     * @return OptionChoice
     */
    public function setSubwayType($subwayType)
    {
        $this->subwayType = $subwayType;

        return $this;
    }

    /**
     * Get subwayType.
     *
     * @return string
     */
    public function getSubwayType()
    {
        return $this->subwayType;
    }

    /**
     * Set parent.
     *
     * @param \Clab\RestaurantBundle\Entity\OptionChoice $parent
     *
     * @return OptionChoice
     */
    public function setParent(\Clab\RestaurantBundle\Entity\OptionChoice $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \Clab\RestaurantBundle\Entity\OptionChoice
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children.
     *
     * @param \Clab\RestaurantBundle\Entity\OptionChoice $children
     *
     * @return OptionChoice
     */
    public function addChildren(\Clab\RestaurantBundle\Entity\OptionChoice $children)
    {
        $this->childrens[] = $children;

        return $this;
    }

    /**
     * Remove children.
     *
     * @param \Clab\RestaurantBundle\Entity\OptionChoice $children
     */
    public function removeChildren(\Clab\RestaurantBundle\Entity\OptionChoice $children)
    {
        $this->childrens->removeElement($children);
    }

    /**
     * Get childrens.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChildrens()
    {
        return $this->childrens;
    }

    /**
     * Set gallery.
     *
     * @param \Clab\MediaBundle\Entity\Gallery $gallery
     *
     * @return OptionChoice
     */
    public function setGallery(\Clab\MediaBundle\Entity\Gallery $gallery = null)
    {
        $this->gallery = $gallery;

        return $this;
    }

    /**
     * Get gallery.
     *
     * @return \Clab\MediaBundle\Entity\Gallery
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * Set option.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductOption $option
     *
     * @return OptionChoice
     */
    public function setOption(\Clab\RestaurantBundle\Entity\ProductOption $option = null)
    {
        $this->option = $option;

        return $this;
    }

    /**
     * Get option.
     *
     * @return \Clab\RestaurantBundle\Entity\ProductOption
     */
    public function getOption()
    {
        return $this->option;
    }

    /**
     * Add element.
     *
     * @param \Clab\ShopBundle\Entity\CartElement $element
     *
     * @return OptionChoice
     */
    public function addElement(\Clab\ShopBundle\Entity\CartElement $element)
    {
        $this->elements[] = $element;

        return $this;
    }

    /**
     * Remove element.
     *
     * @param \Clab\ShopBundle\Entity\CartElement $element
     */
    public function removeElement(\Clab\ShopBundle\Entity\CartElement $element)
    {
        $this->elements->removeElement($element);
    }

    /**
     * Get elements.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Set restaurant.
     *
     * @param \Clab\RestaurantBundle\Entity\Restaurant $restaurant
     *
     * @return OptionChoice
     */
    public function setRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * Get restaurant.
     *
     * @return \Clab\RestaurantBundle\Entity\Restaurant
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }

    /**
     * Set client.
     *
     * @param \Clab\BoardBundle\Entity\Client $client
     *
     * @return OptionChoice
     */
    public function setClient(\Clab\BoardBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \Clab\BoardBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }
}
