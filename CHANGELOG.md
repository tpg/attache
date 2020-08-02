# Changelog

## [Unreleased]
### Changed
* The configuration requires the `server` config setting has been changed from an array of objects to an object of servers keyed by their names.
* The `name` server config attribute has been removed.

## [0.6.4] 13-07-2020
### Changed
* The `before-prep-composer` and `after-prep-composer` script hooks have been renamed to `before-prepcomposer` and `after-prepcomposer` respectively.

## [0.6.3] 13-07-2020
### Added
* Added two new script tags: `@root` points to the project root on the server. There is now a `@path` tag which takes one parameter. So `@path:releases` will give you the project releases path.
* The `deploy` command will now check if an installation is present. If not, it will fail and exit. An installation must be completed first.

## [0.6.2] 07-07-2020
### Fixed
* Fixed a bug in the script compiler that was not able to fetch the latest release ID since scripts are compiled BEFORE connecting to the server. The release ID is now passed into the compiler.

### Changed
* The `install` command will now attempt to create the project root directory with a `mkdir -p` command.

## [0.6.1] 25-06-2020
### Fixed
* Fixed a bug in the `init` command that wasn't locating the Git config correctly and wasn't setting the default remote URL.
* Fixed a bug in the `init` command that was not setting the Git remote correctly when more than one remote is configured.

## [0.6.0] 22-06-2020
### Changed
* Rewrote how tasks are executed locally. Attaché should now throw an exception if one or more commands fail in the same way the SSH scripts work.

### Fixed
* Fixed a bug with the `releases:activate` command that was not creating the correct symlink.

## [0.5.3] 09-06-2020
### Added
* Added the ability to change the commands run during the build stage.

## [0.5.2] 09-06-2020
### Fixed
* There was a chance that the build process could cause a timeout which would cause Attache to crash. The limit has been removed.

## [0.5.1] 31-05-2020
### Added
* Added a new `dev` setting to the Composer config.

### Changed
* Running SSH commands will now happen synchronously and will not return output until the entire task is complete. This should completely do away with the random errors received when getting the currrent releases list.
* Updates to the `install` command to make it just a little less error prone.

## [0.4.16] 19-05-2020
### Changed
* Rearranged the deployment steps a little to avoid a rare bug where the composer installation needed access to the `.env` config.

## [0.4.15] 12-05-2020
### Added
* Added a new `releases:delete` command.

## [0.4.14] 12-05-2020
### Fixed
* Fixed a bug in the `releases:list` command that would crash if a release has a non-timestamp directory name.

## [0.4.13] 05-05-2020
### Fixed
Fixed a bug that was not executing the `migrate` Artisan command correctly.

## [0.4.12] 30-04-2020
### Added
* Ability to specify additional assets to copy to the server during deployment that are not included in the repository.

## [0.4.11] 29-04-2020
### Fixed
* Fixed a bug in the `InstallCommand` that was erroring out when no intallation was found.
* The `install` command will now perform its own check. 

## [0.4.10] 28-04-2020
### Changed
* Set the execution timeout to 0 when running the deployer.
* Fixed a bug in the `ScriptTest` that was expecting the wrong exception.

## [0.4.9] 21-04-2020
### Changed
* The `branch` and `port` server attributes are no longer required and will default to `master` and `22` respectively.
* If there is a missing attribute, the exception will now include the name of the attribute.
* Server configurations will now through a `ServerException` instead of a `RuntimeException`.
* Invalid script tags will now through a `ConfigurationException` instead of a `RuntimeException`.

### Fixed
* Fixed a bug causing a crash when intializing a project that does not have a Git remote yet.

## [0.4.7] 14-04-2020
### Added
* No longer need to specify the server name for single server configs.

### Fixed
* Updated the version number in the `attache` bin correctly.

## [0.4.5] 19-03-2020
### Fixed
* Fixed a bug in the `deploy` command that was not running the `prune` command correctly when uring the `--prune` option.
* Fixed a bug in the `deploy` command that was not passing the server name to the `prune` command.

## [0.4.3] 19-03-2020
### Fixed
* Fixed a bug where only the current task would exit if a non-zero exit code is returned.
* Fixed a bug that was not running the `artisan storage:link` command correctly.
* Fixed a bug when running global composer but still prefixing with the PHP binary.

## [0.4.2] 19-03-2020
### Fixed
* The `Initializer::getConfig()` method was not setting `composer.local` to a valid boolean value.

### Changed
* Cleaned up the `Command::execute()` method a little.
* The `Command::fire()` method now returns a valid integer value by default. 

## [0.4.1] 18-03-2020
### Added
* Added a set of tags to the scripts so that correct deployment values can be used.
* Added a new `ScriptCompiler` class that translates tags into actual values.
* The `Server` instances now have a new `latestReleaseId()` method and a `releaseIds()` method.
* Added a test for the `ScriptCompiler`.

## [0.4.0] 17-03-2020
### Added
* Added a new `default` server feature.
* Added a `Command::requiresServer()` method for commands that expect a specified server.
* Added missing documentation to all class methods.

### Changed
* The server name is no longer required for commands that need a server if a `default` is set.
* The `ReleasesActivateCommand` arguments have been reversed.
* A fairly large cleanup of all the classes.
* All of the task and step methods in `Deployer` no longer accept a `Server` instance as a parameter.
* All commands now extend an `Attache\Console\Command` abstract class.

### Removed
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
