<?php

namespace Clab\ShopBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CancelTestCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('clickeat:ordercanceltest')
            ->setDescription('Cancel test order')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->enterScope('request');
        $this->getContainer()->set('request', new Request(), 'request');

        $count = $this->getContainer()->get('app_shop.order_manager')->cancelTest();

        $output->writeln($count);
    }
}
