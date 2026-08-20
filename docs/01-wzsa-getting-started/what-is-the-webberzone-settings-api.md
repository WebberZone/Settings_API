---
slug: what-is-the-webberzone-settings-api
title: "What is the WebberZone Settings API"
sections: [01-wzsa-getting-started]
tags: [settings-api, overview, developer]
status: publish
order: 1
---

[kbtoc]

The WebberZone Settings API is a reusable PHP library that wraps the [native WordPress Settings API](https://developer.wordpress.org/plugins/settings/settings-api/). It turns a nested PHP array of field definitions into a complete tabbed admin screen — menu registration, rendering, sanitization, defaults, and asset loading included.

It powers the admin interfaces of Better Search, Contextual Related Posts, Knowledge Base, Top 10, and the other WebberZone plugins.

## It is not a package

There is no Composer package, no npm package, and no build system. Consuming plugins **copy the files directly** into their own source tree and adjust the namespace, hook prefix, and option key. CSS and JS are committed as both source and pre-minified files.

That means you own the copy you ship. Nothing on your users' sites can break because an upstream dependency changed.

## Repository structure

```text
Settings_API/
├── class-options-api.php         # Canonical settings read/write layer (copy near-verbatim)
├── class-settings.php            # Example settings controller (copy and customise)
├── class-metabox.php             # Example post-metabox integration
├── class-admin.php               # Example admin bootstrap wiring the banner
├── class-admin-banner.php        # Reusable admin banner / quick-links header
├── sidebar.php                   # Sidebar partial shown on settings pages
├── css/                          # Admin banner styles (+ RTL and .min variants)
├── util/
│   └── class-hook-registry.php   # Deduplicating wrapper around add_action / add_filter
└── settings/
    ├── class-settings-api.php        # Core orchestrator — menus, sections, fields, encryption
    ├── class-settings-form.php       # Field renderers, one callback per field type
    ├── class-settings-sanitize.php   # Sanitization callbacks matched by field type
    ├── class-settings-wizard-api.php # Optional multi-step setup wizard
    ├── class-metabox-api.php         # Post-metabox helper using the same field arrays
    ├── sidebar.php                   # Inner sidebar partial
    ├── css/                          # admin-style, wizard, tom-select (+ RTL + .min)
    └── js/                           # settings-admin-scripts, apply-cm, media-selector,
                                      # tom-select-init, tom-select.complete (+ .min)
```

## Namespaces

| File | Namespace |
|---|---|
| `settings/*.php` | `WebberZone\Settings_API\Admin\Settings` |
| `class-settings.php`, `class-metabox.php`, `class-admin.php`, `class-admin-banner.php` | `WebberZone\Settings_API\Admin` |
| `class-options-api.php` | `WebberZone\Settings_API` |
| `util/class-hook-registry.php` | `WebberZone\Settings_API\Util` |

When you copy the library into a plugin, rename the root namespace segment (`WebberZone\Settings_API`) to match your plugin's own namespace.

## The moving parts

| Class | Role |
|---|---|
| `Settings_API` | The entry point. Registers menus, sections, and fields; renders the tabbed page; handles save and reset; enqueues assets; encrypts sensitive values. |
| `Settings_Form` | One `callback_*` method per field type. Renders the input HTML and applies the `{$prefix}_after_setting_output` filter. |
| `Settings_Sanitize` | One `sanitize_*_field()` method per field type, selected at save time by `Settings_API::get_sanitize_callback()`. |
| `Options_API` | The read/write layer your plugin exposes to its own code. Blog-aware caching, defaults resolution, per-key filters. |
| `Settings_Wizard_API` | Optional multi-step guided setup, reusing the same field definitions. |
| `Metabox_API` | Renders and saves a post metabox from the same field definitions. |
| `Hook_Registry` | Static registry that prevents duplicate `add_action` / `add_filter` registrations. |
| `Admin_Banner` | Renders a branded header with quick links at the top of your admin screens. |

`class-settings.php`, `class-metabox.php`, and `class-admin.php` are **example implementations**, not library core. Copy one, rename the class, and fill in your own field definitions.

## Where to next

- [Integrating the Settings API]({{ '/docs/01-wzsa-getting-started/integrating-the-settings-api/' | relative_url }}) — the step-by-step copy-and-wire guide.
- [Field definition format]({{ '/docs/01-wzsa-getting-started/field-definition-format/' | relative_url }}) — the array structure everything is built from.
