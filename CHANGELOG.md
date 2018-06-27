# Changelog

All notable changes to `Muzzle` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [0.2.0] - 2018-06-27

### Added
- route pattern matching on path assertions
- improved `Transactions` usability
- added shortcut methods to first and last request on the `Muzzle` instance
- replaced exact match with a contains check for the request body default assertion
- added `setJson` helper to automatically `json_encode`the provided body when building requests

### Deprecated
- Nothing

### Fixed
- fixed generating nested array query strings
- improved assertion failure messages

### Removed
- Nothing

### Security
- Nothing
