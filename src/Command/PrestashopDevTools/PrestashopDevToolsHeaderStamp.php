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
use Symfony\Component\Filesystem\Filesystem;

final class PrestashopDevToolsHeaderStamp extends ComposerPackageCommand
{
    private const SOURCE_HEADERSTAMP_FILE = __DIR__ . '/../../../resources/.header_stamp.txt';
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
        return '* || ^2.0'; // 2.0 is ok but in case  prestashop/php-dev-tools changes it's requirements...
    }

    protected function configure(): void
    {
        $this->setName($this->getComposerScriptName());
        parent::configure();
    }

    public function isToolConfigured(): bool
    {
        return $this->isComposerScriptDefined()
            && $this->headerStampFileExists()
            && $this->isHeaderStampFileCustomized();
    }

    public function configureTool(): void
    {
        // copyHeaderStampFile and add a marker to know if file was customized.
        $this->getIO()->write('Preparing hearder stamp file ... ', false);
        $fs = new Filesystem();
        $fs->copy(self::SOURCE_HEADERSTAMP_FILE, self::HEADERSTAMP_FILE, true);
        file_put_contents(self::HEADERSTAMP_FILE, self::HEADERSTAMP_UNCHANGED_MARKER, FILE_APPEND);
        $this->getIO()->write('<info>OK</info>');

        $this->addComposerScript([sprintf("header-stamp --exclude=vendor,node_modules --license='%s'", self::HEADERSTAMP_FILE)]);

        throw new \RuntimeException(sprintf('You must now edit %s file.', self::HEADERSTAMP_FILE));
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

        return false == strpos($headerStampContents, self::HEADERSTAMP_UNCHANGED_MARKER);
    }
}
