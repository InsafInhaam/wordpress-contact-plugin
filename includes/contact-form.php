<?php

if (!defined('ABSPATH')) {
    die('You cannot be here');
}

add_shortcode('contact', 'show_contact_form');

add_action('rest_api_init', 'create_rest_endpoint');

add_action('init', 'create_submissions_page');

add_action('add_meta_boxes', 'create_meta_boxes');

add_filter('manage_submission_posts_columns', 'custom_submission_columns');

add_action('manage_submission_posts_custom_column', 'fill_submission_columns', 10, 2);

add_action('admin_init', 'setup_search');

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function enqueue_custom_scripts()
{
    wp_enqueue_style('contact-form-plugin', MY_PLUGIN_URL . 'assets/css/contact-plugin.css');

    // Enqueue jQuery (WordPress includes jQuery by default)
    wp_enqueue_script('jquery');
}

function setup_search()
{
    global $typenow;

    if ($typenow === 'submission') {
        add_filter('posts_search', 'submission_search_override', 10, 2);
    }
}

function submission_search_override($search, $query)
{
    global $wpdb;

    if ($query->is_main_query() && !empty($query->query['s'])) {
        $sql = "
          or exists (
              select * from {$wpdb->postmeta} where post_id={$wpdb->posts}.ID
              and meta_key in ('name','email','phone')
              and meta_value like %s
          )
      ";
        $like = '%' . $wpdb->esc_like($query->query['s']) . '%';
        $search = preg_replace(
            "#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#",
            $wpdb->prepare($sql, $like),
            $search
        );
    }

    return $search;
}

function fill_submission_columns($column, $post_id)
{
    switch ($column) {
        case 'name':
            echo esc_html(get_post_meta($post_id, 'name', true));
            break;

        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true));
            break;

        case 'phone':
            echo esc_html(get_post_meta($post_id, 'phone', true));
            break;

        case 'message':
            echo esc_html(get_post_meta($post_id, 'message', true));
            break;
    }
}

function custom_submission_columns($columns)
{
      // Edit the columns for the submission table

      $columns = array(

            'cb' => $columns['cb'],
            'name' => __('Name', 'contact-plugin'),
            'email' => __('Email', 'contact-plugin'),
            'phone' => __('Phone', 'contact-plugin'),
            'message' => __('Message', 'contact-plugin'),
            'date' => 'Date',

      );

      return $columns;
}

function create_meta_boxes()
{
    add_meta_box('custom_contact_form', 'Submission', 'display_submission', 'submission');
}

function display_submission()
{
    // $postmetas = get_post_meta(get_the_ID());

    // unset($postmetas['_edit_lock']);

    // echo '<ul>';

    // foreach ($postmetas as $key => $value) {
    //     echo '<li><strong>' . ucfirst($key) . '</strong>:</br>' . $value[0] . '</li>';
    // }

    // echo '</ul>';

    echo '<ul>';

    echo '<li><strong>Name:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'name', true)) . '</li>';
    echo '<li><strong>Email:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'email', true)) . '</li>';
    echo '<li><strong>Phone:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'phone', true)) . '</li>';
    echo '<li><strong>Message:</strong><br /> ' . esc_html(get_post_meta(get_the_ID(), 'message', true)) . '</li>';

    echo '</ul>';
}

function create_submissions_page()
{
    $args = array(
        'public' => true,
        'has_archive' => true,
        'menu_position' => 30,
        'publicly_queryable' => false,
        'labels' => [
            'name' => 'Submissions',
            'singular_name' => 'Submission',
            'edit_item' => 'View Submission'
        ],
        'supports' => false,
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => false,
        ),
        'map_meta_cap' => true
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
    // Create endpoint for front end to connect to WordPress securely to post form data
    register_rest_route(
        'v1/contact-form',
        'submit',
        array(

            'methods' => 'POST',
            'callback' => 'handle_enquiry'

        )
    );
}

function handle_enquiry($data)
{
    // return new WP_REST_Response('Message not sent', 422);

    // Gte all parameters from form
    $params = $data->get_params();

    // set fields from the form
    $field_name = sanitize_text_field($params['name']);
    $field_email = sanitize_email($params['email']);
    $field_phone = sanitize_text_field($params['phone']);
    $field_message = sanitize_text_field($params['message']);

    // Check if nonce is valid
    if (!isset($params['_wpnonce']) || !wp_verify_nonce($params['_wpnonce'], 'wp_rest')) {
        return new WP_REST_Response('Message not sent', 422);
    }

    // Remove unneeded data from parameters
    unset($params['_wponce']);
    unset($params['_wp_http_referer']);

    // send the email message
    $headers = [];

    $admin_email = get_bloginfo('admin_email');
    $admin_name = get_bloginfo('name');


    // set recipient email
    $recipient__email = get_plugin_options('correct_plugin_recipients');

    if (!$recipient__email) {
        $recipient__email = strtolower(trim($recipient__email));
    } else {
        $recipient__email = $admin_email;
    }

    $headers[] = "From: {$admin_email} <{$admin_name}>";
    $headers[] = "Reply-To: {{$field_name}} <{{$field_email}}>";
    $headers[] = "Content-Type: text/html";

    $subject = 'New enquiry from ' . $field_name;

    $message = '';
    $message .= "<h1>Message has been sent from {$field_name}</h1>";

    $postarr = [
        'post_title' => $field_name,
        'post_type' => 'submission',
        'post_status' => 'publish',
    ];

    $post_id = wp_insert_post($postarr);

    foreach ($params as $label => $value) {

        switch ($label) {
            case 'message':
                $value = sanitize_textarea_field($value);
                break;
            case 'email':
                $value = sanitize_email($value);
                break;
            default:
                $value = sanitize_text_field($value);
        }

        add_post_meta($post_id, sanitize_text_field($label), $value);

        $message .= '<strong>' . sanitize_text_field(ucfirst($label)) . '</strong>:' . $value . '<br/>';
    }

    wp_mail($recipient__email, $subject, $message, $headers);

    $confirmation_message = "The meesage was sent successfully!!";

    if (get_plugin_options('contact_plugin_message')) {
        $confirmation_message = get_plugin_options('contact_plugin_message');
        $confirmation_message = str_replace('{name}', $field_name, $confirmation_message);
    }

    return new WP_REST_Response('The  message was sent successfully', 200);
}

