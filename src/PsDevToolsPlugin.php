<?php

declare(strict_types=1);

namespace SebSept\PsDevToolsPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\PluginInterface;

class PsDevToolsPlugin implements PluginInterface, Capable
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


    public function getCapabilities():array
    {
        return [
           'Composer\Plugin\Capability\CommandProvider' => PsDevToolsCommandProvider::class
       ];
    }
}
