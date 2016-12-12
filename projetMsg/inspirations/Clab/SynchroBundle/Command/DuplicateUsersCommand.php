<?php

namespace Clab\SynchroBundle\Command;

use Clab\LocationBundle\Entity\Address;
use Clab\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DuplicateUsersCommand extends ContainerAwareCommand
{
    private $em;
    private $input;
    private $output;
    private $pdo;
    private $query;
    private $userRepository;

    protected function configure()
    {
        $this
            ->setName('clickeat:users:duplicate')
            ->setDescription('Import client from FastMag')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->userRepository = $this->em->getRepository(User::class);
        $this->createUsers();
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

    protected function removeAllUsers() {
        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            $this->em->remove($user);
        }

        $this->em->flush();
    }

    protected function createUsers($offset = 0) {
        $users = $this->getUsers($offset);
        try{
            foreach ($users as $user) {
                $data = $this->createData($user);
                //$addresses = $this->getAddressesFromDatabase($user);
                $addresses = array();
                $userAddress = $this->formatAddress($user, 'PRINCIPALE');
                $addresses[] = $userAddress;

                $user = $this->createUser($data);
                if(!$user->getHomeAddress()) {
                    $this->createAddresses($addresses, $user);
                }
            }

            $this->em->flush();
            $this->em->clear();
        }catch( \Exception $e){
            $this->output->writeln($e->getMessage());
        }

        $this->createUsers($offset + 100);
    }

    protected function getAddressesFromDatabase($user)
    {
        $this->startPdo();
        $addresses = array();

        $sql = "SELECT * FROM `clientadresse` WHERE `Client` = :userId";

        $this->query = $this->pdo->prepare($sql);
        $this->query->bindParam(':userId', $user['Client'], \PDO::PARAM_INT);

        $this->query->execute();

        foreach ($this->query->fetchAll() as $address) {
            $addresses[] = $this->formatAddress($address);
        }

        $this->closePdo();

        return $addresses;
    }

    protected function formatAddress($data, $name) {
        return array (
            'name' => $name ?: $data['AdrLivraison'] . ' ' . $data['Nom'],
            'street' => $data['Adresse1'],
            'zip' => (int) $data['codepostal'],
            'city' => $data['Ville'],
            'company' => $data['Societe'],
            'building' => $data['Batiment'],
            'doorCode' => $data['Digicode1'],
            'secondDoorCode' => $data['Digicode2'],
            'intercom' => $data['Interphone'],
            'floor' => $data['Etage'],
            'door' => $data['Porte'],
            'staircase' => 'oui' === $data['Escalier'],
            'elevator' => 'oui' === $data['Ascenseur'],
        );
    }

    protected function getUsers($offset)
    {
        $this->startPdo();
        $sql = "SELECT * FROM `clients` where `DateModif` >= NOW() - INTERVAL 1 DAY LIMIT 100 OFFSET :offset";

        //$sql = "SELECT * FROM `clients` where `email` like '' LIMIT 100 OFFSET :offset";

        $this->query = $this->pdo->prepare($sql);
        $this->query->bindParam(':offset', $offset, \PDO::PARAM_INT);

        $this->query->execute();

        $results = $this->query->fetchAll();
        $this->closePdo();

        return $results;
    }

    protected function createData($data) {
        return array (
            'fastMagId' => $data['Client'],
            'username' => $data['email'] ? $data['email'] : $data['Portable'],
            'email' => $data['email'] ? $data['email'] : ($data['Portable'] && $data['Portable'] != '' ? $data['Portable']."@call.matsuri.fr" : $data['Telephone']."@call.matsuri.fr"),
            'plainPassword' => $data['MotPasse'] ? $data['MotPasse'] : uniqid(),
            'phone' => $data['Portable'] && $data['Portable'] != '' ? $data['Portable'] : $data['Telephone'],
            'firstName' => $data['Prenom'],
            'lastName' => $data['Nom'],
            'isMale' => !($data['Sexe'] == 'F'),
            'zipcode' => $data['codepostal'],
            'enabled' => true
        );
    }

    protected function createUser($data) {
        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $exist = false;

        if ($user = $userManager->findUserByEmail($data['email'])) {
            $exist = true;
        } else {
            $user = $userManager->createUser();
            $user->addRole('ROLE_MEMBER');
        }

        foreach ($data as $key => $value) {
            $property = sprintf('set%s', ucfirst($key));
            $user->$property($value);
        }

        $message = $exist ? sprintf('update user : %s', $user->getEmail()) : sprintf('create user : %s', $user->getEmail());

        $this->output->writeLn($message);
        $userManager->updateUser($user, true);

        return $user;
    }

    protected function createAddresses($addresses, $user) {
        foreach ($addresses as $data) {

            $address = new Address();

            foreach ($data as $key => $value) {
                $property = sprintf('set%s', ucfirst($key));
                $address->$property($value);
            }

            $address->setUser($user);
            $user->setHomeAddress($address);
            $this->em->persist($address);
        }

        $this->em->flush();
    }
}
