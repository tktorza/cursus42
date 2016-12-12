<?php

namespace Clab\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserTokenCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('clickeat:user:generate:token')
            ->setDescription('Populate login token')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getContainer()->get('app_user.user_manager')->generateUserToken();
        $output->writeln('OK');
    }
}
