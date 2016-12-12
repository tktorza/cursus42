<?php

namespace Clab\TaxonomyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_taxonomy_term")
 * @ORM\Entity(repositoryClass="Clab\TaxonomyBundle\Repository\TermRepository")
 */
class Term
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
     * @ORM\ManyToMany(targetEntity="Vocabulary", inversedBy="terms", cascade={"all"})
     * @ORM\JoinTable(name="clab_taxonomy_terms_vocabularies",
     *                joinColumns={@ORM\JoinColumn(name="term_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="vocabulary_id", referencedColumnName="id")})
     */
    private $vocabularies;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\MediaBundle\Entity\Image", mappedBy="tags")
     */
    private $images;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", mappedBy="tags")
     */
    private $restaurants;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->vocabularies = new \Doctrine\Common\Collections\ArrayCollection();
        $this->restaurants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
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
        return true;
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

    public function addVocabulary(\Clab\TaxonomyBundle\Entity\Vocabulary $vocabularies)
    {
        $this->vocabularies[] = $vocabularies;
        return $this;
    }

    public function removeVocabulary(\Clab\TaxonomyBundle\Entity\Vocabulary $vocabularies)
    {
        $this->vocabularies->removeElement($vocabularies);
    }

    public function getVocabularies()
    {
        return $this->vocabularies;
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
}
