<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command\SebSept;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('psdt:hello');
        $this->setDescription('Welcome get started here.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getIO()->write('This command show some helps for usage of this package.');

        return 0;
    }
}
