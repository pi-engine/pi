# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 2.5.2 - 2016-02-16

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#13](https://github.com/zendframework/zend-file/pull/13) fixes the behavior
  of the `Zend\File\Transfer` component when multiple uploads using the same
  client name are provided, and no filename filtering is performed; the code now
  ensures that unique names are used in such situations.
- [#14](https://github.com/zendframework/zend-file/pull/14) updates the
  `FilterPluginManager` to work with the updated zend-filter 2.6.0 changes,
  fixing an issue where the zend-file filters were replacing (instead of
  merging) with those in the parent zend-filter implementation.
