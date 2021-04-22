<?php

declare(strict_types=1);


namespace SebSept\PsDevToolsPlugin\Command\SebSept;

use SebSept\PsDevToolsPlugin\Command\ScriptCommand;

class IndexPhpFiller extends ScriptCommand
{
    protected function configure(): void
    {
        $this->setName('fill-indexes');
        parent::configure();
    }

    public function getComposerScriptName(): string
    {
        return 'fill-index';
    }
}
