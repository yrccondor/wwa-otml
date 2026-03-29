<?php
namespace WPWebAuthn\OTML;
if (!defined('ABSPATH')) exit;

function init_logger($content = array()) {
    $content[] = 'One time login link provider registered, version => '.get_option('wwaotml_version')['version'];
    return $content;
}
add_filter('wwa_logger_init', __NAMESPACE__.'\init_logger', 10, 1);

function save_settings($res_id) {
    if(
        (isset($_POST['wwa_ref']) && $_POST['wwa_ref'] === 'true')
        && check_admin_referer('wwa_options_update')
        && validate_privileges()
        && (isset($_POST['magic_link']) && ($_POST['magic_link'] === 'true' || $_POST['magic_link'] === 'false'))
        && (isset($_POST['magic_link_expire']) && is_numeric($_POST['magic_link_expire']))
        && isset($_POST['mail_template'])
    ){
        $post_magic_link = sanitize_text_field(wp_unslash($_POST['magic_link']));
        if ($post_magic_link !== w_get_option('magic_link')) {
            add_log($res_id, 'magic_link: "'.w_get_option('magic_link').'"->"'.$post_magic_link.'"');
        }
        w_update_option('magic_link', $post_magic_link);

        $post_magic_link_expire = intval(sanitize_text_field(wp_unslash($_POST['magic_link_expire'])));
        if ($post_magic_link_expire < 1) {
            $post_magic_link_expire = 1;
        }
        if ($post_magic_link_expire > 30) {
            $post_magic_link_expire = 30;
        }
        $post_magic_link_expire = strval($post_magic_link_expire);
        if ($post_magic_link_expire !== w_get_option('magic_link_expire')) {
            add_log($res_id, 'magic_link_expire: "'.w_get_option('magic_link_expire').'"->"'.$post_magic_link_expire.'"');
        }
        w_update_option('magic_link_expire', $post_magic_link_expire);

        include('wwa-otml-allowed-tags.php');

        $post_mail_template = htmlspecialchars(wpautop(wp_kses(wp_unslash($_POST['mail_template']), $wwaotml_allowed_tags)));
        if($post_mail_template !== w_get_option('mail_template')){
            add_log($res_id, 'mail_template: (updated)');
        }
        w_update_option('mail_template', $post_mail_template);
    }
}
add_action('wwa_save_settings', __NAMESPACE__.'\save_settings', 10, 1);

function admin_page_part() {
    $wwaotml_v_ml = w_get_option('magic_link');
    if ($wwaotml_v_ml === false) {
        w_update_option('magic_link', 'false');
        $wwaotml_v_ml = 'false';
    }

    $wwaotml_v_mle = w_get_option('magic_link_expire');
    if ($wwaotml_v_mle === false) {
        w_update_option('magic_link_expire', '3');
        $wwaotml_v_mle = '3';
    }

    $wwaotml_v_mt = w_get_option('mail_template');
    if ($wwaotml_v_mt === false) {
        include('wwa-otml-email-template.php');
        w_update_option('mail_template', $wwaotml_default_email_template);
        $wwaotml_v_mt = $wwaotml_default_email_template;
    }

    wp_enqueue_script('wwaotml_admin', plugins_url('js/admin.js', __FILE__), array(), get_option('wwaotml_version')['version'], true);
    wp_enqueue_style('wwaotml_admin', plugins_url('css/admin.css', __FILE__), array(), get_option('wwaotml_version')['version']);
?>
<tr>
    <th scope="row"><label for="magic_link"><?php esc_html_e('Allow user login by login link via email', 'wwa-otml');?></label></th>
    <td>
        <fieldset>
            <label><input type="radio" name="magic_link" value="true" <?php if($wwaotml_v_ml === 'true'){?>checked="checked"<?php }?>> <?php esc_html_e('Enable', 'wp-webauthn'); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch ?></label><br>
            <label><input type="radio" name="magic_link" value="false" <?php if($wwaotml_v_ml === 'false'){?>checked="checked"<?php }?>> <?php esc_html_e('Disable', 'wp-webauthn'); // phpcs:ignore WordPress.WP.I18n.TextDomainMismatch ?></label><br>
            <p class="description"><?php _e('Allow users to request one-time login links if they lose their authenticator. The link will be sent to user\'s email address on request.<br><strong>Using one-time login links, users can login directly without WebAuthn authentication.</strong>', 'wwa-otml');?></p>
        </fieldset>
    </td>
</tr>
<tr>
    <th scope="row"><label for="magic_link_expire"><?php esc_html_e('Login link expires in', 'wwa-otml');?></label></th>
    <td>
        <input required name="magic_link_expire" type="number" id="magic_link_expire" value="<?php echo esc_attr($wwaotml_v_mle); ?>" class="regular-text wwa-otml-number-input" min="1" max="30"> <?php esc_html_x('minute(s)', 'Login link expires in...', 'wwa-otml');?>
        <p class="description"><?php esc_html_e('Duration of validity of one-time login links. Must be an integer between 1 and 30.', 'wwa-otml');?></p>
    </td>
</tr>
<tr>
    <th scope="row"><label for="mail_template"><?php esc_html_e('Login link email template', 'wwa-otml');?></label></th>
    <td>
        <button class="button wwa-otml-open-editor"><?php esc_html_e('Open editor', 'wwa-otml');?></button>
        <button class="button wwa-otml-close-editor" style="display:none"><?php esc_html_e('Close editor', 'wwa-otml');?></button>
        <div id="wwa-otml-mail-template-editor" style="height:0">
            <?php
            wp_editor(htmlspecialchars_decode($wwaotml_v_mt), 'wwa-otml-mail-template', array(
                'textarea_name' => 'mail_template',
                'textarea_rows' => 15,
                'wpautop' => true,
                'teeny' => true,
                'media_buttons' => false,
                'tinymce' => array(
                    'toolbar1' => 'bold,italic,underline,strikethrough,|,bullist,numlist,|,alignleft,aligncenter,alignright,|,link,unlink,|,undo,redo',
                    'toolbar2' => '',
                    'toolbar3' => '',
                    'toolbar4' => '',
                ),
                'quicktags' => array(
                    'buttons' => 'strong,em,link,del,ul,ol,li,close',
                ),
            ));
            ?>
        </div>
        <p class="description"><?php esc_html_e('Email template used when sending one-time login links. Placeholders are available for dynamic fields.', 'wwa-otml');?></p>
        <details>
            <summary class="wwa-otml-summary"><?php esc_html_e('Placeholder list', 'wwa-otml');?></summary>
            <ul class="wwa-otml-details">
                <?php _e('<li><code title="Click to select">{% username %}</code> will be replaced with the user\'s name</li><li><code title="Click to select">{% useremail %}</code> will be replaced with the user\'s email address</li><li><code title="Click to select">{% loginurl %}</code> will be replaced with the one-time login link URL</li><li><code title="Click to select">{% expiretime %}</code> will be replaced with the duration of validity of one-time login links (in minute)</li><li><code title="Click to select">{% sitename %}</code> will be replaced with site name</li><li><code title="Click to select">{% homeurl %}</code> will be replaced with site home URL</li><li><code title="Click to select">{% generatedtime %}</code> will be replaced with the date and time the link is generated</li><li><code title="Click to select">{% generatedby %}</code> will be replaced with "registration" or the name of the browser that triggered the link generation</li>', 'wwa-otml');?>
            </ul>
        </details>
    </td>
</tr>
<tr>
    <th scope="row"></th>
</tr>
<?php
}
add_action('wwa_admin_page_extra', __NAMESPACE__.'\admin_page_part', 10, 0);
