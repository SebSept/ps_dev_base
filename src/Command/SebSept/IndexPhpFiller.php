<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command\SebSept;

use Exception;
use SebSept\PsDevToolsPlugin\Command\ScriptCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class IndexPhpFiller extends ScriptCommand
{
    const SOURCE_INDEX_FILE = '/../../../resources/index.php';

    /** @var Filesystem */
    private $fs;

    protected function configure(): void
    {
        $this->setName('fill-indexes');
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
            ->in(getcwd())
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
        $fancyName = str_replace(getcwd(), '.', $target);
        $this->getIO()->info(sprintf("writing new index.php at %s", $fancyName));
        $this->fs->copy($this->getSourceIndexPath(), $target);
    }

    private function getSourceIndexPath(): string
    {
        return __DIR__ . self::SOURCE_INDEX_FILE;
    }
}
