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

use Composer\IO\IOInterface;
use Exception;
use SebSept\PsDevToolsPlugin\Command\Contract\PreCommitRegistrableCommand;
use SebSept\PsDevToolsPlugin\Command\ScriptCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

final class IndexPhpFiller extends ScriptCommand implements PreCommitRegistrableCommand
{
    private const SOURCE_INDEX_FILE = '/../../../resources/index.php';

    /** @var Filesystem */
    private $fs;

    protected function configure(): void
    {
        $this->setName('fill-indexes')
            ->setDescription('Add the missing index.php files on each folder. <comment>Existing index.php files are not overriden.</comment>');
        $this->setHelp($this->getDescription() . <<<'HELP'

    This is a security requirement of Prestashop to avoid the contents to be listed.
    
    The command adds all the missing <comment>index.php</comment> files unless you add the <info>--check-only</info> option.
    With <info>--check-only</info> option the command returns 1 if some files are missing. 0 if all required files are present.

    More informations on the official documentation.
    https://devdocs.prestashop.com/1.7/modules/sell/techvalidation-checklist/#a-file-indexphp-exists-in-each-folder
HELP
        )
            ->addOption('check-only', null, InputOption::VALUE_NONE, 'Checks if index.php are missing');
        parent::configure();
    }

    public function getComposerScriptName(): string
    {
        return 'fill-index';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->isComposerScriptDefined() ?: $this->addComposerScript(['composer psdt:fill-indexes']);

        $this->fs = new Filesystem();
        if ($input->getOption('check-only')) {
            return $this->checkMissingIndexes();
        }

        $this->getIO()->write('Adding missing index.php to all directories.'
            . PHP_EOL . '<comment>Existing index.php are not replaced.</comment>');
        try {
            $this->addMissingIndexFiles();
            $this->getIO()->write('<info>Done</info>');

            return 0;
        } catch (Exception $exception) {
            $this->getIO()->error($exception->getMessage());

            return 1;
        }
    }

    private function addMissingIndexFiles(): void
    {
        foreach ($this->getDirectoryIterator() as $fileInfo) {
            $this->addIndex($fileInfo);
        }
        // also at the root - I haven't found a way to include it in the iterator :/
        $this->addIndex(new \SplFileInfo($this->getcwd()));
    }

    private function addIndex(\SplFileInfo $splFileInfo): void
    {
        $target = sprintf('%s/%s', $splFileInfo->getRealPath(), 'index.php');
        $fancyName = str_replace($this->getcwd(), '.', $target);
        $this->getIO()->info(sprintf('Add index.php if missing at %s', $fancyName));
        $this->fs->copy($this->getSourceIndexPath(), $target);
    }

    private function checkMissingIndexes(): int
    {
        $missingIndexFiles = [];

        foreach ($this->getDirectoryIterator() as $fileInfo) {
            $indexPhpPath = $fileInfo->getPathname() . '/index.php';
            $this->fs->exists($indexPhpPath) ?: array_push($missingIndexFiles, $indexPhpPath);
        }
        // plus index.php at root
        $this->fs->exists($this->getcwd() . '/index.php') ?: array_push($missingIndexFiles, './index.php');

        if (empty($missingIndexFiles)) {
            $this->getIO()->write('Good : no missing index files.');

            return 0;
        }

        $this->getIO()->write('<fg=red>✗ Missing index.php files.</>');
        $this->getIO()->write('Missing index.php : ', true, IOInterface::VERBOSE);
        $this->getIO()->write($missingIndexFiles, true, IOInterface::VERBOSE);
        $this->getIO()->write([
            sprintf('Run <info>composer psdt:%s --check-only -v</info> option to list missing files', $this->getName()),
            sprintf('Or run <info>composer psdt:%s</info> to add missing files.', $this->getName()),
        ]);

        return 1;
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

    /**
     * @return \AppendIterator|\Iterator|\RecursiveIteratorIterator|\Symfony\Component\Finder\Iterator\CustomFilterIterator|\Symfony\Component\Finder\Iterator\DateRangeFilterIterator|\Symfony\Component\Finder\Iterator\DepthRangeFilterIterator|\Symfony\Component\Finder\Iterator\FilecontentFilterIterator|\Symfony\Component\Finder\Iterator\FilenameFilterIterator|\Symfony\Component\Finder\Iterator\FileTypeFilterIterator|\Symfony\Component\Finder\Iterator\PathFilterIterator|\Symfony\Component\Finder\Iterator\SizeRangeFilterIterator|\Symfony\Component\Finder\SplFileInfo[]
     */
    private function getDirectoryIterator()
    {
        return (new Finder())
            ->in($this->getcwd())
            ->directories()
            ->ignoreDotFiles(false)
            ->ignoreVCS(true)
            ->exclude('vendor')
            ->getIterator();
    }

    public function getComposerPrecommitScriptContent(): ?string
    {
        return 'composer psdt:fill-indexes --check-only';
    }
}
