# Theme Customizer API Design Pattern Summary

The Theme Customizer API is part of WordPress core. It provides functions to add form UI for the theme customizer, and saving the data on form submission.

Form building and saving are tightly wound together in the API and requires no manual hooking to handle the save.

## Adding controls to the UI

The Theme Customizer interface has a similar pattern to the Settings API, in that there are settings, sections, and fields -- but in this case the terminology is settings, sections, and controls.

Available controls built-in are Text, Color picker, File Upload, and Image Upload. In addition to those, there are somewhat specific ones that may likely never be used by a developer adding controls: Background Image set and Header Image set.

Each object is it’s own class: WP_Customize_Setting, WP_Customize_Section, WP_Customize_Control.

Settings, Sections, and Controls are each stored in their own array inside of the global WP_Customize_Manager object $wp_customizer. Controls are copied into each of the section objects $controls property during the customize_controls_init action.

The UI is output first by sections, then beneath them the controls directly from the section object, ignoring the global $wp_customizer->controls property.

All registration happens during the ‘customize_register’ action.

First, a theme setting is registered, just like register_setting, but in a more OO way of:

{{{
$wp_customize->add_setting( 'header_textcolor' , array(
    'default'     => '#000000',
    'transport'   => 'refresh',
) );
}}}

Then, a section is added for the setting to be listed in:

{{{
$wp_customize->add_section( 'mytheme_new_section_name' , array(
    'title'      => __( 'Visible Section Name', 'mytheme' ),
    'priority'   => 30,
) );
}}}

And then the control for the setting is setup:

{{{
$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'link_color', array(
	'label'        => __( 'Header Color', 'mytheme' ),
	'section'    => 'mytheme_new_section_name',
	'settings'   => 'mytheme_new_section_name',
) ) );
}}}

The control class WP_Customize_Control can be extended to fit any type of input necessary.

Adding settings will enable automatic saving of the settings, controls do not automatically handle their own saves.
