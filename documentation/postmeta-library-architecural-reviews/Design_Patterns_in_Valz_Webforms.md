Design Patterns in Valz_Webforms
================================

Valz::Webforms is a PHP port of a Perl module, Valz::Webforms that was used as the primary form- and templating-engine behind a custom CMS built in the 90s.



It has been ported to PHP 5.4, updated to HTML5 and integrated into WordPress via a subclass. The WordPress subclass has been further subclassed into classes specific to creating forms for Meta Boxes, Admin Settings/Options, and Widgets.



Basic Objects
-------------

### Webform ($form)

The fundamental object of Webforms is a `$form` object, which is an instance of Valz_Webforms (though typically it is an instance of one of the subclasses: `WP_Webform`, `WP_Meta_Webform`, `WP_Admin_Webform`, `WP_Widget_Webform`).

In one respect the `$form` object represents the HTML form in all of its aspects: - the HTML FORM element itself, with its attributes and behviours - the list of form items - their data - their HTML markup - additional attributes (both HTML-specific and Webform-specific)

A Webform object is instantiated and any number of behaviours and properties can be set on that form, either through the constructor, or afterward using a simple getter-setter API.

### Field

The atomic element of a Webform is the `Field`. A Webform can have one or many fields, but is pretty useless without at least 1 field.

**In the current implementation, Fields are not objects in their own right.** This is currently one of the biggest architectural drawbacks to this implementation, though refactoring to make fields OOP is not an insurmountable task

A field is simultaneously created and added to a form's field list using the `$form->add_field()` or `$form->add_fields()` methods (the latter being a convenience function that allows multiple fields to be created and added at once).

Fields have different `types`, which loosely map to HTML INPUT element types, but with additional specifications (for example a DATE type which maps to a TEXT input type, but with datepicker functionality and checking).

Fields have many parameters that control behaviour, style and functionality. Some of these parameters are common: `id`, `name`, `label`, `type`. Of these, only `id` is actually required - all others are set to defaults.

Many parameters are quite specific to a field type; for example the SELECT field type has a parameter called `items` which can be used to populate the SELECT OPTIONS items.

Outputting a Form
-----------------
The form (including the HTML FORM tag) can be output using `$form->print_form()`. To output just the fields (as is necessary in a Meta Box) without the FORM tag, `echo $form->content`.

Saving a Form
-------------
Since the form knows about each of its fields, and each of its fields' field names, it is able to parse `$_POST` and update fields accordingly using `$form->update_fields_from_input()` where "input" in this context is user input submitted within the $_REQUEST. The WordPress specific subclasses

Workflows
---------
Unless customization is required, much of the heavy lifting is handled through the subclasses specific to a workflow. So, `WP_Meta_Webforms` abstracts most of the work in displaying and saving the custom fields: only the form and its associated fields need be defined using `add_field()`.

Advantages
----------
Implementation across a variety of WordPress workflows is very simple since most of the hard work has been abstracted. This makes spinning up a new Widget, Meta Box or Options page a matter of a few lines of code, mostly dedicated to defining the behviour of the form fields.

The API handles tabular as well as columnar data equally well and has a very versatile templating system that allows deep customization of field and label layouts.

Disadvantages
-------------
The current architecture is pretty procedural and form fields are not Objects at all. This has its obvious disadvantages.

