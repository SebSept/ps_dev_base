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

use Composer\Plugin\Capability\CommandProvider;
use SebSept\PsDevToolsPlugin\Command\PrestashopDevTools\PrestashopDevToolsCsFixer;
use SebSept\PsDevToolsPlugin\Command\PrestashopDevTools\PrestashopDevToolsPhpStan;
use SebSept\PsDevToolsPlugin\Command\SebSept\HelloCommand;
use SebSept\PsDevToolsPlugin\Command\SebSept\IndexPhpFiller;
use SebSept\PsDevToolsPlugin\Command\SebSept\PrecommitHook;

class PsDevToolsCommandProvider implements CommandProvider
{
    public function getCommands(): array
    {
        return [
//            new HelloCommand(),
            new PrestashopDevToolsPhpStan(),
            new PrestashopDevToolsCsFixer(),
            new IndexPhpFiller(),
            new PrecommitHook(),
            ];
    }
}
