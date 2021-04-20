<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command\PrestashopDevTools;

use Composer\Util\Filesystem;
use RuntimeException;
use Symfony\Component\Process\Process;

class PrestashopDevToolsCsFixer extends PrestashopDevTools
{
    const PHP_CS_CONFIGURATION_FILE = '/.php_cs.dist';

    protected function configure(): void
    {
        $this->setName('php-cs-fixer');
        $this->setDescription('Install / Configure / Run Php-cs-fixer from prestashop/prestashop-dev-tools.');
        parent::configure();
    }

    /**
     * @return string
     * @todo renomer getComposerScriptName
     */
    public function getComposerScriptName(): string
    {
        return 'csfix';
    }

    public function isToolConfigured(): bool
    {
        $configurationFileExists = file_exists(getcwd() . self::PHP_CS_CONFIGURATION_FILE);
        $composerScriptExists = $this->readComposerJsonFile()['scripts'][$this->getComposerScriptName()] ?? false;

        return $configurationFileExists && $composerScriptExists;
    }

    public function configureTool(): void
    {
        // @see \SebSept\PsDevToolsPlugin\Command\PrestashopDevTools\PrestashopDevToolsPhpStan::configureTool
        $fs = new Filesystem();
        $csFixeronfigurationFile = getcwd() . self::PHP_CS_CONFIGURATION_FILE;
        $fs->remove($csFixeronfigurationFile);

        // ----- add php-cs-fixer file
        $this->getIO()->write("Installation of {$this->getComposerScriptName()} configuration file : ", false);
        $installCsFixerConfiguration =
            new Process('php vendor/bin/prestashop-coding-standards cs-fixer:init --dest '.getcwd()); // @phpstan-ignore-line
        $installCsFixerConfiguration->start();
        $installCsFixerConfiguration->wait();
        if (!$installCsFixerConfiguration->isSuccessful()) {
            $this->getIO()->error('failed !');
            throw new RuntimeException("{$this->getComposerScriptName()} configuration : {$installCsFixerConfiguration->getErrorOutput()}");
        }

        $this->getIO()->write('<bg=green>successful</bg=green>');
        $this->getIO()->info(' in fact, it\'s only PROBABLY successfull.');
        $this->getIO()->info(' https://github.com/PrestaShop/php-dev-tools/issues/58');

        // ----- add composer script
        $this->addComposerScript(['php-cs-fixer fix']);

        $this->getIO()->write("Composer script <comment>{$this->getComposerScriptName()}</comment> has been added to you composer.json");
        $this->getIO()->write("You can change the path to the Prestashop installation by editing ['scripts'][{$this->getComposerScriptName()}] in your <comment>composer.json</comment>.");
    }
}
