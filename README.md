# Contact Plugin

**Plugin Name:** Contact Plugin  
**Description:** Plugin for my custom contact form  
**Version:** 1.0.0  
**Text Domain:** contact-plugin  

## Description

The Contact Plugin is a simple and efficient solution to add a custom contact form to your WordPress website. This plugin provides an easy-to-use contact form that allows users to send messages directly to the site administrator.

## Features

- Easy integration with a shortcode
- Customizable form fields
- Nonce verification for secure submissions
- AJAX form submission
- Email notifications to the site administrator

## Installation

1. Download the plugin zip file and unzip it.
2. Upload the `contact-plugin` directory to the `/wp-content/plugins/` directory.
3. Activate the plugin through the 'Plugins' menu in WordPress.
4. Ensure that your theme or child theme has `wp_head()` and `wp_footer()` functions in the appropriate template files.

## Usage

1. Create a new page or post where you want to display the contact form.
2. Add the following shortcode to the content area: `[contact]`.
3. Publish the page or post to display the contact form on your site.

## Customization

You can customize the appearance of the contact form by modifying the `contact-form.css` file located in the `includes/templates/` directory.

## Plugin Files

- `contact-plugin.php` - Main plugin file
- `includes/contact-form.php` - Contains the contact form shortcode and AJAX handler
- `includes/templates/contact-form.php` - The contact form template
- `includes/templates/contact-form.css` - CSS file for styling the contact form
- `vendor/autoload.php` - Autoload file for dependencies (if any)

## Support

For support and troubleshooting, please visit the plugin's support page on [WordPress.org](https://wordpress.org/support/plugin/contact-plugin).

## Changelog

### 1.0.0
- Initial release

## License

This plugin is licensed under the GPLv2 or later.

