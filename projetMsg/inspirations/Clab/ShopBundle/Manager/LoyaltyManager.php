<?php

namespace Clab\ShopBundle\Manager;

use Clab\ShopBundle\Entity\Loyalty;
use Clab\ShopBundle\Entity\LoyaltyConfig;
use Clab\UserBundle\Entity\User;
use Clab\UserBundle\Service\UserManager;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Monolog\Logger;
use Clab\ShopBundle\Entity\OrderDetail;
use Clab\ShopBundle\Entity\OrderType;
use Symfony\Component\VarDumper\VarDumper;

class LoyaltyManager
{
    protected $em;
    protected $request;
    protected $clickeatMailManager;
    protected $repository;
    protected $configRepository;

    protected $minimumOrder;
    protected $minValue;
    protected $maxValue;
    protected $roundRatio;
    protected $percentageOfOrder;
    protected $validityPeriod;
    protected $firstValidityPeriod;
    protected $refreshPeriod;

    /**
     * Constructor
     */
    public function __construct(EntityManager $em, Request $request)
    {
        $this->em = $em;
        $this->request = $request;
        $this->repository = $this->em->getRepository(Loyalty::class);
        $this->configRepository = $this->em->getRepository(LoyaltyConfig::class);
    }

    public function __call($method,$arguments) {
        if(method_exists($this, $method)) {
            $this->setConfig();
            return call_user_func_array(array($this,$method),$arguments);
        }
    }

    public function setConfig()
    {
        $loyaltyConfig = $this->configRepository->find(1);

        $this->minimumOrder = $loyaltyConfig->getMinimumOrder();
        $this->minValue = $loyaltyConfig->getMinValue();
        $this->maxValue = $loyaltyConfig->getMaxValue();
        $this->roundRatio = $loyaltyConfig->getRoundRatio();
        $this->percentageOfOrder = $loyaltyConfig->getPercentageOfOrder();
        $this->validityPeriod = $loyaltyConfig->getValidityPeriod();
        $this->refreshPeriod = $loyaltyConfig->getRefreshPeriod();
        $this->firstValidityPeriod = $loyaltyConfig->getFirstValidityPeriod();

        return $this;
    }

    private function generateFirstLoyalties(User $user)
    {
        $now = new \DateTime();
        $validity = $now->modify("+".$this->firstValidityPeriod." months");

        $firstLoyalty = new Loyalty();
        $secondLoyalty = new Loyalty();
        $thirdLoyalty = new Loyalty();

        $firstLoyalty->setValue(5);
        $firstLoyalty->setIsCombinable(false);
        $firstLoyalty->setOrderType(OrderType::ORDERTYPE_PREORDER);
        $firstLoyalty->setMinimumOrder(12);
        $firstLoyalty->setValidUntil($validity);
        $firstLoyalty->setUser($user);
        $firstLoyalty->setIsRefreshed(true);

        $secondLoyalty->setValue(10);
        $secondLoyalty->setIsCombinable(false);
        $secondLoyalty->setOrderType(OrderType::ORDERTYPE_ONSITE);
        $secondLoyalty->setMinimumOrder(25);
        $secondLoyalty->setValidUntil($validity);
        $secondLoyalty->setUser($user);
        $secondLoyalty->setIsRefreshed(true);

        $thirdLoyalty->setValue(15);
        $thirdLoyalty->setIsCombinable(false);
        $thirdLoyalty->setOrderType(OrderType::ORDERTYPE_DELIVERY);
        $thirdLoyalty->setMinimumOrder(45);
        $thirdLoyalty->setValidUntil($validity);
        $thirdLoyalty->setUser($user);
        $thirdLoyalty->setIsRefreshed(true);

        $this->em->persist($firstLoyalty);
        $this->em->persist($secondLoyalty);
        $this->em->persist($thirdLoyalty);
        $this->em->flush();
    }

    private function generateLoyaltyFromOrder($order)
    {
        $now = new \DateTime();
        $loyalty = new Loyalty();

        $loyalty->setUser($order->getProfile());
        $loyalty->setValidUntil($now->modify('+'.$this->validityPeriod.' days'));
        $loyalty->setIsCombinable(true);
        $loyalty->setIsRefreshed(false);
        $loyalty->setOrderType(null);
        $loyalty->setMinimumOrder($this->minValue);

        $value = round($order->getPrice() * $this->percentageOfOrder / 100, $this->roundRatio ,  PHP_ROUND_HALF_UP );

        if ($this->maxValue && $value > $this->maxValue) {
           $value = $this->maxValue;
        }

        $loyalty->setValue(($this->minValue && $value < $this->minValue) ? $this->minValue : $value );

        $this->em->persist($loyalty);
        $this->em->flush();

        return $this;
    }

    private function refreshLoyalties(User $user)
    {
        foreach ($user->getLoyalties() as $loyalty) {
            if (!$loyalty->getIsUsed() && !$loyalty->getIsRefreshed()) {
                $validity = clone $loyalty->getValidUntil();
                $loyalty->setValidUntil($validity->modify('+'.$this->refreshPeriod." days"));
                $loyalty->setIsRefreshed(true);
            }
        }

        $this->em->flush();
        return $this;
    }
}
