<?php

declare(strict_types=1);

namespace SebSept\PsDevToolsPlugin;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

class PsDevToolsPlugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        $io->write('hello ' . __FUNCTION__);
//        $composer->getInstallationManager()->isPackageInstalled();
        // TODO: Implement activate() method.
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        // TODO: Implement deactivate() method.
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        // TODO: Implement uninstall() method.
    }
}
