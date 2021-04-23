<?php

declare(strict_types=1);

namespace SebSept\PsDevToolsPlugin\Command;

interface BaseCommandInterface
{
    public function getComposerScriptName(): string;
}
