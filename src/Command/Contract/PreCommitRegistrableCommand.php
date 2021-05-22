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

namespace SebSept\PsDevToolsPlugin\Command\Contract;

/**
 * Interface PreCommitRegistrableCommand.
 *
 * The command which implements this interface can be registered inside the composer 'pre-commit' script
 */
interface PreCommitRegistrableCommand
{
    /**
     * Contents to write inside composer.json file, in the pre-commit script.
     */
    public function getComposerPrecommitScriptContent(): ?string;
}
