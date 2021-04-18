<?php

declare(strict_types=1);

namespace SebSept\PsDevToolsPlugin\Command;

use Composer\Command\BaseCommand;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Package\Link;
use Exception;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class PsDevToolsBaseCommand extends BaseCommand implements PsDevToolsCommandInterface
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var IOInterface
     */
    protected $io;

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = $this->getIO();
        $this->output = $output;

        // -- uninstallation --
        // needed because manual install is not performed by composer cli
        if ($input->getOption('uninstall')) {
            try {
                return $this->unInstallPackage();
            }
            // Exception thrown by us, yes, no Domain Exception Class implemented yet.
            catch (RuntimeException $exception) {
                $this->io->alert($exception->getMessage());
                return 7;
            } catch (Exception $exception) {
                $this->io->critical($exception->getMessage());
                return 1;
            }
        }

        // -- installation / configuration / execution
        try {
            $runConfiguration = $input->getOption('reconfigure') || !$this->isToolConfigured();
            $readyToRun = $this->isPackageInstalled() && !$runConfiguration;

            $this->isPackageInstalled() ?: $this->installPackage();
            !$runConfiguration ?: $this->configureTool();
            if (!$readyToRun) {
                $this->io->write("<bg=green>{$this->getScriptName()} is installed and configured.</>");
                $this->io->write("run <comment>composer {$this->getName()}</comment> to run the tool.");
                return 0;
            }

            $this->runTool();
        } catch (RuntimeException $exception) {
            $this->io->alert($exception->getMessage());
            return 7;
        } catch (Exception $exception) {
            $this->io->critical($exception->getMessage());
            return 1;
        }

        return 0;
    }

    public function runTool(): void
    {
        $this->getApplication()->find('run-script')->run(
            new ArrayInput([
                'script' => $this->getScriptName(),
            ]),
            $this->output
        );
    }

    final protected function isPackageInstalled(): bool
    {
        $installed = array_key_exists($this->getPackageName(), $this->getInstalledDevRequires());
        $installed
            ? $this->io->write("{$this->getPackageName()} is installed.")
            : $this->io->error("{$this->getPackageName()} is not installed.");

        return $installed;
    }

    final protected function unInstallPackage(): int
    {
        return $this->getApplication()->find('remove')->run(
            new ArrayInput([
                'packages' => [sprintf('%s', $this->getPackageName())],
                '--dev' => true,
            ]),
            $this->output
        );
    }

    // eventuellement utiliser https://github.com/composer/composer/blob/master/src/Composer/Util/SyncHelper.php
    // mais pas de possibilité de désinstallation de la même façon.
    final protected function installPackage(): int
    {
        return $this->getApplication()->find('require')->run(
            new ArrayInput([
                'packages' => [sprintf('%s:%s', $this->getPackageName(), $this->getPackageVersionConstraint())],
                '--dev' => true,
                '--quiet' => true, // ne pas mettre quiet si (very)verbose
            ]),
            $this->output
        );
    }

    /**
     * @return array<string, array|string>
     * @throws \Seld\JsonLint\ParsingException
     */
    final protected function readComposerJsonFile(): array
    {
        return (new JsonFile(getcwd() . '/composer.json'))->read();
    }

    /**
     * @param array<string, array|string> $composerContents
     * @throws Exception
     */
    final protected function writeComposerJsonFile(array $composerContents): void
    {
        (new JsonFile(getcwd() . '/composer.json'))->write($composerContents);
    }

    /**
     * DevRequired packages as an array.
     * Key is package name (eg. mypack/mypack)
     * Value is the version constraint.
     * @return array<string, string>
     */
    private function getInstalledDevRequires(): array
    {
        $devRequires = array_map(
            function (Link $require) {
                return ['version' => $require->getPrettyConstraint(), 'package' => $require->getTarget()];
            },
            $this->getComposer()->getPackage()->getDevRequires()
        );

        return array_combine(array_column($devRequires, 'package'), array_column($devRequires, 'version'));
    }
}
