# Design Patterns in Custom Metadata Manager (CMM)

CMM is a plugin for creating meta fields for various content types with a developer API. There is no UI for managing fields.

The entire functional architecture of the library is built into a singleton class. All data objects specific to the metadata manager (field types, fields, etc.) are stored internally. 

A field is created with a procedural API - x_add_metadata_field(), specifying a field type (e.g. text, email, etc.), a field group, the content type for the field (e.g. `post`), and slew of optional arguments including overriding callbacks for sanitization and rendering. The field object contains all necessary information to handle user interaction with the UI, including capability requirements for editing, labels, placeholder text, etc. The API bears a striking resemblance to WordPress’ object registration functions register_post_type() or register_taxonomy(); WordPress developers should grok the API with ease because of familiarity. Using the procedural, class(ish) registration approach “requires less complexity, and a full OOP approach seems like an unnecessary implementation detail” (Mo).

Field groups can be registered, specifying options including required user capabilities used in the UI, and relevant options to pass along to meta box registration. 

A multifield is a field-like object that can be put into groups, which then serves as a container for other meta fields to be put into. Multifields are stored in the database as nested arrays.

Filters exist for overriding default callbacks on saving and retrieving meta data, a simple affordance for implementing a custom data storage model.

Field data is stored at runtime in a nested array/object fashion:

metadata[$object_type][$group_slug]->fields[$field_slug] = $field;

e.g. metadata[‘page’][‘extra_details’]->fields[‘custom_background_color’]

Javascript assists with multifields as well as repeat fields.

The plugin does not provide its own functions for fetching data for use in templates and elsewhere and instead suggests utilizing core’s meta API.

Other extensibility is possible through filters and actions.

The plugin also supports adding fields to comments and users beyond just posts/CPTs.
