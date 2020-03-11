# Highly opinionated deployment tool for Laravel

We use Laravel Envoy a lot. At the root of almost every major project is an `Envoy.blade.php` file. It fits really well into our workflow and does everything we need. Envoy runs deployments for everything from simple brochureware sites to complete enterprise solutions. It's flexible and easy to configure.

These days we have a fairly standard solution to deployments that works really well. I even wrote an article about it that you cna find [here](https://medium.com/@warrickbayman/zero-downtime-laravel-deployments-with-envoy-version-2-227c8259e31c).

## Why?
We like our deployment process, but there's still a few tasks we wanted to streamline or things I felt needed a nicer interface. That's where Attaché steps in. Attaché is our very own deployment tool for the Laravel applications we work on. At first, Attaché was just supposed to compliment Envoy, but the idea got away from us a little.

Attaché is highly opinionated and is designed around our needs. I want Attaché to help reduce the cognitive load around deployments so from a configuration point of view it needs to be short and very simple.

I created a proof of concept of Attaché, which was built on top of Envoy, but I'm not going to release that.
