# Prestashop Dev Base

This package is composer plugin that provides tools for Prestashop module development.

## Featured local tools

- [php-cs-fixer](https://github.com/FriendsOfPhp/PHP-CS-Fixer) configured using prestashop standard, ready to use out of the box.
- [phpstan](https://phpstan.org/) almost ready to use with Prestashop standard. (first run asks for the path to a Prestashop, and you're done) 
- `fill-indexes` command, to add required index.php. [(recommended by Prestashop)](https://devdocs.prestashop.com/1.7/modules/sell/techvalidation-checklist/#a-file-indexphp-exists-in-each-folder). Replacement for [prestashop/autoindex](https://github.com/PrestaShopCorp/autoindex)

More tools will come 
- [prestashop/header-stamp](https://github.com/PrestaShopCorp/header-stamp/) (update license header in files)
- a tool to install a precommit hook to ensure everything is ok before commiting.
- github actions
- ...

## Featured CI/github actions

This features are not yet included but will come soon.

- php syntax check (php 7.2, php 7.3)
- php-cs-fix (configured using prestashop standard)
- phpstan (configured using prestashop standard)
- symfonycorp/security-checker (checks composer packages with security problem)
- workflow to release to module (zip with the right directory) created when pushing a tag.

## Under the hood

[php-cs-fixer](https://github.com/FriendsOfPhp/PHP-CS-Fixer) and [phpstan](https://phpstan.org/) configuration and bootstraping are provided by [PrestaShop/php-dev-tools/](https://github.com/PrestaShop/php-dev-tools/).

Repository actions are made by [github workflows](https://docs.github.com/en/free-pro-team@latest/actions).

This repository is the glue between these elements.

## How does it work ?

This package is composer plugin, it adds new commmands to composer command line tool.  
Theses commands are under the namespace `psdt` (PrestaShop Developement Tools).

The first time a command is run, a composer script is also added.
For example, the 

For easier use, I highly suggest to use command line autocompletion for composer.  
There's a couple options available, [this one](https://github.com/bamarni/symfony-console-autocomplete) is [recommanded by composer](https://getcomposer.org/doc/03-cli.md#command-line-completion). 

## Provided commands

* psdt:prestashop-dev-tools:php-cs-fixer
* psdt:prestashop-dev-tools:phpstan
* psdt:prestashop-dev-tools:fill-indexes

### fill-indexes

`composer psdt:prestashop-dev-tools:fill-indexes`

Add missing index.php files as [recommended by Prestashop](https://devdocs.prestashop.com/1.7/modules/sell/techvalidation-checklist/#a-file-indexphp-exists-in-each-folder).

I can't include [prestashop/autoindex](https://github.com/PrestaShopCorp/autoindex) because [it targets php 5.6](https://github.com/PrestaShopCorp/autoindex/blob/92e10242f94a99163dece280f6bd7b7c2b79c158/composer.json#L23) and has other issues.  
My replacement is simpler and doesn't require additionnal dependencies.

### php-cs-fixer

`psdt:prestashop-dev-tools:php-cs-fixer [--reconfigure]`

Run Php-cs-fixer from prestashop/prestashop-dev-tools.

The first invokation install the composer package then creates the `.php_cs` file in your working directory.  
The next invokations run the fixer.
In case you want to override the existing `.php_cs` file add the `--reconfigure` option.

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


### What's next ?

See is issues in this github repository.