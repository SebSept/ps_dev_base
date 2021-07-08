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

use Composer\Package\Link;
use Exception;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PsDevToolsPackageCommand.
 */
abstract class ComposerPackageCommand extends BaseCommand
{
    /**
     * @var OutputInterface
     */
    private $output;

    abstract public function getPackageName(): ?string;

    abstract public function getPackageVersionConstraint(): ?string;

    abstract public function isToolConfigured(): bool;

    /**
     * it configures the tool.
     * It doesn't do checks, only perform addComposerScript(), file copy, etc.
     */
    abstract public function configureTool(): void;

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->output = $output;
        // -- installation / configuration / execution
        try {
            $isToolConfigured = $this->isToolConfigured();
            $isPackageInstalled = $this->isPackageInstalled();

            // state messages
            $isPackageInstalled
                ? $this->getIO()->write(sprintf('%s is installed.', $this->getPackageName() ?? $this->getComposerScriptName()))
                : $this->getIO()->error("{$this->getPackageName()} is not installed.");
            $isToolConfigured
                ? $this->getIO()->write("{$this->getComposerScriptName()} is configured.")
                : $this->getIO()->error("{$this->getComposerScriptName()} is not configured.");

            // run
            $runConfiguration = $input->getOption('reconfigure') || !$isToolConfigured;
            $readyToRun = $isPackageInstalled && !$runConfiguration;

            $isPackageInstalled ?: $this->installPackage();
            !$runConfiguration ?: $this->configureTool();
            if (!$readyToRun) {
                $this->getIO()->write("<bg=green>{$this->getComposerScriptName()} is installed and configured.</>");
                $this->getIO()->write("run the same command <comment>composer {$this->getName()}</comment> to run the tool.");

                return 0;
            }

            $this->runComposerScript($output);
        } catch (Exception $exception) {
            $this->getIO()->critical($exception->getMessage());

            return 1;
        }

        return 0;
    }

    /**
     * Common options.
     */
    protected function configure(): void
    {
        $this->addOption('reconfigure', null, InputOption::VALUE_NONE, 'rerun configuration');
    }

    final protected function isPackageInstalled(): bool
    {
        return is_null($this->getPackageName())
            || array_key_exists($this->getPackageName(), $this->getInstalledDevRequires());
    }

    /**
     * @return int execution result code - 1 if no package
     *
     * @throws \Exception
     */
    final protected function installPackage(): int
    {
        if (is_null($this->getPackageName())) {
            return 1;
        }

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
     * DevRequired packages as an array.
     * Key is package name (eg. mypack/mypack)
     * Value is the version constraint.
     *
     * @return array<string, string>
     */
    private function getInstalledDevRequires(): array
    {
        if (is_null($this->getComposer())) {
            return [];
        }
        $devRequires = array_map(
            static function (Link $require) {
                return ['version' => $require->getPrettyConstraint(), 'package' => $require->getTarget()];
            },
            $this->getComposer()->getPackage()->getDevRequires()
        );

        return array_combine(array_column($devRequires, 'package'), array_column($devRequires, 'version')) ?: [];
    }
}
