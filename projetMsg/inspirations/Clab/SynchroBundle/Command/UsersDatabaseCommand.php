<?php

namespace Clab\SynchroBundle\Command;

use Clab\BoardBundle\Entity\UserDataBase;
use Clab\LocationBundle\Entity\Address;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UsersDatabaseCommand extends ContainerAwareCommand
{
    private $em;
    private $input;
    private $output;
    private $offset;
    private $pdo;
    private $query;
    private $userRepository;

    protected function configure()
    {
        $this
            ->setName('clickeat:users:database')
            ->addArgument('restaurantId', InputArgument::REQUIRED, 'restaurantId')
            ->setDescription('Import client to api database')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->userRepository = $this->em->getRepository(User::class);

        $restaurant = $this->em->getRepository(Restaurant::class)->find($input->getArgument('restaurantId'));

        if($restaurant) {
            $this->createUsers($restaurant);
        }
    }

    protected function createUsers(Restaurant $restaurant) {
        $this->offset = is_numeric($this->offset) ? $this->offset + 10 : 0;

        $yesterday = new \DateTime();
        $yesterday->modify("-1 days");

        $users = $this->userRepository
            ->createQueryBuilder('u')
            ->leftJoin('u.homeAddress', 'address')
            ->where('address.city is not null')
            ->andWhere('address.city like :city')
            ->andWhere('u.created >= :yesterday')
            ->setParameter('city', $restaurant->getAddress()->getCity()."%")
            ->setParameter('yesterday', $yesterday)
            ->setFirstResult($this->offset)
            ->setMaxResults(10)
            ->getQuery()
            ->execute()
        ;

        if (!count($users)) {
            return;
        }

        foreach ($users as $userData) {
            $user = $userData;

            $userDb = $this->em->getRepository(UserDataBase::class)->findOneBy(array('user'=> $user, 'restaurant' => $restaurant));

            if(!$userDb) {
                $userDb = new UserDataBase();
                $userDb->setIsDeleted(false);
            }

            $userDb->setUser($user);
            $userDb->setRestaurant($restaurant);
            $userDb->setEmail($user->getEmail());
            $userDb->setHomeAddress($user->getHomeAddress());
            $userDb->setFirstName($user->getFirstName());
            $userDb->setLastName($user->getLastName());
            $userDb->setPhone($user->getPhone());

            $this->em->persist($userDb);
            $this->output->writeln('persist '.$user->getId());
        }

        $this->output->writeln('Flush !');
        $this->em->flush();
        $this->em->clear();

        $this->createUsers($restaurant);
    }

}
