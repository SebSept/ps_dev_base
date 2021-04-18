<?php

declare(strict_types=1);

namespace SebSept\PsDevToolsPlugin;

use Composer\Composer;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Installer\PackageEvent;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

class PsDevToolsPlugin implements PluginInterface, Capable, EventSubscriberInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        $io->debug('Mon plugin est actif');
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        $io->debug('Mon plugin est inactif');
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        $io->info('You choose to remove SebSept/PsDevToolsPlugin :(');
        $io->info('Can you tell me what\'s wrong with it ?');
        $io->info('https://github.com/SebSept/ps_dev_base');
    }

    public function getCapabilities(): array
    {
        return [
            'Composer\Plugin\Capability\CommandProvider' => PsDevToolsCommandProvider::class
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'post-package-install' => 'hello',
        ];
    }

    public function hello(PackageEvent $event): void
    {
        // this happen on post-package-install so it should be an InstallOperation
        // however, for safety it's checked.
        if (!$event->getOperation() instanceof InstallOperation) {
            return;
        }

        // only for self installation
        /** @var InstallOperation $operation */
        $operation = $event->getOperation();
        if($operation->getPackage()->getName() !== 'sebsept/ps_dev_base') {
           return;
        }

        $event->getIO()->write('~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~');
        $event->getIO()->write('~~ <fg=magenta>Congratulation !PsDevTool is now installed</>. ~~');
        $event->getIO()->write('~~ run <comment>composer psdt:hello</comment> to get started.     ~~');
        $event->getIO()->write('~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~');
    }
}
