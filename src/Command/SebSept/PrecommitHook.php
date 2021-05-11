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

use Exception;
use SebSept\PsDevToolsPlugin\Command\PrestashopDevTools\PrestashopDevToolsCsFixer;
use SebSept\PsDevToolsPlugin\Command\PrestashopDevTools\PrestashopDevToolsPhpStan;
use SebSept\PsDevToolsPlugin\Command\ScriptCommand;
use SplFileInfo;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

final class PrecommitHook extends ScriptCommand // implements ConfigurableCommand
{
    const SOURCE_PRECOMMIT_FILE = __DIR__ . '/../../../resources/precommit.sh';
    const PRECOMMIT_FILE = 'precommit.sh';
    const PRECOMMIT_HOOK_FILE = '.git/hooks/pre-commit';

    /** @var Filesystem */
    private $fs;

    final protected function configure(): void
    {
        $this->setName('install-precommit-hook');
        $this->setDescription('Install a git pre-commit hook');
        $this->addOption('reconfigure', null, InputOption::VALUE_NONE, 'rerun configuration and file installations.');
        $this->setHelp(
            $this->getDescription() . <<<'HELP'

 * creates file <comment>precommit.sh</comment> that trigger composer script <info>pre-commit</info>
 * adds a composer script <info>pre-commit</info>
 * symlinks <comment>precommit.sh</comment> to <info>.git/hooks/precommit</info>
 * next runs will trigger the composer script <info>pre-commit</info>

The composer scripts by default triggers the other scripts <info>@phpstan</info>,  <info>@csfix</info> (dry-run) and <info>composer validate</info>.
This is the default, you can edit the content of the script in <comment>composer.json</comment>

The <comment>--reconfigure</comment> allows to resetup, rerun the 3 first steps and override contents.

This is tested on GNU/Linux, it probably works fine on MacOS. Probably not on Windows (feedback and fix are welcome).

HELP
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->fs = new Filesystem();
            /** @var bool $reconfigure */
            $reconfigure = $input->getOption('reconfigure');
            $preCommitHookFileRelativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', self::PRECOMMIT_HOOK_FILE);

            if ($reconfigure) {
                $composerScriptIsDefined = $precommitFileExists = $precommitFileIsSymlinked = $precommitFileIsExecutable = $readyToRun
                    = false;
            } else {
                $composerScriptIsDefined = $this->isComposerScriptDefined();
                $precommitFileExists = $this->precommitFileExists();
                $precommitFileIsSymlinked = $this->isPrecommitFileSymlinked();
                $precommitFileIsExecutable = $this->isPrecommitFileExecutable();
                $readyToRun = $composerScriptIsDefined && $precommitFileExists && $precommitFileIsSymlinked && $precommitFileIsExecutable;
            }

            $composerScriptIsDefined
                ? $this->getIO()->write(sprintf('<info>Composer script %s is installed.</info>', $this->getComposerScriptName()))
                : $this->addComposerScript($this->getComposerScripts());

            $precommitFileExists
                ? $this->getIO()->write(sprintf('<info>Precommit file <comment>%s</comment> is present.</info>', self::PRECOMMIT_FILE))
                : $this->copyPrecommitFile();

            $precommitFileIsSymlinked
                ? $this->getIO()->write(sprintf('<info><comment>%s</comment> is symlinked to <comment>%s</comment></info>', self::PRECOMMIT_FILE, $preCommitHookFileRelativePath))
                : $this->symLinkPrecommitFile();

            $precommitFileIsExecutable
                ? $this->getIO()->write(sprintf('<info><comment>%s</comment> is executable.</info>', $preCommitHookFileRelativePath))
                : $this->makePrecommitFileExecutable();

            $reconfigure && $this->getIO()->write($this->getAdditionnalHelp());

            $readyToRun
                ? $this->runComposerScript($output)
                : $this->getIO()->write(sprintf('run <info>%s</info> command again to run composer script <info>%s</info>', $this->getName(), $this->getComposerScriptName()));

            return 0;
        } catch (Exception $exception) {
            $this->getIO()->error(sprintf('%s failed : %s', $this->getComposerScriptName(), $exception->getMessage()));
            // throw $exception; // for debug purpose.
            return 1;
        }
    }

    /**
     * Name of composer script.
     * In case it change, it also needs to be changed in the information text displayed.
     *
     * @see execute()
     */
    public function getComposerScriptName(): string
    {
        return 'pre-commit';
    }

    /**
     * Composer scripts to launch depending if tools are configured.
     *
     * @return array<int, string>
     */
    private function getComposerScripts(): array
    {
        $scripts = ['composer validate'];
        !(new PrestashopDevToolsCsFixer())->isToolConfigured()
            ?: array_push($scripts, 'vendor/bin/php-cs-fixer fix --dry-run --ansi');
        !(new PrestashopDevToolsPhpStan())->isToolConfigured()
            ?: array_push($scripts, '@phpstan --ansi');

        return $scripts;
    }

    private function precommitFileExists(): bool
    {
        return $this->fs->exists(self::PRECOMMIT_FILE);
    }

    private function copyPrecommitFile(): void
    {
        $this->getIO()->write('Copying pre-commit script file ...', false);
        $this->fs->copy(self::SOURCE_PRECOMMIT_FILE, self::PRECOMMIT_FILE, true);
        $this->getIO()->write('<info>OK</info>');
    }

    private function isPrecommitFileSymlinked(): bool
    {
        $gitPrecommitFile = new SplFileInfo(getcwd() . DIRECTORY_SEPARATOR . self::PRECOMMIT_HOOK_FILE);
        $precommitFile = new SplFileInfo(getcwd() . DIRECTORY_SEPARATOR . self::PRECOMMIT_FILE);

        return $gitPrecommitFile->isLink()
            && $precommitFile->getPathname() === $gitPrecommitFile->getLinkTarget();
    }

    private function symLinkPrecommitFile(): void
    {
        $this->getIO()->write('Symlink to precommit hook...', false);
        if (!symlink(
            getcwd() . DIRECTORY_SEPARATOR . self::PRECOMMIT_FILE,
            getcwd() . DIRECTORY_SEPARATOR . self::PRECOMMIT_HOOK_FILE
        )) {
            throw new Exception('Failed to symlink.');
        }
        // this does nothing (?), no error neither ...
//        $this->fs->symlink(self::PRECOMMIT_FILE, self::PRECOMMIT_HOOK_FILE);
        $this->getIO()->write(' <info>OK</info>');
    }

    private function isPrecommitFileExecutable(): bool
    {
        return (new SplFileInfo(self::PRECOMMIT_FILE))->isExecutable();
    }

    private function makePrecommitFileExecutable(): void
    {
        $this->getIO()->write('Make pre-commit script file executable ...', false);
        $this->fs->chmod(self::PRECOMMIT_FILE, 0755);
        $this->getIO()->write('<info>OK</info>');
    }

    private function getAdditionnalHelp(): string
    {
        return <<<'INFOS'
Before the next commit the git precommit hook will be triggered.
If the pre-commit script return 0 (success), commit will be performed, otherwise aborted.
In case, you can't read the precommit script output and find what's wrong
just run <info>composer psdt:pre-commit</info> .
You can also run this command at any time, before processing the commit, stashing changes for example.
You can edit the script content by editing the script entry <info>pre-commit</info> in <comment>composer.json</comment>.
INFOS;
    }
}
