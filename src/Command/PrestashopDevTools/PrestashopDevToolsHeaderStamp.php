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

use SebSept\PsDevToolsPlugin\Command\ComposerPackageCommand;

final class PrestashopDevToolsHeaderStamp extends ComposerPackageCommand
{
    private const HEADERSTAMP_FILE = '.header_stamp.txt';
    private const HEADERSTAMP_UNCHANGED_MARKER = 'This small piece of text marks the fact this file havn\'t been customized';

    public function getComposerScriptName(): string
    {
        return 'header-stamp';
    }

    public function getPackageName(): ?string
    {
        return 'prestashop/header-stamp';
    }

    public function getPackageVersionConstraint(): ?string
    {
        return '^2.0';
    }

    protected function configure(): void
    {
        $this->setName($this->getComposerScriptName());
        parent::configure();
    }

    public function isToolConfigured(): bool
    {
        var_dump('inst : ' . $this->isPackageInstalled());
        // @todo add output
        return $this->isPackageInstalled()
            && $this->isComposerScriptDefined()
            && $this->headerStampFileExists()
            && $this->isHeaderStampFileCustomized();
    }

    public function configureTool(): void
    {
        /*
         * Some refactoring is needed before implementing this, probably.
         *
         * - The header-stamp package is a requirement of prestashop/php-dev-tools.
         *   - We can rely on the fact that prestashop/php-dev-tools will still include it. :/
         *   - Or write a command that doesn't extends \...\Command\PrestashopDevTools\PrestashopDevTools
         *      - but in this case we should check if \...\ComposerPackageCommand::getInstalledDevRequires
         *      returns the correct response if headerstamp is installed by prestashop/php-dev-tools
         *   - or implements something that can check for multiple composer pachages, not only one.
         * - By the way, getInstalledDevRequires may better check for package provided for normal use And dev use (?)
         */
        throw new \Exception('Implement me');
    }

    private function headerStampFileExists(): bool
    {
        return file_exists(self::HEADERSTAMP_FILE);
    }

    private function isHeaderStampFileCustomized(): bool
    {
        $headerStampContents = file_get_contents(self::HEADERSTAMP_FILE);
        if (false === $headerStampContents) {
            throw new \Exception(sprintf('Failed to read headerstamp contents on file %s', self::HEADERSTAMP_FILE));
        }

        return false !== strpos($headerStampContents, self::HEADERSTAMP_UNCHANGED_MARKER);
    }
}
