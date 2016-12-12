<?php

namespace Clab\ShopBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="caisse_payment")
 * @ORM\Entity()
 */
class Payment
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\ShopBundle\Entity\PaymentMethod")
     * @ORM\JoinTable(name="payment_paymentmethods",
     *      joinColumns={@ORM\JoinColumn(name="payment_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="paymentmethod_id", referencedColumnName="id")}
     *      )
     */
    private $paymentMethods;

    /**
     * @ORM\Column(name="amount", type="float", nullable=true)
     */
    protected $amount;

    /**
     * @ORM\Column(name="is_canceled", type="boolean")
     */
    protected $isCanceled;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     */
    private $updated;

    public function __construct()
    {
        $this->setIsCanceled(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->paymentMethods = new ArrayCollection();
    }

    public function addPaymentMethod(PaymentMethod $paymentMethods)
    {
        $this->paymentMethods[] = $paymentMethods;
    }

    public function removePaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethods->removeElement($paymentMethod);
    }

    public function __toString()
    {
        return $this->amount;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPaymentMethods()
    {
        return $this->paymentMethods;
    }

    /**
     * @param mixed $paymentMethods
     *
     * @return $this
     */
    public function setPaymentMethods($paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param mixed $amount
     *
     * @return $this
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsCanceled()
    {
        return $this->isCanceled;
    }

    /**
     * @param mixed $isCanceled
     *
     * @return $this
     */
    public function setIsCanceled($isCanceled)
    {
        $this->isCanceled = $isCanceled;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     *
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     *
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }
}
