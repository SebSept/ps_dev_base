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

namespace SebSept\PsDevToolsPlugin\Composer;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;
use Composer\Script\Event;
use Symfony\Component\Process\Process;

final class PsDevToolsPlugin implements PluginInterface, Capable, EventSubscriberInterface
{
    /** @var bool */
    private $isFirstRun = false;

    public function activate(Composer $composer, IOInterface $io): void
    {
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        $io->info('You choose to remove SebSept/PsDevToolsPlugin :(');
        $io->info('Can you tell me what\'s wrong with it ?');
        $io->info('https://github.com/SebSept/ps_dev_base');
    }

    /**
     * @return array<string, string>
     */
    public function getCapabilities(): array
    {
        return [
            'Composer\Plugin\Capability\CommandProvider' => PsDevToolsCommandProvider::class,
        ];
    }

    /**
     * @return array<string, string|array<int, array<int, string>>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'post-package-install' => 'preparefirstRun',
            'post-update-cmd' => 'firstRun', // just to be the last output.
        ];
    }

    public function firstRun(Event $event): void
    {
        if (!$this->isFirstRun) {
            return;
        }

        $event->getIO()->info(__CLASS__ . ' first run ...');

        $i = new Process('composer psdt:hello'); // @phpstan-ignore-line
        $i->enableOutput()
            ->setTty(true)
            ->start();
        $i->wait();

        $this->isFirstRun = false;
    }

    public function preparefirstRun(PackageEvent $event): void
    {
        if (!$event->getIO()->isInteractive()) {
            return;
        }

        // this happen on post-package-install so it should be an InstallOperation
        // however, for safety it's checked.
        if (!$event->getOperation() instanceof InstallOperation) {
            return;
        }

        // only for self installation
        /** @var InstallOperation $operation */
        $operation = $event->getOperation();
        if ('sebsept/ps_dev_base' !== $operation->getPackage()->getName()) {
            return;
        }

        $this->isFirstRun = true;
    }
}
