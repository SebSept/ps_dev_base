<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command\Tools\PrestashopDevTools;

use Composer\Util\Filesystem;
use Exception;
use RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

final class PrestashopDevToolsPhpStan extends PrestashopDevTools
{
    const PHPSTAN_CONFIGURATION_FILE = '/phpstan.neon';

    protected function configure(): void
    {
        $this->setName('psdt:prestashop-dev-tools:phpstan');
        $this->setDescription('Install / Configure / Run Phpstan from prestashop/prestashop-dev-tools.');
        $this->addOption('uninstall', null, InputOption::VALUE_NONE, 'uninstall this package :('); // cette option
        // n'est plus necessaire - mais on devrait peut-être la garder quand le gars veux désinstaller ce package...
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
        $phpstanConfigurationFileExists = file_exists(getcwd() . self::PHPSTAN_CONFIGURATION_FILE);
        $composerScriptExists = $this->readComposerJsonFile()['scripts'][$this->getScriptName()] ?? false;

        return $phpstanConfigurationFileExists && $composerScriptExists;
    }

    /**
     * Interactive configuration
     * - add phpstan.neon file (via prestashop-coding-standards)
     * - add "phpstan" composer script
     * No need to check composer.json presence or validation.
     * Fact that this code is running means that composer.json is correct, because composer launched it.
     * @throws Exception
     */
    public function configureTool(): void
    {
        // ----- add phpstan.neon

        // first, delete the file, otherwise the installation process does not write the new file.
        // https://github.com/PrestaShop/php-dev-tools/issues/58
        // that's a bit touchy, it relies on the fact the file name won't change. Otherwise our workaround will fail.
        $fs = new Filesystem();
        $phpstanConfigurationFile = getcwd() . self::PHPSTAN_CONFIGURATION_FILE;
        $fs->remove($phpstanConfigurationFile);

        $this->io->write("Installation of {$this->getScriptName()} configuration file : ", false);

        $installPhpStanConfiguration = new Process('php vendor/bin/prestashop-coding-standards phpstan:init --dest \''. getcwd().'\''); // @phpstan-ignore-line  - This is needed, see comment above
        $installPhpStanConfiguration->start();

        $installPhpStanConfiguration->wait();

        // The process is reported to be successful even if the new file was not written :(
        // that's why the file is deleted before running the phpstan:init
        // Having an --override option in the phpstan:init could solve our problem.
        // fun fact : isSuccessful() is true but getErrorOutput() has content
        if (!$installPhpStanConfiguration->isSuccessful()) {
            $this->io->error('failed !');
            throw new RuntimeException("{$this->getScriptName()} configuration : {$installPhpStanConfiguration->getErrorOutput()}");
        }

        $this->io->write('<bg=green>successful</bg=green>');
        $this->io->info(' in fact, it\'s only PROBABLY successfull.');
        $this->io->info(' https://github.com/PrestaShop/php-dev-tools/issues/58');

        // ------ add composer script
        $this->io->write('To perform code analyse, phpstan needs a path to a Prestashop installation.');
        $prestashopPath = $this->io->ask('What is the path to is this Prestashop installation ? ');
        $this->addComposerScript([
            "@putenv _PS_ROOT_DIR_=$prestashopPath",
            $this->getScriptName()]);

        $this->io->write("Composer script <comment>{$this->getScriptName()}</comment> has been added to you composer.json");
        $this->io->write("You can change the path to the Prestashop installation by editing ['scripts'][{$this->getScriptName()}] in your <comment>composer.json</comment>.");
    }

    public function getScriptName(): string
    {
        return 'phpstan';
    }
}
