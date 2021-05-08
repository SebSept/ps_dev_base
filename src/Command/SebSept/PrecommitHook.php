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
        $this->setHelp(
            $this->getDescription() . <<<'HELP'

 * adds a composer script <info>pre-commit</info>
 * creates file <info>precommit.sh</info>
 * symlinks <info>precommit.sh</info> to <info>.git/hooks/precommit</info>
 * next runs will trigger the composer script <info>pre-commit</info>

The composer scripts by default triggers the other scripts @phpstan @csfix (dry-run) and <info>composer validate</info>.
This is the default, you can edit the content of the script.

The <comment>--reconfigure</comment> allows to resetup, rerun the 3 first steps and override contents.

This is tested on GNU/Linux, it probably works fine on MacOS. Probably not on Windows (?).

HELP
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->fs = new Filesystem();

        try {
            $preCommitHookFileRelativePath = str_replace(__DIR__ . DIRECTORY_SEPARATOR, '', self::PRECOMMIT_HOOK_FILE);

            $this->isComposerScriptDefined()
                ? $this->getIO()->write(sprintf('<info>Composer script %s is installed.</info>', $this->getComposerScriptName()))
                : $this->installComposerScript();

            $this->precommitFileExists()
                ? $this->getIO()->write(sprintf('<info>Precommit file <comment>%s</comment> is present.</info>', self::PRECOMMIT_FILE))
                : $this->copyPrecommitFile();

            $this->isPrecommitFileSymlinked()
                ? $this->getIO()->write(sprintf('<info><comment>%s</comment> is symlinked to <comment>%s</comment></info>', self::PRECOMMIT_FILE, $preCommitHookFileRelativePath))
                : $this->symLinkPrecommitFile();

            $this->isPrecommitFileExecutable()
                ? $this->getIO()->write(sprintf('<info><comment>%s</comment> is executable.</info>', $preCommitHookFileRelativePath))
                : $this->makePrecommitFileExecutable();

            $this->getIO()->write(
                <<<'INFOS'
If everything is ok, before the next commit on this repository, the git precommit hook will be triggered.
If the script is ok, commit will be performed. Otherwise the commit will be aborted.
In case, you don't see the precommit script messages to see what needs to be fixed, you can run
<info>composer psdt:pre-commit<info>.

You can also run this command at any time, before processing the commit, stashing changes for example.

You can edit the script content by editing the script entry <comment>pre-commit</comment> in <comment>composer.json</comment>.
INFOS
            );

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

    private function installComposerScript(): void
    {
        $this->getIO()->write('Installing composer script : ', false);
        $this->addComposerScript($this->getComposerScripts());
        $this->getIO()->write('Ok');
    }

    /**
     * Composer scripts to launch depending if tools are configured.
     *
     * @return array<int, string>
     */
    private function getComposerScripts(): array
    {
        $scripts = ['composer validate'];
        (new PrestashopDevToolsCsFixer())->isToolConfigured()
            ?: array_push($scripts, 'vendor/bin/php-cs-fixer fix --dry-run --ansi');
        (new PrestashopDevToolsPhpStan())->isToolConfigured()
            ?: array_push($scripts, '@phpstan --ansi');

        return $scripts;
    }

    private function precommitFileExists(): bool
    {
        return $this->fs->exists(self::PRECOMMIT_FILE);
    }

    private function copyPrecommitFile(): void
    {
        $this->fs->copy(self::SOURCE_PRECOMMIT_FILE, self::PRECOMMIT_FILE, true);
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
        $this->fs->chmod(self::PRECOMMIT_FILE, 0755);
    }
}
