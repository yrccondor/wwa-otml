<?php
namespace WPWebAuthn\OTML;
if (!defined('ABSPATH')) exit;

// Login by one time link
function ajax_opl_login(){
    $res_id = generate_random_string(5);
    nocache_headers();

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- One-time login link from email; token-based auth, nonce not applicable.
    $redirect_to = isset($_GET['redirect_to']) ? sanitize_text_field(wp_unslash($_GET['redirect_to'])) : '';

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- One-time login link from email; token-based auth, nonce not applicable.
    if (!isset($_GET['wwa_user']) || !isset($_GET['wwa_token'])) {
        wp_safe_redirect(wp_login_url($redirect_to, true));
        exit;
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- One-time login link from email; token-based auth, nonce not applicable.
    $wwa_user = sanitize_text_field(wp_unslash($_GET['wwa_user']));
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- One-time login link from email; token-based auth, nonce not applicable.
    $wwa_token = sanitize_text_field(wp_unslash($_GET['wwa_token']));

    $get_user = get_user($wwa_user);
    if ($get_user === false) {
        add_log($res_id, 'one_time_link: (ERROR)User "'.$wwa_user.'" not found, exit');
        wp_safe_redirect(add_query_arg(array(
            'action' => 'wwa_otl',
            'invalid' => 'true',
        ), wp_login_url($redirect_to)));
        exit;
    }

    // Verify token using constant-time comparison against stored hash
    $stored_hash = get_timed_key_vals('otlu_'.$get_user->user_login);
    if (!is_string($stored_hash) || !hash_equals($stored_hash, hash('sha256', $wwa_token))) {
        add_log($res_id, 'one_time_link: (ERROR)Token for user "'.$get_user->user_login.'" is not verified, exit');
        wp_safe_redirect(add_query_arg(array(
            'action' => 'wwa_otl',
            'invalid' => 'true',
        ), wp_login_url($redirect_to)));
        exit;
    }

    // Token verified — consume immediately to prevent replay
    del_timed_key_vals('otlu_'.$get_user->user_login);

    // If already logged in as the same user, just redirect
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        if ($current_user->user_login === $get_user->user_login) {
            add_log($res_id, 'one_time_link: User "'.$get_user->user_login.'" is already logged in, redirect');
            wp_safe_redirect($redirect_to ? $redirect_to : admin_url());
            exit;
        }
        wp_logout();
    }

    // Log the user in
    wp_set_current_user($get_user->ID, $get_user->user_login);
    wp_set_auth_cookie($get_user->ID, false, is_ssl());
    do_action('wp_login', $get_user->user_login, $get_user);

    add_log($res_id, 'one_time_link: Log user "'.$get_user->user_login.'" in by one time login link');

    wp_safe_redirect($redirect_to ? $redirect_to : admin_url());
    exit;
}
add_action('wp_ajax_wwa_opl_login' , __NAMESPACE__.'\ajax_opl_login');
add_action('wp_ajax_nopriv_wwa_opl_login' , __NAMESPACE__.'\ajax_opl_login');
