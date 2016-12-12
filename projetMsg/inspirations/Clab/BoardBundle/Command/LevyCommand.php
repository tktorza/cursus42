<?php

namespace Clab\BoardBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LevyCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('clickeat:levies')
            ->setDescription('Auto levies')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $this->getContainer()->get('app_admin.billing_manager')->autoLevies();

        $output->writeln($count);
    }
}