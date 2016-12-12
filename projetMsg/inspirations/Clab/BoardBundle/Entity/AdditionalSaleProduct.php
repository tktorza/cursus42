<?php

namespace Clab\BoardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AdditionalSaleProduct.
 *
 * @ORM\Table(name="clickeat_additional_sale_product")
 * @ORM\Entity()
 */
class AdditionalSaleProduct
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\AdditionalSale", inversedBy="additionalSaleProducts" )
     */
    private $additionalSale;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Product", inversedBy="additionalSaleProducts")
     * @ORM\JoinColumn(name="product_id", referencedColumnName="id" )
     */
    private $product;

    /**
     * @ORM\Column(name="price", type="float", nullable=false)
     * @Assert\Range(min=0,minMessage="le supplement indiqué doit être positif")
     */
    private $price;

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
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return AdditionalSaleProduct
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
     * @return AdditionalSaleProduct
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
     * Set price.
     *
     * @param float $price
     *
     * @return AdditionalSaleProduct
     */
    public function setPrice($price)
    {
        if ($price > 0) {
            $this->price = $price;
        } else {
            $this->price = $this->product->getPrice();
        }

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
     * Set additionalSale.
     *
     * @param \Clab\BoardBundle\Entity\AdditionalSale $additionalSale
     *
     * @return AdditionalSaleProduct
     */
    public function setAdditionalSale(\Clab\BoardBundle\Entity\AdditionalSale $additionalSale)
    {
        $this->additionalSale = $additionalSale;

        return $this;
    }

    /**
     * Get additionalSale.
     *
     * @return \Clab\BoardBundle\Entity\AdditionalSale
     */
    public function getAdditionalSale()
    {
        return $this->additionalSale;
    }

    /**
     * Set product.
     *
     * @param \Clab\RestaurantBundle\Entity\Product $product
     *
     * @return AdditionalSaleProduct
     */
    public function setProduct(\Clab\RestaurantBundle\Entity\Product $product)
    {
        $product->addAdditionalSaleProduct($this);
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

    public function __toString()
    {
        return $this->product->getName();
    }
}
