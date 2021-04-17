<?php


namespace SebSept\PsDevToolsPlugin\Command;

interface PsDevToolsCommandInterface
{
    public function getPackageName() : string;
    public function getPackageVersionConstraint() : string;
    public function getScriptName() : string;
    public function isToolConfigured() : bool;
    public function configureTool(): void;
    public function runTool(): void;
}