---
slug: hooks-and-filters-reference
title: "Hooks and filters reference"
sections: [03-wzsa-extending]
tags: [settings-api, hooks, filters, reference, developer]
status: publish
order: 5
---

[kbtoc]

Every hook the library fires is namespaced with the `$prefix` you passed to `Settings_API` — shown here as `{$prefix}`. A plugin using the prefix `crp` listens on `crp_settings_sanitize`, and so on.

## Settings page

| Hook | Type | Fired in | Arguments |
|---|---|---|---|
| `{$prefix}_settings_defaults` | filter | `Settings_API::settings_defaults()` | `$options` |
| `{$prefix}_get_settings_types` | filter | `Settings_API::get_registered_settings_types()` | `$options` |
| `{$prefix}_non_setting_types` | filter | `Settings_API` | `array( 'header', 'descriptive_text' )` |
| `{$prefix}_settings_{$tab}_sanitize` | filter | `Settings_API::settings_sanitize()` | `$input` |
| `{$prefix}_settings_sanitize` | filter | `Settings_API::settings_sanitize()` | `$output`, `$input` |
| `{$prefix}_settings_form_buttons` | action | `Settings_API::show_form()` | `$tab_id`, `$tab_name`, `$settings_sections` |
| `{$prefix}_settings_page_header_before` | action | `Settings_API::plugin_settings()` | — |
| `{$prefix}_settings_page_header` | action | `Settings_API::plugin_settings()` | — |

## Field rendering

| Hook | Type | Fired in | Arguments |
|---|---|---|---|
| `{$prefix}_after_setting_output` | filter | every `Settings_Form::callback_*` | `$html`, `$args` |
| `{$prefix}_setting_field_description` | filter | `Settings_Form::get_field_description()` | `$desc`, `$args` |
| `{$prefix}_settings_form_allowed_html` | filter | `Settings_Form::get_allowed_html()` | `$allowed` |
| `{$prefix}_repeater_field_{$type}` | filter | `Settings_Form::render_repeater_item()` | `$field_args`, `$index` |

## Sanitization

| Hook | Type | Fired in | Arguments |
|---|---|---|---|
| `{$prefix}_sanitize_allowed_tags` | filter | `Settings_Sanitize::sanitize_textarea_field()` | `$allowedtags` |

## Options API

These use the `FILTER_PREFIX` constant on your copy of `Options_API`, which should match the settings prefix.

| Hook | Type | Arguments |
|---|---|---|
| `{$prefix}_get_settings` | filter | `$settings` |
| `{$prefix}_get_option` | filter | `$value`, `$key`, `$default_value` |
| `{$prefix}_get_option_{$key}` | filter | `$value`, `$key`, `$default_value` |
| `{$prefix}_blog_option_{$key}` | filter | `$value`, `$blog_id`, `$key` |
| `{$prefix}_update_option` | filter | `$value`, `$key` |
| `{$prefix}_settings_defaults` | filter | `$defaults` |
| `{$prefix}_get_settings_types` | filter | `$options` |

## Metabox

| Hook | Type | Fired in | Arguments |
|---|---|---|---|
| `{$prefix}_meta_box` | action | `Metabox_API::html()` | `$post` |
| `{$prefix}_metabox_non_setting_types` | filter | `Metabox_API::save()` | `array( 'header', 'descriptive_text' )` |
| `{$prefix}_meta_key` | filter | `Metabox_API::save()` | `$post_meta`, `$post_id` |
| `{$prefix}_metabox_settings` | filter | the example `Metabox` class | `$settings` |

## Setup wizard

| Hook | Type | Arguments |
|---|---|---|
| `{$prefix}_wizard_step_processed` | action | `$step`, `$settings` |
| `{$prefix}_wizard_completed` | action | `$prefix` |
| `{$prefix}_wizard_before_actions` | action | `$current_step`, `$total_steps` |
| `{$prefix}_wizard_completion_before` | action | — |
| `{$prefix}_wizard_completion_message` | action | — |
| `{$prefix}_wizard_completion_after` | action | — |
| `{$prefix}_wizard_completion_buttons` | filter | `$buttons`, `$prefix` |
| `{$prefix}_wizard_version` | filter | `$version`, `$prefix` |

## Example controller

These live in `class-settings.php`, the reference implementation you copy — so they exist only if you keep them.

| Hook | Type | Arguments |
|---|---|---|
| `{$prefix}_translation_strings` | filter | `$strings` |
| `{$prefix}_settings_sections` | filter | `$settings_sections` |
| `{$prefix}_registered_settings` | filter | `$settings` |
| `{$prefix}_settings_general` | filter | `$settings` |
| `{$prefix}_settings_head` | filter | `$settings` |
| `{$prefix}_settings_body` | filter | `$settings` |
| `{$prefix}_settings_footer` | filter | `$settings` |
| `{$prefix}_settings_feed` | filter | `$settings` |
| `{$prefix}_settings_third_party` | filter | `$settings` |
| `{$prefix}_settings_help` | filter | `$help_sidebar` |
| `{$prefix}_help_tabs` | filter | `$help_tabs` |
| `{$prefix}_copyright_text` | filter | `$copyrightnotice` |
| `{$prefix}_settings_defaults` | filter | `$options` |
| `{$prefix}_taxonomy_search_tom_select` | AJAX action | used by the taxonomy Tom Select field |

## Worked examples

Add a field to an existing tab from another plugin:

```php
add_filter(
    'my_plugin_settings_general',
    function ( $settings ) {
        $settings['extra'] = array(
            'id'      => 'extra',
            'name'    => __( 'Extra option', 'my-addon' ),
            'type'    => 'checkbox',
            'default' => 0,
        );
        return $settings;
    }
);
```

Append markup after a specific field:

```php
add_filter(
    'my_plugin_after_setting_output',
    function ( $html, $args ) {
        if ( 'limit' === $args['id'] ) {
            $html .= '<p class="description">' . esc_html__( 'Higher values slow down the query.', 'my-addon' ) . '</p>';
        }
        return $html;
    },
    10,
    2
);
```

Remember that `after_setting_output` runs through `wp_kses()` with the allow-list from `{$prefix}_settings_form_allowed_html`, so extend that list if you need tags it does not permit.
