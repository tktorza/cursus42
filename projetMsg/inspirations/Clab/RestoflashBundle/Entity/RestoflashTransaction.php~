<?php

namespace Clab\RestoflashBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_restoflash_transaction")
 * @ORM\Entity(repositoryClass="Clab\RestoflashBundle\Entity\Repository\RestoflashTransactionRepository")
 */
class RestoflashTransaction
{
    const RESTOFLASH_TRANSACTION_STATUS_NEW = 0;
    const RESTOFLASH_TRANSACTION_STATUS_OPEN = 10;
    const RESTOFLASH_TRANSACTION_STATUS_VALIDATED = 20;
    const RESTOFLASH_TRANSACTION_STATUS_EXPIRED = 30;
    const RESTOFLASH_TRANSACTION_STATUS_REJECTED = 40;
    const RESTOFLASH_TRANSACTION_STATUS_CANCELED = 50;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="restoflashId", type="float", nullable=true)
     */
    private $restoflashId;

    /**
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

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
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    public function __construct()
    {
       $this->created = new \DateTime();
       $this->updated = new \DateTime();
       $this->setAmount(0);
       $this->setStatus(self::RESTOFLASH_TRANSACTION_STATUS_NEW);
    }

    public function isOpen()
    {
        return $this->getStatus() == self::RESTOFLASH_TRANSACTION_STATUS_OPEN;
    }

    public function isValidated()
    {
        return $this->getStatus() == self::RESTOFLASH_TRANSACTION_STATUS_VALIDATED;
    }

    public function updateStatus($status)
    {
        switch ($status) {
            case 'OPEN':
                $this->setStatus(self::RESTOFLASH_TRANSACTION_STATUS_OPEN);
                break;
            case 'VALIDATED':
                $this->setStatus(self::RESTOFLASH_TRANSACTION_STATUS_VALIDATED);
                break;
            case 'EXPIRED':
                $this->setStatus(self::RESTOFLASH_TRANSACTION_STATUS_EXPIRED);
                break;
            case 'REJECTED':
                $this->setStatus(self::RESTOFLASH_TRANSACTION_STATUS_REJECTED);
                break;
            case 'CANCELED':
                $this->setStatus(self::RESTOFLASH_TRANSACTION_STATUS_CANCELED);
                break;
            default:
                $this->setStatus(self::RESTOFLASH_TRANSACTION_STATUS_REJECTED);
                break;
        }
    }

    public function getReference()
    {
        return 'CLICKEAT-TRANSACTION-' . $this->getId();
    }

    public function getMilliTimestamp()
    {
        return $this->getCreated()->getTimestamp() * 1000;
    }

    public function getFormattedAmount()
    {
        $amount = number_format($this->getAmount(), 2);
        $amount = str_replace('.', '', $amount);
        $amount = str_replace(',', '', $amount);

        return $amount;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setRestoflashId($restoflashId)
    {
        $this->restoflashId = $restoflashId;
        return $this;
    }

    public function getRestoflashId()
    {
        return $this->restoflashId;
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

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function getAmount()
    {
        return $this->amount;
    }
}
