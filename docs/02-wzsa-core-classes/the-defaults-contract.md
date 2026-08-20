---
slug: the-defaults-contract
title: "The defaults contract"
sections: [02-wzsa-core-classes]
tags: [settings-api, defaults, developer]
status: publish
order: 5
---

[kbtoc]

`Settings::get_defaults()` is the single source of truth for field defaults, and `Options_API::get_default_option()` reads it instead of building every field definition. That is what makes an option read safe before `init` — building the definitions runs `esc_html__()` on every label and triggers WordPress's *translation loading triggered too early* notice.

Four rules govern the array. Breaking any of them produces a defect that phpcs, phpstan, and static inspection all miss.

## 1. No translation calls

Nothing in the array may translate, and nothing it calls may translate transitively. Avoiding the translation stack is the entire reason the array exists.

## 2. Values are pre-normalised

Each value must already match what `settings_defaults()` emits **after** its casts. Checkbox defaults are `1` and `0`, never `true` and `false`. A `false` where the saved default is `0` breaks block attributes and REST schemas typed as `number`.

## 3. Every registered option has an entry

Including fields whose definition omits `'default'` entirely. Those resolve to `''` in `settings_defaults()`, so without an explicit `''` here the option silently resolves to `false`. Section headers and descriptive text are the only exclusions.

## 4. The array is unfiltered

`{$prefix}_settings_defaults` is applied by the two consumers — `settings_defaults()` and `Options_API::get_default_option()` — so it runs exactly once on each path, and a filter callback cannot recurse into field building.

## Defaults that cannot be constants

A default that must be translated or computed at runtime cannot live in the array. Store the raw base value and have the caller pass the translated or computed value as the getter's second argument, which short-circuits the default lookup:

```php
$title = my_plugin_get_option( 'toc_title', __( 'Table of Contents', 'my-plugin' ) );
```

## Initialise `$prefix` at declaration

```php
public static $prefix = 'my_plugin';
```

`Settings::$prefix` must be initialised where it is declared, not only in the constructor. The static methods are reachable on the frontend, where the `Settings` object is never instantiated, and a null prefix there fires `_settings_defaults` instead of `{$prefix}_settings_defaults`.

## Verifying the invariant

Inside the WebberZone workspace, run the checker after any settings change:

```bash
dev-tools/check-settings-defaults.sh <plugin>
```

It compares `get_defaults()` against `settings_defaults()` through WP-CLI and reports missing keys, mismatched values, and orphaned entries.
