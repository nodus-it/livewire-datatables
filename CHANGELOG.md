# Changelog

All notable changes to `livewire-datables` will be documented in this file

## 0.8.0 - 2024-03-XX

- The search word is now split by every space

## 0.7.0 - 2024-03-13

- Added support for PHP v8.3
- Changed the minimum PHP version to v8.1
- Added the ability to customize the session key with a suffix in order to prevent shared sessions for listviews that are used multiple times
- Fixed wrong default translation key in the confirmation modal component

## 0.6.0 - 2022-12-18

- Added support for PHP v8.2
- Added PHP types to most of the code
- Added the option to disable sort and search for specific columns
- Added the option to disable all widgets

## 0.5.0 - 2022-05-04

- Added support for PHP v8.1
- Changed the minimum PHP version to v8.0
- Added new ``ConfirmModal`` component
- Added loading indicator for the datatable
- Added a new ``@livewireDatatableStyles`` blade directive
- Changed signature of ``Button::setConfirmation()``
- Renamed ``Button::getDisplayButton()`` to ``Button::isAllowedToRender()``
- CSP nonce is now configurable via the core package config
- Fixed several minor issues in the blade views

## Older releases
For older releases is no changelog documented. Refer to the commit history in case you need further details for these versions.
