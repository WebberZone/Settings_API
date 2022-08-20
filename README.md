# WebberZone Settings_API class

The WebberZone Settings_API class allows WordPress plugin authors to easily added a tabbed settings interface to their plugin. This serves as a wrapper to the [WordPress Settings API](http://codex.wordpress.org/Settings_API).

## Installation

In your plugin you will need to include the following files:

1. class-settings-api.php
2. class-plugin-name-settings.php
3. options-api.php
4. sidebar.php

Within these files replace:

* Plugin_Name with the name of your plugin
* plugin_name with a unique prefix
* plugin-name with the name of your plugin's language domain where wrapped in `_e()`, `__()` functions

## Settings_API class - class-settings-api.php

The Settings_API consists of one plugin class file called `class-settings-api.php` which is the main class that handles the creation of a tabbed settings interface and handles the display and validationg of the settings.

You will need to change the `namespace` at the top of the file to ensure you don't have any clashed with other plugins using the same Settings_API.

## Plugin_Name_Settings - class-plugin-name-settings.php

This is an example implementation of the Settings_API class.
