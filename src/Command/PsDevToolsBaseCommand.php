<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command;

use Composer\Command\BaseCommand;
use Composer\IO\IOInterface;
use Composer\Package\Link;
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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = $this->getIO();
        $this->output = $output;
    }

    final protected function isPackageInstalled(): bool
    {
        $installed = array_key_exists($this->getPackageName(), $this->getInstalledDevRequires());
        $installed
            ? $this->io->info("{$this->getPackageName()} is installed.")
            : $this->io->info("{$this->getPackageName()} is not installed.");

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

    final protected function installPackage(): int
    {
        return $this->getApplication()->find('require')->run(
            new ArrayInput([
                'packages' => [sprintf('%s:%s', $this->getPackageName(), $this->getPackageVersionConstraint())],
                '--dev' => true,
                ]),
            $this->output
        );
    }

    /**
     * DevRequired packages as an array.
     * Key is package name (eg. mypack/mypack)
     * Value is the version constraint.
     * @return array
     */
    private function getInstalledDevRequires(): array
    {
        $devRequires = array_map(
            function (Link $require) {
                return ['version' => $require->getPrettyConstraint(), 'package' => $require->getTarget()];
            },
            $this->getComposer()->getPackage()->getDevRequires());

        return array_combine(array_column($devRequires, 'package'), array_column($devRequires, 'version'));

    }



}