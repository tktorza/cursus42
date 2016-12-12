<?php

namespace Clab\SynchroBundle\Listener;

use Clab\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

class UserListener {
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
        $this->fluentPDO = new \FluentPDO($pdo);
    }

    public function preUpdate(LifecycleEventArgs $args) {
        $this->construct($args);
        $user = $this->getObject($args);

        if (!$user) {
            return;
        }

        $this->updateToFastmag($user);
    }

    public function postPersist(LifecycleEventArgs $args) {
        $this->construct($args);
        $user = $this->getObject($args);

        if (!$user) {
            return;
        }

        if ($user->getFastmagId()) {
            $this->updateToFastmag($user);
        } else {
            $this->createToFastmag($user);
        }
    }

    private function updateToFastmag(User $user) {
        $data = $this->composerFastMagUser($user);

        $this->fluentPDO->update('clients')->set($data)->where('Client', $user->getFastmagId())->execute();
    }

    private function createToFastmag(User $user) {
        $data = $this->composerNewFastMagUser($user);

        $this->fluentPDO->insertInto('clients')->values($data)->execute();
        $userInFastmag = $this->fluentPDO->from('clients')->where('email', $user->getEmail())->fetch();

        $user->setFastmagId($userInFastmag['Client']);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->fluentPDO->close();
    }

    private function composerFastMagUser(User $user) {
        return array(
            'Portable' => $user->getPhone(),
            'Prenom' => $user->getFirstName(),
            'Nom' => $user->getLastName(),
            'codepostal' => $user->getZipcode()
        );
    }

    private function composerNewFastMagUser(User $user) {
        return array(
            'Portable' => $user->getPhone(),
            'email' => $user->getEmail(),
            'Prenom' => $user->getFirstName(),
            'Nom' => $user->getLastName(),
            'codepostal' => $user->getZipcode(),
            'DateModif' => $user->getUpdated()->getTimestamp()
        );
    }

    private function getObject(LifecycleEventArgs $args) {
        $object = $args->getObject();
        if ($object instanceof User) {
            return $args->getObject();
        }

        return false;
    }
}
