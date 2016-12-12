<?php

namespace Clab\ApiBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clickeat_caisse_session")
 * @ORM\Entity(repositoryClass="Clab\ApiBundle\Repository\SessionCaisseRepository")
 */
class SessionCaisse
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @ORM\Column(name="date_start", type="datetime", nullable = true)
     */
    protected $dateStart;

    /**
     * @ORM\Column(name="date_end", type="datetime", nullable = true)
     */
    protected $dateEnd;

    /**
     * @ORM\Column(name="cash_flow_end", type="float", nullable = true)
     */
    protected $cashFlowEnd;

    /**
     * @ORM\Column(name="cash_flow_start", type="float", nullable = true)
     */
    protected $cashFlowStart;

    /**
     * @ORM\Column(name="cash_flow_diff", type="float", nullable = true)
     */
    protected $cashFlowDiff;

    /**
     * @ORM\Column(name="cash_flow_end_theoric", type="float", nullable = true)
     */
    protected $cashFlowEndTheoric;

    /**
     * @ORM\Column(name="in_out", type="array", nullable = true)
     */
    protected $inOut;

    /**
     * @ORM\Column(name="refund", type="array", nullable = true)
     */
    protected $refund;

    /**
     * @ORM\Column(name="cash", type="float", nullable = true)
     */
    protected $cash;

    /**
     * @ORM\Column(name="cb", type="float", nullable = true)
     */
    protected $cb;

    /**
     * @ORM\Column(name="resto_ticket", type="float", nullable = true)
     */
    protected $restoTicket;

    /**
     * @ORM\Column(name="check_amount", type="float", nullable = true)
     */
    protected $check;

    /**
     * @ORM\Column(name="amex", type="float", nullable = true)
     */
    protected $amex;

    /**
     * @ORM\Column(name="product_switch", type="float", nullable = true)
     */
    protected $productSwitch;

    /**
     * @ORM\Column(name="commercial_gesture", type="float", nullable = true)
     */
    protected $commercialGesture;

    /**
     * @ORM\Column(name="accidental_debit", type="float", nullable = true)
     */
    protected $accidentalDebit;

    /**
     * @ORM\Column(name="test_error", type="float", nullable = true)
     */
    protected $testError;

    /**
     * @ORM\Column(name="client_problem", type="float", nullable = true)
     */
    protected $clientProblem;

    /**
     * @ORM\Column(name="commentary", type="text", nullable = true)
     */
    protected $commentary;


    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     */
    private $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User", inversedBy="sessionsCaisse")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * @ORM\Column(name="orders", type="array", nullable = true)
     */
    protected $orders;

    /**
     * @ORM\Column(name="tva", type="array", nullable = true)
     */
    protected $tva;

    /**
     * @ORM\Column(name="device", type="text", nullable = false)
     */
    private $device;

    /**
     * @ORM\Column(name="device_name", type="text", nullable = true)
     */
    private $deviceName;

    private $inOuts;
    private $formatedDateStart;
    private $formatedDateEnd;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * @param mixed $dateStart
     *
     * @return $this
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormatedDateStart()
    {
        return $this->dateStart ? $this->dateStart->format('d-m-Y G:i'): null;
    }

    public function setFormatedDateStart($formatedDateStart)
    {
        $this->formatedDateStart = $formatedDateStart;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDateEnd()
    {
        return $this->dateEnd;
    }

    /**
     * @param mixed $dateEnd
     *
     * @return $this
     */
    public function setDateEnd($dateEnd)
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFormatedDateEnd()
    {
        return $this->dateEnd ? $this->dateEnd->format('d-m-Y G:i') : null;
    }

    public function setFormatedDateEnd($formatedDateEnd)
    {
        $this->formatedDateEnd = $formatedDateEnd;

        return $this;
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

    /**
     * @return mixed
     */
    public function getCashFlowStart()
    {
        return $this->cashFlowStart;
    }

    /**
     * @param mixed $cashFlowStart
     *
     * @return $this
     */
    public function setCashFlowStart($cashFlowStart)
    {
        $this->cashFlowStart = $cashFlowStart;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCashFlowEnd()
    {
        return $this->cashFlowEnd;
    }

    /**
     * @param mixed $cashFlowEnd
     *
     * @return $this
     */
    public function setCashFlowEnd($cashFlowEnd)
    {
        $this->cashFlowEnd = $cashFlowEnd;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCashFlowDiff()
    {
        return $this->cashFlowDiff;
    }

    /**
     * @param mixed $cashFlowDiff
     *
     * @return $this
     */
    public function setCashFlowDiff($cashFlowDiff)
    {
        $this->cashFlowDiff = $cashFlowDiff;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCashFlowEndTheoric()
    {
        return $this->cashFlowEndTheoric;
    }

    /**
     * @param mixed $cashFlowEndTheoric
     *
     * @return $this
     */
    public function setCashFlowEndTheoric($cashFlowEndTheoric)
    {
        $this->cashFlowEndTheoric = $cashFlowEndTheoric;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInOut()
    {
        return $this->inOut;
    }

    /**
     * @param mixed $inOut
     *
     * @return $this
     */
    public function setInOut(array $inOut)
    {
        $this->inOut = $inOut;

        return $this;
    }

    public function addInOut($inOut, $date)
    {
        $this->inOut[] = ['inOut' => $inOut, 'date' => $date];

        return $this;
    }

    public function setInOuts($inOuts)
    {
        $this->inOuts = $inOuts;

        return $this;
    }

    public function getInOuts()
    {
        $inOuts = 0.;

        if (is_array($this->inOut)) {
            foreach ($this->inOut as $inOut) {
                if(is_array($inOut) && isset($inOut['inOut'])) {
                    $inOuts += $inOut['inOut'];
                }
            }
        }

        return $inOuts;
    }
    /**
     * @return mixed
     */
    public function getRefund()
    {
        return $this->refund;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setRefund(array $refund)
    {
        $this->refund = $refund;

        return $this;
    }

    public function addRefund($orderId, $type, $amount)
    {
        $this->refund[] = ['orderId'=> $orderId, 'type' => $type, 'amount' => $amount];

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCash()
    {
        return $this->cash;
    }

    /**
     * @return $this
     */
    public function setCash($cash)
    {
        $this->cash = $cash;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCb()
    {
        return $this->cb;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setCb($cb)
    {
        $this->cb = $cb;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRestoTicket()
    {
        return $this->restoTicket;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setRestoTicket($restoTicket)
    {
        $this->restoTicket = $restoTicket;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCheck()
    {
        return $this->check;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setCheck($check)
    {
        $this->check = $check;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAmex()
    {
        return $this->amex;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setAmex($amex)
    {
        $this->amex = $amex;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductSwitch()
    {
        return $this->productSwitch;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setProductSwitch($productSwitch)
    {
        $this->productSwitch = $productSwitch;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommercialGesture()
    {
        return $this->commercialGesture;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setCommercialGesture($commercialGesture)
    {
        $this->commercialGesture = $commercialGesture;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccidentalDebit()
    {
        return $this->accidentalDebit;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setAccidentalDebit($accidentalDebit)
    {
        $this->accidentalDebit = $accidentalDebit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTestError()
    {
        return $this->testError;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setTestError($testError)
    {
        $this->testError = $testError;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClientProblem()
    {
        return $this->clientProblem;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setClientProblem($clientProblem)
    {
        $this->clientProblem = $clientProblem;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCommentary()
    {
        return $this->commentary;
    }

    /**
     * @param mixed $cashRefund
     *
     * @return $this
     */
    public function setCommentary($commentary)
    {
        $this->commentary = $commentary;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }

    /**
     * @param mixed $restaurant
     *
     * @return $this
     */
    public function setRestaurant($restaurant)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getDevice()
    {
        return $this->device;
    }

    public function setDevice($device)
    {
        $this->device = $device;

        return $this;
    }

    public function getDeviceName()
    {
        return $this->deviceName;
    }

    public function setDeviceName($deviceName)
    {
        $this->deviceName = $deviceName;

        return $this;
    }


    public function getOrders()
    {
        return $this->orders;
    }

    public function setOrders($orders)
    {
        $this->orders = $orders;

        return $this;
    }

    public function getTva()
    {
        return $this->tva;
    }

    public function setTva($tva)
    {
        $this->tva = $tva;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }
}
