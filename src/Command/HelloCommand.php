<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('psdt:hello');
        $this->setDescription('Welcome + Wizard : nothing implemented.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getIO()->write('La commande de bienvenue. Pas implémentée pour le moment.');

        return 0;
    }
}
