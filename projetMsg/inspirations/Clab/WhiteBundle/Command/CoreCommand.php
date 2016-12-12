<?php

namespace Clab\WhiteBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CoreCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('generate:core')
            ->setDescription('Create a chainstore')
            ->addArgument(
                'name',
                InputArgument::REQUIRED,
                'name of the chainstore'
            )
            ->addArgument(
                'email',
                InputArgument::REQUIRED,
                'email of the super admin'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'password of the super admin'
            )
            ->addArgument(
                'client_payment',
                InputArgument::REQUIRED,
                'Is the subcription paied by chainstore'
            )
            ->addArgument(
                'price',
                InputArgument::REQUIRED,
                'Price of the plan'
            )
            ->addArgument(
                'idStripe',
                InputArgument::REQUIRED,
                'ID of the stripe plan'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $price = $input->getArgument('price');
        $idStripe = $input->getArgument('idStripe');
        $image = $this->getContainer()->get('clab_core.core_manager')->createDefaultImage();
        $clientPayment = $input->getArgument('client_payment');
        $command = $this->getApplication()->find('fos:user:create');

        $arguments = array(
            'command' => 'fos:user:create',
            'username'    => $email,
            'email'    => $email,
            'password'    => $password,
        );

        $fosInput = new ArrayInput($arguments);
        $command->run($fosInput, $output);

        $command2 = $this->getApplication()->find('fos:user:promote');

        $arguments2 = array(
            'command' => 'fos:user:promote',
            'username'    => $email,
            '--super'  => true,
        );

        $fosInputRole = new ArrayInput($arguments2);
        $command2->run($fosInputRole, $output);
        $user = $this->getContainer()->get('doctrine')->getRepository('ClabUserBundle:User')->find(1);
        $user->addRole('ROLE_MANAGER');
        $chainstore = $this->getContainer()->get('clab_core.core_manager')->createChainStore($name,$user,$clientPayment);
        $taxes = $this->getContainer()->get('clab_core.core_manager')->createTaxes();
        $plan = $this->getContainer()->get('clab_core.core_manager')->createPlan($name,$price,$idStripe);
        $apps = $this->getContainer()->get('clab_core.core_manager')->createApps();
        $types = $this->getContainer()->get('clab_core.core_manager')->createOrderTypes();
        $payments = $this->getContainer()->get('clab_core.core_manager')->createPaymentMethods();
        if($types == true)
        {
            $output->writeln("Création des apps ok");
        }
        if($payments == true)
        {
            $output->writeln("Création des apps ok");
        }
        if($apps == true)
        {
            $output->writeln("Création des types de commandes ok");
        }
        else
        {
            $output->writeln('<error>Erreur dans la création des apps</error>');
        }
        if($plan == true)
        {
            $output->writeln("Création des plans ok");
        }
        else
        {
            $output->writeln('<error>Erreur dans la création du plan</error>');
        }
        if($taxes == true)
        {
            $output->writeln("Création des TVA ok");
        }
        else
        {
            $output->writeln('<error>Erreur dans la création des TVA</error>');
        }
        if($chainstore == true)
        {
            $output->writeln("Chaine initialisée");
        }
        else
        {
            $output->writeln('<error>Erreur dans la création de la chaine</error>');
        }


        $output->writeln('<info>Marque blanche initialisée</info>');
    }
}
