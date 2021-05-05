# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.5] - 5 May 2021
### Fixed
Re-add CSP whitelisting

## [2.0.4] - 30 April 2021
### Fixed
- Fix block retrieval with Layout instead LayoutFactory (@sprankhub)
- Make sure view model is set correctly (@sprankhub)

## [2.0.3] - 29 October 2020
### Fixed
- Fix error when block is not present

## [2.0.2] - 28 October 2020
### Fixed
- Category Sort By not working properly with 2.4.X because of weird product loading (70)
- Refactored legacy Registry into request
- Move Config class to new namespace
- PHPCS fixes for Magento Marketplace compliance

## [2.0.1] - 29 July 2020
### Added
- Magento 2.4 support

## [2.0.0] - 2020-07-21
### Removed
- Legacy CustomerData class
- Dev dependency with Mockery

### Fixed
- Upgrade PHPUnit to be 2.4 compatible
- Bumped minimum PHP to 7.2

## [1.1.3] - 2020-05-30
### Fixed
- Add a new CSP whitelist for M2.3.5+

## [1.1.2] - 2020-03-31
### Fixed
- Some small code quality things

## [1.1.1] - 2020-02-10
### Added
- Add ACL file for configuring access to config

## [1.1.0] - 2019-11-23
### Added
- Major rewrite to remove custom sections in favor of DI plugin
- No more reloading of sections

## [1.0.3] - 2019-08-07
### Fixed
- Use currency code instead of currency symbol (@nicolas-medina)

## [1.0.2] - 2019-06-15
### Fixed
- Move cookie-restriction-mode to JS to work with FPC

## [1.0.1] - 2019-03-13
### Fixed
- Fix duplicate code
- Add compatibility with Magento 2.2 again (2.1 is dropped permanently)
- Fix invalid template path
- Fix reloading issues with quote

## [1.0.0] - 2019-02-26
### Fixed
- First release with Changelog
- See GitHub commits for earlier messages
