# WebberZone Settings API

A reusable, namespaced wrapper around the native [WordPress Settings API](https://developer.wordpress.org/plugins/settings/settings-api/) that powers the admin interfaces across WebberZone plugins. Declare your fields once as a PHP array and get a tabbed settings page, sanitization, defaults, metaboxes, and an optional setup wizard.

Full documentation: <https://webberzone.github.io/Settings_API/>

This is **not** a Composer package. There is no build system and no `composer.json`. You copy the files into your plugin, rename the namespace, and own the copy you ship.

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

## Including this in your plugin

### 1. Copy the files

Bring the following into your plugin, keeping the relative layout:

| From | Typical destination |
|---|---|
| `settings/` (classes, `css/`, `js/`, `sidebar.php`) | `includes/admin/settings/` |
| `class-settings.php`, `class-metabox.php`, `sidebar.php` | `includes/admin/` |
| `class-options-api.php` | `includes/` |
| `util/class-hook-registry.php` | `includes/util/` |
| `class-admin.php`, `class-admin-banner.php`, `css/` | `includes/admin/` — only if you want the banner |

### 2. Rename the namespace

Replace `WebberZone\Settings_API` with your plugin's root namespace throughout:

| File | Namespace to rename |
|---|---|
| `settings/*.php` | `WebberZone\Settings_API\Admin\Settings` |
| `class-settings.php`, `class-metabox.php`, `class-admin.php`, `class-admin-banner.php` | `WebberZone\Settings_API\Admin` |
| `class-options-api.php` | `WebberZone\Settings_API` |
| `util/class-hook-registry.php` | `WebberZone\Settings_API\Util` |

Autoload the classes with a PSR-4 map in `composer.json`, or `require_once` them before first use.

### 3. Update the identifiers

The example files ship with the sample identifiers used by Add to All. Replace them everywhere:

| What | Sample value | Replace with |
|---|---|---|
| Hook prefix | `ata` | Your plugin prefix, e.g. `crp`, `bsearch` |
| Option key | `ata_settings` | Your option name |
| Text domain | `add-to-all` | Your text domain |
| Menu slug | `ata_options_page` | Your menu slug |

`class-options-api.php` holds two of these as constants:

```php
const SETTINGS_OPTION = 'my_plugin_settings';
const FILTER_PREFIX   = 'my_plugin';
```

### 4. Define your settings

Implement `get_registered_settings()` in your copied `Settings` class. Fields are keyed by section (tab), then by field ID:

```php
'general' => array(
    'enabled' => array(
        'id'      => 'enabled',
        'name'    => esc_html__( 'Enable the widget', 'my-plugin' ),
        'desc'    => esc_html__( 'Adds the widget below every post.', 'my-plugin' ),
        'type'    => 'checkbox',
        'default' => 1,
    ),
    'limit'   => array(
        'id'      => 'limit',
        'name'    => esc_html__( 'Number of items', 'my-plugin' ),
        'type'    => 'number',
        'min'     => 1,
        'max'     => 50,
        'size'    => 'small',
        'default' => 6,
    ),
),
```

26 field types are available — text, url, csv, numbercsv, postids, color, number, textarea, css, html, wysiwyg, checkbox, toggle, multicheck, radio, radiodesc, select, posttypes, taxonomies, thumbsizes, file, password, sensitive, repeater, header, and descriptive_text. See the [field types reference](https://webberzone.github.io/Settings_API/docs/02-wzsa-core-classes/field-types-reference/).

### 5. Instantiate the Settings API

```php
add_action(
    'admin_menu',
    function () {
        $settings = new \My_Plugin\Admin\Settings();
        $settings->initialise_settings();
    }
);
```

Inside `initialise_settings()`:

```php
$this->settings_api = new Settings\Settings_API(
    $this->settings_key,
    self::$prefix,
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

Pass your **plugin's own version** as `props['version']` — it cache-busts the enqueued CSS and JS on every release.

### 6. Keep the defaults array in sync

`Settings::get_defaults()` is the single source of truth for defaults and must mirror what `settings_defaults()` emits. It carries no translation calls, so an option read is safe before `init`. Four rules apply, and none of them are caught by phpcs or phpstan — read [the defaults contract](https://webberzone.github.io/Settings_API/docs/02-wzsa-core-classes/the-defaults-contract/) before shipping.

### 7. Expose a getter

Give the rest of your plugin one stable entry point instead of touching the option directly:

```php
function my_plugin_get_option( $key = '', $default_value = null ) {
    return \My_Plugin\Options_API::get_option( $key, $default_value );
}
```

Passing an explicit second argument short-circuits the default lookup, which is how a translated or computed default is handled:

```php
$title = my_plugin_get_option( 'toc_title', __( 'Table of Contents', 'my-plugin' ) );
```

## Optional extras

| Feature | What to add | Docs |
|---|---|---|
| Post metaboxes | `Metabox_API` with the same field arrays; values save to `_{$prefix}_{$field_id}` post meta | [Metabox API](https://webberzone.github.io/Settings_API/docs/03-wzsa-extending/metabox-api/) |
| Setup wizard | `Settings_Wizard_API` with a `steps` array | [Setup wizard](https://webberzone.github.io/Settings_API/docs/03-wzsa-extending/setup-wizard-api/) |
| Admin banner | `Admin_Banner` with quick links, hooked to `in_admin_header` | [Admin banner](https://webberzone.github.io/Settings_API/docs/03-wzsa-extending/admin-banner/) |
| Duplicate-safe hooks | `Hook_Registry::add_action()` / `add_filter()` | [Hook Registry](https://webberzone.github.io/Settings_API/docs/03-wzsa-extending/hook-registry/) |

Every hook the library fires is namespaced with your prefix — `{$prefix}_settings_sanitize`, `{$prefix}_after_setting_output`, `{$prefix}_settings_defaults`, and the rest are listed in the [hooks and filters reference](https://webberzone.github.io/Settings_API/docs/03-wzsa-extending/hooks-and-filters-reference/).

## Keeping your copy up to date

Because the library is copy-pasted, upstream fixes have to be propagated by hand. This repository is canonical for `settings/*.php` and `class-admin-banner.php`. Shared PHP is never byte-identical — namespaces and per-plugin `@since` tags differ — but the CSS and JS should be.

## Contributing & support

- Issues and pull requests: <https://github.com/WebberZone/Settings_API>
- Working implementations live across WebberZone plugins such as Better Search, Contextual Related Posts, Knowledge Base, and Top 10.

If you ship this code in your plugin, please keep the copyright headers intact and send improvements back as pull requests.
