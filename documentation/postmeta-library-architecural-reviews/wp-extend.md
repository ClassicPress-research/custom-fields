# Design Patterns in WordPress Extend (WPX)

WPX is a plugin for creating meta box GUI for custom fields for custom post types, custom taxonomies, and options pages with a developer API or via UI in the Dashboard: https://bitbucket.org/alkah3st/wp-extend. The UI in the Dashboard allows users to manage fields as well as custom post types, taxonomies, and options pages created by the plugin. The plugin also allows meta fields to be assigned to built in post types and taxonomies (Posts, Pages, Tags, and Categories).

## How it works

The plugin registers several custom post types of its own: “Post Types,” “Taxonomies,” “Meta Fields,” and “Options Pages” and then uses these to loop through, register and add metaboxes for each post from these CPTs it finds.  WPX also creates a Groups taxonomy to sort Meta Field posts. (These all appear in the Dashboard to admin users) Originally, this plugin was just an API that wrapped register_post_types with a function that accepts arrays of meta fields, which were then passed to a monstrous loop that then registered all the post types and added all the metaboxes based on the data in the arrays. I used to have a set of files that I’d load when WP initialized, which would then call the API with all the arrays of meta boxes in their own files. But after using ProcessWire for some other projects (http://processwire.com/), I wanted to be able to manage custom fields in the Dashboard like Processwire allows. In PW, there is only one CPT (they call it Pages) and you simply associate custom fields with that CPT, all inside its admin interface. I wanted to do something similar and manage all the custom fields in WP like you manage posts, so I can just assign them to other posts that represented CPTs or Taxonomies or Options Pages. I also wanted to be able to configure those CPTs/taxonomies/options pages in the Dashboard before I registered them, that way my workflow went from setting up the infrastructure in the Dasboard -> making templates to display my data. This is what WPX tries to do.

Some quick definitions to be more explicit

custom fields are represented by posts in the Meta Field CPT. 
a metabox is represented by terms in the Groups taxonomy (a single Group can have multiple custom fields)
custom taxonomies are represented by the WPX Taxonomies CPT.
custom posts are represented by the WPX Post Types CPT.
options pages are represented by the WPX Options Pages CPT.

For each post the plugin finds that was created via the Post Types CPT, it:

attempts to register the CPT based on the parameters specified for the post in the Dashboard;
collects and constructs an object of all the posts under the Meta Fields CPT that belong to the CPT it just registered (these represent custom fields). Each post in the Meta Fields CPT can be added to a Group so that meta boxes can be sorted in a manual order via the Group term’s order field;
loops through the object and adds a meta box to the CPT’s edit screen in the order specified by its Group;
the metabox is then rendered with appropriate markup, depending on context, from the set of existing field types in the plugin plus any field types extended by the theme

Options pages are registered in a separate loop; taxonomies get registered automatically in the above-described process.

The plugin does provide a few helper functions (as static functions from the wpx class) to retrieve meta data more easily, but in the templates you can retrieve your data as you normally would using core functions.

A full explanation of how it works is here: https://bitbucket.org/alkah3st/wp-extend/wiki/Home.

Developers can also access the APIs used by the plugin directly: 

https://bitbucket.org/alkah3st/wp-extend/wiki/API

## Drawbacks

The biggest flaw in this whole setup (other than having me as the person who created it! I’m relatively inexperienced when it comes to plugin development) is that WPX has to run all the time. It has to go through and register all the CPTs and so on and set up the metaboxes whenever WP loads, since the definitions of the CPTs and so on are stored in the DB. The plugin is really just a template for registering all that data in a single sendoff. This will probably cause a noticeable slowdown if we’re dealing with thousands of custom meta fields or hundreds of CPTs. I have tested the plugin in several production websites (disquietinternational.org is one example), but I can’t vouch for its performance at a higher level.

## Wishlist

Obviously only loading everything on demand (say, when you’re on the CPT’s post edit screen, or wherever it is necessary; the problem I’ve found with this is that in the front end, you never know when a template will need to have the CPTs registered)
The ability to manage user and comment metadata in the same way the plugin handles everything else
A more sensible way to handle ordering / grouping custom fields into metaboxes outside of the Group taxonomy
A more sensible way to handle user role management for options pages created via the plugin/API (if you want to allow non-admins to save options pages there’s a lot of annoying things you have to do because of a limitation in the way WP handles saving settings page data)
Better javascript
A repeatable field type; it’s doable but I didn’t get that far
More field types (there is a way in the existing API to create new field types, however)
