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
use Symfony\Component\Process\Process;

final class PrestashopDevToolsPhpStan extends PrestashopDevTools
{
    const PHPSTAN_CONFIGURATION_FILE = '/phpstan.neon';

    protected function configure(): void
    {
        $this->setName('phpstan');
        $this->setDescription('Install / Configure / Run Phpstan from prestashop/prestashop-dev-tools.');
        parent::configure();
    }

    /**
     * It Tool configurated ?
     * Tool is considered configured if
     * - phpstan.neon exists
     * - "phpstan" composer script exists.
     *
     * @throws \Seld\JsonLint\ParsingException
     */
    public function isToolConfigured(): bool
    {
        $phpstanConfigurationFileExists = file_exists(getcwd().self::PHPSTAN_CONFIGURATION_FILE);
        $composerScriptExists = $this->readComposerJsonFile()['scripts'][$this->getComposerScriptName()] ?? false;

        return $phpstanConfigurationFileExists && $composerScriptExists;
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
        // ----- add phpstan.neon

        // first, delete the file, otherwise the installation process does not write the new file.
        // https://github.com/PrestaShop/php-dev-tools/issues/58
        // that's a bit touchy, it relies on the fact the file name won't change. Otherwise our workaround will fail.
        $fs = new Filesystem();
        $phpstanConfigurationFile = getcwd().self::PHPSTAN_CONFIGURATION_FILE;
        $fs->remove($phpstanConfigurationFile);

        $this->getIO()->write("Installation of {$this->getComposerScriptName()} configuration file : ", false);

        $installPhpStanConfiguration =
            new Process('php vendor/bin/prestashop-coding-standards phpstan:init --dest \''.getcwd().'\''); // @phpstan-ignore-line  - This is needed, see comment above
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

        // ------ add composer script
        $this->getIO()->write('To perform code analyse, phpstan needs a path to a Prestashop installation.');
        $prestashopPath = $this->getIO()->ask('What is the path to is this Prestashop installation ? ');
        $this->addComposerScript([
            "@putenv _PS_ROOT_DIR_=$prestashopPath",
            $this->getComposerScriptName(), ]);

        $this->getIO()
            ->write("Composer script <comment>{$this->getComposerScriptName()}</comment> has been added to you composer.json");
        $this->getIO()
            ->write("You can change the path to the Prestashop installation by editing ['scripts'][{$this->getComposerScriptName()}] in your <comment>composer.json</comment>.");
    }

    public function getComposerScriptName(): string
    {
        return 'phpstan';
    }
}
