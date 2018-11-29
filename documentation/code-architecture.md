# Code Architecture

## Procedural methods in the global namespace

Developer API will be procedural, like `register_post_type()`, though these could be wrappers to OOP classes behind the scenes.

### Register a basic form

    $form_obj = register_form( 'my-form-id', $args );
    
### Register and add form fields

    $field_obj = register_form_field( 'address-1', $args );
    
Add a field directly to an already registered form, no need to capture the field object

    register_form_field( 'my-form-id', 'address-1', $args );
    
Add the field to an already registered form

    $address_field_obj = register_field( 'address-1', $args );
    add_form_field( $form_obj, $address_field_obj );
    add_form_field( 'my-form-id', 'address-1' );
    
Insert a field into a form, pushing subsequent fields "down" the list

    add_form_field( 'my-form-id', 'field-id', 3 );
    
### Batch adding of fields

Manually

    $fields = array(
        'field-id-1' => $field_1_args,
        'field-id-2' => $field_2_args,
        'field-id-3' => $field_3_args
    );
    foreach( $fields as $field_id => $field_args ){
        register_form_field( 'my-form-id', $field_id, $field_args );
    }
    
Using a convenience function

    add_form_fields( $form_obj, array( $field_obj_1, 'field-id-2', $field_obj_3 ) );
    
Batch registration of generic form fields, then explicitly adding them to a form

    $field_obj_array = register_form_fields( array(
        'field-id-1' => $field_1_args,
        'field-id-2' => $field_2_args,
        'field-id-3' => $field_3_args
    ) );
    add_form_fields( $form_obj, $field_obj_array );
    

Batch registration of form fields, adding them to the form at once  
    
    register_form_fields( $form_obj, array (
        'field-id-1' => $field_1_args,
        'field-id-2' => $field_2_args,
        'field-id-3' => $field_3_args
    ) );
    

    
    
    
### Register an HTML template for outputting a "row" of the form

    $template_obj = register_template( 'separate-label', '<p class="form-field"><label for="%id%" >%label%</label>%field%</p>' );
    
Use the template as the default template for a form

    form_set_field_template( 'my-form-id', 'separate-label' );
    form_set-field_template( $form_obj, $template_obj );
    
Or set it when the form is registered

    register_form( 'my-form-id', array( 'field_template' => 'separate-label' ) );
    register_form( 'my-form-2', array( 'field_template' => '<label for="$id%">%label% %field%</label>' ) );







## Option 2: Object Instantiation

This is off the table as of https://github.com/wordpress-metadata/metadata-ui-api/issues/3 per @nacin, but I'm leaving it here because we still might want to do something behind the scenes.

### Create a basic form as an instance of `WP_Form`

    $form = new WP_Form( array(
        'id' => 'my-id', // Optional, could be auto-generated. Not really needed, other than for CSS purposes
        'post_types' => array( 'post', 'page', 'my-cpt-slug' ), // Optional. Auto-registers metabox for these post types
        'metabox_callback' => array( $this, 'my_metabox_cb' ), // Optional. Used to override default metabox behaviour
        'save_post_callback' => array( $this, 'my_save_post_cb' ), // Optional. Used to override default meta save
    ) );


### Create a new, reusable text field. 

Do we want to keep a single, generic `WP_Form_Field` object to keep the WP_ namespace relatively uncluttered? 

    $text_field_1 = new WP_Form_Field( 'text', 'my_text_field_1', array(
        'label' => __( 'Enter Name' )
    ) );
    
Or do we create a more nested object hierarchy that inherits from and extends `WP_Form_Field`?

    $text_field_2 = new WP_Text_Field( 'my_text_field_2' );
    
Which allows perhaps some easier extension by developers

    class TA_Custom_Text_Field extends WP_Text_Field {
        public function __construct( $id, $args ){
            parent::__construct( $id, $args );
        }
    }
    
### Register a field with an existing form

    $form->register_field( $text_field_1 );
    
### Register an anonymous field

    $form->register_field( new WP_Text_Field( 'my_text_field_3' ) );
    
### Register a bunch of fields

    $form->register_fields( array(
        $text_field_1,
        $text_field_2,
        new WP_Text_Field( 'my_text_field_3' )
    ) );
    
### Get a field

    $first_field = $form->get_field_at( 0 );
    
### Specify the position of a field

    $form->add_field_at( 0, $text_field_0 ); // will insert at the "top" of the field list
    
    $third_field = $form->get_field_at( 2 );
    $form->add_field_before( $third_field );
    
### Remove a field

    $form->remove_field( $my_text_field_0 );
    $my_text_field_2 = $form->remove_field_at( 1 );
    
### Register a field template

    $form->set_field_template( '<p class="form-field"><label for="%id%" >%label%</label>%field%</p>' );
