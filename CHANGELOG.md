# Changelog

## [0.2.2] 16-03-2020
### Added
* Added a new `tty()` method to the `Ssh` class to force TTY use if it's supported.

### Changed
* The output will now clear the screen between each step.
* Force ANSI output when running Composer install.
* Removed an old unused `installed` method from the `ReleaseService` class. 

## [0.2.1] 16-03-2020
### Added
* Added a `common` server configuration option.

## [0.2.0] 16-03-2020
### Added
* Added new config options to specify the binary names for `composer` and `php`.
* Added a config option to force composer to be installed locally.

### Changed
* The server configuration is now merged using `array_replace_recursive`.
* The init command will now include the `php` and `composer` settings by default.

## [0.1.1] 14-03-2020
### Added
* Added a `releases:down` and a `releases:up` command to quickly take deployments offline and back online.

### Changed
* The Config file created by the `init` command will now include a default `paths` object.
* Added the `migrate` config option to the default config file.

### Fixed
* Fixed a bug in `Ssh::run()` method that will only attempt to run the callback if it's actually supplied.

## [0.1.0] 14-03-2020
### Added
* Changed how `Ssh::getProcess` runs commands to make them safer.
* The runner will now exit if a non-zero exit code is returned.
* Added an `init` command to install new config files.
* Added an `install` command to run the initial deployments.
* Moved all the logic from the `DeployCommand` into a `Deployer` class.
* Wrote out a bunch for the `README.md` file.
