<?php

add_shortcode('contact', 'show_contact_form');
add_shortcode('rest_api_init', 'create_rest_endpoint');

function show_contact_form()
{
      include MY_PLUGIN_PATH . 'includes/templates/contact-form.php';
}

function create_rest_endpoint()
{
    register_rest_route('v1/contact-form', 'submit', array(
        'method' => 'POST',
        'callback' => 'handle_enquiry'
    )
    );
}

function handle_enquiry($data){
    $params = $data->get_params();

    if(!wp_verify_nonce($params['_wponce'], 'wp_rest'))
    {
        return new WP_REST_Response('Message no sent', 422);
    }

    unset($params['_wponce']);
    unset($params['_wp_http_referer']);

    // send the email message
    $headers = [];

    $admin_email = get_bloginfo('admin_email');
    $admin_name = get_bloginfo('name');

    $headers[] = "From: {$admin_email} <{$admin_name}>";
    $headers[] = "Reply-To: {{$params['name']}} <{{$params['email']}}>";

    $subject = 'New enquiry from '. $params['name'];

    $message = '';
    $message .= "Message has been sent from {$params['name']}";

    foreach($params as $label => $value)
    {
        $message .= ucfirst($label) . ':' . $value;
    }

    wp_mail($admin_email, $subject, $message, $headers);
}

