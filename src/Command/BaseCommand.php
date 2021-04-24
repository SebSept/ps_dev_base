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

namespace SebSept\PsDevToolsPlugin\Command;

use Composer\Command\BaseCommand as ComposerBaseCommand;
use Composer\Json\JsonFile;
use Exception;

/**
 * Class BaseCommand.
 *
 * All commands in this package must extend this base command.
 */
abstract class BaseCommand extends ComposerBaseCommand implements BaseCommandInterface
{
    /**
     * Set command name and prepend the common namespace.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name): self
    {
        return parent::setName("psdt:$name");
    }

    /**
     * @return array<string, array<string>>
     *
     * @throws \Seld\JsonLint\ParsingException
     */
    final protected function readComposerJsonFile(): array
    {
        @trigger_error('remplacer par IsComposerScriptDefined');

        return (new JsonFile(getcwd() . '/composer.json'))->read();
    }

    /**
     * @param array<string, array|string> $composerContents
     *
     * @throws Exception
     */
    final private function writeComposerJsonFile(array $composerContents): void
    {
        (new JsonFile(getcwd() . '/composer.json'))->write($composerContents);
    }

    /**
     * @param array<string> $scripts
     *
     * @throws \Seld\JsonLint\ParsingException
     */
    final protected function addComposerScript(array $scripts): void
    {
        $composerJsonContents = $this->readComposerJsonFile();
        $composerJsonContents['scripts'][$this->getComposerScriptName()] = $scripts;
        $this->writeComposerJsonFile($composerJsonContents);
    }
}
