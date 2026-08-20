---
slug: settings-api-reference
title: "Settings_API reference"
sections: [02-wzsa-core-classes]
tags: [settings-api, reference, developer]
status: publish
order: 1
---

[kbtoc]

`Settings_API` (`settings/class-settings-api.php`) is the orchestrator. It registers menus, sections, and fields, renders the tabbed page, saves and resets, and enqueues the admin assets.

## Constructor

```php
new Settings_API( $settings_key, $prefix, $args );
```

| Parameter | Type | Purpose |
|---|---|---|
| `$settings_key` | string | The WordPress option name every field is saved into. |
| `$prefix` | string | Namespaces all dynamic hooks and script handles. |
| `$args` | array | `translation_strings`, `settings_sections`, `registered_settings`, `upgraded_settings`, `props`. |

The constructor defines `WZ_SETTINGS_API_VERSION` if it is not already defined, so multiple plugins bundling the library share one constant.

## Props

Passed as `$args['props']` and applied by `set_props()`:

| Prop | Default | Purpose |
|---|---|---|
| `menus` | `array()` | Menu pages to register. See below. |
| `default_tab` | `general` | Tab shown when the page loads. |
| `admin_footer_text` | `''` | Replaces the admin footer text on your settings page. |
| `help_sidebar` | `''` | HTML for the contextual help sidebar. |
| `help_tabs` | `array()` | Contextual help tabs. |
| `version` | library version | Cache-busting version for enqueued assets — pass your plugin's version. |

## Menu registration

`add_custom_menu_page()` accepts one array per menu entry:

```php
array(
    'type'        => 'submenu', // submenu, management, options, theme, plugins,
                                // users, dashboard, posts, media, links, pages, comments
    'parent_slug' => 'options-general.php',
    'page_title'  => __( 'My Plugin Settings', 'my-plugin' ),
    'menu_title'  => __( 'My Plugin', 'my-plugin' ),
    'capability'  => 'manage_options',
    'menu_slug'   => 'my_plugin_options_page',
    'icon_url'    => 'dashicons-admin-generic', // top-level menus only
    'position'    => null,
)
```

`get_capability_for_menu()` resolves the capability from a set of roles, falling back to `manage_options`, so a plugin can open its settings screen to editors or a custom role without hard-coding a capability.

## Selected methods

| Method | Purpose |
|---|---|
| `admin_init()` | Calls `register_setting()` and `add_settings_field()` for every declared field, wiring each to the matching `Settings_Form::callback_*` method. |
| `settings_defaults()` | Builds the default value array from the field definitions. |
| `get_default_option( $key )` | Returns a single default. |
| `settings_sanitize( $input )` | Runs on save; delegates per-field sanitization to `Settings_Sanitize`. |
| `settings_reset()` | Restores defaults. |
| `get_registered_settings_types()` | Returns `field_id => type` for every registered field. |
| `get_locked_settings()` | Returns the IDs of fields marked `disabled` or `pro`, so their saved values are preserved on submit. |
| `show_navigation()` / `show_form()` | Render the tab strip and the active tab's form. |
| `parse_field_args( $field, $section )` | Fills in field defaults; also used by the wizard and metabox APIs. |
| `enqueue_scripts_styles( $prefix, $args )` | Static. Enqueues the shared admin assets for a given prefix. |

## Encryption helpers

Fields of type `sensitive` are encrypted at rest:

```php
$encrypted = Settings_API::encrypt_api_key( $plain, $prefix );
$plain     = Settings_API::decrypt_api_key( $encrypted, $prefix );
```

`get_encryption_key()` uses `AUTH_SALT`, falling back to `SECURE_AUTH_SALT`, and finally to a hash derived from the namespace and prefix. Encryption uses OpenSSL where available, then libsodium, and stores plaintext only if neither extension exists.

## Assets

Scripts and styles are **registered** on `admin_enqueue_scripts` and **enqueued** only on the plugin's own settings page. Handles follow the pattern `wz-{$prefix}-admin`, `wz-{$prefix}-codemirror`, and so on. `SCRIPT_DEBUG` decides whether the `.min` variants load. jQuery UI Tabs, `wp-color-picker`, CodeMirror, Tom Select, and the media uploader are pulled in as required by the field types in use.
