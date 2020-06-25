# A Highly opinionated deployment tool for Laravel

[![Build Status](https://travis-ci.org/tpg/attache.svg?branch=master)](https://travis-ci.org/tpg/attache)
[![Packagist Version](https://img.shields.io/packagist/v/thepublicgood/attache)](https://packagist.org/packages/thepublicgood/attache)

Attaché is a deployment tool for Laravel originally based on the Laravel Envoy task runner and built around the ideas I wrote [here](https://medium.com/@warrickbayman/zero-downtime-laravel-deployments-with-envoy-version-2-227c8259e31c). The original version of Attaché was actually just a wrapper around Envoy with a predefind script (hence the name). It's evolved quite a bit since then.

Learn how to use Attaché: **[Official Documentation](https://tpg.github.io/attache)**

---
## Quick Start

Install Attache globally using Composer:

```
composer global require thepublicgood/attache
```

Create a new `.attache.json` configuration file in your project with:

```bash
attache init
```

Update the config file to reflect your server and repository settings making sure the specified root directory exists on the server. Then install your project onto the server with: 

```bash
attache install
```

Now update the new `.env` file on your server, cache the config, and whatever other tasks you need to complete. That's it! Deployment complete.

Whenever you need to deploy a new version, simply run:

```bash
attache deploy
```

You can see all the releases on the server with:

```bash
attache releases:list
```

And clean them up with:

```bash
attache releases:prune
```

For more things you can do, simple run `attache` without any commands to get a list.

**Happy deploying!**
