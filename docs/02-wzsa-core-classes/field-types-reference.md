---
slug: field-types-reference
title: "Field types reference"
sections: [02-wzsa-core-classes]
tags: [settings-api, fields, reference, developer]
status: publish
order: 2
---

[kbtoc]

`Settings_Form` (`settings/class-settings-form.php`) holds one `callback_*` method per field type. `Settings_API::admin_init()` wires each registered field to the matching callback, and every callback runs its HTML through the `{$prefix}_after_setting_output` filter before echoing.

An unrecognized `type` falls through to `callback_missing()`, which prints a notice naming the field — useful when a typo silently drops a setting. On save, the same unknown type falls back to `Settings_Sanitize::sanitize_missing()`, so nothing is stored raw.

## Text and text-like

| Type | Renders | Notes |
|---|---|---|
| `text` | Single-line input | Honours `size`, `placeholder`, `readonly`, `required`, `disabled`. |
| `url` | Single-line input | Sanitized with `esc_url_raw`. |
| `csv` | Single-line input | A comma-separated list of strings; each item is trimmed. |
| `numbercsv` | Single-line input | A comma-separated list of numbers. |
| `postids` | Single-line input | A comma-separated list of post IDs. Renders as `text`. |
| `password` | Password input | Sanitized with `sanitize_text_field()`. Use `sensitive` for API keys. |
| `sensitive` | Password-style input | Value is encrypted at rest and masked in the UI, showing only the last four characters. |
| `number` | Number input | Uses `min`, `max`, and `step`. |
| `color` | Color picker | Adds the `color-field` class and enqueues `wp-color-picker`. |
| `file` | Input plus a **Choose File** button | Opens the media uploader. Override the button label with `'options' => array( 'button_label' => … )`. Sanitized with `esc_url_raw()`. |

## Multi-line

| Type | Renders | Notes |
|---|---|---|
| `textarea` | Plain textarea | `large-text` by default. |
| `css` | Textarea | Renders as a plain textarea. Add `'field_class' => 'codemirror_css'` to attach the CodeMirror editor. |
| `html` | Textarea | Renders as a plain textarea. Add `'field_class' => 'codemirror_html'` (or `codemirror_js`) to attach the CodeMirror editor. |
| `wysiwyg` | `wp_editor()` | A teeny `wp_editor()` instance. `size` sets the wrapper's max width (default `500px`); extra `wp_editor()` settings can be passed through `options`. |

## Choices

| Type | Renders | Notes |
|---|---|---|
| `checkbox` | A toggle switch | Saves `1` or `0`. Add the `no-toggle` field class to render a plain checkbox instead. |
| `toggle` | Same as `checkbox` | An explicit alias, for readability in field definitions. |
| `multicheck` | A list of checkboxes | `options` is `value => label`. Saved as a comma-separated list of the checked keys. |
| `radio` | A list of radio buttons | `options` is `value => label`; validated against that list on save. |
| `radiodesc` | Radio buttons with descriptions | `options` is a list of arrays with `id`, `name`, and `description`; the `id` values are what save validates against. |
| `select` | Dropdown | `options` is `value => label`; a value outside that list falls back to the field's `default` on save. Setting any `chosen` key adds the legacy `chosen` class. For a searchable Tom Select control, add `'field_class' => 'ts_autocomplete'`. |
| `posttypes` | Checkbox list of public post types | Saved as a comma-separated list of post-type slugs. |
| `taxonomies` | Checkbox list of public taxonomies | Saved as a comma-separated list of taxonomy slugs. |
| `thumbsizes` | Radio list of registered image sizes | Each option shows its dimensions and crop flag. |

## Structural

| Type | Renders | Notes |
|---|---|---|
| `header` | A section heading row | Holds no value; excluded from defaults and sanitization. |
| `descriptive_text` | A block of text | Holds no value; use it for inline explanations. |

## Repeater

`repeater` renders an accordion of repeatable rows with add, remove, and reorder controls, driven by inline jQuery. Declare the sub-fields with `fields`:

```php
'my_rows' => array(
    'id'                => 'my_rows',
    'name'              => __( 'Custom rows', 'my-plugin' ),
    'type'              => 'repeater',
    'live_update_field' => 'label',
    'new_item_text'     => __( 'New row', 'my-plugin' ),
    'fields'            => array(
        array(
            'id'   => 'label',
            'name' => __( 'Label', 'my-plugin' ),
            'type' => 'text',
        ),
        array(
            'id'      => 'count',
            'name'    => __( 'Count', 'my-plugin' ),
            'type'    => 'number',
            'default' => 5,
        ),
    ),
),
```

| Argument | Purpose |
|---|---|
| `fields` | The sub-field definitions. Sub-fields do **not** inherit the parent's arguments, except that a disabled parent disables them all. |
| `live_update_field` | Which sub-field's value becomes the collapsed row title. Defaults to `name`. |
| `live_update_field_options` | Maps raw values to display labels for that title. |
| `new_item_text` | Title shown on a row with no value yet. |

Each row carries a hidden `row_id` so rows stay identifiable across saves and reordering. Sub-field HTML can be filtered with `{$prefix}_repeater_field_{$type}`.

## Shared behavior

- `field_class` adds CSS classes; each class is passed through `sanitize_html_class()`.
- `field_attributes` renders arbitrary HTML attributes as `attribute => value`.
- Every field type has a sanitizer as of Settings API 3.0.0; see the [sanitization reference]({{ '/docs/02-wzsa-core-classes/sanitization-reference/' | relative_url }}).
- Setting `pro => true` disables the input the same way `disabled => true` does, and both are returned by `get_locked_settings()` so that the saved value survives a submit in which the input was never posted.
