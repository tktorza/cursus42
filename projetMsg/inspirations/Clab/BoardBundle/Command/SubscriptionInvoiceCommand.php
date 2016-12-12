<?php

namespace Clab\BoardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionInvoiceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('clickeat:subscriptioninvoice')
            ->setDescription('Auto subscription invoice')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $this->getContainer()->get('app_admin.billing_manager')->autoSubscriptionInvoice();

        $output->writeln($count);
    }
}