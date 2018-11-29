# Background

Our original scope was to provide an API for adding form UI elements for post metadata. Historically in core, there is a simplistic UI for editing custom fields: a meta box on the Edit Post screen with textareas for each field's contents. This shows how unopinionated core has been in regards to metadata. From a developer's perspective, getter and setter functions exist with basic sanitization and authorization and that's it.

Core's unopinionated respect towards metadata is actually a good thing. It's opened up a vacuum for third-party libraries to fill in with a wide variety of solutions that employ different architectural patterns.

## Research

To take insight from these libraries, we researched their architecture to uncover desirable architectural patterns for a core-worthy plugin.

### Existing Post Meta Libraries

These libraries include: [Custom Metadata Manager](https://github.com/Automattic/custom-metadata), [Pods](http://wordpress.org/plugins/pods/), [Custom Metaboxes and Fields](https://github.com/jaredatch/Custom-Metaboxes-and-Fields-for-WordPress), [Humanmade’s Custom Meta Boxes](https://github.com/humanmade/Custom-Meta-Boxes), [WPAlchemy](https://github.com/farinspace/wpalchemy), [Advanced Custom Fields (ACF)](http://wordpress.org/plugins/advanced-custom-fields/), [Custom Field Suite](http://wordpress.org/plugins/custom-field-suite/), [Types](http://wordpress.org/plugins/types/), [FieldManager](http://fieldmanager.org/), [Super CPT](https://github.com/mboynes/super-cpt), [Easy Custom Fields](http://wordpress.org/plugins/easy-custom-fields/), [WordPress Settings API Class](https://github.com/tareq1988/wordpress-settings-api-class), [piklist](http://wordpress.org/plugins/piklist/), [Option Tree](https://github.com/valendesigns/option-tree), [vafpress-framework](http://vafpress.com/vafpress-framework/), [wp-forms](https://github.com/jbrinley/wp-forms), [Sunrise](https://bitbucket.org/newclarity/sunrise-1), [Advanced Post Manager](http://wordpress.org/plugins/advanced-post-manager/), [Themosis](http://www.themosis.com/), [oik-fields](http://www.oik-plugins.com/oik-plugins/oik-fields-custom-post-type-field-apis/), [Tax-Meta-Class](https://github.com/bainternet/Tax-Meta-Class), [MetaBox](https://github.com/rilwis/meta-box), [KC Settings](http://wordpress.org/plugins/kc-settings/), [Developers Custom Fields](http://wordpress.org/plugins/developers-custom-fields/), [Simple Fields](http://wordpress.org/plugins/simple-fields/), [Nmwdhj](http://wordpress.org/plugins/momtaz-nmwdhj/), [BMoney Custom Meta Boxes](http://briandichiara.com/code/custom-meta-boxes/), [WordPress Extend (WPX)](https://bitbucket.org/alkah3st/wp-extend), [WordPress-Cuztom-Helper](https://github.com/Gizburdt/Wordpress-Cuztom-Helper).

We invited authors of these libraries to present an overview over Google Hangout of their library and anything of note that came up architecturally in development. All these can be found in [a Youtube playlist](https://www.youtube.com/playlist?list=PL3VvzYmI35PD9tDw0WlHYNoe7DVd4nfal).

We also wrote up architectural overviews of these(time/effort permitting), so we could compare architecture at a glance rather than diving into code. [Fieldmanager](postmeta-library-architectural-reviews/fieldmanager.md), [Custom Metadata Manager](postmeta-library-architectural-reviews/custom-metadata-manager.md), [Custom Metaboxes and Fields](postmeta-library-architectural-reviews/cuztom-helper.md), [WordPress Extend (WPX)](postmeta-library-architectural-reviews/wp-extend.md)

### Further scope & Related WP core API architectural reviews

Although this is a project by the Metadata component team originally scoped for creating a post metadata UI API, basically what we are creating is a form element-building API with a contextual form handler. A form handler has utility in other places in the administrative area, such as settings pages, taxonomy term metadata, Theme Customizer fields, etc. The form handler should be extensible and versatile enough to support any context, including a generic front-end form.

To this end, we endeavored into architectural reviews of core APIs. See [Settings API](wp-core-api-architecture-reviews/settings-api.md), Theme Customizer API(currently being written).
