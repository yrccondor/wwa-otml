<?php
/*
Plugin Name: WP-WebAuthn One Time Login Link Provider
Plugin URI: https://flyhigher.top
Description: Provide one-time login link capability as a backup for WP-WebAuthn.
Version: 1.0.0
Author: Axton
Author URI: https://axton.cc
License: GPLv3
Text Domain: wwa-otml
Domain Path: /languages
Network: true
Requires Plugins: wp-webauthn
*/
/* Copyright 2024 Axton
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version  of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/

namespace WPWebAuthn\OTML;
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, __NAMESPACE__.'\init');
register_uninstall_hook(__FILE__, __NAMESPACE__.'\uninstall');

function init() {
    if (version_compare(get_bloginfo('version'), '5.0', '<') || !w_is_plugin_active('wp-webauthn/wp-webauthn.php')) {
        deactivate_plugins(basename(__FILE__)); //disable
    } else {
        init_data();
    }
}

function init_data() {
    if (!get_option('wwaotml_init')) {
        // Init
        include('wwa-otml-email-template.php');
        $wwaotml_init_options = array(
            'magic_link' => 'false',
            'magic_link_expire' => '3',
            'mail_template' => $wwaotml_default_email_template,
        );
        update_option('wwaotml_options', $wwaotml_init_options);
        include('wwa-otml-version.php');
        update_option('wwaotml_version', $wwaotml_version);
        update_option('wwaotml_init', md5(gmdate('Y-m-d H:i:s')));
    } else {
        include('wwa-otml-version.php');
        if (!get_option('wwaotml_version') || get_option('wwaotml_version')['version'] != $wwaotml_version['version']) {
            update_option('wwaotml_version', $wwaotml_version); //update version
        }
    }
}

function uninstall() {
    if (is_multisite()) {
        $sites = get_sites(array('fields' => 'ids'));
        foreach ($sites as $blog_id) {
            switch_to_blog($blog_id);
            uninstall_site();
            restore_current_blog();
        }
    } else {
        uninstall_site();
    }
}

function uninstall_site() {
    delete_option('wwaotml_options');
    delete_option('wwaotml_version');
    delete_option('wwaotml_init');
    delete_option('wwaotml_timed_key_vals');
}

init_data();

function loaded() {
    return true;
}

// Wrap options API
function w_get_option($option_name) {
    $val = get_option('wwaotml_options');
    if (isset($val[$option_name])) {
        return $val[$option_name];
    } else {
        return false;
    }
}

function w_update_option($option_name, $option_value) {
    $options = get_option('wwaotml_options');
    $options[$option_name] = $option_value;
    update_option('wwaotml_options', $options);
    return true;
}

function w_get_option_wwa($option_name) {
    $val = get_option('wwa_options');
    if ($val === false) {
        return false;
    }
    if (isset($val[$option_name])) {
        return $val[$option_name];
    } else {
        return false;
    }
}

function w_is_plugin_active($plugin) {
    return in_array($plugin, (array) get_option('active_plugins', array()), true) || is_plugin_active_for_network($plugin);
}

// Multi-language support
function load_textdomain() {
    load_plugin_textdomain('wwa-otml', false, dirname(plugin_basename(__FILE__)).'/languages');
}
add_action('init', __NAMESPACE__.'\load_textdomain');

// Add CSS and JS in login page
function login_js() {
    $redirect_to = isset($_GET['redirect_to']) ? sanitize_text_field(wp_unslash($_GET['redirect_to'])) : '';
    wp_enqueue_script('wwaotml_login', plugins_url('js/login.js', __FILE__), array(), get_option('wwaotml_version')['version'], true);
    wp_localize_script('wwaotml_login', 'wwaotml_php_vars', array(
        'request_url' => add_query_arg('action', 'wwa_otl', wp_login_url($redirect_to)),
        'separator' => apply_filters('login_link_separator', ' | '),
        'one_time' => __('One Time Login', 'wwa-otml'),
    ));
}
add_action('login_enqueue_scripts', __NAMESPACE__.'\login_js', 999);

// Add log to WP-WebAuthn
function add_log($id, $content = '', $init = false) {
    if (!w_is_plugin_active('wp-webauthn/wp-webauthn.php')) {
        return;
    }
    if (w_get_option_wwa('logging') !== 'true' && !$init) {
        return;
    }
    $log = get_option('wwa_log');
    if ($log === false) {
        $log = array();
    }
    $log[] = '['.current_time('mysql').']['.$id.'] '.$content;
    update_option('wwa_log', $log);
}

// Get user by username or email
function get_user($username) {
    if (w_get_option_wwa('email_login') !== 'true') {
        return get_user_by('login', $username);
    } else {
        if (is_email($username)) {
            return get_user_by('email', $username);
        }
        return get_user_by('login', $username);
    }
}

// Add a timed key-val by name
function add_timed_key_vals($name, $val, $expire) {
    $current = get_option('wwaotml_timed_key_vals');
    if (!$current) {
        $current = array();
    }
    $current = array_filter($current, function($v) {
        return $v['expire'] >= time();
    });
    $current[$name] = array(
        'val' => wp_json_encode($val),
        'expire' => time() + $expire
    );
    update_option('wwaotml_timed_key_vals', $current);
}

// Get a timed key-val by name
function get_timed_key_vals($name) {
    $current = get_option('wwaotml_timed_key_vals');
    if (!$current) {
        return false;
    }
    $current = array_filter($current, function($v) {
        return $v['expire'] >= time();
    });
    update_option('wwaotml_timed_key_vals', $current);
    if (isset($current[$name])) {
        return json_decode($current[$name]['val'], true);
    }
    return false;
}

// Remove a timed key-val by name
function del_timed_key_vals($name) {
    $data = get_option('wwaotml_timed_key_vals');
    if ($data && isset($data[$name])) {
        unset($data[$name]);
        update_option('wwaotml_timed_key_vals', $data);
    }
}

// Check user privileges
function validate_privileges() {
    $user = wp_get_current_user();
    $allowed_roles = array('administrator');
    if (array_intersect($allowed_roles, $user->roles)) {
        return true;
    }
    return false;
}

// Identify browser and platform from User-Agent for email attribution
function get_current_browser() {
    $u_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '';

    $platform = '';
    $platform_patterns = array(
        'Android'   => '/Android/i',
        'iOS'       => '/iPhone|iPad|iPod/i',
        'Chrome OS' => '/\bCrOS\b/',
        'Windows'   => '/Windows/i',
        'Mac OS'    => '/Macintosh|Mac OS X/i',
        'Linux'     => '/Linux/i',
    );
    foreach ($platform_patterns as $label => $pattern) {
        if (preg_match($pattern, $u_agent)) {
            $platform = $label;
            break;
        }
    }

    $browser = '';
    $browser_patterns = array(
        'Opera'             => '/OPR\/|Opera/i',
        'Microsoft Edge'    => '/Edg[e\/]/i',
        'Mozilla Firefox'   => '/Firefox/i',
        'Google Chrome'     => '/Chrome/i',
        'Apple Safari'      => '/Safari/i',
        'Internet Explorer' => '/MSIE|Trident/i',
    );
    foreach ($browser_patterns as $label => $pattern) {
        if (preg_match($pattern, $u_agent)) {
            $browser = $label;
            break;
        }
    }

    if ($browser !== '' && $platform !== '') {
        $name = $browser.' / '.$platform;
    } elseif ($browser !== '') {
        $name = $browser;
    } else {
        $name = __('Unknown Browser', 'wwa-otml');
    }

    if (!is_user_logged_in()) {
        $requester_ip = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : false;
        if ($requester_ip) {
            $name .= ' ('.$requester_ip.')';
        }
    }
    return $name;
}

// Create random strings for user ID
function generate_random_string($length = 10) {
    // Use cryptographically secure pseudo-random generator in PHP 7+
    if (function_exists('random_bytes')) {
        $bytes = random_bytes(round($length / 2));
        return bin2hex($bytes);
    } else {
        // Not supported, use normal random generator instead
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[wp_rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
}

function base64_encode_url($string) {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($string));
}

function base64_decode_url($string) {
    return base64_decode(str_replace(['-', '_'], ['+', '/'], $string));
}

// Create one-time login URL
function create_onetime_login_url($source, $user, $redirect_to = '') {
    $key = base64_encode_url(wp_generate_password(72, true, true));
    $url = add_query_arg(array(
        'action' => 'wwa_opl_login',
        'wwa_token' => urlencode($key),
        'wwa_user' => urlencode($user),
    ), admin_url('admin-ajax.php'));

    if ($redirect_to !== '') {
        $url = add_query_arg('redirect_to', urlencode($redirect_to), $url);
    }

    $expire = intval(w_get_option('magic_link_expire')) * 60;
    add_timed_key_vals('otlu_'.$user, hash('sha256', $key), $expire);
    add_log(
        generate_random_string(5),
        'one_time_link: One-time login URL created for user "'.$user.'", usage => "'.$source.'", expire => "'.gmdate('Y-m-d H:i:s', current_time('timestamp') + $expire).'"'
    );
    return $url;
}

// Build email contents from the template
function build_otl_mail_content($user_email, $user_login, $link, $generated_by) {
    $mail_content = htmlspecialchars_decode(w_get_option('mail_template'));
    $mail_content = str_replace('{% useremail %}', $user_email, $mail_content);
    $mail_content = str_replace('{% homeurl %}', home_url(), $mail_content);
    $mail_content = str_replace('{% expiretime %}', w_get_option('magic_link_expire'), $mail_content);
    $mail_content = str_replace('{% sitename %}', get_bloginfo('name'), $mail_content);
    $mail_content = str_replace('{% username %}', $user_login, $mail_content);
    $mail_content = str_replace('{% generatedtime %}', current_time('mysql'), $mail_content);
    $mail_content = str_replace('{% generatedby %}', $generated_by, $mail_content);

    $mail_content = str_replace('{% loginurl %}', $link, $mail_content);

    return $mail_content;
}

// Send magic link after user registration
function new_user_notification_email($wp_new_user_notification_email, $user, $blogname) {
    if (w_get_option_wwa('after_user_registration') === 'mail') {
        $user_login = stripslashes($user->user_login);
        $user_email = stripslashes($user->user_email);

        $mail_content = build_otl_mail_content($user_email, $user_login, create_onetime_login_url('register', $user_login), __('registration', 'wwa-otml'));

        /* translators: %s: blog name */
        $wp_new_user_notification_email['subject'] = sprintf(__('[%s] Please login following the one time link', 'wwa-otml'), $blogname);
        $wp_new_user_notification_email['headers'] = array('Content-Type: text/html; charset=UTF-8');
        $wp_new_user_notification_email['message'] = $mail_content;
    }

    return $wp_new_user_notification_email;
}
if (w_get_option_wwa('after_user_registration') === 'mail') {
    add_filter('wp_new_user_notification_email', __NAMESPACE__.'\new_user_notification_email', 10, 3);
}

// Display a custom form for requesting the one time login link
function add_otl_form() {
    $http_post = ('POST' === (isset($_SERVER['REQUEST_METHOD']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_METHOD'])) : false));
    $errors = new \WP_Error();
    $redirect_to = isset($_GET['redirect_to']) ? sanitize_url(wp_unslash($_GET['redirect_to'])) : '';
    if ($http_post) {
        $error = false;

        if (isset($_POST['user_login']) && trim(sanitize_text_field(wp_unslash($_POST['user_login']))) !== '') {
            if (w_get_option('magic_link') === 'true') {
                $user = get_user(trim(sanitize_text_field(wp_unslash($_POST['user_login']))));
                if ($user) {
                    $user_login = stripslashes($user->user_login);
                    $user_email = stripslashes($user->user_email);

                    $mail_content = build_otl_mail_content($user_email, $user_login, create_onetime_login_url('login', $user_login, $redirect_to), get_current_browser());

                    /* translators: %s: blog name */
                    if (!wp_mail($user->user_email, wp_specialchars_decode(sprintf(__('[%s] Please login following the one time link', 'wwa-otml'), get_bloginfo('name'))), $mail_content, array('Content-Type: text/html; charset=UTF-8'))) {
                        $errors->add(
                            'opl_email_failure',
                            __('<strong>Error:</strong> The email could not be sent. Your site may not be correctly configured to send emails.', 'wwa-otml')
                        );
                        $error = true;
                    }
                }
            }
        } else {
            wp_safe_redirect(add_query_arg(array(
                'action' => 'wwa_otl',
                'empty' => 'true',
            ), wp_login_url($redirect_to)));
        }

        login_header(__('Request One Time Login Link', 'wwa-otml'), '', $errors);

        if (!$error) {
        ?>
            <form name="lostpasswordform" id="lostpasswordform" class="wwa-otml-form">
                <p>
                    <?php esc_html_e('An email message has been sent to the corresponding email address. Check your inbox and follow the instructions.', 'wwa-otml'); ?>
                </p>
            </form>
        <?php } else { ?>
            <form name="lostpasswordform" id="lostpasswordform" class="wwa-otml-form">
                <p>
                    <?php esc_html_e('Email not sent.', 'wwa-otml'); ?>
                </p>
                <p class="submit">
                    <a id="wp-submit" class="button button-primary button-large" href="<?php echo esc_url(add_query_arg(array('action' => 'wwa_otl'), wp_login_url($redirect_to))); ?>"><?php esc_html_e('Go Back', 'wwa-otml'); ?></a>
                </p>
            </form>
        <?php } ?>

        <p id="nav">
            <a href="<?php echo esc_url(wp_login_url($redirect_to)); ?>"><?php esc_html_e('Log in'); ?></a>
            <?php
            if (get_option('users_can_register')) {
                $registration_url = sprintf('<a href="%s">%s</a>', esc_url(wp_registration_url()), __('Register'));
                echo esc_html(apply_filters('login_link_separator', ' | '));
                echo apply_filters('register', $registration_url);
            }
            echo esc_html(apply_filters('login_link_separator', ' | '));
            $html_link = sprintf('<a href="%s">%s</a>', esc_url(wp_lostpassword_url($redirect_to)), __('Lost your password?'));
            echo apply_filters('lost_password_html_link', $html_link);
            ?>
        </p>
        <?php
        login_footer('user_login');
        exit;
    }

    if (isset($_GET['invalid']) && $_GET['invalid'] === 'true') {
        $errors->add('invalidkey', __('<strong>Error:</strong> Your one time login link appears to be invalid. Please request a new link below.', 'wwa-otml'));
    }
    if (isset($_GET['empty']) && $_GET['empty'] === 'true') {
        $errors->add('empty_username', __('<strong>Error:</strong> Please enter a username or email address.'));
    }

    login_header(__('Request One Time Login Link', 'wwa-otml'), '<p class="message">'.__('Please enter your username or email address. You will receive an email message with a one time login link.', 'wwa-otml').'</p>', $errors);
    ?>

    <?php if (w_get_option('magic_link') === 'true') { ?>
        <form name="lostpasswordform" id="lostpasswordform" action="<?php echo esc_url(add_query_arg(array('action' => 'wwa_otl'), wp_login_url($redirect_to))); ?>" method="post" class="wwa-otml-form">
            <p>
                <label for="user_login"><?php esc_html_e('Username or Email Address'); ?></label>
                <input type="text" name="user_login" id="user_login" class="input" value="" size="20" autocapitalize="off" autocomplete="username" required="required">
            </p>
            <p class="submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php esc_attr_e('Request Link', 'wwa-otml'); ?>">
            </p>
        </form>
    <?php } else { ?>
        <form name="lostpasswordform" id="lostpasswordform" class="wwa-otml-form">
            <p>
                <?php esc_html_e('You cannot create one time login links now. Please contact the site administrator.', 'wwa-otml'); ?>
            </p>
        </form>
    <?php } ?>

    <p id="nav">
        <a href="<?php echo esc_url(wp_login_url($redirect_to)); ?>"><?php esc_html_e('Log in'); ?></a>
        <?php
        if (get_option('users_can_register')) {
            $registration_url = sprintf('<a href="%s">%s</a>', esc_url(wp_registration_url()), __('Register'));
            echo esc_html(apply_filters('login_link_separator', ' | '));
            echo apply_filters('register', $registration_url);
        }
        echo esc_html(apply_filters('login_link_separator', ' | '));
        $html_link = sprintf('<a href="%s">%s</a>', esc_url(wp_lostpassword_url($redirect_to)), __('Lost your password?'));
        echo apply_filters('lost_password_html_link', $html_link);
        ?>
    </p>
    <?php
    login_footer('user_login');
    exit;
}
add_filter('login_form_wwa_otl', '__return_true');
add_action('login_form_wwa_otl', __NAMESPACE__.'\add_otl_form');

include('wwa-otml-admin.php');
include('wwa-otml-ajax.php');
