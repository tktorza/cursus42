<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_restaurant_mealslot")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\MealSlotRepository")
 */
class MealSlot
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
     * @ORM\ManyToOne(targetEntity="MealSlot", inversedBy="childrens")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="MealSlot", mappedBy="parent")
     */
    protected $childrens;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(name="position", type="float")
     */
    protected $position;

    /**
     * @ORM\Column(name="disabledProducts", type="text", nullable=true)
     */
    protected $disabledProducts;

    /**
     * @ORM\Column(name="customPrices", type="text", nullable=true)
     */
    protected $customPrices;

    /**
     * @ORM\ManyToOne(targetEntity="Restaurant", inversedBy="mealSlots")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\Client", inversedBy="mealSlots")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    protected $client;

    /**
     * @ORM\ManyToMany(targetEntity="Meal", inversedBy="slots", cascade={"persist"})
     * @ORM\JoinTable(name="clickeat_restaurant_meals_slots",
     *                joinColumns={@ORM\JoinColumn(name="slot_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="meal_id", referencedColumnName="id")})
     */
    protected $meals;

    /**
     * @ORM\ManyToMany(targetEntity="ProductCategory", inversedBy="slots", cascade={"persist"})
     * @ORM\JoinTable(name="clickeat_restaurant_meal_slots_product_categories",
     *                joinColumns={@ORM\JoinColumn(name="slot_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="product_category_id", referencedColumnName="id")})
     */
    protected $productCategories;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->setPosition(999);

        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->meals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->productCategories = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function isOnline()
    {
        return $this->getIsOnline();
    }
    public function isDeleted()
    {
        return $this->getIsDeleted();
    }

    public function setDisabledProducts(array $disabledProducts)
    {
        $this->disabledProducts = serialize($disabledProducts);

        return $this;
    }

    public function getDisabledProducts()
    {
        if ($this->disabledProducts) {
            return unserialize($this->disabledProducts);
        } else {
            return array();
        }
    }

    public function setCustomPrices(array $customPrices)
    {
        $this->customPrices = serialize($customPrices);

        return $this;
    }

    public function getCustomPrices()
    {
        if ($this->customPrices) {
            return unserialize($this->customPrices);
        } else {
            return array();
        }
    }

    /**
     * @param mixed $childrens
     *
     * @return $this
     */
    public function setChildrens($childrens)
    {
        $this->childrens = $childrens;

        return $this;
    }

    /**
     * @param mixed $meals
     *
     * @return $this
     */
    public function setMeals($meals)
    {
        $this->meals = $meals;

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
     * @return MealSlot
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
     * @return MealSlot
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
     * @return MealSlot
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
     * @return MealSlot
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
     * Set name.
     *
     * @param string $name
     *
     * @return MealSlot
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return MealSlot
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set position.
     *
     * @param float $position
     *
     * @return MealSlot
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
     * Set parent.
     *
     * @param \Clab\RestaurantBundle\Entity\MealSlot $parent
     *
     * @return MealSlot
     */
    public function setParent(\Clab\RestaurantBundle\Entity\MealSlot $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \Clab\RestaurantBundle\Entity\MealSlot
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children.
     *
     * @param \Clab\RestaurantBundle\Entity\MealSlot $children
     *
     * @return MealSlot
     */
    public function addChildren(\Clab\RestaurantBundle\Entity\MealSlot $children)
    {
        $this->childrens[] = $children;

        return $this;
    }

    /**
     * Remove children.
     *
     * @param \Clab\RestaurantBundle\Entity\MealSlot $children
     */
    public function removeChildren(\Clab\RestaurantBundle\Entity\MealSlot $children)
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
     * Set restaurant.
     *
     * @param \Clab\RestaurantBundle\Entity\Restaurant $restaurant
     *
     * @return MealSlot
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
     * Add meal.
     *
     * @param \Clab\RestaurantBundle\Entity\Meal $meal
     *
     * @return MealSlot
     */
    public function addMeal(\Clab\RestaurantBundle\Entity\Meal $meal)
    {
        $this->meals[] = $meal;

        return $this;
    }

    /**
     * Remove meal.
     *
     * @param \Clab\RestaurantBundle\Entity\Meal $meal
     */
    public function removeMeal(\Clab\RestaurantBundle\Entity\Meal $meal)
    {
        $this->meals->removeElement($meal);
    }

    /**
     * Get meals.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMeals()
    {
        return $this->meals;
    }

    public function setProductCategories($productCategories)
    {
        $this->productCategories = $productCategories;

        return $this;
    }

    /**
     * Add productCategory.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductCategory $productCategory
     *
     * @return MealSlot
     */
    public function addProductCategory(\Clab\RestaurantBundle\Entity\ProductCategory $productCategory)
    {
        $this->productCategories[] = $productCategory;

        return $this;
    }

    /**
     * Remove productCategory.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductCategory $productCategory
     */
    public function removeProductCategory(\Clab\RestaurantBundle\Entity\ProductCategory $productCategory)
    {
        $this->productCategories->removeElement($productCategory);
    }

    /**
     * Get productCategories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductCategories()
    {
        return $this->productCategories;
    }

    /**
     * Set client.
     *
     * @param \Clab\BoardBundle\Entity\Client $client
     *
     * @return MealSlot
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
