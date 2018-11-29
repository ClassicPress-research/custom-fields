ClassicPress Custom Fields API
=======================

An API for building form UI for ClassicPress content types (post types, users, comments, Settings options, etc.). The current focus is **post types**, with the overall goal of a uniform API to work with across all objects to add fields to forms.

The current code is from an initial attempt at with Fields project for WordPress 4+ years ago that was stalled when the decision was made to based the fields project on the needs of the Customizer.  But since that project effectively died and ClassisPress is not WordPress, time to try again.


## Example:

```php
	/**
	 * Example init of fields
	 */
	function example_init()  {

		register_post_type( 'example_solution',  array(
			'label'   =>  __( 'Solutions',  'ex-sol' ),
			'public'  =>  true,
			'rewrite' =>  true,
			'form'    =>  'after-title'
		) );

		register_post_field( 'website', 'example_solution',  array(
			'type'              =>  'url',
			'label'             =>  __( 'Website',  'ex-sol' ),
			'html_placeholder'  =>  'http://www.example.com',
			'html_size'         =>  50
		) );

		register_post_field( 'tagline', 'example_solution',  array(
			'label'     =>  __( 'Tagline',  'ex-sol' ),
			'html_size' =>  50
		) );

		register_post_field( 'blurb', 'example_solution',  array(
			'type'      =>  'textarea',
			'label'     =>  __( 'Blurb',  'ex-sol' ),
			'html_size' =>  160
		) );

	}
	add_action( 'init', 'example_init' );
```

## Contributing

We welcome contributions. To get started, just submit an issue so we can [start discussing it](https://github.com/ClassicPress-research/custom-fields/issues) to see if it will be compatible with our direction.

## DISCLAIMER

This software is in alpha until otherwise noted. There is no guarantee on backwards compatibility nor a warrantee. It is not recommended to be used on any production site.

## LICENSE

GPLv2 or later. See [License](LICENSE.txt).
