<?php

namespace Clab\SynchroBundle\Command;

use Clab\UserBundle\Entity\User;
use Clab\ShopBundle\Entity\Loyalty;
use Clab\ShopBundle\Entity\OrderType;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DuplicateLoyaltyCommand extends ContainerAwareCommand
{
    private $em;
    private $input;
    private $output;
    private $pdo;
    private $query;
    private $loyaltyRepository;
    private $userRepository;

    protected function configure()
    {
        $this
            ->setName('clickeat:loyalty:duplicate')
            ->setDescription('Import loyalty from FastMag')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->loyaltyRepository = $this->em->getRepository(Loyalty::class);
        $this->userRepository = $this->em->getRepository(User::class);

        $this->createLoyalties();
    }

    protected function startPdo() {
        $this->pdo = $pdo = new \PDO(
            'mysql:host=37.187.132.229;dbname=fastmag_matsuri',
            'click-eat',
            'yummy123'
        );
    }

    protected function closePdo() {
        $this->query->closeCursor();
        $this->pdo = null;
    }

    protected function createLoyalties($offset = 0) {
        $loyalties = $this->getLoyalties($offset);

        foreach ($loyalties as $loyalty) {
            $this->createLoyalty($loyalty);
        }

        $this->em->flush();
        $this->em->clear();
        $this->createLoyalties($offset + 100);
    }

    protected function createLoyalty($loyaltyData) {
        $exist = $this->loyaltyRepository->findOneByFastmagId($loyaltyData['ID']);
        $loyalty = $exist ? $exist : new Loyalty();

        $user = $this->userRepository->findOneByFastmagId($loyaltyData['Client']);

        if ($user) {
            $loyalty->setBarCode($loyaltyData['GiftCardBarCode']);
            $loyalty->setOrderType($this->getType($loyaltyData['Motif']));
            $loyalty->setFastmagId($loyaltyData['ID']);
            $loyalty->setCreated(new \DateTime($loyaltyData['DateCreation']));
            $loyalty->setValidUntil(new \DateTime($loyaltyData['DateValidite']));
            $loyalty->setValue($loyaltyData['Montant']);
            $loyalty->setIsCombinable($loyaltyData['Cumulable']);
            $loyalty->setUser($user);
            $loyalty->setMinimumOrder(0);

            if ($loyaltyData['NbProlongations']) {
                $loyalty->setIsRefreshed(true);
            }

            $this->em->persist($loyalty);

            $user->addLoyalty($loyalty);
            $this->em->persist($user);

            $message = $exist ? sprintf('update loyalty : %s', $loyalty->getFastmagId()) : sprintf('create loyalty : %s', $loyalty->getFastmagId());

            $this->output->writeLn($message);
        }
    }

    protected function getType($type) {
        switch ($type) {
            case 'FID_EMP':
                $type = OrderType::ORDERTYPE_PREORDER;
                break;
            case 'FID_LIV':
                $type = OrderType::ORDERTYPE_DELIVERY;
                break;
            case 'FID_SPL':
                $type = OrderType::ORDERTYPE_ONSITE;
                break;
            default:
                $type = null;
                break;
        }

        return $type;
    }

    protected function getLoyalties($offset)
    {
        $this->startPdo();
        $sql = "SELECT * FROM `cartescadeaux` where  `Montant` > 0 and `DateValidite` >= NOW()  LIMIT 100 OFFSET :offset";

        $this->query = $this->pdo->prepare($sql);
        $this->query->bindParam(':offset', $offset, \PDO::PARAM_INT);

        $this->query->execute();

        $results = $this->query->fetchAll();
        $this->closePdo();

        return $results;
    }
}