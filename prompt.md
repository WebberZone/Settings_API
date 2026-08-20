# Set up the WebberZone Settings API in this plugin

You are integrating the WebberZone Settings API into the WordPress plugin in the current working directory.

Source of truth: <https://github.com/WebberZone/Settings_API>
Documentation: <https://webberzone.github.io/Settings_API/>

This library is **not** a Composer package. It is copied into the plugin and owned by it. There is no build step.

## Before you start

Establish these five values from the plugin (ask the user only for what you cannot determine from the code):

| Value | Example | Where it is used |
|---|---|---|
| Root namespace | `My_Plugin` | Every copied file |
| Hook prefix | `my_plugin` | All dynamic hooks and script handles |
| Option key | `my_plugin_settings` | The single option every field saves into |
| Text domain | `my-plugin` | All translation calls |
| Menu slug | `my_plugin_options_page` | The settings page URL |

## Steps

1. **Fetch the library.** Clone or download <https://github.com/WebberZone/Settings_API> to a temporary location. Do not add it as a submodule or a Composer dependency.

2. **Copy the files** into the plugin, keeping the relative layout:

   | From | Suggested destination |
   |---|---|
   | `settings/` (classes, `css/`, `js/`, `sidebar.php`) | `includes/admin/settings/` |
   | `class-settings.php`, `class-metabox.php`, `sidebar.php` | `includes/admin/` |
   | `class-options-api.php` | `includes/` |
   | `util/class-hook-registry.php` | `includes/util/` |
   | `class-admin.php`, `class-admin-banner.php`, `css/` | `includes/admin/` — only if the admin banner is wanted |

   Match the plugin's existing directory conventions where they differ from the suggestions.

3. **Rename the namespaces.** Replace the root segment `WebberZone\Settings_API` with the plugin's own namespace:

   - `settings/*.php` → `<Root>\Admin\Settings`
   - `class-settings.php`, `class-metabox.php`, `class-admin.php`, `class-admin-banner.php` → `<Root>\Admin`
   - `class-options-api.php` → `<Root>`
   - `util/class-hook-registry.php` → `<Root>\Util`

   Then autoload them — add the namespace to the plugin's PSR-4 map, or `require_once` the files before first use, whichever the plugin already does.

4. **Replace the sample identifiers.** The example files ship with Add to All's values: prefix `ata`, option key `ata_settings`, text domain `add-to-all`, menu slug `ata_options_page`. Replace every occurrence with the values established above. In `class-options-api.php` these are constants:

   ```php
   const SETTINGS_OPTION = 'my_plugin_settings';
   const FILTER_PREFIX   = 'my_plugin';
   ```

   In `class-settings.php`, initialise `$prefix` **at its declaration**, not only in the constructor — the static methods are reachable on the frontend where the class is never instantiated:

   ```php
   public static $prefix = 'my_plugin';
   ```

5. **Define the settings.** Rewrite `get_settings_sections()` and `get_registered_settings()` in the copied `Settings` class for this plugin. Fields are keyed by section (tab), then by field ID, and every field ID must be unique across the whole plugin because all tabs save into one option:

   ```php
   'general' => array(
       'enabled' => array(
           'id'      => 'enabled',
           'name'    => esc_html__( 'Enable the widget', 'my-plugin' ),
           'desc'    => esc_html__( 'Adds the widget below every post.', 'my-plugin' ),
           'type'    => 'checkbox',
           'default' => 1,
       ),
   ),
   ```

   Available types: `text`, `url`, `csv`, `numbercsv`, `postids`, `color`, `number`, `textarea`, `css`, `html`, `wysiwyg`, `checkbox`, `toggle`, `multicheck`, `radio`, `radiodesc`, `select`, `posttypes`, `taxonomies`, `thumbsizes`, `file`, `password`, `sensitive`, `repeater`, `header`, `descriptive_text`.

   If the plugin has existing settings, map each one onto a field of the matching type rather than inventing new keys, so saved values survive.

6. **Instantiate the API** on `admin_menu`:

   ```php
   add_action(
       'admin_menu',
       function () {
           $settings = new \My_Plugin\Admin\Settings();
           $settings->initialise_settings();
       }
   );
   ```

   Pass the plugin's own version constant as `props['version']` so each release cache-busts the admin CSS and JS.

7. **Fill in `get_defaults()`.** This flat array is the single source of truth for defaults and must obey four rules, none of which phpcs or phpstan can catch:

   1. No translation calls, and nothing that translates transitively.
   2. Values pre-normalised to what `settings_defaults()` emits — checkbox defaults are `1`/`0`, never `true`/`false`.
   3. An entry for every registered option, including fields with no `'default'` key (those resolve to `''`). Only `header` and `descriptive_text` are excluded.
   4. The array itself is unfiltered — `{$prefix}_settings_defaults` is applied by its two consumers.

   A default that must be translated or computed at runtime does not belong here. Store the raw base value and pass the computed value as the getter's second argument instead.

8. **Expose a getter** so the rest of the plugin never touches the option directly:

   ```php
   function my_plugin_get_option( $key = '', $default_value = null ) {
       return \My_Plugin\Options_API::get_option( $key, $default_value );
   }
   ```

9. **Verify.** Run the plugin's own checks — `composer phpcbf` then `composer test` in a WebberZone plugin — and load the settings page in wp-admin. Confirm the tabs render, saving persists, and Reset restores defaults.

## Things that are easy to get wrong

- Every field type has a sanitizer as of Settings API 3.0.0, and an unknown type falls back to `sanitize_missing()` — nothing is stored raw. Add an explicit `sanitize_callback` only when a field needs something narrower than its type's default.
- `select`, `radio`, `radiodesc`, and `thumbsizes` validate the submitted value against the field's own `options` and fall back to the field's `default`. Declare `options` and `default` accurately or valid input can be discarded.
- Use the `sensitive` type, not `password`, for API keys — it encrypts the value at rest and masks it in the UI.
- CodeMirror attaches via `'field_class' => 'codemirror_css'` / `'codemirror_html'` / `'codemirror_js'`. A `css` or `html` field without that class is a plain textarea.
- A searchable dropdown needs `'field_class' => 'ts_autocomplete'` (Tom Select). The `chosen` argument only adds a legacy class.
- Do not modify the copied `settings/*.php` files to suit one plugin. They are shared verbatim across WebberZone plugins apart from the namespace, and local edits are lost on the next sync.

## Reference

- Field types: <https://webberzone.github.io/Settings_API/docs/02-wzsa-core-classes/field-types-reference/>
- Options API: <https://webberzone.github.io/Settings_API/docs/02-wzsa-core-classes/options-api-reference/>
- Defaults contract: <https://webberzone.github.io/Settings_API/docs/02-wzsa-core-classes/the-defaults-contract/>
- Hooks and filters: <https://webberzone.github.io/Settings_API/docs/03-wzsa-extending/hooks-and-filters-reference/>
- Setup wizard, metabox, admin banner, hook registry: <https://webberzone.github.io/Settings_API/docs/>
