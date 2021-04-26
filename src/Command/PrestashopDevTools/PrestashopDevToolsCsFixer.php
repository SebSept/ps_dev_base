<?php
/**
 * SebSept Ps_dev_base - Tools for quality Prestashop Module development.
 *
 * Copyright (c) 2021 Sébastien Monterisi
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/MIT
 *
 * @author    Sébastien Monterisi <contact@seb7.fr>
 * @copyright since 2021 Sébastien Monterisi
 * @license   https://opensource.org/licenses/MIT MIT License
 */

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
        $this->setDescription('php-cs-fixer with Prestashop standards.');
        $this->setHelp(
            $this->getDescription() . <<<'HELP'

    Format php files for complying with the Prestashop standards.

    On the first run (or with <info>--reconfigure</info>) :
      * the <info>PrestaShop/php-dev-tools</info> package will be installed if needed.
      * <info>.php_cs</info> file will be created with the Prestashop standard styles. <comment>Destructive, get your files under version control</comment>
      * the composer script <info>csfix</info> will be added. So you can invoke this command with <info>composer csfix</info> 
      
    The next runs will trigger the fixer. All php files will be formated according to the Prestashop standard.
    
        
    You can tweak the formating by editing <info>.php_cs</info> file.
    You can add options to the composer command, for example 'composer csfix --dry-run'.
    If you want to permanently change an option, edit composer.json and find the <info>csfix</info> script.

    Provided by <info>PrestaShop/php-dev-tools/</info> - https://github.com/PrestaShop/php-dev-tools/.
HELP
        );

        parent::configure();
    }

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
            new Process('php vendor/bin/prestashop-coding-standards cs-fixer:init --dest ' . getcwd()); // @phpstan-ignore-line
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
