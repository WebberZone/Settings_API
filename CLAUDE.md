# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this library is

WebberZone Settings API is a reusable PHP library that wraps the native WordPress Settings API. It powers the admin interfaces across WebberZone plugins (Better Search, Contextual Related Posts, Knowledge Base, etc.). It is not distributed as a Composer package — consuming plugins copy the files directly into their own source tree and adjust the namespace, prefix, and option key to match the plugin.

There is no `composer.json`, `package.json`, or build system in this repository. CSS and JS assets are committed as both source and pre-minified files. There are no automated tests.

## Repository structure

```
Settings_API/
├── class-settings.php           # Example settings-controller (copy and customise per plugin)
├── class-metabox.php            # Example post-metabox integration (copy and customise)
├── sidebar.php                  # Sidebar partial shown on settings pages
├── util/
│   └── class-hook-registry.php  # Deduplication wrapper around add_action / add_filter
└── settings/
    ├── class-settings-api.php        # Core orchestrator — menus, sections, fields, encryption
    ├── class-settings-form.php       # Field-renderer callbacks (one method per field type)
    ├── class-settings-sanitize.php   # Sanitization callbacks matched by field type
    ├── class-settings-wizard-api.php # Optional multi-step setup wizard
    ├── class-metabox-api.php         # Post-metabox helper reusing the same field definitions
    ├── sidebar.php                   # Inner sidebar partial
    ├── css/                          # admin-style, wizard, tom-select (+ RTL + .min variants)
    └── js/                           # settings-admin-scripts, apply-cm, media-selector,
                                      # tom-select-init, tom-select.complete (+ .min variants)
```

## Namespaces

| File | Namespace |
|---|---|
| `settings/*.php` | `WebberZone\Settings_API\Admin\Settings` |
| `class-settings.php`, `class-metabox.php` | `WebberZone\Settings_API\Admin` |
| `util/class-hook-registry.php` | `WebberZone\Settings_API\Util` |

When copying the library into a plugin, rename the root namespace segment (`WebberZone\Settings_API`) to match the plugin's own namespace.

## Key classes and responsibilities

### `Settings_API` (`settings/class-settings-api.php`) — version 2.8.2
The main entry point. Constructed with a `$settings_key` (WordPress option name), a `$prefix` (used to namespace all hooks and JS handles), and an `$args` array containing sections, registered settings, and presentation props.

Responsibilities:
- Registers admin menus (submenu, top-level, or any `add_*_page` variant) via `add_custom_menu_page()`.
- Calls `register_setting()` and `add_settings_field()` for every declared field.
- Renders the tabbed settings page (`show_navigation()` + `show_form()`) with Save and Reset buttons.
- Auto-initialises defaults in `wp_options` on first load.
- Enqueues all required scripts/styles (jQuery UI Tabs, wp-color-picker, CodeMirror, Tom Select, media uploader).
- Handles save/reset via `settings_sanitize()`, which delegates per-field sanitization to `Settings_Sanitize`.
- Provides static helpers `encrypt_api_key()` / `decrypt_api_key()` (OpenSSL then libsodium, falling back to plaintext) for `sensitive` field types.
- Exposes contextual help tabs and a sidebar partial.

### `Settings_Form` (`settings/class-settings-form.php`)
Holds one `callback_*` method for each field type. `Settings_API::admin_init()` wires each registered field to the appropriate callback. Field types supported:

`text`, `url`, `csv`, `color`, `numbercsv`, `postids`, `textarea`, `css`, `html`, `checkbox`, `multicheck`, `radio`, `radiodesc`, `thumbsizes`, `number`, `select`, `posttypes`, `taxonomies`, `wysiwyg`, `file`, `password`, `repeater`, `sensitive`, `header`, `descriptive_text`

The `repeater` type renders an accordion-style list of sub-fields with add/remove/reorder controls and a live-title update, all driven by inline jQuery.

Every callback applies the `{$prefix}_after_setting_output` filter before echoing.

### `Settings_Sanitize` (`settings/class-settings-sanitize.php`)
Provides one `sanitize_*_field()` method per field type. `Settings_API::get_sanitize_callback()` looks up the right method by field type at save time. Handles text, number, CSV, checkbox, multicheck, posttypes, taxonomies, color, email, URL, sensitive (encrypts via `Settings_API::encrypt_api_key()`), and repeater (recursively sanitizes sub-fields).

### `Settings_Wizard_API` (`settings/class-settings-wizard-api.php`)
Optional multi-step guided setup wizard. Constructed with the same `$settings_key` / `$prefix` pattern plus a `$steps` array. Registers its own admin page, renders step navigation, and saves each step's fields directly into the plugin's options via `Settings_Sanitize`. Shares the same CSS asset (`wizard.css`).

### `Metabox_API` (`settings/class-metabox-api.php`)
Renders a standard WordPress post metabox using the same field-definition array format as `Settings_API`. Each field value is stored as individual post meta with the key `_{$prefix}_{$field_id}`. Handles nonce verification and capability checks on save.

### `Hook_Registry` (`util/class-hook-registry.php`)
Static registry that wraps `add_action` / `add_filter` and prevents duplicate registrations. Used by the example `Settings` and `Metabox` classes. Provides `add_action()`, `add_filter()`, `remove_action()`, `remove_filter()`, `remove_all_hooks()`.

### `Settings` (`class-settings.php`) and `Metabox` (`class-metabox.php`)
These are **example/reference implementations**, not part of the library core. Each consuming plugin copies one or both, renames the class, and fills in `get_registered_settings()`, menu slugs, prefix, and option key.

## How consuming plugins integrate the library

1. Copy the `settings/` directory, `util/class-hook-registry.php`, and the example controller file(s) into the plugin.
2. Update the namespace, `$prefix` (e.g. `crp`, `bsearch`), `$settings_key`, text domain, and menu slugs.
3. Implement `get_registered_settings()` returning the field-definition array, and `initialise_settings()` to instantiate `Settings_API`.
4. Hook instantiation to `admin_menu` or `plugins_loaded`:

```php
add_action( 'admin_menu', function() {
    $settings = new \MyPlugin\Admin\Settings();
    $settings->initialise_settings();
} );
```

5. The library fires dynamic filters the plugin can use:
   - `{$prefix}_settings_defaults` — override default values
   - `{$prefix}_settings_{$tab}_sanitize` — intercept input before field-level sanitization
   - `{$prefix}_settings_sanitize` — filter the final saved array
   - `{$prefix}_after_setting_output` — modify rendered field HTML
   - `{$prefix}_non_setting_types` — declare additional display-only field types

## Field definition format

Each entry in `get_registered_settings()` is keyed by section (tab) ID, containing an array of field arrays:

```php
'general' => array(
    'my_field' => array(
        'id'      => 'my_field',
        'name'    => __( 'Label', 'textdomain' ),
        'desc'    => __( 'Description', 'textdomain' ),
        'type'    => 'text',         // see field types above
        'default' => '',
        // optional: 'options', 'min', 'max', 'step', 'size', 'field_class',
        //           'field_attributes', 'placeholder', 'readonly', 'required',
        //           'disabled', 'pro', 'sanitize_callback'
    ),
),
```

## Assets

Scripts and styles are registered (not enqueued) in `admin_enqueue_scripts` and enqueued only on the plugin's own settings page via `Settings_API::enqueue_scripts_styles()`. The JS handle pattern is `wz-{$prefix}-admin`, `wz-{$prefix}-codemirror`, etc. `SCRIPT_DEBUG` controls whether `.min` variants are loaded.
