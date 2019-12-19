# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [Unreleased]
### Added|Changed|Deprecated|Removed|Fixed|Security
Nothing so far

## 1.10.2 - 2019-12-19
### Fixed
- PHP 7.4 curly brace string offset access deprecation

## 1.10.1 - 2018-12-21
### Fixed
- Update `composer.lock`.
- Update code to conform with `zicht/standards-php` 3.4.0.

## 1.10.0 - 2018-11-29
### Added
- `Cryptor` to do some basic and not too fancy encryption/decryption with `openssl`

## 1.9.1 - 2018-11-09
### Fixed
- Now using composer scripts for phpunit and linter
- Fixed multiple phpcs issues
- Removed test files from autoload

## 1.9.0 - 2018-02-28
* Optional php7 support in composer (e4fdac99a4c1e558c9ebe8b7c1ca377c9ae57a7e)
* Fix link generation with brackets (fc8cfe8b39e5437235814cc13e1210c51bbbb8aa)

## 1.8.0 - 2017-01-19
* Replaces the `iconv` transliteration implementation with
  `transliterator_transliterate` from `intl`.

## 1.7.0
* 1.7.0 - add `Str::slugify`
* 1.7.1 - deprecated slugify, because it already existed under a different name
* 1.7.2 - fixes some documentation issues

## 1.6.0
* 1.6.0 - add `Str::truncate`

## 1.5.0
* 1.5.0 - add `Str::rolenize`
* 1.5.1 - remove `Str::rolenize` (it did not belong here, and was tagged by accident)
* 1.5.2 
  * Documentation: Made the code for Net::isLocalIpv4() a bit better legible
  * Fix: When systemizing a name, "soft hyphens" should be removed

## 1.4.0
* 1.4.0 - Adds better detection of links in plain text.
* 1.4.1 - Fixes some edge cases related to link detection
* 1.4.2 - Fixes even more edge cases related to link detection

## 1.3.0
* Add `Xml::format()`

## 1.2.0
* 1.2.0 - add `Net::isLocalIpv4()`
* 1.2.1 - fix encoding issue; assume UTF-8
* 1.2.2 - fixes link detection
* 1.2.3 - moves the implementation of createLinks() to a separate public method

## 1.1.0
* 1.1.0 - adds `Zicht\Util\Mutex` - a simple mutex implementation using `flock`

## 1.0.0
* 1.0.0 - first stable release
* 1.0.1 - add `Str::ascii()` and `Str::systemize()`
* 1.0.2 - add `Html::fromText()` and `Html::filter()`
* 1.0.3 - add `Zicht\Util\TreeTools`
* 1.0.4 - add `Zicht\Util\Url`
