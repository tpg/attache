# A Highly opinionated deployment tool for Laravel

[![Build Status](https://travis-ci.org/tpg/attache.svg?branch=master)](https://travis-ci.org/tpg/attache)

Attaché is a deployment tool for Laravel originally based on the Laravel Envoy task runner and built around the ideas I wrote [here](https://medium.com/@warrickbayman/zero-downtime-laravel-deployments-with-envoy-version-2-227c8259e31c). The original version of Attaché was actually just a wrapper around Envoy with a predefind script (hence the name).

Attaché as evolved somewhat and is now a standalone deployment tool. It's still highly opinionated and still follows the same ideas, but is more flexible and no longer dependent on Envoy.

> Attaché is the result of me scratching an itch and digging through the Laravel Envoy source code.

## Installation
Attaché should be installed globally through Composer:

```
composer global require thepublicgood/attache
```

If you haven't done so already, make sure you have `~/.composer/vendor/bin` in your path.

## Setting up your project
Attaché assumes a few things about your projects and servers and you'll want to make sure these tasks are taken care of before you start using Attaché.

Firstly, your project MUST be stored in a Git repository. It doesn't really matter which one, as long as you can clone the project onto your server using a private key.

You will also need to make some changes to the web server you're using. Again, it doesn't matter which one you're running as long as you can set it up to serve a symbolic link.

At the root of your project, install a new Attaché config file by using the `attache init` command.

```
attache init
```

This will create a new `.attache.json` file in your project. Attaché can usually figure out the URL of your Git remote and will automatically insert it into the config file for you. The default config looks like this:

```json
{
    "repository": "git@github.com:user/repo.git",
    "servers": [
        {
            "name": "production",
            "host": "example.text",
            "port": 22,
            "user": "user",
            "root": "/path/to/the/application",
            "paths": {
                "releases": "releases",
                "serve": "live",
                "storage": "storage",
                "env": ".env"
            },
            "php": {
                "bin": "php"
            },
            "composer": {
                "bin": "composer",
                "local": false
            },
            "branch": "master",
            "migrate": false,
        }
    ]
}
```

There are a number of other configuration options, but this is the bare minimum. Firstly, the `repository` setting should point to the remote URL of your repository. By default the `init` command will look for a configured remote regardless of it's name. However, if you have more than one remote configured, Attaché will ask you to pick one.

You can specify as many servers as you like. Make sure you update the config for each one.

### Default configuration options

| Settings | Description                                            |
|----------|--------------------------------------------------------|
| name     | The key that server is referrred to as                 |
| host     | The servers hostname. Domain or IP address             |
| port     | The SSH port number (must be specified)                |
| user     | The username to log into the server with               |
| root     | The basepath where the application will be deployed to |
| paths    | The paths that Attaché will create in root             |
| branch   | The repository branch to clone from                    |
| migrate  | Perform a `migrate --force` as part of the deployment  |

### PHP options
In same cases, you may need to specify what the PHP binary name. Since PHP is usually in the users path, Attaché expects this to simply be "php". However, you may need it to be something like "php74" or similar if your host is running more than one version of PHP. You may even need to specify the absolute path to the binary. You can do so using the `php` options. Pass the path of the PHP binary to the `bin` option:

```json
"php": {
    "bin": "php74"
}
```

### Composer options
Similarly, you may need to specify the name of the Composer binary, or even have composer downloaded for you. Attaché allows this through the use of the `composer` settings. There are two supported Composer settings: `bin` and `local`. The `bin` setting specifies the name of the composer binary, and Attaché assumes this to simply be `composer`. Attaché also assumes that you have Composer installed on the path so an install can be done with a simple `composer install`. However, if you don't have permission to install Composer in such a way, you can set `local` to `true` and Attaché will download a copy of Composer for you into the project root.

```json
"composer": {
    "bin": "composer.phar",
    "local": true
}
```

If you have `local` set to `true`, Attaché will automatically run a `composer self-update` for you. However, this is not done if you have it installed globally.

### Paths

Attaché will create a number of items inside the project root. You can change the names of these items by specifying them in the `paths` config option. You don't need to specify them all. Only the ones you want to change.

| Path       | Description                                                      |
|------------|------------------------------------------------------------------|
| `releases` | Where the individual releases are placed                         |
| `serve`    | The name of the symbolic link that the web server needs to serve |
| `storage`  | The Laravel storage directory that is symlinked into the release |
| `.env`     | The Laravel .env file that is symlinked into the release         |

The defaults for the paths are the same of the key names. So if you don't specify a `serve` item, then Attaché will create a `serve` symbolic link.

## Common configuration
You can specify as many servers as you need in the config file, but there are times when all your deployments will actually be on the same physical server, or you might use the same git branch, or the same root path across multiple servers. You don't need to specify the same settings over and over again. Instead you can specify a `common` configuration block which can contain any of the valid server config options, except `name`. If you have a `common` block that will be applied to all the servers you have configured first and then the per-server config will be merged.

```json
{
    "common": {
        "host": "common-host.test",
        "port": 22,
        "user": "common-user",
        "branch": "master"
    },
    "servers": [
        {
            "name": "production",
            "root": "/path/to/production"
        },
        {
            "name": "staging",
            "root": "/path/to/staging",
        },
        {
            "name": "testing",
            "root": "/path/to/testing",
            "branch": "testing"
        }
    ]
}
```

You can override any of the common settings by simply specifying them per server. This can also be very useful if your deployment is made up of different components. Perhaps a set of microservices.

## Safety first
In many cases the `.attache.json` file will contain some sensitive information. Usernames. hostnames and even port numbers could be considered fairly sensitive information. Attaché does not support password authentication for SSH connections so do not attempt to put your password in this file.

It's a good idea to place `.attache.json` in your `.gitignore` file and simply keep a copy of it somewhere on your own computer. If you ever loose you config file, it should be simple enough to rewrite. Attaché does all the hard work for you anyway.

## First deployment

The first deployment of an application to a server is called an `install`. Installing is similar to a standard deployment, but runs a few extra tasks. Installation can only be run once per server and Attaché will stop you from running it again if you attempt to do so. Installing a application again can be a destructive process. If you want to re-install a project, rather create a new config file with a new root path and run the install again. This will install the application to a new location. You'll need to update your web server to point to the location.

To run an install, you can use the `attache install` command:

```
attache install production
```

This will run the following tasks:

1. Run `yarn prod` locally to compile assets
2. Run `git clone` remotely to install the project on the server
3. Run `composer install` to install Composer dependencies
4. Move the `storage` directory to the configurated location
5. Copy a local `.env` file to the remote `.env`
6. Create a symlink for the `storage` directory
7. Create a symlink for the `.env` file
8. Run `article migrate` remotely if specified in the config file
9. Run `scp` locally to copy the compiled assets to the server
10. Create a symlink to the new release for the web server.

Many of the steps here are similar to a standard deployment except that the `storage` and `.env` items are placed in their proper locations.

### Custom .env
One of the important steps taken during install is to place a `.env` file in the right location. By default Attaché will use the content of the `.env.example` file at the root of your project. However, this file is usually commited to your repo so it should never contain any actual data.

Instead, it's recommended that you create a new `.env.install` file with the correct values for the application to run on the server and then use the `--env` option to pass that file to the install command:

```
attache install production --env=.env.install
```

If you are planning to run migrations, then you will need to update the database connection details in this file, and it should never be committed to your repository.

## Deploying
Once your first install is complete, any subsequent deployment can be done using the `attache deploy` command. The `deploy` command is similar to the `install` command, but does not overwrite the `storage` and `.env` items. The following tasks are run for each deployment. Each task is made up of 1 or more steps:

#### Build Task
1. Run `yarn prod` locally to compile assets

#### Deploy Task
2. Run `git clone` remotely to install the project on the server
3. Run `composer install` to install Composer dependencies
4. Create a symlink for the `storage` directory
5. Create a symlink for the `.env` file
6. Run `article migrate` remotely if specified in the config file

#### Assets Task
7. Run `scp` locally to copy the compiled assets to the server

#### Live Task
8. Create a symlink to the new release for the web server.

### Pruning releases after deployment

By default the `deploy` command will leave any previous release intact. However, if you would like to remove old release during deployment, you can pass the `--prune` option. This will delete all old releases leaving only the new release and the previous one. This means you can still rollback to the previous release if needed.

```
attache deploy production --prune
```

## Server scripts
Attaché provides a simple solution to extending the deployment tasks called "scripts". A script can be anything you run on the command line. So for example, Attaché simply runs `yarn prod` as a build script. But if you need to run some additional tasks before or after, you can add a script that will simply be added to the deployment script and run at the correct time.

There are quite a few script hooks you can use, and each one has a `before` and `after` variant. Scripts are also per server which means you can add them to your `common` config, or have different scripts for different servers. Each script MUST return an array of commands.

```json
{
    "servers": [
        [
            "name": "production",
            "root": "/path/to/application",
            "scripts": {
                "before-build": [
                    "some-script-to-run-before-building-1",
                    "some-script-to-run-before-building-2"
                ],
                "after-composer": [
                    "some-script-to-run-after-composer-install"
                ]
            }
        ]
    ]
}
```

The following task script hooks can be used (remember that there are `before` and `after` variants of all the script hooks):

| script | description                                    |
|--------|------------------------------------------------|
| build  | Run before or after the **build** task         |
| deploy | Run before or after the entire **deploy** task |
| assets | Run before or after the **assets** task        |
| live   | Run before or after the **live** task          |

The `deploy` task has quite a number of steps and each of those steps has their own script hooks as well.

| script        | description                                                       |
|---------------|-------------------------------------------------------------------|
| clone         | Run before or after the project is cloned to the server           |
| prep-composer | Run before or after the composer installation or self-update      |
| composer      | Run before or after the `composer install` command                |
| install       | Run before or after the installation steps                        |
| symlinks      | Run before or after the `storage` and `.env` symlinks are created |
| migrate       | Run before or after the database is migrated                      |

It's important to note that some scripts won't run unless they're included in the deployment script. For exmaple, the `install` scripts are ONLY run when using the `install` command. Similarly, the `migrate` scripts will only run if the `migrate` setting is set to `true` in the config.

> An example of how we use the `after-migrate` script is to run a `mysqldump` command before migrating the database. Or you could use the `after-deploy` script to run a `artisan config:cache`. Attaché server scripts give you quite a lot of power.

## Managing releases
Attache keeps releases on the server until they are prunned. Over time, these releases can add up and end up using a fair amount of space. If you are not prunning releases during deployment, then you'll need to manually manage the releases yourself. Attache provides a few useful tools to help do that.

### Listing releases
You can see a list of releases that are currently on the server using the `releases:list` command:

```
attache releases:list production
```

This command will print a table of releases along with their release dates and the currently active release.

```
---------------- --------------------- ------------
 ID               Release Date
---------------- --------------------- ------------
 20200312173308   12 March 2020 17:33
 20200313211155   13 March 2020 21:11   <-- active
---------------- --------------------- ------------
```

### Rollback

If you ever find yourself in a position where you need to rollback a deployment to the previous release, you can use the `releases:rollback` command.

```
attache releases:rollback production
```

This will always rollback to the release before the current active one. You can use the command more than one if you have a few releases. Each time you do, the release prior to the currently active one will be activated.

```
---------------- --------------------- ------------
ID               Release Date
---------------- --------------------- ------------
20200311114830   11 March 2020 11:48
20200312173308   12 March 2020 17:33   <-- active
20200313211155   13 March 2020 21:11
---------------- --------------------- ------------
```

### Activating a specific release

Instead of rolling back through a lot of releases to activate the one you want, you can activate a specific release using the `releases:activate` command and pass the ID of the release you want:

```
attache releases:activate production 20200311114830
```

To get back to the most recent release, instead of providing the ID, you can use the `latest` keyword:

```
attache releases:activate production latest
```

That will always activate the most recent release.

### Cleaning up

If you have a long list of releases that need to be prunned, you can use the `releases:prune` command. By default, the `prune` command will remove all the old releases except the current active release and the one before it. However, if have 10 releases and only want to remove 4 of them, you can pass the `--count` option to limit the number of releases to prune:

```
attache releases:prune production --count=4
```

## Taking servers offline
Attaché is intended to be a zero-downtime deployment tool. But sometimes you may need to take the deployment offline. You can do with easily with:

```
attache releases:down production
```

This will run the `artsan down` command on the currently active release.

When you're done, you can bring the release back online using the `releases:up` command:

```
attache releases:up production
```

## Opening an SSH connection
As a convenience, Attaché also includes an `ssh` command that you can use to easy open an SSH connectio to the specified server:

```
attache ssh production
```
