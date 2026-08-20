---
slug: sanitization-reference
title: "Sanitization reference"
sections: [02-wzsa-core-classes]
tags: [settings-api, sanitization, security, developer]
status: publish
order: 3
---

[kbtoc]

`Settings_Sanitize` (`settings/class-settings-sanitize.php`) provides one `sanitize_*_field()` method per field type. At save time `Settings_API::get_sanitize_callback()` looks up the method matching the field's `type`; a field can override it with its own `sanitize_callback`.

## How a save runs

1. `settings_sanitize()` reads the current option and merges the submitted input over it, so untouched tabs keep their values.
2. The tab-specific filter `{$prefix}_settings_{$tab}_sanitize` runs on the raw input.
3. Every registered field is walked. Types listed in `{$prefix}_non_setting_types` (`header`, `descriptive_text` by default) are skipped.
4. Submitted fields go through their sanitizer. Fields absent from the submission are deleted — **unless** they are locked (`disabled` or `pro`), because a disabled input is never posted.
5. `{$prefix}_settings_sanitize` filters the final array before it is written.

## Callbacks by type

| Method | Applies to | Behaviour |
|---|---|---|
| `sanitize_text_field()` | `text`, and most text-like types | Delegates to `sanitize_textarea_field()`. |
| `sanitize_textarea_field()` | `textarea`, `css`, `html`, `wysiwyg` | `wp_kses()` against `$allowedposttags` extended with `script`, `style`, and `link` tags, filterable via `{$prefix}_sanitize_allowed_tags`. |
| `sanitize_number_field()` | `number` | `filter_var()` with `FILTER_SANITIZE_NUMBER_INT`. |
| `sanitize_csv_field()` | `csv` | Sanitizes the string, then trims each comma-separated item. |
| `sanitize_numbercsv_field()` | `numbercsv` | Casts each item with `absint()` and drops empties. |
| `sanitize_postids_field()` | `postids` | Casts each item with `absint()` and drops IDs that no longer resolve to a post. |
| `sanitize_checkbox_field()` | `checkbox`, `toggle` | Returns `1` or `0`. The hidden `-1` companion input is what makes an unchecked box save as `0`. |
| `sanitize_multicheck_field()` | `multicheck` | Returns a comma-separated list of the checked keys; `-1` means nothing checked. |
| `sanitize_posttypes_field()` | `posttypes` | Delegates to `sanitize_multicheck_field()`. |
| `sanitize_taxonomies_field()` | `taxonomies` | Delegates to `sanitize_multicheck_field()`. |
| `sanitize_color_field()` | `color` | `sanitize_hex_color()`. |
| `sanitize_email_field()` | email fields | `sanitize_email()`. |
| `sanitize_url_field()` | `url` | `esc_url_raw()`. |
| `sanitize_sensitive_field()` | `sensitive` | Encrypts new input via `Settings_API::encrypt_api_key()`. An empty value clears the key; a masked value (containing `**`) leaves the stored key untouched. |
| `sanitize_repeater_field()` | `repeater` | Recursively sanitizes each row's sub-fields by their own types, preserving `row_id`. A locked repeater returns its stored rows, so forged submissions cannot write to it. |

## Types without a sanitizer

`get_sanitize_callback()` returns `false` when no `sanitize_{$type}_field()` method exists and the field declares no `sanitize_callback` of its own — `file`, `password`, `select`, `radio`, `radiodesc`, and `thumbsizes` are in that group. Those values are stored exactly as submitted, from the `array_merge()` of the saved settings with the input.

Where the value is constrained by the UI alone — a `select` or `radio` whose options you control — that is usually acceptable. Where it is free text, declare an explicit sanitizer:

```php
'logo_path' => array(
    'id'                => 'logo_path',
    'name'              => __( 'Logo', 'my-plugin' ),
    'type'              => 'file',
    'sanitize_callback' => 'esc_url_raw',
),
```

## Helper

`sanitize_tax_slugs( &$settings, $source_key, $target_key )` is a static helper for taxonomy pickers. It parses values in the `Name (taxonomy:term_taxonomy_id)` format, resolves each to a real term, and writes the term taxonomy IDs into `$target_key` while normalising the display slugs in `$source_key`. Plain category names are still accepted for backwards compatibility.

## Overriding a sanitizer

```php
'my_field' => array(
    'id'                => 'my_field',
    'name'              => __( 'My field', 'my-plugin' ),
    'type'              => 'text',
    'sanitize_callback' => 'my_plugin_sanitize_my_field',
),
```

For broader changes, filter the whole array instead:

```php
add_filter(
    'my_plugin_settings_sanitize',
    function ( $output, $input ) {
        $output['derived'] = md5( $output['source'] );
        return $output;
    },
    10,
    2
);
```
