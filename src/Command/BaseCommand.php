<?php

declare(strict_types=1);

namespace SebSept\PsDevToolsPlugin\Command;

use Composer\Command\BaseCommand as ComposerBaseCommand;
use Composer\Json\JsonFile;
use Exception;
use InvalidArgumentException;

/**
 * Class BaseCommand
 *
 * All commands in this package must extend this base command.
 */
abstract class BaseCommand extends ComposerBaseCommand implements BaseCommandInterface
{
    public function setName($name) : self
    {
        // temp while rewriting
        if (strpos($name, ':')) {
            throw new InvalidArgumentException('pas de : dans le nom de commande');
        }

        return parent::setName("psdt:prestashop-dev-tools:$name");
    }

    /**
     * @return array<string, array|string>
     * @throws \Seld\JsonLint\ParsingException
     */
    final protected function readComposerJsonFile(): array
    {
        trigger_error('remplacer par IsComposerScriptDefined');
        return (new JsonFile(getcwd() . '/composer.json'))->read();
    }

    /**
     * @param array<string, array|string> $composerContents
     * @throws Exception
     */
    final private function writeComposerJsonFile(array $composerContents): void
    {
        (new JsonFile(getcwd() . '/composer.json'))->write($composerContents);
    }

    /**
     * @param array<string> $scripts
     * @throws \Seld\JsonLint\ParsingException
     */
    final protected function addComposerScript(array $scripts) :void
    {
        $composerJsonContents = $this->readComposerJsonFile();
        $composerJsonContents['scripts'][$this->getComposerScriptName()] = $scripts;
        $this->writeComposerJsonFile($composerJsonContents);
    }
}
