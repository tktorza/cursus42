<?php

namespace Clab\BoardBundle\Entity;

use Clab\RestaurantBundle\Entity\Meal;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\Restaurant;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * AdditionalSale.
 *
 * @ORM\Table(name="clickeat_additional_sale")
 * @ORM\Entity(repositoryClass="Clab\BoardBundle\Repository\AdditionalSaleRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class AdditionalSale
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    protected $isOnline;

    /**
     * @ORM\Column(name="multiple", type="boolean")
     */
    protected $multiple;

    /**
     * @ORM\Column(name="minimum", type="integer", nullable=true)
     * @Assert\Range(min=0,minMessage="le minimum indiqué doit être positif")
     */
    protected $minimum;

    /**
     * @ORM\Column(name="maximum", type="integer", nullable=true)
     * @Assert\Range(min=0,minMessage="le maximum indiqué doit être positif")
     */
    protected $maximum;

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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\OneToOne(targetEntity="Clab\RestaurantBundle\Entity\Meal")
     * @ORM\JoinColumn(name="meal_id", referencedColumnName="id", nullable=true )
     */
    protected $meal;

    /**
     * @ORM\OneToOne(targetEntity="Clab\RestaurantBundle\Entity\Product")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true)
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", inversedBy="additionalSales")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    protected $restaurant;

    /**
     * @ORM\OneToMany(targetEntity="Clab\BoardBundle\Entity\AdditionalSaleProduct", mappedBy="additionalSale",cascade={"persist","remove"})
     */
    protected $additionalSaleProducts;

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
     * Constructor.
     */
    public function __construct()
    {
        $this->setIsOnline(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->additionalSaleProducts = new ArrayCollection();
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return AdditionalSale
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
     * Set isOnline.
     *
     * @param bool $isOnline
     *
     * @return AdditionalSale
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
     * Set multiple.
     *
     * @param bool $multiple
     *
     * @return AdditionalSale
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Get multiple.
     *
     * @return bool
     */
    public function getMultiple()
    {
        return $this->multiple;
    }

    /**
     * Set minimum.
     *
     * @param int $minimum
     *
     * @return AdditionalSale
     */
    public function setMinimum($minimum)
    {
        $this->minimum = $minimum;

        return $this;
    }

    /**
     * Get minimum.
     *
     * @return int
     */
    public function getMinimum()
    {
        return $this->minimum;
    }

    /**
     * Set maximum.
     *
     * @param int $maximum
     *
     * @return AdditionalSale
     */
    public function setMaximum($maximum)
    {
        $this->maximum = $maximum;

        return $this;
    }

    /**
     * Get maximum.
     *
     * @return int
     */
    public function getMaximum()
    {
        return $this->maximum;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return AdditionalSale
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
     * @return AdditionalSale
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
     * @return AdditionalSale
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
     * Set meal.
     *
     * @param \Clab\RestaurantBundle\Entity\Meal $meal
     *
     * @return AdditionalSale
     */
    public function setMeal(Meal $meal = null)
    {
        if (!is_null($meal)) {
            $meal->setAdditionalSale($this);
        }
        $this->meal = $meal;

        return $this;
    }

    /**
     * Get meal.
     *
     * @return \Clab\RestaurantBundle\Entity\Meal
     */
    public function getMeal()
    {
        return $this->meal;
    }

    /**
     * Set product.
     *
     * @param \Clab\RestaurantBundle\Entity\Product $product
     *
     * @return AdditionalSale
     */
    public function setProduct(Product $product = null)
    {
        if (!is_null($product)) {
            $product->setAdditionalSale($this);
        }
        $this->product = $product;

        return $this;
    }

    /**
     * Get product.
     *
     * @return \Clab\RestaurantBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set restaurant.
     *
     * @param \Clab\RestaurantBundle\Entity\Restaurant $restaurant
     *
     * @return AdditionalSale
     */
    public function setRestaurant(Restaurant $restaurant = null)
    {
        if (!is_null($restaurant)) {
            $restaurant->addAdditionalSale($this);
        }
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
     * Add additionalSaleProduct.
     *
     * @param \Clab\BoardBundle\Entity\AdditionalSaleProduct $additionalSaleProduct
     *
     * @return AdditionalSale
     */
    public function addAdditionalSaleProduct(AdditionalSaleProduct $additionalSaleProduct)
    {
        $additionalSaleProduct->setAdditionalSale($this);
        $this->additionalSaleProducts[] = $additionalSaleProduct;

        return $this;
    }

    /**
     * Remove additionalSaleProduct.
     *
     * @param \Clab\BoardBundle\Entity\AdditionalSaleProduct $additionalSaleProduct
     */
    public function removeAdditionalSaleProduct(AdditionalSaleProduct $additionalSaleProduct)
    {
        $this->additionalSaleProducts->removeElement($additionalSaleProduct);
    }

    /**
     * Get additionalSaleProducts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdditionalSaleProducts()
    {
        return $this->additionalSaleProducts;
    }

    /**
     * @Assert\Callback
     */
    public function isAdditionalSaleValid(ExecutionContextInterface $context)
    {
        if ($this->isOnline) {
            if (empty($this->additionalSaleProducts)) {
                $context
                    ->buildViolation('Veuillez indiquer au moins un produit proposé par la vente additionnelle')
                    ->atPath('isOnline')
                    ->addViolation();
            } else {
                foreach ($this->additionalSaleProducts as $additionalSaleProduct) {
                    if ($additionalSaleProduct->getPrice() < 0) {
                        $context
                            ->buildViolation('Veuillez indiquer un supplement pour chacun des produits proposés par la vente additionnelle')
                            ->atPath('isOnline')
                            ->addViolation();
                    }
                }
            }
        }
    }

    /**
     * @Assert\Callback
     */
    public function isMaximumValid(ExecutionContextInterface $context)
    {
        $additionalSaleProducts = array();
        if ($this->multiple) {
            if (!is_null($this->minimum) && $this->minimum >= 0 && !is_null($this->maximum) && $this->maximum < $this->minimum) {
                $context
                    ->buildViolation('Veuillez indiquer un maximum supérieur ou égal au minimum')
                    ->atPath('multiple')
                    ->addViolation();
            }
            foreach ($this->additionalSaleProducts as $additionalSaleProduct) {
                if (count($additionalSaleProduct->getProduct()->getOptions()) > 0) {
                    $additionalSaleProducts[] = $additionalSaleProduct->getProduct();
                }
            }
            if (count($additionalSaleProducts) > 1) {
                $context
                    ->buildViolation('Vous pouvez proposer au maximum un produit avec option dans une vente additionnelle avec un choix multiple')
                    ->atPath('multiple')
                    ->addViolation();
            }
        }
    }
}
