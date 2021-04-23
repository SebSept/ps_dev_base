<?php

declare(strict_types=1);

namespace SebSept\PsDevToolsPlugin\Composer;

use Composer\Plugin\Capability\CommandProvider;
use SebSept\PsDevToolsPlugin\Command\PrestashopDevTools\PrestashopDevToolsCsFixer;
use SebSept\PsDevToolsPlugin\Command\PrestashopDevTools\PrestashopDevToolsPhpStan;
use SebSept\PsDevToolsPlugin\Command\SebSept\HelloCommand;
use SebSept\PsDevToolsPlugin\Command\SebSept\IndexPhpFiller;

class PsDevToolsCommandProvider implements CommandProvider
{
    public function getCommands(): array
    {
        return [
            new HelloCommand(),
            new PrestashopDevToolsPhpStan(),
            new PrestashopDevToolsCsFixer(),
            new IndexPhpFiller(),
            ];
    }
}
