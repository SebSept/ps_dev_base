<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command;

use Exception;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PrestashopDevTools extends PsDevToolsBaseCommand
{
    protected function configure(): void
    {
        $this->setName('psdt:prestashop-dev-tools');
        $this->setDescription('Install / Configure / Run Prestashop dev tools.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = $this->getIO();

        try {
            $this->isPackageInstalled() ?: $this->installPackage();
            $this->isToolConfigured() ?: $this->configureTool();
            $this->runTool();
        }
        catch (RuntimeException $exception) {
            $this->io->alert($exception->getMessage());
            return 7;
        }
        catch(Exception $exception) {
            $this->io->critical($exception->getMessage());
            return 1;
        }

        return 0;
    }

    public function getPackageName(): string
    {
        return 'prestashop/php-dev-tools';
    }

    public function getPackageVersionConstraint(): string
    {
        return '3.*';
    }

    public function isToolConfigured(): bool
    {
        trigger_error('not implemented.'.__FUNCTION__);

        return true;
    }

    public function configureTool(): void
    {
        trigger_error('not implemented.'.__FUNCTION__);
    }

    public function runTool(): void
    {
        trigger_error('not implemented.'.__FUNCTION__);
    }
}