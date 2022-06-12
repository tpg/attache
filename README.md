# A Highly opinionated deployment tool for Laravel

![Run Tests](https://github.com/tpg/attache/workflows/Run%20Tests/badge.svg)
[![Packagist Version](https://img.shields.io/packagist/v/thepublicgood/attache)](https://packagist.org/packages/thepublicgood/attache)

> After going backwards and forewards on this one for a while, I've decided to archive this project. I've had a good run with Attaché, but I haven't used it for a project in a long while and it really doesn't have a huge demand. I haven't updated the project for a while and although there was an attempt to write a version 2, there are better and more robust deployment options out there. I myself have returned to using plain old Envoy as it fits really nicely into my CD pipeline.
>
> In it's place, I'm writing a simple package that includes some of the more useful tools from Attache. This works well in conjuction with something like Envoy. Attaché will no longer get any updates or changes going forward.

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
