<?php

namespace Clab\RestoflashBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_restoflash_token")
 * @ORM\Entity(repositoryClass="Clab\RestoflashBundle\Entity\Repository\RestoflashTokenRepository")
 */
class RestoflashToken
{
    const RESTOFLASH_TOKEN_STATUS_NEW = 0;
    const RESTOFLASH_TOKEN_STATUS_OPEN = 10;
    const RESTOFLASH_TOKEN_STATUS_VALIDATED = 20;
    const RESTOFLASH_TOKEN_STATUS_EXPIRED = 30;
    const RESTOFLASH_TOKEN_STATUS_REJECTED = 40;
    const RESTOFLASH_TOKEN_STATUS_CANCELED = 50;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @ORM\Column(name="token", type="string", nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(name="credit", type="float", nullable=true)
     */
    private $credit;

    /**
     * @ORM\Column(name="maxTransaction", type="float", nullable=true)
     */
    private $maxTransaction;

    public function __construct()
    {
       $this->created = new \DateTime();
       $this->updated = new \DateTime();
       $this->setStatus(self::RESTOFLASH_TOKEN_STATUS_NEW);
    }

    public function getReference()
    {
        return 'CLICKEAT-TOKEN-' . $this->getId();
    }

    public function getMilliTimestamp()
    {
        return $this->getCreated()->getTimestamp() * 1000;
    }

    public function isActive()
    {
        return $this->getStatus() == self::RESTOFLASH_TOKEN_STATUS_VALIDATED;
    }

    public function updateStatus($status)
    {
        switch ($status) {
            case 'OPEN':
                $this->setStatus(self::RESTOFLASH_TOKEN_STATUS_OPEN);
                break;
            case 'VALIDATED':
                $this->setStatus(self::RESTOFLASH_TOKEN_STATUS_VALIDATED);
                break;
            case 'EXPIRED':
                $this->setStatus(self::RESTOFLASH_TOKEN_STATUS_EXPIRED);
                break;
            case 'REJECTED':
                $this->setStatus(self::RESTOFLASH_TOKEN_STATUS_REJECTED);
                break;
            case 'CANCELED':
                $this->setStatus(self::RESTOFLASH_TOKEN_STATUS_CANCELED);
                break;
            default:
                $this->setStatus(self::RESTOFLASH_TOKEN_STATUS_REJECTED);
                break;
        }
    }

    public function updateInfos(array $infos = array()) {
        if(isset($infos['avalaibleCredit'])) {
            $this->setCredit($infos['avalaibleCredit']);
        }

        if(isset($infos['currentTransactionMaxValue'])) {
            $this->setMaxTransaction($infos['currentTransactionMaxValue']);
        }
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

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function setCredit($credit)
    {
        $this->credit = $credit;
        return $this;
    }

    public function getCredit()
    {
        return $this->credit;
    }

    public function setMaxTransaction($maxTransaction)
    {
        $this->maxTransaction = $maxTransaction;
        return $this;
    }

    public function getMaxTransaction()
    {
        return $this->maxTransaction;
    }
}
