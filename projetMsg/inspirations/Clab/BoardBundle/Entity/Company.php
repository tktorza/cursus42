<?php

namespace Clab\BoardBundle\Entity;

use Clab\ShopBundle\Entity\OrderDetail;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Clab\BoardBundle\Entity\Company.
 * @ORM\Table(name="company")
 * @ORM\Entity(repositoryClass="Clab\BoardBundle\Repository\CompanyRepository")
 */
class Company
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="account_code", type="text", nullable=true)
     */
    protected $accountCode;

    /**
     * @ORM\Column(name="client_payment", type="string")
     */
    protected $companyPayment;

    /**
     * @ORM\Column(name="last_payment_date", type="datetime", nullable=true)
     */
    protected $lastPaymentDate;

    /**
     * @ORM\Column(name="next_due_date", type="datetime", nullable=true)
     */
    protected $nextDueDate;

    /**
     * @ORM\Column(name="balance", type="float", nullable=true)
     */
    protected $balance;

    /**
     * @ORM\OneToMany(targetEntity="Clab\UserBundle\Entity\User", mappedBy="company")
     */
    protected $users;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address")
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     */
    protected $address;

    /**
     * @ORM\Column(name="phone" , type="string", nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(name="email", type="string", nullable=true)
     */
    protected $email;

    /**
     * @ORM\OneToMany(targetEntity="Clab\ShopBundle\Entity\OrderDetail", mappedBy="company")
     * @ORM\OrderBy({"created" = "desc"})
     */
    protected $orders;

    /**
     * @ORM\Column(name="payments_history", type="array", nullable=true)
     */
    protected $paymentsHistory;

    /**
     * Company constructor.
     */
    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new DateTime();
        $this->updated = new DateTime();
        $this->balance = 0;
        $this->paymentsHistory = [];

        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }
    public function setId($id)
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return (string) $this->name;
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
        $this->isOnline = $isOnline;

        return $this;
    }

    public function isOnline()
    {
        return $this->isOnline;
    }

    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    public function isDeleted()
    {
        return $this->getIsDeleted();
    }

    /**
     * @return mixed
     */
    public function getCompanyPayment() {

        return $this->companyPayment;
    }

    /**
     * @param mixed $clientPayment
     * @return $this
     */
    public function setCompanyPayment($companyPayment) {

        $this->companyPayment = $companyPayment;
        return $this;
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

    public function addUser(\Clab\UserBundle\Entity\User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(\Clab\UserBundle\Entity\User $user)
    {
        $this->managers->removeElement($user);
    }

    public function getUsers()
    {
        return $this->users;
    }

    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
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

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setLastPaymentDate($lastPaymentDate)
    {
        $this->lastPaymentDate = $lastPaymentDate;

        return $this;
    }

    public function getLastPaymentDate()
    {
        return $this->lastPaymentDate;
    }

    public function setNextDueDate($nextDueDate)
    {
        $this->nextDueDate = $nextDueDate;

        return $this;
    }

    public function getNextDueDate()
    {
        return $this->nextDueDate;
    }

    public function setBalance($balance)
    {
        $this->balance = $balance;

        return $this;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $Address
     *
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function addOrder(OrderDetail $order)
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
        }

        return $this;
    }

    public function removeorder(OrderDetail  $order)
    {
        $this->orders->removeElement($order);
    }

    public function getOrders()
    {
        return $this->orders;
    }

    public function updateBalance(OrderDetail  $order)
    {
        $this->balance += $order->getPrice();

        return $this;
    }

    public function addPaymentHistory($amount, DateTime $date)
    {
        $this->paymentsHistory[] = array('amount' => $amount, 'date' => $date->format('d/m/Y H:i'));
    }

    public function setPaymentsHistory($paymentsHistory)
    {
        $this->paymentsHistory = $paymentsHistory;

        return $this;
    }

    public function getPaymentsHistory()
    {
        return $this->paymentsHistory;
    }

    public function chargeCompany()
    {
        $date = new DateTime();
        $this->addPaymentHistory($this->balance, $date);
        $this->setBalance(0);
        $this->setLastPaymentDate($date);
        $this->setNextDueDate($date->modify("+1 month"));

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccountCode()
    {
        return $this->accountCode;
    }

    /**
     * @param mixed $accountCode
     */
    public function setAccountCode($accountCode)
    {
        $this->accountCode = $accountCode;

        return $this;
    }
}
