<?php

namespace Clab\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BookmarkCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('clickeat:migrate:bookmark')
            ->setDescription('Populate new Timesheets')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('app_user.user_manager')->migrateFavorite();
        $output->writeln('OK');
    }
}
