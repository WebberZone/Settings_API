---
slug: metabox-api
title: "Metabox API"
sections: [03-wzsa-extending]
tags: [settings-api, metabox, developer]
status: publish
order: 2
---

[kbtoc]

`Metabox_API` (`settings/class-metabox-api.php`) renders a post metabox from the same field-definition arrays used by the settings page, and saves each value as individual post meta.

## Constructing

```php
new Metabox_API(
    array(
        'settings_key'        => 'my_plugin_meta',
        'prefix'              => 'my_plugin',
        'post_type'           => 'post',
        'title'               => __( 'My Plugin options', 'my-plugin' ),
        'registered_settings' => self::get_registered_settings(),
        'translation_strings' => self::get_translation_strings(),
    )
);
```

| Argument | Purpose |
|---|---|
| `settings_key` | Used to build the form field names. It is **not** the meta key. |
| `prefix` | Used for hooks and for the meta keys. |
| `post_type` | Post type, list of post types, or `WP_Screen` the box appears on. |
| `title` | Metabox heading. |
| `registered_settings` | A flat list of field definitions — not the tab-keyed structure used by the settings page. |
| `translation_strings` | Label overrides passed through to `Settings_Form`. |

The constructor hooks itself to `admin_enqueue_scripts`, `add_meta_boxes`, and `save_post_{$post_type}`.

## Storage

Each field is stored as its own post meta entry:

```text
_{$prefix}_{$field_id}
```

A field whose submitted value is empty has its meta row deleted rather than stored as an empty string, so `get_post_meta()` falls back to the plugin's global setting.

## Saving

`save()` bails on autosave, verifies the `{$prefix}_meta_box_nonce` nonce, and checks `edit_post` for the current user. Each posted value is then run through `Settings_Sanitize::sanitize_{$type}_field()` for its own field type — an unknown type falls through to `sanitize_missing()`. Types listed in `{$prefix}_metabox_non_setting_types` (`header` and `descriptive_text` by default) are skipped.

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `{$prefix}_meta_box` | action | Fires while the metabox renders. Receives the post object. |
| `{$prefix}_metabox_settings` | filter | Filters the field definitions before rendering. |
| `{$prefix}_metabox_non_setting_types` | filter | Adds display-only field types. |
| `{$prefix}_meta_key` | filter | Filters the full sanitized meta array before it is written. Receives the array and the post ID. |

## Reading the values

Because each field is its own meta key, a per-post override reads naturally with a global fallback:

```php
$limit = get_post_meta( $post_id, '_my_plugin_limit', true );

if ( '' === $limit ) {
    $limit = my_plugin_get_option( 'limit' );
}
```
