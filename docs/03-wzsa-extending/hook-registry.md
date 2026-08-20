---
slug: hook-registry
title: "Hook Registry"
sections: [03-wzsa-extending]
tags: [settings-api, hooks, developer]
status: publish
order: 3
---

[kbtoc]

`Hook_Registry` (`util/class-hook-registry.php`) is a static wrapper around `add_action()` and `add_filter()` that refuses duplicate registrations. It exists because the example controllers can be instantiated more than once in a request — on a settings page, in a metabox, and during a wizard step — and WordPress would happily attach the same callback several times.

## Usage

```php
use My_Plugin\Util\Hook_Registry;

Hook_Registry::add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
Hook_Registry::add_filter( 'the_content', array( $this, 'append_widget' ), 20 );
```

Both return `true` when the hook was registered and `false` when it was rejected as a duplicate — or as invalid.

## Methods

| Method | Purpose |
|---|---|
| `register( $hook_type, $hook_name, $callback, $priority = 10, $args = 1 )` | The underlying registration. `$hook_type` is `action` or `filter`. |
| `add_action( $hook_name, $callback, $priority = 10, $args = 1 )` | Shorthand for `register( 'action', … )`. |
| `add_filter( $hook_name, $callback, $priority = 10, $args = 1 )` | Shorthand for `register( 'filter', … )`. |
| `remove( $hook_type, $hook_name, $callback, $priority = 10 )` | Unregisters and forgets a hook. |
| `remove_action()` / `remove_filter()` | Type-specific shorthands. |
| `get_hooks()` | The full registry: type, name, callback, priority, argument count, and closure ID. |
| `remove_all_hooks()` | Unregisters everything the registry knows about. |
| `create_hook_key()` / `callback_to_string()` | Build the identity key used for deduplication. |

## What counts as a duplicate

The identity key combines the hook name, a string representation of the callback, and the priority. Functions, static and instance method arrays, and invokable objects all resolve to a stable string, so registering the same callback twice at the same priority is caught. The same callback at a *different* priority is a distinct hook and registers normally.

Closures are the exception: each closure gets a fresh `uniqid()`, so two identical-looking closures are always treated as different callbacks. If you need deduplication, pass a named function or a method array instead.

## Invalid input

`register()` returns `false` without touching WordPress when the hook type is neither `action` nor `filter`, the hook name is empty, the priority is negative, or the argument count is below one.
