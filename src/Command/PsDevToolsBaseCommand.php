<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command;

use Composer\Command\BaseCommand;

abstract class PsDevToolsBaseCommand extends BaseCommand implements PsDevToolsCommandInterface
{
    protected function isPackageInstalled(): bool
    {
        trigger_error('not implemented.' . __FUNCTION__);

        return false;
    }

    protected function installPackage(): void
    {
        trigger_error('not implemented.' . __FUNCTION__);
    }
}