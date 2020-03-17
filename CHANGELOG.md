# Changelog

## [0.4.0] 17-03-2020
## Added
* Added a new `default` server feature.
* Added a `Command::requiresServer()` method for commands that expect a specified server.
* Added missing documentation to all class methods.

## Changed
* The server name is no longer required for commands that need a server if a `default` is set.
* The `ReleasesActivateCommand` arguments have been reversed.
* A fairly large cleanup of all the classes.
* All of the task and step methods in `Deployer` no longer accept a `Server` instance as a parameter.
* All commands now extend an `Attache\Console\Command` abstract class.

## Removed
* The `servers:show` command has been removed as it doesn't really serve any useful purpose.

## [0.3.2] 17-03-2020
### Changed
* Changed how the `ConfigurationProvider::setConfig` method loads config.
* Added some new tests around the loading of config files.

## [0.3.1] 17-03-2020
### Added
* Added a new more tests.
* Support for the new server scripts feature.

### Changed
* Updated `ReleaseService::fetch()` to throw an exception if there is no response from the server.
* Refactored the `Deployer` class to allow for better testing.

## [0.2.3] 17-03-2020
### Fixed
* Fixed a major bug which was overwriting the `.env` file during deployment.

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
