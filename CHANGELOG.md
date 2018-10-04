# Changelog

All notable changes to `Muzzle` will be documented in this file.

Updates should follow the [Keep a CHANGELOG](http://keepachangelog.com/) principles.

## [0.3.0] - 2018-10-04

### Added
- shifted the project from building the expected request and then comparing it to the actual, to instead defining a list of expectations that are run against the actual request
- `Muzzle::append` now expects `Expectation` instances rather than `Transaction` instances 
- introduce regex pattern matching for query parameters and JSON payloads
- added the `should` method to allow a quick `callable` to be used for custom expectations
- expectations are called as each request is made eliminating the need to "close" the test

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- the `makeAssertions` method was removed
- the `Container` class and references were removed
- the `MuzzleIntegration` trait was removed

### Security
- Nothing

## [0.2.0] - 2018-06-27

### Added
- route pattern matching on path assertions
- improved `Transactions` usability
- added shortcut methods to first and last request on the `Muzzle` instance
- replaced exact match with a contains check for the request body default assertion
- added `setJson` helper to automatically `json_encode`the provided body when building requests
- allow `JsonFixture` to be cast to a string
- added `setJson` helper to `ResponseBuilder`
- name middleware when added and insure they come before history

### Deprecated
- Nothing

### Fixed
- fixed generating nested array query strings
- improved assertion failure messages

### Removed
- Nothing

### Security
- Nothing
