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

Since Settings API 3.0.0 every field type resolves to a callback. A type with no method of its own falls back to `sanitize_missing()`, so no value is ever stored exactly as submitted.

## How a save runs

1. `settings_sanitize()` starts from the **stored** option, so untouched tabs keep their values and no submitted value reaches the output before it is sanitized.
2. The tab-specific filter `{$prefix}_settings_{$tab}_sanitize` runs on the raw input.
3. Every registered field is walked. Types listed in `{$prefix}_non_setting_types` (`header`, `descriptive_text` by default) are skipped.
4. Submitted fields go through their sanitizer. Fields absent from the submission are deleted — **unless** they are locked (`disabled` or `pro`), because a disabled input is never posted.
5. Keys the tab filter added that are not registered settings are still run through `sanitize_missing()` rather than stored raw.
6. `{$prefix}_settings_sanitize` filters the final array before it is written.

Every callback receives the field configuration as its second argument, which is how the choice types validate against the options they actually rendered.

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
| `sanitize_file_field()` | `file` | `esc_url_raw()`. |
| `sanitize_password_field()` | `password` | `sanitize_text_field()`. |
| `sanitize_wysiwyg_field()` | `wysiwyg` | `wp_kses_post()`. |
| `sanitize_css_field()` | `css` | `wp_strip_all_tags()`. |
| `sanitize_html_field()` | `html` | Delegates to `sanitize_textarea_field()`. |
| `sanitize_select_field()` | `select` | Validates against the field's own `options`; an unknown value falls back to the field's `default`. |
| `sanitize_radio_field()` | `radio` | Same option validation as `select`. |
| `sanitize_radiodesc_field()` | `radiodesc` | Validates against the `id` of each declared option. |
| `sanitize_thumbsizes_field()` | `thumbsizes` | Validates against the declared sizes plus the `{$prefix}_thumbnail` size the form injects at render time. |
| `sanitize_missing()` | any type with no method | Deep-sanitizes arrays, passes scalars through `sanitize_text_field()`, and blanks objects and nulls. |
| `sanitize_sensitive_field()` | `sensitive` | Encrypts new input via `Settings_API::encrypt_api_key()`. An empty value clears the key; a masked value (containing `**`) leaves the stored key untouched. |
| `sanitize_repeater_field()` | `repeater` | Recursively sanitizes each row's sub-fields by their own types, preserving `row_id`. A locked repeater returns its stored rows, so forged submissions cannot write to it. |

## Choice fields fall back to their default

`sanitize_choice()` backs `select`, `radio`, `radiodesc`, and `thumbsizes`. A submitted value that is not among the field's declared options is replaced by the field's `default` — not blanked — and values are matched both directly and through `sanitize_key()`, because the select callback prints its option values that way.

## Helper

`sanitize_tax_slugs( &$settings, $source_key, $target_key )` is a static helper for taxonomy pickers. It parses values in the `Name (taxonomy:term_taxonomy_id)` format, resolves each to a real term, and writes the term taxonomy IDs into `$target_key` while normalising the display slugs in `$source_key`. Plain category names are still accepted for backwards compatibility.

## Overriding a sanitizer

A field's own `sanitize_callback` takes precedence over the callback for its type:

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
