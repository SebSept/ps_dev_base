<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command\Tools\PrestashopDevTools;

use Exception;
use RuntimeException;
use SebSept\PsDevToolsPlugin\Command\PsDevToolsBaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

final class PrestashopDevToolsPhpStan extends PrestashopDevTools
{
    protected function configure(): void
    {
        $this->setName('psdt:prestashop-dev-tools:phpstan');
        $this->setDescription('Install / Configure / Run Phpstan from prestashop/prestashop-dev-tools.');
        $this->addOption('uninstall', null, InputOption::VALUE_NONE, 'uninstall this package :(');
        $this->addOption('reconfigure', null, InputOption::VALUE_NONE, 'rerun configuration');
    }

    /**
     * It Tool configurated ?
     * Tool is considered configured if
     * - phpstan.neon exists
     * - "phpstan" composer script exists
     * @return bool
     * @throws \Seld\JsonLint\ParsingException
     */
    public function isToolConfigured(): bool
    {
        $phpstanConfigurationFileExists = file_exists(getcwd() . '/phpstan.neon');
        $composerScriptExists = $this->readComposerJsonFile()['scripts'][$this->getScriptName()] ?? false;

        $configured = $phpstanConfigurationFileExists && $composerScriptExists;
        $configured
            ? $this->io->write("{$this->getScriptName()} is configured.")
            : $this->io->error("{$this->getScriptName()} is not configured.");

        return $configured;
    }

    /**
     * Interactive configuration
     *
     * - add "phpstan" composer script
     * - add phpstan.neon file (via prestashop-coding-standards)
     *
     * No need to check composer.json presence or validation.
     * fact that this code is running is that composer.json is correct, because composer launched it.
     * @throws Exception
     */
    public function configureTool(): void
    {
        // ------ add composer script
        $this->io->write('To perform code analyse, phpstan needs a path to a Prestashop installation.');
        $prestashopPath = $this->io->ask('What is the path to is this Prestashop installation ? ');

        $composerJsonContents = $this->readComposerJsonFile();
        $composerJsonContents['scripts'][$this->getScriptName()] = [
            "@putenv _PS_ROOT_DIR_=$prestashopPath",
            "phpstan"];
        $this->writeComposerJsonFile($composerJsonContents);

        $this->io->write("Composer script <comment>{$this->getScriptName()}</comment> has been added to you composer.json");
        $this->io->write("You can change the path to the Prestashop installation by editing ['scripts'][{$this->getScriptName()}] in your <comment>composer.json</comment>.");

        // ----- add phpstan.neon
        $this->io->write("Configuration of {$this->getPackageName()} : ", false);
        $installPhpStanConfiguration =
            new Process(['php', 'vendor/bin/prestashop-coding-standards', 'phpstan:init', '--dest', getcwd()]);
        $installPhpStanConfiguration->start();
        $installPhpStanConfiguration->wait();
        if (!$installPhpStanConfiguration->isSuccessful()) {
            $this->io->error('failed !');
            throw new RuntimeException("{$this->getPackageName()} configuration : {$installPhpStanConfiguration->getErrorOutput()}");
        }
        $this->io->write('<bg=green>successful</bg=green>');
    }

    public function getScriptName(): string
    {
        return 'phpstan';
    }
}
