<?php

add_shortcode('contact', 'show_contact_form');

add_action('rest_api_init', 'create_rest_endpoint');

add_action('init', 'create_submissions_page');

add_action('add_meta_boxes', 'create_meta_boxes');

function create_meta_boxes()
{
    add_meta_box('custom_contact_form', 'Submission', 'display_submission', 'submission');
}

function display_submission()
{
    echo `Hello`;
}

function create_submissions_page()
{
    $args = array(
        'public' => true,
        'has_archive' => true,
        'labels' => [
            'name' => 'Submissions',
            'singular_name ' => 'Submissions',
        ],
        // 'capability_type' => 'post',
        // 'capability' => ['create_posts', false],
        'supports' => ['custom-fields'],
    );

    register_post_type('submission', $args);
}

function show_contact_form()
{
    ob_start();
    include MY_PLUGIN_PATH . '/includes/templates/contact-form.php';
    return ob_get_clean();
}

function create_rest_endpoint()
{
    register_rest_route(
        'v1/contact-form',
        'submit',
        array(
            'method' => 'POST',
            'callback' => 'handle_enquiry'
        )
    );
}

function handle_enquiry($data)
{

    return new WP_REST_Response('Message not sent', 422);

    $params = $data->get_params();

    if (!wp_verify_nonce($params['_wponce'], 'wp_rest')) {
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

    $subject = 'New enquiry from ' . $params['name'];

    $message = '';
    $message .= "Message has been sent from {$params['name']}";

    $postarr = [
        'post_title' => $params['name'],
        'post_type' => 'submission',
    ];

    $post_id = wp_insert_post($postarr);

    foreach ($params as $label => $value) {
        $message .= '<strong>' . ucfirst($label) . '</strong>:' . $value;

        add_post_meta($post_id, $label, $value);
    }



    wp_mail($admin_email, $subject, $message, $headers);

    return new WP_REST_Response('The  message was sent successfully', 200);
}

