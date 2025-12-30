This is a WordPress plugin. [Official download available on WordPress.org](https://wordpress.org/plugins/attachments/).

# Attachments

Attachments allows you to simply append any number of items from your WordPress Media Library to Posts, Pages, and Custom Post Types

## Philosophy

Attachments is a code-focused plugin; there's no configuration UI out of the box. This was an intentional move based on personal preference that offers a number of other benefits, primarily the ability to version control your configuration without having to worry about the database aspect.

Attachments is based on the concept of Instances. An Attachments Instance can be thought of as a meta box on an edit screen. Each Instance can have various attributes that control the file types allowed, the fields for each asset, and the number of assets you're allowed to attach for example. Understanding this is **fundamental** to understanding the configuration and usage as the name is used both to define each Instance *and* retrieve it's data. Once you've got your mind around that, implementation should be a breeze.

## Documentation

There's quite a bit of documentation available. I would suggest checking out the [Table of Contents](docs/TOC.md) first. The primary segments of documentation are:

* [Overview](docs/overview.md)
* [Installation](docs/installation.md)
* **[Upgrade Notice](docs/upgrade.md)** *Pay specific attention if upgrading from a version of Attachments before 3.0*
* [Usage](docs/usage.md)
* [Hooks](docs/hooks.md)
* [Screenshots](docs/screenshots.md)
* [Frequently Asked Questions](docs/faq.md)
* [Changelog](docs/changelog.md)
* [Roadmap](docs/roadmap.md)

## Main Screenshot

This is an Attachments Instance on a Post edit screen. The fields are fully customizable and you can have as many Instances as you'd like, anywhere.

![Attachments on an edit screen](https://jonchristopher.us/images/attachments/main.png)

[View other Screenshots](docs/screenshots.md)
