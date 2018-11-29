<?php

class Example_Solution {
	const POST_TYPE = 'example_solution';
	const TAXONOMY = 'example_attribute';

	static function on_load() {
		add_action( 'init', array( __CLASS__, '_init' ) );
	}

	static function _init() {

		register_taxonomy( self::TAXONOMY, self::POST_TYPE, array(
			'labels'            => array(
				'name'                       => _x( 'Solution Attributes', 'Taxonomy General Name', 'ex-sol' ),
				'singular_name'              => _x( 'Solution Attribute', 'Taxonomy Singular Name', 'ex-sol' ),
				'menu_name'                  => __( 'Solution Attributes', 'ex-sol' ),
				'all_items'                  => __( 'All Attributes', 'ex-sol' ),
				'parent_item'                => __( 'Parent Attribute', 'ex-sol' ),
				'parent_item_colon'          => __( 'Parent Attribute:', 'ex-sol' ),
				'new_item_name'              => __( 'New Attribute Name', 'ex-sol' ),
				'add_new_item'               => __( 'Add New Attribute', 'ex-sol' ),
				'edit_item'                  => __( 'Edit Attribute', 'ex-sol' ),
				'update_item'                => __( 'Update Attribute', 'ex-sol' ),
				'separate_items_with_commas' => __( 'Separate attributes with commas', 'ex-sol' ),
				'search_items'               => __( 'Search attributes', 'ex-sol' ),
				'add_or_remove_items'        => __( 'Add or remove attributes', 'ex-sol' ),
				'choose_from_most_used'      => __( 'Choose from the most used attributes', 'ex-sol' ),
			),
			'hierarchical'      => false,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'rewrite'           => array(
				'slug'         => 'attributes',
				'with_front'   => true,
				'hierarchical' => true,
			),
			'query_var'         => 'example_attribute',
		) );


		register_post_type( self::POST_TYPE, array(
			'label'               => __( 'Solutions', 'ex-sol' ),
			'description'         => __( 'Project Management Solutions', 'ex-sol' ),
			'labels'              => array(
				'name'               => _x( 'Solutions', 'Post Type General Name', 'ex-sol' ),
				'singular_name'      => _x( 'Solution', 'Post Type Singular Name', 'ex-sol' ),
				'menu_name'          => __( 'Solutions', 'ex-sol' ),
				'parent_item_colon'  => __( 'Parent Solution:', 'ex-sol' ),
				'all_items'          => __( 'All Solutions', 'ex-sol' ),
				'view_item'          => __( 'View Solution', 'ex-sol' ),
				'add_new_item'       => __( 'Add New Solution', 'ex-sol' ),
				'add_new'            => __( 'New Solution', 'ex-sol' ),
				'edit_item'          => __( 'Edit Solution', 'ex-sol' ),
				'update_item'        => __( 'Update Solution', 'ex-sol' ),
				'search_items'       => __( 'Search solutions', 'ex-sol' ),
				'not_found'          => __( 'No solutions found', 'ex-sol' ),
				'not_found_in_trash' => __( 'No solutions found in Trash', 'ex-sol' ),
			),
			'supports'            => array(
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'revisions',
				'page-attributes',
				'custom-fields'
			),
			'taxonomies'          => array( 'attributes' ),
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 5,
			'menu_icon'           => '',
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'query_var'           => self::POST_TYPE,
			'rewrite'             => array(
				'slug'       => 'solutions',
				'with_front' => true,
				'pages'      => true,
				'feeds'      => true,
			),
			'capability_type'     => 'page',
			'default_form'        => 'after_title',
		) );

		register_post_field( 'website', self::POST_TYPE, array(
			'type'                => 'url',
			'label'               => __( 'Website', 'ex-sol' ),
			'label:lang'          => 'en',
			'label:style'         => 'font-size:24px;',
			'label:wrapper:class' => 'test2',
			'placeholder'         => 'http://www.example.com',
			'size'                => 50,
			'align'               => 'right',
			'input:width'         => 100,
			'input:required'      => true,
			'wrapper:style'       => 'margin-bottom:20em;',
			'input:accesskey'     => 'u',
			'help:style'          => 'height:20px;border:1px solid blue',
			'help:wrapper:style'  => 'height:50px;border:1px solid green',
		) );

		register_post_field( 'example_text_field', self::POST_TYPE, array(
			'label'      => __( 'Example Text Field', 'ex-sol' ),
			'input:size' => 50,
		) );

		register_post_field( 'tagline', self::POST_TYPE, array(
			'wrapper:style'            => 'margin-bottom:2em;',
			'label'                    => __( 'Tagline', 'ex-sol' ),
			'size'                     => 50,
			'label:wrapper:html:class' => 'test',
		) );

		register_post_field( 'blurb', self::POST_TYPE, array(
			'type'                         => 'textarea',
			'label'                        => __( 'Blurb', 'ex-sol' ),
			'features[input]:rows'         => 10,
			'features[input]:element:cols' => 80,
		) );

	}

}
Example_Solution::on_load();