<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command;

use Exception;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
        return '4.*';
    }

    public function isToolConfigured(): bool
    {
        $this->io->error('a terminer '.__FUNCTION__.__FILE__);
        $configured =  file_exists(getcwd().'/phpstan.neon');
        $configured
            ? $this->io->info("{$this->getPackageName()} is already configured")
            : $this->io->info("{$this->getPackageName()} not configured");

        return $configured;
    }

    public function configureTool(): void
    {
        $this->io->write("Configuration of {$this->getPackageName()} : ",false);
        $installPhpStanConfiguration = new Process(['php','vendor/bin/prestashop-coding-standards','phpstan:init', '--dest', getcwd()]);
        $installPhpStanConfiguration->start();
        $installPhpStanConfiguration->wait();
        if(!$installPhpStanConfiguration->isSuccessful()) {
            $this->io->error('failed !');
            throw new RuntimeException("{$this->getPackageName()} configuration : {$installPhpStanConfiguration->getErrorOutput()}");
        }
        $this->io->write('<bg=green>successful</bg=green>');
    }

    public function runTool(): void
    {
        $this->getApplication()->find('run-script')->run(
            new ArrayInput([
                'script' => $this->getScriptName(),
            ]),
            $this->output
        );
    }

    public function getScriptName(): string
    {
        return 'phpstan';
    }


}