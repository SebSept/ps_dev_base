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

namespace SebSept\PsDevToolsPlugin\Command\SebSept;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class HelloCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this->setName('psdt:hello');
        $this->setDescription('Not yet implemented');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        var_dump($this->getIO()->isInteractive());
        $this->getIO()->write('This command show some helps for usage of this package.'); // not displayed
        $this->getIO()->askConfirmation('oi K');
        $this->getIO()->write('<info>ee</info>');
        $this->getIO()->error('implement me');
//        throw new \Exception('implement me');

        return 0;
    }
}
