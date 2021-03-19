# Laravel settings

[![Latest Version on Packagist](https://img.shields.io/packagist/v/takethelead/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/takethelead/laravel-settings)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/takethelead/laravel-settings/Tests?label=tests)](https://github.com/takethelead/laravel-settings/actions?query=workflow%3ATests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/takethelead/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/takethelead/laravel-settings)

Overwrite config settings with values from the database.

## Installation

You can install the package via composer:

```bash
composer require takethelead/laravel-settings
```

In order to use this package you will need to publish its configuration file:

```
php artisan vendor:publish --provider="TakeTheLead\Settings\SettingsServiceProvider" --tag="config"
```

And migrate the database

```
php artisan migrate
```

## Usage

### Overwriting values
This package allows you to define config values that should be overwritten with a value from the database.
The keys of these config values can be defined in `config/laravel-settings.php`, where you can find a key `overwrites`.

#### How does this work?

Imagine the following config file

```
<?php 
// config/some-config-file.php
return [
    'key1' => 'fallback_value_for_key_1',
    'key2' => 'fallback_value_for_key_2,
];
```

In order to overwrite the value for `key2` you will have to create a new setting in the database.
You can do this by running the following artisan command (or by creating a migration):

```
// Updates an existing setting or creates a new one if it does not exists.
php artisan laravel-settings:update fallback_value_for_key_2
```

> please note that string values will be stored encrypted

Now that we have created a new setting in the database we have to tell the application to overwrite it. You can do that in `config/laravel-settings.php`

```
// ...
'overwrites' => [
    'some-config-file.key2' => 'the_database_setting_key_you_choose_in_the_previous_step',
],
// ....
```

That's it, whenever you run `config('some-config-file.key2')` you will get the value from the database instead of the fallback value from the config file.

## Does this impact performance?
No it doesn't, we cache all settings. So whenever a setting has been overwritten once, we will cache its query result forever.
Unless you change the value of the setting, then we will clear the cache and and we will need to query for that setting once again.

If you cache your configuration files using `php artisan config:cache`, the overwritten values will aslo be cached and no queries will be performed during a request.

## Available artisan commands

|Command|Description|
|---|---|
|php artisan laravel-settings:list|List all settings|
|php artisan laravel-settings:update {setting}|Update a setting, or creates it if it does not exist|

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Joren Van Hocht](https://github.com/jorenvh)
- [Take The Lead](https://takethelead.be)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

