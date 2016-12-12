<?php

namespace Clab\ReviewBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FacebookRatingCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('review:facebookratings')
            ->setDescription('Facebook Ratings')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $count = $this->getContainer()->get('clab_review.review_manager')->fetchFacebookRatings();

        $output->writeln($count);
    }
}
