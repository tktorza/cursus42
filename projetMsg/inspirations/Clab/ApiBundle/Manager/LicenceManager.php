<?php

namespace Clab\ApiBundle\Manager;

use Clab\ApiBundle\Entity\Licence;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\UserBundle\Repository\LicenceRepository;
use Doctrine\ORM\EntityManager;
use Sonata\CoreBundle\Exception\InvalidParameterException;

class LicenceManager
{
    private $em;
    /**
     * @var LicenceRepository
     */
    private $repository;
    /**
     * @var Licence
     */
    private $licence;
    private $licenceNumber;
    private $serial;
    private $restaurant;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository(Licence::class);
        $this->licence = $this->licenceNumber = $this->serial = $this->restaurant = null;
    }

    public function setLicenceNumber($licenceNumber) {
        $this->licenceNumber = $licenceNumber;

        return $this;
    }

    public function setSerial($serial) {
        $this->serial = $serial;

        return $this;
    }

    public function setRestaurant(Restaurant $restaurant) {
        $this->restaurant = $restaurant;

        return $this;
    }

    /**
     * Check is a licence is ready to apply
     *
     * @return int
     */
    private function licenceIsAvailable() {
        $licence = $this->repository->licenceIsAvailable($this->licenceNumber, $this->restaurant);
        $licenceIsFreeToAttributed = count($licence);

        if ($licenceIsFreeToAttributed) {
            $this->licence = $licence;
        }

        return $licenceIsFreeToAttributed;
    }

    /**
     * Create a new licence object and persist it
     *
     * @return bool
     */
    public function createLicence() {
        if (!$this->restaurant) {
            throw new InvalidParameterException('You need to give a Restaurant id');
        }

        $this->generateLicenceNumber();

        $this->licence = new Licence();
        $this->licence->setLicence($this->licenceNumber);
        $this->licence->setRestaurant($this->restaurant);

        $this->saveLicence();

        return array('success' => true, 'licence' => $this->licenceNumber);
    }

    /**
     * Generate a ten digits licence number
     */
    private function generateLicenceNumber() {
        $random = rand(0, pow(10, 10) - 1);
        $this->licenceNumber = str_pad($random, 10, 0, STR_PAD_LEFT);
    }

    /**
     * Generate a serial
     *
     * @return string
     */
    private function generateSerial() {
        $now = new \DateTime(null, new \DateTimeZone('Europe/Paris'));
        $serial = sha1(md5($now->format('dmyhmi')));

        return $serial;
    }

    /**
     * Set a new hardware to a free licence
     *
     * @return array
     */
    public function applyToLicence() {
        if (!$this->licenceNumber || !$this->restaurant) {
            throw new InvalidParameterException('You need to give a Restaurant id and a licence');
        }

        if ($this->licenceIsAvailable()) {
            $serial = $this->generateSerial();
            $this->licence->setSerial($serial);

            $this->saveLicence();

            return array('success' => true, 'message' => $serial);
        }

        return array('success' => false, 'message' => 'Licence is already attributed');
    }

    /**
     * Reset an attributed licence
     */
    public function resetLicence() {
        if (!$this->licenceNumber || !$this->restaurant || !$this->serial) {
            throw new InvalidParameterException('You need to give a Restaurant id, a licence number and a serial');
        }

        $this->licence = $this->repository->getOne($this->licenceNumber, $this->restaurant, $this->serial);

        if (!count($this->licence)) {
            return array('success' => false);
        }

        $this->licence->resetSerial();
        $this->licence->setResetDate();

        $this->saveLicence();

        return array('success' => true);
    }

    /**
     * ping an attributed licence
     */
    public function pingLicence() {
        if (!$this->licenceNumber || !$this->restaurant || !$this->serial) {
            throw new InvalidParameterException('You need to give a Restaurant id, a licence number and a serial');
        }

        $this->licence = $this->repository->getOne($this->licenceNumber, $this->restaurant, $this->serial);

        if (!$this->licence) {
            return array('success' => false);
        }

        $this->licence->setPingDate();
        $this->saveLicence();

        return array('success' => true);
    }

    /**
     * Save the licence
     */
    public function saveLicence() {
        $this->em->persist($this->licence);
        $this->em->flush();
    }
}