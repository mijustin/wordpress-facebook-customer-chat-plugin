<?php
/*
Plugin Name: Facebook Customer Chat Plugin
Version: 1.0.1
Plugin URI: https://megamaker.co/facebook-customer-chat-wordpress-plugin/
Author: Justin Jackson
Author URI: https://justinjackson.ca
Description: Use the new Facebook Messenger Platform. Easily embed Facebook Messenger chat in your site without redirecting to Facebook.
Text Domain: wordpress-facebook-customer-chat-plugin
Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('FB_CHAT_PLUGIN')) {

    class FB_CHAT_PLUGIN {

        var $plugin_version = '1.0.1';

        function __construct() {
            define('FB_CHAT_PLUGIN_VERSION', $this->plugin_version);
            $this->plugin_includes();
        }

        function plugin_includes() {
            if (is_admin()) {
                add_filter('plugin_action_links', array($this, 'plugin_action_links'), 10, 2);
            }
            add_action('plugins_loaded', array($this, 'plugins_loaded_handler'));
            add_action('admin_init', array($this, 'settings_api_init'));
            add_action('admin_menu', array($this, 'add_options_menu'));
            add_action('wp_head', array($this, 'add_tracking_code'));
            add_filter('emd_custom_link_attributes', array($this, 'emd_custom_link_attributes'), 10, 2);
        }

        function plugins_loaded_handler()
        {
            load_plugin_textdomain('wordpress-facebook-customer-chat-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
        }

        function plugin_url() {
            if ($this->plugin_url)
                return $this->plugin_url;
            return $this->plugin_url = plugins_url(basename(plugin_dir_path(__FILE__)), basename(__FILE__));
        }

        function plugin_action_links($links, $file) {
            if ($file == plugin_basename(dirname(__FILE__) . '/main.php')) {
                $links[] = '<a href="options-general.php?page=fb-chat-settings">'.__('Settings', 'wordpress-facebook-customer-chat-plugin').'</a>';
            }
            return $links;
        }
        function add_options_menu() {
            if (is_admin()) {
                add_options_page(__('Facebook Messenger Platform', 'wordpress-facebook-customer-chat-plugin'), __('Facebook Messenger Platform', 'wordpress-facebook-customer-chat-plugin'), 'manage_options', 'fb-chat-settings', array($this, 'options_page'));
            }
        }
        function settings_api_init(){
            	register_setting( 'fbchatpage', 'fb_chat_settings' );

                add_settings_section(
                        'fb_chat_section',
                        __('General Settings', 'wordpress-facebook-customer-chat-plugin'),
                        array($this, 'fb_chat_settings_section_callback'),
                        'fbchatpage'
                );

                add_settings_field(
                        'fb_id',
                        __('Tracking ID', 'wordpress-facebook-customer-chat-plugin'),
                        array($this, 'fb_id_render'),
                        'fbchatpage',
                        'fb_chat_section'
                );
        }
        function fb_id_render() {
            $options = get_option('fb_chat_settings');
            ?>
            <input type='text' name='fb_chat_settings[fb_id]' value='<?php echo $options['fb_id']; ?>'>
            <p class="description"><?php printf(__('Enter your Facebook Page ID.', 'wordpress-facebook-customer-chat-plugin'), '178765444010');?></p>
            <?php
        }
        function fb_chat_settings_section_callback() {
                //echo __( 'This section description', 'fbchat' );
        }

        function options_page() {
            $url = "https://megamaker.co/facebook-customer-chat-wordpress-plugin/";
            $link_text = sprintf(wp_kses(__('Please visit the <a target="_blank" href="%s">Facebook Customer Chat WordPress Plugin</a> documentation page for usage instructions.', 'wordpress-facebook-customer-chat-plugin'), array('a' => array('href' => array(), 'target' => array()))), esc_url($url));
            ?>
            <div class="wrap">
            <h2>Facebook Customer Chat WordPress Plugin - v<?php echo $this->plugin_version; ?></h2>
            <div class="update-nag"><?php echo $link_text;?></div>
            <form action='options.php' method='post'>
            <?php
            settings_fields( 'fbchatpage' );
            do_settings_sections( 'fbchatpage' );
            submit_button();
            ?>
            </form>
            </div>
            <?php
        }

        function is_logged_in(){
            $is_logged_in = false;
            if(is_user_logged_in()){ //the user is logged in
                if(current_user_can('editor') || current_user_can('administrator')){
                    $is_logged_in = true;
                }
            }
            return $is_logged_in;
        }

        function add_tracking_code() {
            if(!$this->is_logged_in()) {
                $options = get_option( 'fb_chat_settings' );
                $tracking_id = $options['fb_id'];
                if(isset($tracking_id) && !empty($tracking_id)){
                    $ouput = <<<EOT
                    <!-- Tracking code generated with Facebook Chat plugin v{$this->plugin_version} -->
                    <script>
                    window.fbAsyncInit = function() {
                      FB.init({
                        appId            : '1820043301628783',
                        autoLogAppEvents : true,
                        xfbml            : true,
                        version          : 'v2.11'
                      });
                    };

                    (function(d, s, id){
                       var js, fjs = d.getElementsByTagName(s)[0];
                       if (d.getElementById(id)) {return;}
                       js = d.createElement(s); js.id = id;
                       js.src = "https://connect.facebook.net/en_US/sdk.js";
                       fjs.parentNode.insertBefore(js, fjs);
                     }(document, 'script', 'facebook-jssdk'));
                  </script>
                    <div class="fb-customerchat"
                     page_id="$tracking_id">
                    </div>
                    <!-- / Facebook Chat plugin -->
EOT;

                    echo $ouput;
                }
            }
        }

    }

    $GLOBALS['FB_CHAT_PLUGIN'] = new FB_CHAT_PLUGIN();
}
