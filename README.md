![alt text](https://cdn.marshmallow-office.com/media/images/logo/marshmallow.transparent.red.png "marshmallow.")

# Marshmallow Commands

[![Latest Version on Packagist](https://img.shields.io/packagist/v/marshmallow/commands.svg?style=flat-square)](https://packagist.org/packages/marshmallow/commands)
[![Tests](https://img.shields.io/github/actions/workflow/status/marshmallow-packages/commands/php-syntax-checker.yml?branch=main&label=tests&style=flat-square)](https://github.com/marshmallow-packages/commands/actions/workflows/php-syntax-checker.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/marshmallow/commands.svg?style=flat-square)](https://packagist.org/packages/marshmallow/commands)

A collection of handy Artisan commands that can be reused across all Marshmallow projects.

## Installation

Install the package via Composer:

```bash
composer require marshmallow/commands
```

The service provider (`Marshmallow\Commands\CommandsServiceProvider`) is auto-discovered, so there is nothing else to register. The commands become available as soon as the package is installed.

## Commands

| Command | Description |
| --- | --- |
| `env:set {key} {value}` | Add a variable to your `.env` file, or update it in place if the key already exists. |
| `marshmallow:resource {resource_name?} {package_name?} {--force}` | Generate a Laravel Nova resource from the package's stub into your `app/Nova` folder. Prompts for any missing argument; use `--force` to overwrite an existing resource without confirmation. |
| `marshmallow:clear` | Run all of Laravel's cache-clearing commands in one go (`cache:clear`, `route:clear`, `config:clear`, `view:clear`, `clear-compiled`). |
| `package:status {--cs-fixer} {--has-workflow=}` | List a table of symlinked path-repository packages (read from the project `composer.json`) that have outstanding git changes. |
| `cron:show` | Experimental: parse the scheduler defined in `app/Console/Kernel.php` and derive a single combined cron expression. |

> **Note:** `cron:show` is a work in progress and currently dumps its intermediate output for debugging rather than returning a finished cron line.

## Usage

Set (or update) an environment variable:

```bash
php artisan env:set APP_NAME Marshmallow
```

Generate a Nova resource from the bundled stub:

```bash
php artisan marshmallow:resource Order orders
```

Clear all Laravel caches at once:

```bash
php artisan marshmallow:clear
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for recent changes.

## Contributing

Contributions are welcome. Please open an issue or pull request on the [repository](https://github.com/marshmallow-packages/commands) and follow Marshmallow's standard contribution process.

## Security Vulnerabilities

Please report security vulnerabilities by email to [stef@marshmallow.dev](mailto:stef@marshmallow.dev) rather than via the public issue tracker.

## Credits

- [Stef](https://marshmallow.dev)
- [All Contributors](https://github.com/marshmallow-packages/commands/contributors)

## License

The MIT License. Please see the [License File](LICENSE) for more information.
