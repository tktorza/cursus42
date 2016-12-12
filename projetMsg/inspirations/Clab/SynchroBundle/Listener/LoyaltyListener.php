<?php

namespace Clab\SynchroBundle\Listener;

use Clab\ShopBundle\Entity\Loyalty;
use Clab\ShopBundle\Entity\OrderType;
use Clab\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use FluentPDO;

class LoyaltyListener {
    private $entityManager;
    private $repository;
    private $fluentPDO;
    private $fastMagEnabled = true;

    public function construct(LifecycleEventArgs $args)
    {
        if (!$this->fastMagEnabled) {
            return;
        }

        $this->entityManager = $args->getEntityManager();
        $this->repository = $this->entityManager->getRepository(User::class);
        $pdo = new \PDO(
            'mysql:host=37.187.132.229;dbname=fastmag_test',
            'eric',
            'matsuri2013'
        );
        $this->fluentPDO = new FluentPDO($pdo);
    }

    public function preUpdate(LifecycleEventArgs $args) {
        $this->construct($args);
        $loyalty = $this->getObject($args);

        if (!$loyalty) {
            return;
        }

        $this->updateToFastmag($loyalty);
    }

    public function postPersist(LifecycleEventArgs $args) {
        $this->construct($args);
        $loyalty = $this->getObject($args);

        if (!$loyalty) {
            return;
        }

        if ($loyalty->getFastmagId()) {
            $this->updateToFastmag($loyalty);
        } else {
            $this->createToFastmag($loyalty);
        }
    }

    private function updateToFastmag(Loyalty $loyalty) {
        $data = $this->composerFastMagLoyalty($loyalty);

        $this->fluentPDO->update('cartescadeaux')->set($data)->where('ID', $loyalty->getFastmagId())->execute();
    }

    private function createToFastmag(Loyalty $loyalty) {
        $data = $this->composerNewFastMagLoyalty($loyalty);
        $user = $loyalty->getUser();

        $this->fluentPDO->insertInto('cartescadeaux')->values($data)->execute();
        $loyaltyInFastmag = $this->fluentPDO->from('cartescadeaux')->where('email', $user->getEmail())->fetch();

        $loyalty->setFastmagId($loyaltyInFastmag['ID']);
        $this->entityManager->persist($loyalty);
        $this->entityManager->flush();

        $this->fluentPDO->close();
    }

    private function composerFastMagLoyalty(Loyalty $loyalty) {

        return array(
            'Client' => $loyalty->getUser()->getFastMagId(),
            'DateValidite' => $loyalty->getValidUntil()->format('Y-m-d'),
            'DateCreation' => $loyalty->getCreated()->format('Y-m-d'),
            'DateDebut' => $loyalty->getCreated()->format('Y-m-d'),
            'Montant' => ($loyalty->getIsUsed() ? 0.00 : $loyalty->getValue()),
            'MontantInitial' => $loyalty->getValue(),
            'NbProlongations' => intval($loyalty->getIsUsed()),
        );
    }

    private function composerNewFastMagLoyalty(Loyalty $loyalty) {
        $motif = 'FID_GLOBAL';
        if($loyalty->getOrderType()) {
            switch($loyalty->getOrderType()) {
                case OrderType::ORDERTYPE_PREORDER :
                    $motif = 'FID_EMP';
                    break;
                case OrderType::ORDERTYPE_DELIVERY:
                    $motif = 'FID_LIV';
                    break;
                case OrderType::ORDERTYPE_ONSITE:
                    $motif = 'FID_SPL';
                    break;
            };
        }


        return array(
            'Client' => $loyalty->getUser()->getFastMagId(),
            'DateValidite' => $loyalty->getValidUntil()->format('Y-m-d'),
            'DateCreation' => $loyalty->getCreated()->format('Y-m-d'),
            'DateDebut' => $loyalty->getCreated()->format('Y-m-d'),
            'Montant' => $loyalty->getValue(),
            'MontantInitial' => $loyalty->getValue(),
            'NbProlongations' => intval($loyalty->getIsUsed()),
            'Cumulable' => $loyalty->getIsCombinable(),
            'Motif' => $motif,
            'VenteOrigine' => 0
        );
    }

    private function getObject(LifecycleEventArgs $args) {
        $this->construct($args);
        $object = $args->getObject();

        if ($object instanceof Loyalty) {
            return $args->getObject();
        }

        return false;
    }
}
