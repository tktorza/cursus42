<?php

namespace Clab\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\Meal;

/**
 * @ORM\Table(name="clickeat_shop_cartelements")
 * @ORM\Entity(repositoryClass="Clab\ShopBundle\Repository\CartElementRepository")
 */
class CartElement
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="hash", type="string")
     */
    private $hash;

    /**
     * @ORM\Column(name="quantity", type="float")
     */
    private $quantity;

    /**
     * @ORM\Column(name="price", type="float")
     */
    private $price;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Product", cascade={"all"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id", nullable=true)
     */
    protected $product;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Meal", cascade={"all"})
     * @ORM\JoinColumn(name="meal_id", referencedColumnName="id", nullable=true)
     */
    protected $meal;

    /**
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="elements", cascade={"all"})
     * @ORM\JoinColumn(name="cart_id", referencedColumnName="id")
     */
    protected $cart;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\RestaurantBundle\Entity\OptionChoice", inversedBy="elements")
     * @ORM\JoinTable(name="clickeat_shop_cartelements_choices",
     *                joinColumns={@ORM\JoinColumn(name="cartelement_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="choice_id", referencedColumnName="id")})
     */
    protected $choices;

    /**
     * @ORM\ManyToOne(targetEntity="CartElement", inversedBy="childrens", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="CartElement", mappedBy="parent", cascade={"persist", "remove"})
     */
    protected $childrens;

    /**
     * @ORM\ManyToOne(targetEntity="Sale")
     * @ORM\JoinColumn(name="sale_id", referencedColumnName="id")
     */
    protected $sale;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Tax", cascade={"all"})
     * @ORM\JoinColumn(name="tax_id", referencedColumnName="id")
     */
    protected $tax;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\AdditionalSaleProduct")
     * @ORM\JoinColumn(name="additional_sale_product_id", referencedColumnName="id", nullable=true)
     */
    protected $additionalSaleProduct;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\ShopBundle\Entity\CartElement")
     * @ORM\JoinTable(name="clickeat_shop_additional_sale_products_cartelements",
     *      joinColumns={@ORM\JoinColumn(name="clickeat_shop_cartelement_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="additional_sale_product_cartelement_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $addSaleProductCartElements;

    public function __construct()
    {
        $this->setHash(sha1(time().rand(10, 100)));
        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->choices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->addSaleProductCartElements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getProxy()
    {
        if ($product = $this->getProduct()) {
            return $product;
        }

        return $this->getMeal();
    }

    public function getTotalPrice()
    {
        $total = $this->getPrice();

        foreach ($this->getChildrens() as $children) {
            $total += $children->getPrice() * $children->getQuantity();
        }

        return $total * $this->getQuantity();
    }

    public function getRawPrice()
    {
        if ($this->getProxy() && $this->getProxy()->getTax()) {
            return $this->getProxy()->getTax()->getRawPrice($this->getPrice());
        }

        return $this->getPrice();
    }

    public function updatePrice($orderType = OrderType::ORDERTYPE_PREORDER)
    {
        $price = 0;

        if (!is_null($this->additionalSaleProduct)) {
            $price = $price + $this->additionalSaleProduct->getPrice();
        } else {
            $price = $price + $this->getProxy()->getCurrentPrice($orderType);
        }

        if ($this->getChoices() && count($this->getChoices()) > 0) {
            foreach ($this->getChoices() as $choice) {
                $price = $price + $choice->getPrice();
            }
        }

        $this->setPrice($price);
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
     * Set quantity.
     *
     * @param float $quantity
     *
     * @return CartElement
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get quantity.
     *
     * @return float
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set price.
     *
     * @param float $price
     *
     * @return CartElement
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
        return $this->price;
    }

    /**
     * Set product.
     *
     * @param \Clab\RestaurantBundle\Entity\Product $product
     *
     * @return CartElement
     */
    public function setProduct(\Clab\RestaurantBundle\Entity\Product $product = null)
    {
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
     * Set meal.
     *
     * @param \Clab\RestaurantBundle\Entity\Meal $meal
     *
     * @return CartElement
     */
    public function setMeal(\Clab\RestaurantBundle\Entity\Meal $meal = null)
    {
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
     * Set cart.
     *
     * @param \Clab\ShopBundle\Entity\Cart $cart
     *
     * @return CartElement
     */
    public function setCart(\Clab\ShopBundle\Entity\Cart $cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart.
     *
     * @return \Clab\ShopBundle\Entity\Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Add choice.
     *
     * @param \Clab\RestaurantBundle\Entity\OptionChoice $choice
     *
     * @return CartElement
     */
    public function addChoice(\Clab\RestaurantBundle\Entity\OptionChoice $choice)
    {
        $this->choices[] = $choice;

        return $this;
    }

    /**
     * Set choice.
     *
     * @param \Clab\RestaurantBundle\Entity\OptionChoice $choice
     *
     * @return CartElement
     */
    public function setChoices($choices)
    {
        foreach ($choices as $choice) {
            $this->choices[] = $choice;
        }

        return $this;
    }

    /**
     * Remove choice.
     *
     * @param \Clab\RestaurantBundle\Entity\OptionChoice $choice
     */
    public function removeChoice(\Clab\RestaurantBundle\Entity\OptionChoice $choice)
    {
        $this->choices->removeElement($choice);
    }

    /**
     * Get choices.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Set parent.
     *
     * @param \Clab\ShopBundle\Entity\CartElement $parent
     *
     * @return CartElement
     */
    public function setParent(\Clab\ShopBundle\Entity\CartElement $parent = null)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return \Clab\ShopBundle\Entity\CartElement
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Add children.
     *
     * @param \Clab\ShopBundle\Entity\CartElement $children
     *
     * @return CartElement
     */
    public function addChildren(\Clab\ShopBundle\Entity\CartElement $children)
    {
        $this->childrens[] = $children;

        return $this;
    }

    /**
     * Remove children.
     *
     * @param \Clab\ShopBundle\Entity\CartElement $children
     */
    public function removeChildren(\Clab\ShopBundle\Entity\CartElement $children)
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
     * Set sale.
     *
     * @param \Clab\ShopBundle\Entity\Sale $sale
     *
     * @return CartElement
     */
    public function setSale(\Clab\ShopBundle\Entity\Sale $sale = null)
    {
        $this->sale = $sale;

        return $this;
    }

    /**
     * Get sale.
     *
     * @return \Clab\ShopBundle\Entity\Sale
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * Set tax.
     *
     * @param \Clab\RestaurantBundle\Entity\Tax $tax
     *
     * @return CartElement
     */
    public function setTax(\Clab\RestaurantBundle\Entity\Tax $tax = null)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get tax.
     *
     * @return \Clab\RestaurantBundle\Entity\Tax
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set hash.
     *
     * @param string $hash
     *
     * @return CartElement
     */
    public function setHash($hash)
    {
        $this->hash = $hash;

        return $this;
    }

    /**
     * Get hash.
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set additionalSaleProduct.
     *
     * @param \Clab\BoardBundle\Entity\AdditionalSaleProduct $additionalSaleProduct
     *
     * @return CartElement
     */
    public function setAdditionalSaleProduct(\Clab\BoardBundle\Entity\AdditionalSaleProduct $additionalSaleProduct = null)
    {
        $this->additionalSaleProduct = $additionalSaleProduct;

        return $this;
    }

    /**
     * Get additionalSaleProduct.
     *
     * @return \Clab\BoardBundle\Entity\AdditionalSaleProduct
     */
    public function getAdditionalSaleProduct()
    {
        return $this->additionalSaleProduct;
    }

    /**
     * Add addSaleProductCartElement.
     *
     * @param \Clab\ShopBundle\Entity\CartElement $addSaleProductCartElement
     *
     * @return CartElement
     */
    public function addAddSaleProductCartElement(\Clab\ShopBundle\Entity\CartElement $addSaleProductCartElement)
    {
        $this->addSaleProductCartElements[] = $addSaleProductCartElement;

        return $this;
    }

    /**
     * Remove addSaleProductCartElement.
     *
     * @param \Clab\ShopBundle\Entity\CartElement $addSaleProductCartElement
     */
    public function removeAddSaleProductCartElement(\Clab\ShopBundle\Entity\CartElement $addSaleProductCartElement)
    {
        $this->addSaleProductCartElements->removeElement($addSaleProductCartElement);
    }

    /**
     * Get addSaleProductCartElements.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAddSaleProductCartElements()
    {
        return $this->addSaleProductCartElements;
    }
}
