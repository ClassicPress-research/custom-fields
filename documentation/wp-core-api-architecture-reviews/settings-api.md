# Settings API Design Pattern Summary

The Settings API is part of WordPress core. It provides functions to add form UI for settings pages, and saving the data on form submission.

Form building and saving are somewhat separated in the API.

## Adding settings to the UI

The admin interface has historical UI patterns in settings pages, which a developer can tap into.

Settings are stored in a hierarchy of containers as a multi-dimensional array - pages contain sections contain fields.

Starting with the largest container, a settings page is added, or a built-in settings page is used (Reading, Discussion, etc.). Pages are added via via add_menu_page(), add_submenu_page(), etc.

A settings page contains sections, which are added via add_settings_section()with optional title and callback to output markup at the top of the section, all stored in $wp_settings_sections.

A section contains multiple fields. Fields are technically rows in a table element which represents a section. Fields specify a callback to output any markup.

All of these containers are artificial grouping constructs. None of them dictate the way settings data is stored.

Adding settings will not enable automatic saving of the settings; this part of the API is exclusively for form-building.

## Saving settings data

To save data, settings need to be registered at runtime via register_setting().  This function whitelists a setting for saving routines and adds an optional sanitization callback. The sanitization callback is baked into basic option setter functions ( update_option() ). Setting data is stored in $new_whitelist_options and related to the settings page the input element is found on. On submission of the settings page, whitelisted options are looped through for the  particular page, and settings(options) are updated.
