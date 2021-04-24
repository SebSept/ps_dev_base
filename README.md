# Prestashop Dev Base

This package is composer plugin that provides **tools for [Prestashop](https://github.com/prestashop/prestashop) module development**.  
It's made to **remove the burden of intallation and configuration of essential development tools**. 

No need to go back to the package documentation on each install and perform tedious configuration you never remember.

## Requirements 

The single requirement is just to have composer installed.
If you do not have composer, you can leave this page and [start learning it](https://getcomposer.org/).

For easier use, I highly suggest to use command line autocompletion for composer.  
There's a couple options available, [this one](https://github.com/bamarni/symfony-console-autocomplete) is [recommanded by composer](https://getcomposer.org/doc/03-cli.md#command-line-completion).

## Featured tools

This is just a starting point.

- Code formating : [php-cs-fixer](https://github.com/FriendsOfPhp/PHP-CS-Fixer) configured using prestashop standard, ready to use out of the box.
- Code analysis : [phpstan](https://phpstan.org/) almost ready to use with Prestashop standard, it asks a question then you're ready. 
- `fill-indexes` command, to add required index.php files. (see below for details)

More tools will come 
- [prestashop/header-stamp](https://github.com/PrestaShopCorp/header-stamp/) (update license header in files)
- a tool to install a precommit hook to ensure everything is ok before commiting.
- GitHub actions
- ...

## Featured CI/github actions

These features are not yet included but will come soon.

- php syntax check (php 7.2, php 7.3)
- php-cs-fix (configured using prestashop standard)
- phpstan (configured using prestashop standard)
- symfonycorp/security-checker (checks composer packages with security problem)
- workflow to release to module (zip with the right directory) created when pushing a tag.

## How does it work ?

This package is composer plugin, it adds new commmands to composer command line tool.  
These commands are under the namespace `psdt` (PrestaShop Developement Tools).

The first time a command is run, a composer script is also added.
For example, the 

## Provided commands

* psdt:prestashop-dev-tools:php-cs-fixer
* psdt:prestashop-dev-tools:phpstan
* psdt:fill-indexes

### fill-indexes

`composer psdt:fill-indexes`

Add the missing index.php files on each folder.
Existing index.php files are not overriden.

This is a security requirement of Prestashop to avoid the contents to be listed.

More information [on the official documentation](https://devdocs.prestashop.com/1.7/modules/sell/techvalidation-checklist/#a-file-indexphp-exists-in-each-folder).

I can't include [prestashop/autoindex](https://github.com/PrestaShopCorp/autoindex) because [it targets php 5.6](https://github.com/PrestaShopCorp/autoindex/blob/92e10242f94a99163dece280f6bd7b7c2b79c158/composer.json#L23) and has other issues.  
My replacement is simpler and doesn't require additionnal dependencies.

### php-cs-fixer

`psdt:prestashop-dev-tools:php-cs-fixer [--reconfigure]`

Format php files for complying with the Prestashop standards.
This allows consistent code base.

On the first run :
* the _PrestaShop/php-dev-tools_ package will be installed if needed.
* _.php_cs_ file will be created with the Prestashop standard styles. Destructive, get your files under version control
* the composer script _csfix_ will be added. So you can invoke this command with `composer csfix`

The next runs will run the fixer. All files will be formated according to the Prestashop standard.

Provided by [PrestaShop/php-dev-tools/](https://github.com/PrestaShop/php-dev-tools/).  
Autoinstallation provided by this package.

Allows complying with the [Prestashop standards](https://devdocs.prestashop.com/1.7/development/coding-standards/).

### phpstan

`psdt:prestashop-dev-tools:phpstan [--reconfigure]`

Run Phpstan from prestashop/prestashop-dev-tools.

Just like psdt:prestashop-dev-tools:php-cs-fixer, the first run install the package and creates/overrides the phpstan.neon configuration with Prestashop standards.

Provided by [PrestaShop/php-dev-tools/](https://github.com/PrestaShop/php-dev-tools/).  
Autoinstallation provided by this package.

Allows complying with the [Prestashop standards](https://devdocs.prestashop.com/1.7/development/coding-standards/).

## Installation

> This package is not yet on packagist. (no release published yet)
> so, before, your composer.json must include :

```json
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/SebSept/ps_dev_base",
      "package": {
        "name": "sebsept/ps_dev_base"
      }
    }
  ],
```

At the root of your module, in a shell, run (not yet available, see below)
`composer require --dev sebsept/ps_dev_base:2.x-dev`

## Under the hood / Credits

[php-cs-fixer](https://github.com/FriendsOfPhp/PHP-CS-Fixer) and [phpstan](https://phpstan.org/) configuration and bootstraping are provided by [PrestaShop/php-dev-tools/](https://github.com/PrestaShop/php-dev-tools/).  
Repository actions are made by [github workflows](https://docs.github.com/en/free-pro-team@latest/actions).

## What's next ?

See is issues in this github repository.

## Development notes (for this package)

Install the precommit with `ln -s $(pwd)/precommit.sh .git/hooks/pre-commit` (works on linux)
