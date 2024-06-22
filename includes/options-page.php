<?php

if(!defined('ABSPATH')){
    die('You cannot be here ');
}

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('after_setup_theme', 'load_carbon_fields');
add_action('carbon_fields_register_fields', 'create_options_page');

function load_carbon_fields()
{
    \Carbon_Fields\Carbon_Fields::boot();
}

function create_options_page()
{
    Container::make('theme_options', 'Contact Form')
        ->set_icon('dashicons-media-text')
        ->add_fields(
            array(
                Field::make('checkbox', 'contact_plugin_active', __('Active')),
                Field::make('text', 'contact_plugin_recipients', __('Recipients Emails'))->set_attribute('placeholder', 'your@email.com')->set_help_text('The email that the form is submitted to'),

                Field::make('textarea', 'contact_plugin_message', __('Confirmation Message'))->set_attribute('placeholder', 'Your Message')->set_help_text('Type the message you want to submitter to recive'),

            )
        );
}


