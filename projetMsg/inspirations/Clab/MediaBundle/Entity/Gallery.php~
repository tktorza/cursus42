<?php

namespace Clab\MediaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="media_gallery")
 * @ORM\Entity(repositoryClass="Clab\MediaBundle\Repository\GalleryRepository")
 */
class Gallery
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
     * @ORM\ManyToOne(targetEntity="Gallery", inversedBy="childrens")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Gallery", mappedBy="parent")
     */
    protected $childrens;

    /**
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="gallery",cascade={"persist"})
     * @ORM\OrderBy({"id" = "desc"})
     */
    protected $images;

    /**
     * @ORM\ManyToOne(targetEntity="Image")
     * @ORM\JoinColumn(name="default_id", referencedColumnName="id")
     */
    protected $default;

    /**
     * @ORM\Column(name="dirName", type="string", length=255)
     */
    private $dirName;

    /**
     * @ORM\Column(name="is_generic", type="boolean")
     */
    private $is_generic;

    protected $cover;
    private $mobileCover;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
        $this->is_generic = false;
    }

    public function __toString()
    {
        return (string) $this->getId();
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
        return true;
    }

    public function getCover()
    {
        $images = $this->getImages();

        if (count($images) > 0) {
            $find = false;
            foreach ($images as $image) {
                if ($image->getIsPromoted()) {
                    return $image;
                    $find = true;
                }
            }

            if (!$find) {
                return $images[0];
            }
        } else {
            return $this->getDefault();
        }
    }

    public function setCover($cover)
    {
        foreach ($this->getImages() as $image) {
            if ($image == $cover) {
                $image->setIsPromoted(true);
            } else {
                $image->setIsPromoted(false);
            }
        }

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function addImage(\Clab\MediaBundle\Entity\Image $images)
    {
        $this->images[] = $images;

        return $this;
    }

    public function removeImage(\Clab\MediaBundle\Entity\Image $images)
    {
        $this->images->removeElement($images);
    }

    public function getImages()
    {
        return $this->images;
    }

    public function setImages()
    {
    }

    public function setDirName($dirName)
    {
        $this->dirName = $dirName;

        return $this;
    }

    public function getDirName()
    {
        return $this->dirName;
    }

    public function setDefault(\Clab\MediaBundle\Entity\Image $default = null)
    {
        $this->default = $default;

        return $this;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function addProduct(\Clab\RestaurantBundle\Entity\Product $products)
    {
        $this->products[] = $products;

        return $this;
    }

    public function removeProduct(\Clab\RestaurantBundle\Entity\Product $products)
    {
        $this->products->removeElement($products);
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function addMeal(\Clab\RestaurantBundle\Entity\Meal $meals)
    {
        $this->meals[] = $meals;

        return $this;
    }

    public function removeMeal(\Clab\RestaurantBundle\Entity\Meal $meals)
    {
        $this->meals->removeElement($meals);
    }

    public function getMeals()
    {
        return $this->meals;
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

    public function setIsGeneric($isGeneric)
    {
        $this->is_generic = $isGeneric;

        return $this;
    }

    public function getIsGeneric()
    {
        return $this->is_generic;
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

    public function setParent(\Clab\MediaBundle\Entity\Gallery $parent = null)
    {
        $this->parent = $parent;
        $parent->addChildren($this);

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function addChildren(\Clab\MediaBundle\Entity\Gallery $childrens)
    {
        $this->childrens[] = $childrens;

        return $this;
    }

    public function removeChildren(\Clab\MediaBundle\Entity\Gallery $childrens)
    {
        $this->childrens->removeElement($childrens);
    }

    public function getChildrens()
    {
        return $this->childrens;
    }
}
