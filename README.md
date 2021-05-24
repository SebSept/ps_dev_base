# Prestashop Dev Base

This package provides **tools for [Prestashop](https://github.com/prestashop/prestashop) module development**.  
It's made to **remove the burden of intallation and configuration of essential development tools**. 

![code quality](https://github.com/SebSept/ps_dev_base/actions/workflows/phpstan_and_style.yml/badge.svg)

Quik start : 
```shell
composer require --dev sebsept/ps_dev_base
composer list psdt
```


## Requirements 

The single requirement is just to have composer 2 installed (not composer 1.x).
If you do not have composer, you can leave this page and [start learning it](https://getcomposer.org/).

For easier use, I highly suggest to use command line autocompletion for composer.  
There's a couple options available, [this one](https://github.com/bamarni/symfony-console-autocomplete) is [recommanded by composer](https://getcomposer.org/doc/03-cli.md#command-line-completion).

## Featured tools

- Code formating : [php-cs-fixer](https://github.com/FriendsOfPhp/PHP-CS-Fixer) configured using prestashop standard, ready to use out of the box.
- Code analysis : [phpstan](https://phpstan.org/) autodetect PrestaShop root directory or  asks (nothing more to do). 
- `fill-indexes` command, to add required index.php files. (see below for details)
- git pre-commit hook installer (details below)

More tools will come ...

## How does it work ?

This package is composer plugin, it adds new commmands to composer command line tool.  
These commands are under the namespace `psdt` (PrestaShop Developement Tools).

The first time a command is run, a composer script is also added.
For example, the php-cs-fixer can be invoked with `composer csfix` this is a bit shorter.
You can even take an additionnal step by [defining an alias](https://duckduckgo.com/?q=linux+alias&t=github&ia=web).

## Provided commands

* [psdt:php-cs-fixer](#fill-indexes)
* [psdt:phpstan](#phpstan)
* [psdt:fill-indexes](#fill-indexes)
* [psdt:install-precommit-hook](#git-pre-commit-hook-installer) (not supported on Windows yet)

### php-cs-fixer

`composer psdt:php-cs-fixer [--reconfigure]`

Format php files for complying with the Prestashop standards.
This allows consistent code base.

On the first run (or when `--reconfigure` option is used):
* the _PrestaShop/php-dev-tools_ package will be installed if needed.
* _.php_cs_ file will be (re)created with the Prestashop standard styles. (Destructive operation, get your files under version control!)
* the composer script _csfix_ will be added. So you can invoke this command with `composer csfix`

The next runs will run the fixer. All files will be formated according to the Prestashop standard.

Provided by [PrestaShop/php-dev-tools/](https://github.com/PrestaShop/php-dev-tools/).  
Autoinstallation provided by this package.

Allows complying with the [Prestashop standards](https://devdocs.prestashop.com/1.7/development/coding-standards/).

### phpstan

`composer psdt:phpstan [--reconfigure]`

Run phpstan configured with  [Prestashop standards](https://devdocs.prestashop.com/1.7/development/coding-standards/) against a PrestaShop installation.

The first run or `composer psdt:phpstan --reconfigure` do : 
  - install `prestashop/prestashop-dev-tools` if needed
  - creates/overrides the phpstan.neon configuration with Prestashop standards.
  - guess the \_PS_ROOT_DIR_ and asks for confirmation (or you can provide another path) (this path is needed for analyse)
  - install a composer script `phpstan`

The next runs will trigger `composer psdt:phpstan`

Provided by [PrestaShop/php-dev-tools/](https://github.com/PrestaShop/php-dev-tools/).  
Autoinstallation provided by this package.

### fill-indexes

`composer psdt:fill-indexes [--check-only]`

Add the missing index.php files on each folder.
Existing index.php files are not overriden.

`--check-only` option only list the missing index.php files without adding them.  
This option is usefull for running in the git's pre-commit hook.

This is a security requirement of Prestashop to avoid the contents to be listed.

More information [on the official documentation](https://devdocs.prestashop.com/1.7/modules/sell/techvalidation-checklist/#a-file-indexphp-exists-in-each-folder).

I can't include [prestashop/autoindex](https://github.com/PrestaShopCorp/autoindex) because [it targets php 5.6](https://github.com/PrestaShopCorp/autoindex/blob/92e10242f94a99163dece280f6bd7b7c2b79c158/composer.json#L23) and has other issues.  
My replacement is simpler and doesn't require additionnal dependencies.

### Git Pre-commit hook installer

`composer psdt:install-precommit-hook [--reconfigure]`

- add file `precommit.sh`
- symlink it to `.git/hooks/pre-commit`
- make it executable
- add a composer script `pre-commit`

Before a commit is performed the composer script `pre-commit` will be triggered and must succeed (return 0), otherwise the commit is aborted.

The commands in composer `pre-commit` script hook are provided by command implementing the `PreCommitRegistrableCommand` interface.  
This is currently `phpstan`, `php-cs-fixer` and `fill-indexes`

You can tweak the script by just editing the composer script.  
You can run the `composer psdt:install-precommit-hook` (or `composer run-script pre-commit`) to predict if commit will fail or not. 

## Installation

`composer require --dev sebsept/ps_dev_base`

## Under the hood / Credits

[php-cs-fixer](https://github.com/FriendsOfPhp/PHP-CS-Fixer) and [phpstan](https://phpstan.org/) configuration and bootstraping are provided by [PrestaShop/php-dev-tools/](https://github.com/PrestaShop/php-dev-tools/).  
Repository actions are made by [github workflows](https://docs.github.com/en/free-pro-team@latest/actions).

## What's next ?

See is issues in this GitHub repository.

## Development notes (for this package)

Install the precommit hook with `ln -s $(pwd)/precommit.sh .git/hooks/pre-commit` (works on linux).
Ensure to also make the file executable `chmod +x precommit.sh`.
