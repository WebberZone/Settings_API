---
slug: options-api-reference
title: "Options_API reference"
sections: [02-wzsa-core-classes]
tags: [settings-api, options, multisite, developer]
status: publish
order: 4
---

[kbtoc]

`Options_API` (`class-options-api.php`) is the settings read/write layer a plugin exposes to its own code, and the counterpart to a procedural `*_get_option()` helper. Unlike the example controller, this file is meant to be copied close to verbatim — change only the namespace and the two constants:

```php
const SETTINGS_OPTION = 'my_plugin_settings';
const FILTER_PREFIX   = 'my_plugin';
```

## Methods

| Method | Returns | Notes |
|---|---|---|
| `get_settings()` | array | The raw saved option, cached per request and filtered through `{$prefix}_get_settings`. |
| `get_settings_with_defaults()` | array | Saved values merged over the full defaults, so newly registered fields still resolve. |
| `get_option( $key, $default_value = null )` | mixed | The workhorse. A `null` default falls back to `get_default_option( $key )`. |
| `get_blog_option( $blog_id, $key, $default_value = false )` | mixed | Reads another site's value on multisite, switching blogs only when needed. |
| `update_option( $key, $value )` | bool | Writes one key, filtered through `{$prefix}_update_option`. |
| `update_settings( array $settings, bool $merge = true, bool $autoload = true )` | bool | Writes many keys at once; `$merge` false replaces the option outright. |
| `delete_option( $key )` | bool | Removes one key from the option. |
| `get_settings_defaults()` | array | Built from the field definitions — only safe **after** `init`. |
| `get_default_option( $key )` | mixed | One default from the flat `get_defaults()` array; safe before `init`. Returns `false` for unregistered keys. |
| `get_registered_settings_types()` | array | `field_id => type` for every registered field, filtered through `{$prefix}_get_settings_types`. |
| `reset_settings()` | bool | Overwrites the option with the defaults. |
| `flush_cache( $blog_id = null )` | void | Clears the per-request cache for one blog, or all of them. |

## The cache is keyed by blog ID

The per-request cache is **not** a single static array; it is keyed by blog ID. An unkeyed cache returns the wrong site's settings after a `switch_to_blog()` in the same request — which is exactly what `get_blog_option()` does internally, and what network admin screens do in a loop. On single site the key is always `0`.

## Two default paths, on purpose

`get_settings_defaults()` calls `Settings::settings_defaults()`, which walks the full field definitions. Those definitions call `esc_html__()` on every label, so running them before `init` triggers WordPress's *translation loading triggered too early* notice.

`get_default_option()` therefore reads `Settings::get_defaults()` instead — a flat array with no translation calls — making a single-key read safe at any point in the request. The [defaults contract]({{ '/docs/02-wzsa-core-classes/the-defaults-contract/' | relative_url }}) is what keeps the two in agreement.

## Filters

| Filter | Fires in | Arguments |
|---|---|---|
| `{$prefix}_get_settings` | `get_settings()` | `$settings` |
| `{$prefix}_get_option` | `get_option()` | `$value`, `$key`, `$default_value` |
| `{$prefix}_get_option_{$key}` | `get_option()` | `$value`, `$key`, `$default_value` |
| `{$prefix}_blog_option_{$key}` | `get_blog_option()` | `$value`, `$blog_id`, `$key` |
| `{$prefix}_update_option` | `update_option()` | `$value`, `$key` |
| `{$prefix}_settings_defaults` | `get_default_option()` | `$defaults` |
| `{$prefix}_get_settings_types` | `get_registered_settings_types()` | `$options` |

Here `{$prefix}` is the `FILTER_PREFIX` constant, which should match the `$prefix` passed to `Settings_API`.

## Expose a procedural helper

Most WebberZone plugins wrap the class so template code and third-party integrations have a stable function to call:

```php
function my_plugin_get_option( $key = '', $default_value = null ) {
    return \My_Plugin\Options_API::get_option( $key, $default_value );
}
```

Passing an explicit second argument short-circuits the default lookup entirely — which is how a default that must be translated or computed at runtime is handled:

```php
$title = my_plugin_get_option( 'toc_title', __( 'Table of Contents', 'my-plugin' ) );
```
