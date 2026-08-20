---
slug: admin-banner
title: "Admin banner"
sections: [03-wzsa-extending]
tags: [settings-api, admin, branding, developer]
status: publish
order: 4
---

[kbtoc]

`Admin_Banner` (`class-admin-banner.php`) renders a branded header with quick links above your plugin's admin screens. It hooks `in_admin_header`, so it sits above the page content without any change to the settings markup.

## Configuring

```php
new Admin_Banner(
    array(
        'capability' => 'manage_options',
        'prefix'     => 'my_plugin',
        'screen_ids' => array( 'settings_page_my_plugin_options_page' ),
        'page_slugs' => array( 'my_plugin_options_page' ),
        'strings'    => array(
            'region_label' => esc_html__( 'My Plugin quick links', 'my-plugin' ),
            'nav_label'    => esc_html__( 'My Plugin admin shortcuts', 'my-plugin' ),
            'eyebrow'      => esc_html__( 'My Plugin', 'my-plugin' ),
            'title'        => esc_html__( 'Configure My Plugin.', 'my-plugin' ),
            'text'         => esc_html__( 'Manage settings and explore the docs.', 'my-plugin' ),
        ),
        'sections'   => array(
            'settings' => array(
                'label' => esc_html__( 'Settings', 'my-plugin' ),
                'url'   => admin_url( 'options-general.php?page=my_plugin_options_page' ),
                'type'  => 'primary',
            ),
        ),
        'style'      => array(
            'version' => MY_PLUGIN_VERSION,
        ),
    )
);
```

| Argument | Default | Purpose |
|---|---|---|
| `capability` | `manage_options` | Who sees the banner. |
| `allow_network` | `false` | Whether it renders on network admin screens. |
| `prefix` | `''` | Derives the CSS class prefix and the style handle. |
| `screen_ids` | `array()` | Screens the banner appears on. Falls back to the IDs declared by the sections. |
| `page_slugs` | `array()` | Page slugs the banner appears on. Falls back to the slugs declared by the sections. |
| `exclude_screen_bases` | `array( 'post', 'post-new' )` | Screen bases that never show the banner. |
| `sections` | `array()` | The quick links. |
| `strings` | `array()` | `region_label`, `nav_label`, `eyebrow`, `title`, `text`. |
| `link_target` | `_self` | Default link target. |
| `style` | `array()` | Stylesheet `handle`, `deps`, `version`, `filename`, `url`. |

## Sections

Each section needs at least a `label` and a `url`; entries missing either are dropped. Optional keys are `type` (defaults to `secondary`), `target`, `rel`, and per-section `screen_ids` / `page_slugs` so a link can be limited to one screen.

## Styles

If no `url` is supplied, the stylesheet resolves to `css/admin-banner{-rtl}{.min}.css` next to the class file, honouring `SCRIPT_DEBUG` and RTL locales. Pass your plugin's version as `style['version']` so a release busts the cached CSS.

The banner registers its hooks through [`Hook_Registry`]({{ '/docs/03-wzsa-extending/hook-registry/' | relative_url }}), so instantiating the admin bootstrap more than once in a request is harmless.

`class-admin.php` in the repository is a working example of wiring the banner into a plugin's admin bootstrap.
