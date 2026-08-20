---
slug: field-definition-format
title: "Field definition format"
sections: [01-wzsa-getting-started]
tags: [settings-api, fields, developer]
status: publish
order: 3
---

[kbtoc]

Everything the Settings API renders comes from two arrays: the sections (tabs) and the registered settings.

## Sections

Sections are the tabs across the top of the settings page, declared as `id => Title`:

```php
public static function get_settings_sections() {
    $sections = array(
        'general' => __( 'General', 'my-plugin' ),
        'styles'  => __( 'Styles', 'my-plugin' ),
        'feed'    => __( 'Feed', 'my-plugin' ),
    );

    return apply_filters( self::$prefix . '_settings_sections', $sections );
}
```

The `default_tab` prop decides which one opens first.

## Registered settings

`get_registered_settings()` returns an array keyed by section ID. Each section holds field arrays keyed by field ID:

```php
'general' => array(
    'my_field' => array(
        'id'      => 'my_field',
        'name'    => __( 'Label', 'my-plugin' ),
        'desc'    => __( 'Description shown under the field.', 'my-plugin' ),
        'type'    => 'text',
        'default' => '',
    ),
),
```

## Supported arguments

Every field is run through `Settings_API::parse_field_args()`, which fills in these defaults:

| Argument | Default | Purpose |
|---|---|---|
| `id` | `null` | Field ID. Also the key in the saved options array. |
| `name` | `''` | Label shown in the left column. |
| `desc` | `''` | Description rendered under the field. |
| `type` | `text` | One of the [supported field types]({{ '/docs/02-wzsa-core-classes/field-types-reference/' | relative_url }}). |
| `size` | `null` | Input size class — `small`, `regular`, `large`. |
| `options` | `''` | Choice list for `select`, `radio`, `multicheck`, `thumbsizes`, and the sub-options of `radiodesc`. |
| `default` | `''` | Default value used until the option is saved. |
| `min` | `0` | Minimum for `number`. |
| `max` | `999999` | Maximum for `number`. |
| `step` | `1` | Step for `number`. |
| `field_class` | `''` | Extra CSS classes on the input. |
| `field_attributes` | `array()` | Arbitrary HTML attributes as `attribute => value`. |
| `placeholder` | `''` | Placeholder text. |
| `readonly` | `false` | Renders the input read-only. |
| `required` | `false` | Marks the field required and appends an asterisk to the label. |
| `disabled` | `false` | Disables the input. |
| `pro` | `false` | Marks the field as a premium feature; also disables the input. |
| `section` | current section | Set automatically. |

Some field types accept extra arguments — `chosen` on `select` to enable Tom Select, `fields` / `live_update_field` / `new_item_text` on `repeater`, and `sanitize_callback` to override the type's default sanitizer.

## A worked example

```php
public static function settings_general() {
    $settings = array(
        'header_general' => array(
            'id'   => 'header_general',
            'name' => '<strong>' . esc_html__( 'General options', 'my-plugin' ) . '</strong>',
            'type' => 'header',
        ),
        'enabled'        => array(
            'id'      => 'enabled',
            'name'    => esc_html__( 'Enable the widget', 'my-plugin' ),
            'desc'    => esc_html__( 'Adds the widget below every post.', 'my-plugin' ),
            'type'    => 'checkbox',
            'default' => 1,
        ),
        'limit'          => array(
            'id'      => 'limit',
            'name'    => esc_html__( 'Number of items', 'my-plugin' ),
            'type'    => 'number',
            'min'     => 1,
            'max'     => 50,
            'size'    => 'small',
            'default' => 6,
        ),
        'post_types'     => array(
            'id'      => 'post_types',
            'name'    => esc_html__( 'Post types to include', 'my-plugin' ),
            'type'    => 'posttypes',
            'default' => 'post',
        ),
    );

    return $settings;
}
```

## Display-only types

`header` and `descriptive_text` render content but hold no value. They are declared in the `{$prefix}_non_setting_types` filter and are skipped when defaults are built and when input is sanitized. Add your own display-only types through that filter.

## Field IDs and the saved option

All fields across all tabs are saved into a **single option** — the `$settings_key` you passed to `Settings_API`. Field IDs must therefore be unique across the entire plugin, not just within a tab.
