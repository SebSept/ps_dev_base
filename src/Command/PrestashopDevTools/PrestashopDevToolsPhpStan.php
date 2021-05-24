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
use Exception;
use RuntimeException;
use SebSept\PsDevToolsPlugin\Command\Contract\PreCommitRegistrableCommand;
use Symfony\Component\Process\Process;

final class PrestashopDevToolsPhpStan extends PrestashopDevTools implements PreCommitRegistrableCommand
{
    const PHPSTAN_CONFIGURATION_FILE = '/phpstan.neon';

    protected function configure(): void
    {
        $this->setName($this->getComposerScriptName());
        $this->setDescription('phpstan with Prestashop standards.');
        $this->setHelp(
            $this->getDescription() . <<<'HELP'

     Analyse php files for errors and warnings. With the Prestashop standards.

    On the first run (or with <info>--reconfigure</info>) :
      * the <info>PrestaShop/php-dev-tools</info> package will be installed if needed.
      * <info>phpstan.neon.dist</info> file will be created with the Prestashop bootstraping*. <comment>Destructive, get your files under version control</comment>
      * the composer script <info>phpstan</info> will be added. So you can invoke this command with <info>composer phpstan</info> 
      
    (* The second step asks you for a Prestashop installation path. So phpstan can check your code against a real Prestashop.)
    
    The next runs will trigger the phpstan command. All php files will be formated according to the Prestashop standard.
   
    
    You can tweak your code level checking by editing the <info>phpstan.neon</info> file.
    For example, you can lower the level the of phpstan requirements to something lower than 'max', (from 0 to 8).
    
    If you want to change the Prestashop reference path, edit composer.json and find the <info>phpstan</info> script.

    Provided by <info>PrestaShop/php-dev-tools/</info> - https://github.com/PrestaShop/php-dev-tools/.
HELP
        );

        parent::configure();
    }

    /**
     * Is Tool configurated ?
     * Tool is considered configured if
     * - phpstan.neon exists
     * - "phpstan" composer script exists.
     */
    public function isToolConfigured(): bool
    {
        $phpstanConfigurationFileExists = file_exists(getcwd() . self::PHPSTAN_CONFIGURATION_FILE);

        return $phpstanConfigurationFileExists && $this->isComposerScriptDefined();
    }

    /**
     * Interactive configuration
     * - add phpstan.neon file (via prestashop-coding-standards)
     * - add "phpstan" composer script
     * No need to check composer.json presence or validation.
     * Fact that this code is running means that composer.json is correct, because composer launched it.
     *
     * @throws Exception
     */
    public function configureTool(): void
    {
        $this->copyPhpStanNeonFile();
        $this->askPrestashopPathAndAddComposerScripts();
    }

    public function getComposerScriptName(): string
    {
        return 'phpstan';
    }

    private function copyPhpStanNeonFile(): void
    {
        // first, delete the file, otherwise the installation process does not write the new file.
        // https://github.com/PrestaShop/php-dev-tools/issues/58
        // that's a bit touchy, it relies on the fact the file name won't change. Otherwise our workaround will fail.
        $fs = new Filesystem();
        $phpstanConfigurationFile = getcwd() . self::PHPSTAN_CONFIGURATION_FILE;
        $fs->remove($phpstanConfigurationFile);

        $this->getIO()->write("Installation of {$this->getComposerScriptName()} configuration file : ", false);

        $installPhpStanConfiguration =
            new Process('php vendor/bin/prestashop-coding-standards phpstan:init --dest \'' . getcwd() . '\''); // @phpstan-ignore-line  - This is needed, see comment above
        $installPhpStanConfiguration->start();
        $installPhpStanConfiguration->wait();

        // The process is reported to be successful even if the new file was not written :(
        // that's why the file is deleted before running the phpstan:init
        // Having an --override option in the phpstan:init could solve our problem.
        // fun fact : isSuccessful() is true but getErrorOutput() has content
        if (!$installPhpStanConfiguration->isSuccessful()) {
            $this->getIO()->error('failed !');
            throw new RuntimeException("{$this->getComposerScriptName()} configuration : {$installPhpStanConfiguration->getErrorOutput()}");
        }

        $this->getIO()->write('<bg=green>successful</bg=green>');
        $this->getIO()->info(' in fact, it\'s only PROBABLY successfull.');
        $this->getIO()->info(' https://github.com/PrestaShop/php-dev-tools/issues/58');
    }

    /**
     * @throws \Seld\JsonLint\ParsingException
     */
    private function askPrestashopPathAndAddComposerScripts(): void
    {
        $this->getIO()->write('To perform code analyse, phpstan needs a path to a Prestashop installation.');
        $guessedPsRoot = realpath(getcwd() . '/../../') ?: '';
        $prestashopPath = $this->getIO()->ask(
            sprintf('What is the path to is this Prestashop installation %s ? ', '(leave blank to use <comment>' . $guessedPsRoot . '</comment>)'),
            $guessedPsRoot
        );
        $this->addComposerScript([
            "@putenv _PS_ROOT_DIR_=$prestashopPath",
            'phpstan analyse', ]);
    }

    public function getComposerPrecommitScriptContent(): ?string
    {
        return $this->isToolConfigured() && $this->isComposerScriptDefined() ? '@phpstan' : null;
    }
}
