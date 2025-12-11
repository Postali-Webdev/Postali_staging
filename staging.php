<?php
/*
* Plugin Name: Postali - Add Staging Banner
* Description: Adds notification banner when placing site into staging. 
* Version: 1.1.5
* Author: Postali Webdev
* Author URI: https://www.postali.com
*/


// ACF Options Pages
add_action('init', 'add_staging_options_function');
function add_staging_options_function() {
    if (function_exists('acf_add_options_page')) {
        $page = acf_add_options_page(array(
            'menu_title' => 'Staging Banner',
            'menu_slug' => 'staging',
            'capability' => 'edit_posts',
            'icon_url'      => 'dashicons-warning', // Add this line and replace the second inverted commas with class of the icon you like
            'redirect' => false
        ));
    }
}

add_action('init', 'add_acf_field_function');
function add_acf_field_function() {

    if( function_exists('acf_add_local_field_group') ):

        acf_add_local_field_group(array (
            'key' => 'group_1',
            'title' => 'Staging URL',
            'fields' => array (
                array (
                    'key' => 'field_stagingURL',
                    'label' => 'Toggle staging banner on',
                    'name' => 'enable_banner',
                    'type' => 'radio',
                    'wrapper' => array (
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'choices' => array(
                        'on' => 'On',
                        'off' => 'Off',
                    ),
                ),
                array (
                    'key' => 'field_enableBanner',
                    'label' => 'Staging URL',
                    'name' => 'staging_url',
                    'type' => 'text',
                    'prefix' => '',
                    'instructions' => 'Please enter the staging domain name. <strong>*IMPORTANT: Do NOT include https:// at the beginning and .com at the end.</strong>',
                    'required' => 0,
                    'wrapper' => array (
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),

                    'conditional_logic' => array (
                        'field' => 'field_stagingURL',
                        'operator' => '==',
                        'value' => 'on',
                    ),

                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                    'readonly' => 0,
                    'disabled' => 0,
                ),

                array (
                    'key' => 'field_stagingUsername',
                    'label' => 'Staging Username',
                    'name' => 'staging_username',
                    'type' => 'text',
                    'prefix' => '',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array (
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),

                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                    'readonly' => 0,
                    'disabled' => 0,
                ),

                array (
                    'key' => 'field_stagingPassword',
                    'label' => 'Staging Password',
                    'name' => 'staging_password',
                    'type' => 'text',
                    'prefix' => '',
                    'instructions' => '',
                    'required' => 0,
                    'wrapper' => array (
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),

                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                    'readonly' => 0,
                    'disabled' => 0,
                ),


                array (
                    'key' => 'field_slack_token',
                    'label' => 'Slack OAuth Token',
                    'name' => 'slack_token',
                    'type' => 'password',
                    'prefix' => '',
                    'instructions' => 'Please enter the Bot User OAuth Token found in your Slack app OAuth & Permissions settings.',
                    'required' => 1,
                    'wrapper' => array (
                        'width' => '',
                        'class' => '',
                        'id' => '',
                    ),
                    'default_value' => '',
                    'placeholder' => '',
                    'prepend' => '',
                    'append' => '',
                    'maxlength' => '',
                    'readonly' => 0,
                    'disabled' => 0,
                )
            ),
            'location' => array (
                array (
                    array (
                        'param' => 'options_page',
                        'operator' => '==',
                        'value' => 'staging',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
        ));
        
    endif;
}

// debug logging function
if (!function_exists('write_log')) {
    function write_log($log) {
        if (true === WP_DEBUG) {
            if (is_array($log) || is_object($log)) {
                error_log(print_r($log, true));
            } else {
                error_log($log);
            }
        }
    }
}



function handle_staging_page_update( $post_id ) {
    $options_page = get_current_screen();
    // Check if the updated post is an options page
    if ( $options_page->id === 'toplevel_page_staging' ) {
        // Get the updated field value
        $staging_status = get_field('enable_banner','options');
        $site_name = get_bloginfo( 'name' ); 
        $oauth_token = get_field('slack_token','options');
        $currentUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        $currentDomain = parse_url($currentUrl, PHP_URL_HOST);
        $currentDomain = str_replace('www.', '', $currentDomain);
        $staging_url = 'https://' . get_field('staging_url','options') . '.com';

        if ( strpos($staging_url, $currentDomain) !== false ) {
            $isStaging = true;
        } else {
            $isStaging = false;
        }

        if( $staging_status == 'on' && !$isStaging ) { 
            $notification_text = json_encode($site_name . ' is currently in staging');
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://slack.com/api/chat.postMessage',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>"
                {
                    'channel': 'C07B0NHHZAA',
                    'text': $notification_text
                }",
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json; charset=utf-8',
                    "Authorization: Bearer $oauth_token"
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            // logging  response
            //write_log( [ 'response' => $response, ] );
        } elseif( $staging_status == 'off' && !$isStaging ) {
            $notification_text = json_encode($site_name . ' is out of staging');
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://slack.com/api/chat.postMessage',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS =>"
                {
                    'channel': 'C07B0NHHZAA',
                    'text': $notification_text
                }",
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json; charset=utf-8',
                    "Authorization: Bearer $oauth_token"
                ),
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            // logging  response
            //write_log( [ 'response' => $response, ] );
        }   
    }
}
add_action('acf/options_page/save', 'handle_staging_page_update', 10, 1);

require_once( 'BFIGitHubPluginUploader.php' );
if ( is_admin() ) {
    new BFIGitHubPluginUpdater( __FILE__, 'Postali-Webdev', "Postali_staging" );
}

?>
