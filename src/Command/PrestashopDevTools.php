<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command;

use Exception;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class PrestashopDevTools extends PsDevToolsBaseCommand
{
    protected function configure(): void
    {
        $this->setName('psdt:prestashop-dev-tools');
        $this->setDescription('Install / Configure / Run Prestashop dev tools.');
        $this->addOption('uninstall', null, InputOption::VALUE_NONE, 'uninstall this package :(' );
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        // -- uninstallation --
        // needed because manual install is not performed by composer cli
        if($input->getOption('uninstall')) {
            try {
                return $this->unInstallPackage();
            }
            // Exception thrown by us, yes, no Domain Exception Class implemented yet.
            catch (RuntimeException $exception) {
                $this->io->alert($exception->getMessage());
                return 7;
            }
            catch(Exception $exception) {
                $this->io->critical($exception->getMessage());
                return 1;
            }
        }

        // -- installation / configuration / execution

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