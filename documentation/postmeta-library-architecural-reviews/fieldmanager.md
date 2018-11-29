# Design Patterns in Fieldmanager

Fieldmanager is a WordPress plugin for managing multiple types of meta data for many kinds of content types. This includes built-in and custom post types, taxonomy terms, users, options (via either built-in admin submenu pages, or custom admin pages), and front-end forms. The plugin offers tools(PHP classes) for developers to do this, no builder UI.

A field is registered at runtime by instantiating a PHP object, of a field type class which inherits from an abstract class(e.g. Fieldmanager_Textarea). The field type object offers methods for describing its form UI elements (including enqueueing helper JS and CSS), validation and sanitization. For example, the Fieldmanager_Link field type simply supplies `sanitize_url()` as a sanitization function, and inherits all other functionality from the base.

Field objects are not stored anywhere, although a developer could add a container for registered field objects.

Groups must be created from multiple fields, by stuffing them into a Group object, which itself is a subclass of the Fieldmanager_Field base class.

Field objects by themselves do not relate to post types, etc., and correspondingly, fields do not display in the UI automatically after registration. Controller-like objects, called contexts, are provided for this purpose. Fields are registered, put into groups, and then related to one of these contexts, which is specific to a content object (e.g. Context_Post, Context_User, even front-end page). The context will output the container (e.g. meta box) on a context’s edit screen (e.g. Edit Post screen), and handle the saving of data based on the context's respective API.

All group(and its children’s) data is saved as a nested array in serialized PHP.
