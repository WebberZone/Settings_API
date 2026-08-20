---
slug: setup-wizard-api
title: "Setup wizard API"
sections: [03-wzsa-extending]
tags: [settings-api, wizard, onboarding, developer]
status: publish
order: 1
---

[kbtoc]

`Settings_Wizard_API` (`settings/class-settings-wizard-api.php`) adds an optional multi-step guided setup that reuses the same field definitions as the settings page and writes straight into the plugin's options.

## Constructing the wizard

```php
new Settings_Wizard_API(
    'my_plugin_settings',
    'my_plugin',
    array(
        'steps'               => self::get_wizard_steps(),
        'translation_strings' => self::get_wizard_strings(),
        'page_slug'           => 'my_plugin_wizard',
        'hide_when_completed' => true,
        'show_in_menu'        => true,
        'admin_menu_position' => 999,
        'menu_args'           => array(
            'parent'     => 'options-general.php', // empty for a dashboard page
            'capability' => 'manage_options',
        ),
    )
);
```

| Argument | Default | Purpose |
|---|---|---|
| `steps` | `array()` | The step definitions. |
| `translation_strings` | `array()` | Overrides for the button and navigation labels. |
| `page_slug` | `{$prefix}_wizard` | Admin page slug. |
| `hide_when_completed` | `true` | Removes the submenu entry once the wizard is finished. |
| `show_in_menu` | `true` | Set false to reach the wizard only by direct URL. |
| `admin_menu_position` | `999` | Priority for the `admin_menu` hook. |
| `menu_args` | parent `''`, capability `manage_options` | Where the page is registered and who can see it. |

## Step definitions

Steps are an ordered array. Each step has a title, an optional description, and a `settings` array in exactly the [field definition format]({{ '/docs/01-wzsa-getting-started/field-definition-format/' | relative_url }}):

```php
'basics' => array(
    'title'       => __( 'The basics', 'my-plugin' ),
    'description' => __( 'Choose where the plugin should be active.', 'my-plugin' ),
    'settings'    => array(
        'post_types' => array(
            'id'      => 'post_types',
            'name'    => __( 'Post types', 'my-plugin' ),
            'type'    => 'posttypes',
            'default' => 'post',
        ),
    ),
),
```

## How a step is saved

`process_step()` runs on `admin_init`, only when the wizard form was submitted. It verifies the `{$prefix}_wizard_nonce` nonce and the `manage_options` capability, then dispatches on the submitted action:

| Action | Effect |
|---|---|
| `next_step` | Saves the current step, advances, redirects. |
| `previous_step` | Goes back without saving. |
| `finish_setup` | Saves the current step, marks the wizard complete, shows the completion page. |
| `skip_wizard` | Marks the wizard complete and returns to the admin. |

Each submitted value is sanitized by `Settings_Sanitize::sanitize_{$type}_field()`, matching its field type, before being written into the plugin's option.

## Completion state

Completion is stored in the standalone option `{$prefix}_wizard_completed`. Useful methods:

| Method | Purpose |
|---|---|
| `is_wizard_completed()` | Whether the wizard has been finished or skipped. |
| `should_show_wizard()` | Whether the wizard should be surfaced to this user. |
| `trigger_wizard()` | Force the wizard to appear, for example after a major upgrade. |
| `reset_wizard()` | Clear the completion flag so it runs again. |
| `get_current_step()` / `get_current_step_config()` | Where the user is, and that step's definition. |

## Hooks

| Hook | Type | Purpose |
|---|---|---|
| `{$prefix}_wizard_step_processed` | action | Fires after a step's values are saved. Receives the step number and the saved array. |
| `{$prefix}_wizard_completed` | action | Fires when the wizard is marked complete. |
| `{$prefix}_wizard_before_actions` | action | Renders above the navigation buttons. |
| `{$prefix}_wizard_completion_before` / `_after` | actions | Wrap the completion screen. |
| `{$prefix}_wizard_completion_message` | action | Renders in place of the default completion message. |
| `{$prefix}_wizard_completion_buttons` | filter | The buttons shown on the completion screen. |
| `{$prefix}_wizard_version` | filter | Version reported by the wizard. |

The wizard shares the settings page's assets and adds `wizard.css`.
