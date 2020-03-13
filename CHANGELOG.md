# Changelog

## [Unreleased]
### Added
* Changed how `Ssh::getProcess` runs commands to make them safer.
* The runner will now exit if a non-zero exit code is returned.
* Added an `init` command to install new config files.
* Added an `install` command to run the initial deployments.
* Moved all the logic from the `DeployCommand` into a `Deployer` class.
* Wrote out a bunch for the `README.md` file.
