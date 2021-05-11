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

namespace SebSept\PsDevToolsPlugin\Command\SebSept;

use Exception;
use SebSept\PsDevToolsPlugin\Command\ScriptCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class IndexPhpFiller extends ScriptCommand
{
    const SOURCE_INDEX_FILE = '/../../../resources/index.php';

    /** @var Filesystem */
    private $fs;

    protected function configure(): void
    {
        $this->setName('fill-indexes')
            ->setDescription('Add the missing index.php files on each folder. <comment>Existing index.php files are not overriden.</comment>');
        $this->setHelp($this->getDescription() . <<<'HELP'

    This is a security requirement of Prestashop to avoid the contents to be listed.

    More informations on the official documentation.
    https://devdocs.prestashop.com/1.7/modules/sell/techvalidation-checklist/#a-file-indexphp-exists-in-each-folder
HELP
        );
        parent::configure();
    }

    public function getComposerScriptName(): string
    {
        return 'fill-index';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fs = new Filesystem();
        $this->getIO()->write('Adding missing index.php to all directories.'
            . PHP_EOL . '<comment>Existing index.php are not replaced.</comment>');
        try {
            $this->recursivelyAddIndexes();
            $this->getIO()->write('<info>Done</info>');

            return 0;
        } catch (Exception $exception) {
            $this->getIO()->error($exception->getMessage());

            return 1;
        }
    }

    private function recursivelyAddIndexes(): void
    {
        $directoryIterator = (new Finder())
            ->in($this->getcwd())
            ->directories()
            ->exclude('vendor')
            ->getIterator();

        foreach ($directoryIterator as $fileInfo) {
            $this->addIndex($fileInfo);
        }
    }

    private function addIndex(\SplFileInfo $splFileInfo): void
    {
        $target = sprintf('%s/%s', $splFileInfo->getRealPath(), 'index.php');
        $fancyName = str_replace($this->getcwd(), '.', $target);
        $this->getIO()->info(sprintf('Add index.php if missing at %s', $fancyName));
        $this->fs->copy($this->getSourceIndexPath(), $target);
    }

    private function getSourceIndexPath(): string
    {
        return __DIR__ . self::SOURCE_INDEX_FILE;
    }

    private function getcwd(): string
    {
        $cwd = getcwd();
        if (false === $cwd) {
            throw new \RuntimeException('getcwd() returned false. Failed to determine the current directory.');
        }

        return $cwd;
    }
}
