---
slug: integrating-the-settings-api
title: "Integrating the Settings API"
sections: [01-wzsa-getting-started]
tags: [settings-api, integration, developer]
status: publish
order: 2
---

[kbtoc]

The library is copied into your plugin rather than installed. This page walks through what to copy, what to rename, and how to wire it into WordPress.

## 1. Copy the files

Bring the following into your plugin, keeping the relative layout:

- The whole `settings/` directory (classes, `css/`, `js/`, `sidebar.php`).
- `util/class-hook-registry.php`.
- `class-options-api.php`.
- `class-settings.php` and, if you need post metaboxes, `class-metabox.php`.
- `sidebar.php`, plus `class-admin-banner.php` and `css/` if you want the admin banner.

A typical destination is `includes/admin/settings/` for the `settings/` directory and `includes/admin/` for the example controllers.

## 2. Rename the namespace

Replace `WebberZone\Settings_API` with your plugin's root namespace throughout. Autoload the classes either through a PSR-4 map in `composer.json` or with `require_once` calls before first use.

## 3. Update identifiers

The example files ship with the sample identifiers used by Add to All. Replace them everywhere:

| What | Sample value | Replace with |
|---|---|---|
| Hook prefix | `ata` | Your plugin prefix, e.g. `crp`, `bsearch` |
| Option key | `ata_settings` | Your option name |
| Text domain | `add-to-all` | Your text domain |
| Menu slugs | `ata_options_page` | Your menu slug |

`Options_API` holds two of these as class constants:

```php
const SETTINGS_OPTION = 'my_plugin_settings';
const FILTER_PREFIX   = 'my_plugin';
```

## 4. Define your settings

Implement `get_registered_settings()` in your copied `Settings` class, returning fields keyed by section. See [Field definition format]({{ '/docs/01-wzsa-getting-started/field-definition-format/' | relative_url }}).

## 5. Instantiate

```php
add_action(
    'admin_menu',
    function () {
        $settings = new \My_Plugin\Admin\Settings();
        $settings->initialise_settings();
    }
);
```

Inside `initialise_settings()`, the controller constructs `Settings_API` with your option key, prefix, and arrays:

```php
$settings_api = new Settings_API(
    'my_plugin_settings',
    'my_plugin',
    array(
        'translation_strings' => $this->get_translation_strings(),
        'settings_sections'   => self::get_settings_sections(),
        'registered_settings' => self::get_registered_settings(),
        'upgraded_settings'   => array(),
        'props'               => array(
            'menus'             => $this->get_menus(),
            'default_tab'       => 'general',
            'admin_footer_text' => $this->get_admin_footer_text(),
            'help_sidebar'      => $this->get_help_sidebar(),
            'help_tabs'         => $this->get_help_tabs(),
            'version'           => MY_PLUGIN_VERSION,
        ),
    )
);
```

Pass your **plugin's own version** as `props['version']`. It is used to cache-bust the enqueued CSS and JS, so every plugin release refreshes browser caches.

## 6. Maintain the defaults array

`Settings::get_defaults()` is the single source of truth for defaults and must stay in sync with the field definitions. The rules are strict and none of them are caught by phpcs or phpstan — read [The defaults contract]({{ '/docs/02-wzsa-core-classes/the-defaults-contract/' | relative_url }}) before you ship.

## 7. Expose a getter

Give your plugin a procedural helper wrapping `Options_API`, so the rest of the codebase never touches the option directly:

```php
function my_plugin_get_option( $key = '', $default_value = null ) {
    return \My_Plugin\Options_API::get_option( $key, $default_value );
}
```

## Keeping the copy in sync

Because the library is copy-pasted, an upstream fix has to be propagated by hand. The `Settings_API` repository is the canonical source for `settings/*.php` and `class-admin-banner.php`. Shared PHP is never byte-identical — the namespace and the per-plugin `@since` tags differ — but the CSS and JS should be.
