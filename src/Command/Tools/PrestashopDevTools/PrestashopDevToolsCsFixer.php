<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command\Tools\PrestashopDevTools;

use Composer\Util\Filesystem;
use RuntimeException;
use SebSept\PsDevToolsPlugin\Command\PsDevToolsBaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

class PrestashopDevToolsCsFixer extends PrestashopDevTools
{
    const PHP_CS_CONFIGURATION_FILE = '/.php_cs.dist';

    protected function configure(): void
    {
        $this->setName('psdt:prestashop-dev-tools:php-cs-fixer');
        $this->setDescription('Install / Configure / Run Php-cs-fixer from prestashop/prestashop-dev-tools.');
        $this->addOption('reconfigure', null, InputOption::VALUE_NONE, 'rerun configuration');
    }

    public function getScriptName(): string
    {
        return 'csfix';
    }

    public function isToolConfigured(): bool
    {
        $configurationFileExists = file_exists(getcwd() . self::PHP_CS_CONFIGURATION_FILE);
        $composerScriptExists = $this->readComposerJsonFile()['scripts'][$this->getScriptName()] ?? false;

        return $configurationFileExists && $composerScriptExists;
    }

    public function configureTool(): void
    {
        // @see \SebSept\PsDevToolsPlugin\Command\Tools\PrestashopDevTools\PrestashopDevToolsPhpStan::configureTool
        $fs = new Filesystem();
        $csFixeronfigurationFile = getcwd() . self::PHP_CS_CONFIGURATION_FILE;
        $fs->remove($csFixeronfigurationFile);

        // ----- add php-cs-fixer file
        $this->io->write("Installation of {$this->getScriptName()} configuration file : ", false);
        $installCsFixerConfiguration =
            new Process('php vendor/bin/prestashop-coding-standards cs-fixer:init --dest '.getcwd()); // @phpstan-ignore-line
        $installCsFixerConfiguration->start();
        $installCsFixerConfiguration->wait();
        if (!$installCsFixerConfiguration->isSuccessful()) {
            $this->io->error('failed !');
            throw new RuntimeException("{$this->getScriptName()} configuration : {$installCsFixerConfiguration->getErrorOutput()}");
        }

        $this->io->write('<bg=green>successful</bg=green>');
        $this->io->info(' in fact, it\'s only PROBABLY successfull.');
        $this->io->info(' https://github.com/PrestaShop/php-dev-tools/issues/58');

        // ----- add composer script
        $this->addComposerScript(['php-cs-fixer fix']);

        $this->io->write("Composer script <comment>{$this->getScriptName()}</comment> has been added to you composer.json");
        $this->io->write("You can change the path to the Prestashop installation by editing ['scripts'][{$this->getScriptName()}] in your <comment>composer.json</comment>.");
    }
}
