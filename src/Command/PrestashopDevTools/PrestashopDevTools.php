<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command\PrestashopDevTools;

use SebSept\PsDevToolsPlugin\Command\BaseCommand;

abstract class PrestashopDevTools extends BaseCommand
{
    final public function getPackageName(): string
    {
        return 'prestashop/php-dev-tools';
    }

    final public function getPackageVersionConstraint(): string
    {
        return '4.*';
    }
}
