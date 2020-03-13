# A Highly opinionated deployment tool for Laravel

[![Build Status](https://travis-ci.org/tpg/attache.svg?branch=master)](https://travis-ci.org/tpg/attache)

Attaché is a deployment tool for Laravel originally based on the Laravel Envoy task runner and built around the ideas I wrote [here](https://medium.com/@warrickbayman/zero-downtime-laravel-deployments-with-envoy-version-2-227c8259e31c). The original version of Attaché was aactually just a wrapper around Envoy with a predefind script (hence the name).

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

You will also need to make some changes to the webserver you're using. Again, it doesn't matter which server you're running as long as you can set it up to serve a symbolic link.

At the root of your project, install a new Attaché config file by using typing the following:

```
attache init
```

This will create a new `.attache.json` file in your project. Attaché can usually figure out the URL of your Git remote and will automatically insert it into the config file for you. The file should look something like this:

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
            "branch": "master",
            "migrate": false,
        }
    ]
}
```

There are a number of other configuration options, but this is the bare minimum. Firstly, the `repository` setting should point to the remote URL of your repository. By default the `init` command will look for a configured remote regardless of it's name. However, if you have more than one remote configured, Attaché will ask you to pick one.

```
attache init
```

You'll need to update the server config:

| Settings | Description                                            |
|----------|--------------------------------------------------------|
| name     | The key that server is referrred to as                 |
| host     | The servers hostname. Domain or IP address             |
| port     | The SSH port number (must be specified)                |
| user     | The username to log into the server with               |
| root     | The basepath where the application will be deployed to |
| branch   | The repository branch to clone from                    |
| migrate  | Perform a `migrate --force` as part of the deployment  |

## First deployment

The first deployment of an application to a server is called an `install`. Installing is similar to a standard deployment, but runs a few extra tasks. Installation can only be run once per server and Attaché will stop you from running it again as it can be a destructive process.

To run an install, you can use the `attache install` command:

```
attache install production
```

This command will run the following tasks:

1. Run `yarn prod` locally to compile assets
2. Run `git clone` remotely to install the project on the server
3. Run `composer install` to install Composer dependencies
4. Move the `storage` directory to the configurated location
4. Copy a local `.env` file to the remote `.env`
5. Create a symlink for the `storage` directory
6. Create a symlink for the `.env` file
7. Run `article migrate` remotely if specified in the config file
8. Run `scp` locally to copy the compiled assets to the server
9. Create a symlink to the new release for the web server.

Many of the steps here are similar to a standard deployment except that the `storage` and `.env` items are placed in their proper locations.

### Custom environment
One of the important steps taken during install is to place a `.env` file in the right location. By default Attaché will use the content of the `.env.example` file at the root of your project. However, this file is usually commited to your repo so it should never contain any actual data.

Instead, it's recommended that you create a new `.env.install` file with the correct values for the application to run on the server and then use the `--env` option to pass that file to the install command:

```
attache install production --env=.env.install
```

If you are planning to run migrations, then you will need to update the database connection details in this file, and it should never be committed to your repository.

## Deploying
Once your first install is complete, any subsequent deployment can be done using the `attache deploy` command. The `deploy` command is similar to the `install` command, but does not overwrite the `storage` and `.env` items.

By default the `deploy` command will leave any previous release intact. However, if you would like to remove old release during deployment, you can pass the `--prune` option. This will delete all old releases leaving only the new release and the one it replaces.

```
attache deploy production --prune
```

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
