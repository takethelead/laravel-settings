# Changelog

All notable changes to `Laravel settings` will be documented in this file

## 1.4.0 - 2023-12-12
- Added support for Laravel 10

## 1.3.0 - 2022-03-29
- Dropped support for PHP 7.4
- Added support for PHP 8.0 & 8.1
- Added support for Laravel 9

## 1.2.0 - 2021-03-19
- Add support for json settings
- Bugfix: bumped minimum required Laravel version to 8.2 as the `newFactory` method was added then. On Lower versions the test suite would fail.
- Bugfix: autoloading & namespace issue
- Configured Github Actions

## 1.1.1 - 2020-10-14
- The type column should not be nullable

## 1.1.0 - 2020-10-14
- Boolean values will also be encrypted

## 1.0.1 - 2020-10-13

- Fix casting issue
- Add extra check to make sure a model exists before throwing the TypeNotQueriedException 

## 1.0.0 - 2020-10-13

- initial release
