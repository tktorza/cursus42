<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RestaurantMenu.
 *
 * @ORM\Table(name="clickeat_restaurant_menu")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\RestaurantMenuRepository")
 */
class RestaurantMenu
{
    const RESTAURANT_MENU_TYPE_DEFAULT = 100;
    const RESTAURANT_MENU_TYPE_DELIVERY = 200;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="type", type="integer")
     */
    protected $type;

    /**
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    protected $isOnline;

    /**
     * @ORM\ManyToOne(targetEntity="Restaurant", inversedBy="restaurantMenus",cascade={"persist"})
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\BoardBundle\Entity\Client", inversedBy="restaurantMenus")
     * @ORM\JoinColumn(name="chain_store_id", referencedColumnName="id")
     */
    protected $chainStore;

    /**
     * @ORM\ManyToMany(targetEntity="Product", inversedBy="restaurantMenus")
     * @ORM\JoinTable(name="clickeat_restaurant_menu_products",
     *                joinColumns={@ORM\JoinColumn(name="restaurant_menu_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="product_id", referencedColumnName="id")})
     */
    protected $products;

    /**
     * @ORM\ManyToMany(targetEntity="Meal", inversedBy="restaurantMenus", cascade={"all"})
     * @ORM\JoinTable(name="clickeat_restaurant_menu_meals",
     *                joinColumns={@ORM\JoinColumn(name="restaurant_menu_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="meal_id", referencedColumnName="id")})
     */
    protected $meals;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setIsOnline(true);

        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->meals = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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
     * @param mixed $products
     *
     * @return $this
     */
    public function setProducts($products)
    {
        $this->products = $products;

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
     * Set type.
     *
     * @param int $type
     *
     * @return RestaurantMenu
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return RestaurantMenu
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
     * Set isOnline.
     *
     * @param bool $isOnline
     *
     * @return RestaurantMenu
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
     * Set restaurant.
     *
     * @param \Clab\RestaurantBundle\Entity\Restaurant $restaurant
     *
     * @return RestaurantMenu
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
     * Set chainStore.
     *
     * @param \Clab\BoardBundle\Entity\Client $chainStore
     *
     * @return RestaurantMenu
     */
    public function setChainStore(\Clab\BoardBundle\Entity\Client $chainStore = null)
    {
        $this->chainStore = $chainStore;

        return $this;
    }

    /**
     * Get chainStore.
     *
     * @return \Clab\BoardBundle\Entity\Client
     */
    public function getChainStore()
    {
        return $this->chainStore;
    }

    /**
     * Add product.
     *
     * @param \Clab\RestaurantBundle\Entity\Product $product
     *
     * @return RestaurantMenu
     */
    public function addProduct(\Clab\RestaurantBundle\Entity\Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product.
     *
     * @param \Clab\RestaurantBundle\Entity\Product $product
     */
    public function removeProduct(\Clab\RestaurantBundle\Entity\Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Add meal.
     *
     * @param \Clab\RestaurantBundle\Entity\Meal $meal
     *
     * @return RestaurantMenu
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
}
