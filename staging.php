<?php
/*
* Plugin Name: Postali - Add Staging Banner
* Description: Adds notification banner when placing site into staging. 
* Version: 1.1
* Author: Postali
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


// Add Staging Lightbox
function staging_admin_notice() {
    $staging_status = get_field('enable_banner','options');
    $staging_username = get_field('staging_username','options');
    $staging_password = get_field('staging_password','options');
    $site_name = get_bloginfo( 'name' ); 
    $currentUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $currentDomain = parse_url($currentUrl, PHP_URL_HOST);
    $currentDomain = str_replace('www.', '', $currentDomain);
    $staging_url = 'https://' . get_field('staging_url','options') . '.com';
    if ( strpos($staging_url, $currentDomain) !== false ) {
        $isStaging = true;
    } else {
        $isStaging = false;
    }
    if( $staging_status == 'on' && !$isStaging) : ?>
        <div id="notice-overlay">~
            <div id="staging-notice">
                <div id="notice-top">
                    <div id="notice-top-inner"><span class="notice-icon" id="notice-alert">&#9888; </span><span id="notice-title">POSTALI NOTICE:</span></div>
                    <p>Site is currently in staging</p> 
                    <span class="notice-icon" id="notice-close">&#10005;</span>
                </div>
                <div id="notice-bottom">
                    <p>Proceed to the staging url before making changes: <a href="<?php echo $staging_url; ?>" title="Staging url"><?php echo $staging_url; ?></a></p>
                    <p><strong>Staging Username:</strong> <?php echo $staging_username; ?> &nbsp; | &nbsp; <strong>Staging Password:</strong> <?php echo $staging_password; ?></p>
                    <p>Changes made here may be overwritten and lost. </br>Contact Development for more information.</p>
                </div>
            </div>
        </div>
        <?php wp_enqueue_style( 'styles', '/wp-content/plugins/Postali_staging-main/staging.css'); ?>
        <script>
            jQuery( function($){ 
                $('#notice-close').click( function() {
                    $('#notice-overlay').css('display', 'none');
                    $('#wpcontent').css('overflow', 'revert').css('position', 'revert').css('padding-left', '20px');
                });
            });
        </script>
    <?php endif;
}
add_action('admin_notices', 'staging_admin_notice');



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


// Automatic plugin updates from the GitHub repository
function automatic_GitHub_updates($data) {
    // plugin information
    //$plugin   = basename(get_template_directory()); // Folder name of the current plugin
    $plugin = 'postali_staging';
    $current = wp_get_plugin()->get('Version'); // Get the version of the current plugin
    // GitHub information	    
    $user = 'Postali-Webdev'; // The GitHub username hosting the repository
    $repo = 'Postali_staging'; // Repository name as it appears in the URL
    $token = get_field('github_token', 'option'); //https://github.com/settings/personal-access-tokens/new
    $file = @json_decode(@file_get_contents('https://api.github.com/repos/'.$user.'/'.$repo.'/releases/latest', false,
            stream_context_create(['http' => ['header' => "User-Agent: ".$user."\r\nAuthorization: token $token\r\n"]])
        ));

    if($file) {
        $zip_source = $file->zipball_url;
        $update = filter_var($file->tag_name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        // Only return a response if the new version number is higher than the current version
        if($update > $current) {
            $data->response[$plugin] = array(
                'plugin'       =>  'Postali_staging-main',
                // Strip the version number of any non-alpha characters (excluding the period)
                // This way you can still use tags like v1.1 or ver1.1 if desired
                'new_version' => $update,
                'url'         => 'https://github.com/'.$user.'/'.$repo,
                'package'     => $zip_source
            );
        }
    }
    return $data;

    echo $data;    
}
add_filter('pre_set_site_transient_update_plugins', 'automatic_GitHub_updates', 100, 1);



function rename_updated_plugin($true, $hook_extra, $result) {
    // Check if the updated plugin is the one we want to rename
    if ($hook_extra['plugin'] === 'Postali_staging-main') {
        // Get the path to the updated plugin
        $plugin_dir = $result['destination'];
        // Rename the plugin directory
        $new_plugin_dir = WP_CONTENT_DIR . '/plugins/Postali_staging-main';
        if (rename($plugin_dir, $new_plugin_dir)) {
            // Rename successful
        } else {
            // Rename failed, log an error
            error_log("Failed to rename plugin directory to $new_plugin_dir");
        }   
    }
}
add_action('upgrader_post_install', 'rename_updated_plugin', 10, 3);



add_filter('http_request_args', function($parsed_args, $url) {
    $token = get_field('github_token', 'option'); //https://github.com/settings/personal-access-tokens/new
    $user = 'Postali-Webdev'; // The GitHub username hosting the repository
    $repo = 'Postali_staging'; // Repository name as it appears in the URL

    if (str_contains($url, "$user/$repo")) {
        $parsed_args['headers'] = ['Authorization'=> 'token '.$token];
    }
    return $parsed_args;
}, 10, 2);


?>
