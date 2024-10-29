<?php
/*
    Plugin Name: Alt Text Describer
    Description: Autogenerate alternative text of images in bulk for better SEO.
    Version: 1.06
    Author: Prisakaru
    Author URI: https://prisakaru.lt
    License: GPLv3
    License URI: https://www.gnu.org/licenses/gpl-3.0.html
*/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
require_once ABSPATH.'wp-admin/includes/class-wp-list-table.php';
require_once plugin_dir_path(__FILE__).'includes/class-database-operations.php';
require_once plugin_dir_path(__FILE__).'includes/class-image-operations.php';
require_once plugin_dir_path(__FILE__).'includes/class-requests-operations.php';
require_once plugin_dir_path(__FILE__).'includes/class-image-list-table.php';

add_action('admin_menu', 'prisakaru_atd_menu');
add_action('plugins_loaded', 'prisakaru_atd_init_plugin');
add_action('admin_enqueue_scripts', 'prisakaru_atd_enqueue_custom_scripts_and_styles');
add_action('wp_ajax_generate_alt_for_images', 'prisakaru_atd_generate_alt_for_images');
add_action('wp_ajax_generate_alt_for_all_images', 'prisakaru_atd_generate_alt_for_all_images');
add_action('add_attachment', 'prisakaru_atd_generate_alt_on_upload');
add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'prisakaru_atd_add_settings_link');
add_filter('plugin_row_meta', 'prisakaru_atd_custom_author_link', 10, 2);


/**
 * Creates links in plugins settings
 *
 * @param array $links Array of links.
 */
function prisakaru_atd_add_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=alt-text-describer">Settings</a>';
    $faq           = '<a href="https://prisakaru.lt/faq/">FAQ</a>';
    array_push($links, $settings_link, $faq);
    return $links;

}//end prisakaru_atd_add_settings_link()


/**
 * Creates author links in plugins settings
 *
 * @param array  $links Array of links.
 * @param string $file  Filebase.
 */
function prisakaru_atd_custom_author_link($links, $file)
{
    $plugin_file = plugin_basename(__FILE__);

    if ($file === $plugin_file) {
        $author_link = '<a href="https://prisakaru.lt">Prisakaru</a>';
        foreach ($links as $key => $value) {
            if (strpos($value, 'Prisakaru') !== false) {
                $links[$key] = str_replace('Prisakaru', $author_link, $value);
            }
        }

        $new_links = [
            '<a href="https://prisakaru.lt/#contact-container">Suggest a feature</a>',
            '<a href="https://prisakaru.lt/plugin/">Changelog</a>',
        ];
        $links     = array_merge($links, $new_links);
    }

    return $links;

}//end prisakaru_atd_custom_author_link()


/**
 * Generates alt texts for images only without alt
 */
function prisakaru_atd_generate_alt_for_images()
{
    $nonce = isset($_POST['nonce']) ? sanitize_key($_POST['nonce']) : '';
    if (! $nonce || ! wp_verify_nonce($nonce, 'pris-vv144-477')) {
        die('Unauthorized request!');
    }

    $req_operations = new prisakaru_atd_Requests_Operations();
    $language       = get_option('prisakaru_alt_describer_lang', 'English');
    $req_operations->generate_alt_for_images($language);

}//end prisakaru_atd_generate_alt_for_images()


/**
 * Generates alt text for all images
 */
function prisakaru_atd_generate_alt_for_all_images()
{
    if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'pris-vv144-477')) {
        die('Unauthorized request!');
    }

    $req_operations = new prisakaru_atd_Requests_Operations();
    $language       = get_option('prisakaru_alt_describer_lang', 'English');
    $req_operations->generate_alt_for_all_images($language);

}//end prisakaru_atd_generate_alt_for_all_images()


/**
 * Plugin initiation function
 */
function prisakaru_atd_init_plugin()
{
    $db_operations = new prisakaru_atd_Database_Operations();
    $db_operations->create_descriptions_table();

}//end prisakaru_atd_init_plugin()


/**
 * Creates admin menu option
 */
function prisakaru_atd_menu()
{
    add_menu_page(
        'Alt Text Describer',
        'Alt Text Describer',
        'manage_options',
        'alt-text-describer',
        'prisakaru_atd_admin_page',
        plugin_dir_url(__FILE__).'assets/icons/plugin_icon.png',
        10
    );

}//end prisakaru_atd_menu()


/**
 * Adds styles and scripts
 */
function prisakaru_atd_enqueue_custom_scripts_and_styles()
{
    wp_enqueue_script('describer-script', plugin_dir_url(__FILE__).'assets/describer-script.js', [ 'jquery' ], '1.0', true);
    wp_localize_script(
        'describer-script',
        'pris_vars',
        [
            'ajax_nonce' => wp_create_nonce('pris-vv144-477'),
        ]
    );
    wp_enqueue_style('describer-style', plugin_dir_url(__FILE__).'assets/describer-style.css', [], '1.0', 'all');

}//end prisakaru_atd_enqueue_custom_scripts_and_styles()


/**
 * Creates tabs in plugin administration panel
 */
function prisakaru_atd_admin_page()
{
    if (isset($_POST['prisakaru_atd_nonce'])) {
        $nonce = sanitize_text_field(wp_unslash($_POST['prisakaru_atd_nonce']));
        if (! wp_verify_nonce($nonce, 'prisakaru_atd_action')) {
            wp_die('Nonce verification failed!');
        }
    }
    ?>
    <div class="wrap">
        <h2></h2>
        <h2 class="nav-tab-wrapper">
        <a href="?page=alt-text-describer&tab=settings" class="nav-tab <?php echo isset($_GET['tab']) && 'settings' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>">Settings</a>
        <a href="?page=alt-text-describer&tab=describer" class="nav-tab <?php echo isset($_GET['tab']) && 'describer' === $_GET['tab'] ? 'nav-tab-active' : ''; ?>">Describer</a>
        </h2>
        <?php
        $active_tab = isset($_GET['tab']) ? sanitize_key(wp_unslash($_GET['tab'])) : 'settings';
        switch ($active_tab) {
            case 'settings':
                prisakaru_atd_settings_page();
            break;

            case 'describer':
                prisakaru_atd_describer_page();
            break;

            default:
                prisakaru_atd_settings_page();
        }
        ?>
    </div>
    <?php

}//end prisakaru_atd_admin_page()


/**
 * Includes admin settings tab content
 */
function prisakaru_atd_settings_page()
{
    include_once plugin_dir_path(__FILE__).'includes/partials/admin-settings-page.php';

}//end prisakaru_atd_settings_page()


/**
 * Includes admin describer tab content
 */
function prisakaru_atd_describer_page()
{
    include_once plugin_dir_path(__FILE__).'includes/partials/admin-describer-page.php';

}//end prisakaru_atd_describer_page()


/**
 * Generates alt text for images on image upload
 *
 * @param string $attachment_id Id of image.
 */
function prisakaru_atd_generate_alt_on_upload($attachment_id)
{
    $setting_on = get_option('prisakaru_describer_on_upload', 'false');
    if ('false' === $setting_on) {
        return;
    }

    $attachment_url = wp_get_attachment_url($attachment_id);
    $language       = get_option('prisakaru_alt_describer_lang', 'English');
    $req_operations = new prisakaru_atd_Requests_Operations();
    $req_operations->make_single_request($attachment_url, $attachment_id, $language);

}//end prisakaru_atd_generate_alt_on_upload()
