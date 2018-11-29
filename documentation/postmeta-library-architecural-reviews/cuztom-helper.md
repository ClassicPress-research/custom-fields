# Design Patterns in Cuztom Helper

Cuztom Helper is a library which can be used in Themes and Plugins (It automaticly sees its path, so files will be included). It can also be placed in a custom folder, with the path set manually. It supports meta data for post types, users, terms. Forms are generated automatically. There is no form builder or something like that. It’s just a helper for developers.

All data is registered with an object with params: id, name, data. The data param is a big array with all the fields, tabs/accordions, grouped fields and their associated fields. The three object types (Meta_Box, User_Meta, Term_Meta) all extend from a class called Cuztom_Meta, which has a build method. This method converts all arrays to objects (recursive). The output method just checks some things (like, is there a description) and outputs the fields (with their own output method).

The fields are saved as separate post_meta.
