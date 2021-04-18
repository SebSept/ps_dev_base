<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin;

use Composer\Plugin\Capability\CommandProvider;
use SebSept\PsDevToolsPlugin\Command\HelloCommand;

class PsDevToolsCommandProvider implements CommandProvider
{
    public function getCommands():array
    {
        return [
            new HelloCommand,
            new Command\Tools\PrestashopDevToolsPhpStan,
            ];
    }
}
