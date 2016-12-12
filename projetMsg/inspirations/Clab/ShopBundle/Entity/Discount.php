<?php

namespace Clab\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_shop_discount")
 * @ORM\Entity(repositoryClass="Clab\ShopBundle\Repository\DiscountRepository")
 */
class Discount
{
    const DISCOUNT_TYPE_ALL = 0;
    const DISCOUNT_TYPE_MEAL = 100;
    const DISCOUNT_TYPE_PRODUCT = 200;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    private $isOnline;

    /**
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    private $isDeleted;

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
     * @ORM\Column(name="percent", type="float")
     */
    private $percent;

    /**
     * @ORM\Column(name="type", type="float")
     */
    private $type;

    /**
     * @ORM\Column(name="isMultisite", type="boolean")
     */
    private $isMultisite;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\RestaurantBundle\Entity\Restaurant", inversedBy="discounts")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id", nullable=true)
     */
    protected $restaurant;

    public function getDescription()
    {
        switch ($this->getType()) {
            case self::DISCOUNT_TYPE_ALL:
                return '-' . $this->getPercent() . '% sur l\'addition';
                break;
            case self::DISCOUNT_TYPE_MEAL:
                return '-' . $this->getPercent() . '% sur les formules';
                break;
            case self::DISCOUNT_TYPE_PRODUCT:
                return '-' . $this->getPercent() . '% sur l\'addition (hors formules)';
                break;
            default:
                return '';
                break;
        }
    }

    public static function getTypeChoices()
    {
        return array(
            self::DISCOUNT_TYPE_ALL => 'Sur l\'addition',
            self::DISCOUNT_TYPE_MEAL => 'Sur les formules uniquement',
            self::DISCOUNT_TYPE_PRODUCT => 'Sur l\'addition (hors formules)',
        );
    }

    public function __toString() { return $this->getName(); }

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->setPercent(20);
        $this->setType(self::DISCOUNT_TYPE_ALL);
        $this->setIsMultisite(false);
    }

    public function isOnline() { return $this->getIsOnline(); }
    public function isDeleted() { return $this->getIsDeleted(); }

    public function getCartDiscountAmount($cart)
    {
        $price = 0;

        foreach ($cart->getElements() as $element) {
            if($element->getMeal() && ($this->getType() == self::DISCOUNT_TYPE_ALL || $this->getType() == self::DISCOUNT_TYPE_MEAL)) {
                $price += $element->getTotalPrice() * $this->getPercent() / 100;
            } elseif($element->getProduct() && ($this->getType() == self::DISCOUNT_TYPE_ALL || $this->getType() == self::DISCOUNT_TYPE_PRODUCT)) {
                $price += $element->getTotalPrice() * $this->getPercent() / 100;
            }
        }

        return $price;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set isOnline
     *
     * @param boolean $isOnline
     *
     * @return Discount
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline
     *
     * @return boolean
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return Discount
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Discount
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Discount
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Discount
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug
     *
     * @param string $slug
     *
     * @return Discount
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set percent
     *
     * @param float $percent
     *
     * @return Discount
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * Get percent
     *
     * @return float
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * Set type
     *
     * @param float $type
     *
     * @return Discount
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return float
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set isMultisite
     *
     * @param boolean $isMultisite
     *
     * @return Discount
     */
    public function setIsMultisite($isMultisite)
    {
        $this->isMultisite = $isMultisite;

        return $this;
    }

    /**
     * Get isMultisite
     *
     * @return boolean
     */
    public function getIsMultisite()
    {
        return $this->isMultisite;
    }

    /**
     * Set restaurant
     *
     * @param \Clab\RestaurantBundle\Entity\Restaurant $restaurant
     *
     * @return Discount
     */
    public function setRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * Get restaurant
     *
     * @return \Clab\RestaurantBundle\Entity\Restaurant
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }
}
