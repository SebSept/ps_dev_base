<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command\Tools\PrestashopDevTools;

use SebSept\PsDevToolsPlugin\Command\PsDevToolsBaseCommand;

abstract class PrestashopDevTools extends PsDevToolsBaseCommand
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
