<?php
/*
Plugin Name: WP Post Expires
Description: A simple plugin allow to set the posts, the time after which will be performed one of 3 actions: "Add prefix to title", "Move to drafts", "Move to trash".
Version:     1.2.4
Author:      XNicON
Author URI:  https://xnicon.ru
License:     GPL2
Text Domain: wp-post-expires
Domain Path: /languages

Copyright 2016-2019  XNicON  (x-icon@ya.ru)

WP Post Expires is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

WP Post Expires is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with WP Post Expires; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class XNPostExpires {
    private $plugin_version = '1.2.4';
    private $url_assets;
    private static $timezone;
    private $settings = [];

    public static function init() {
        return new self;
    }

    public function __construct() {

        load_plugin_textdomain('wp-post-expires', false, dirname(plugin_basename( __FILE__ )) . '/languages');

        $this->settings   = $this->getSettings();
        $this->url_assets = plugin_dir_url( __FILE__ ).'assets/';

        add_action('the_post', [$this, 'expiredPost']);

        if (current_user_can('manage_options')) {
            add_action('admin_init', [$this, 'registerSettings']);
        }

        if (current_user_can('edit_posts')) {
            add_action('admin_enqueue_scripts', [$this, 'gutenbergOrClassic']);

            add_action('add_meta_boxes', [$this, 'addMetaBox']);
            add_action('save_post', [$this, 'saveBoxFields']);
        }

        foreach (array_keys($this->settings['post_types']) as $type) {
            register_meta($type, 'xn-wppe-expiration', ['show_in_rest' => true]);
            register_meta($type, 'xn-wppe-expiration-action', ['show_in_rest' => true]);
            register_meta($type, 'xn-wppe-expiration-prefix', ['show_in_rest' => true]);
        }
    }

    private function getSettings() {
        $settings_load = get_option('xn_wppe_settings');

        if(empty($settings_load) || !is_array($settings_load)) {
            $settings_load = [];
        }

        if(!isset($settings_load['post_types'])) {
            $settings_load['post_types']['post'] = 1;
        }

        if(!isset($settings_load['action'])) {
            $settings_load['action'] = 'add_prefix';
        }

        if(!isset($settings_load['prefix'])) {
            $settings_load['prefix'] = __('Expired', 'wp-post-expires') . ':';
        }else{
            $settings_load['prefix'] = esc_attr($settings_load['prefix']);
        }

        return $settings_load;
    }

    public function gutenbergOrClassic( $hook ) {
        global $post;

        if (($hook == 'post-new.php' || $hook == 'post.php')
            && in_array($post->post_type, array_keys($this->settings['post_types']))) {

            if (use_block_editor_for_post($post->ID)) {
                $this->loadScripts();
            } else {
                $this->loadScriptsClassic();
            }
        }
    }

    public function loadScripts() {
        wp_enqueue_script('xn-plugin-js', $this->url_assets.'js/plugin-scripts.js', ['wp-plugins', 'wp-i18n', 'wp-date'], $this->plugin_version);
        wp_enqueue_style('xn-plugin-css', $this->url_assets.'css/plugin-style.css', [], $this->plugin_version);

        wp_set_script_translations('xn-plugin-js', 'wp-post-expires', plugin_dir_path( __FILE__ ) . 'languages');
    }

    public function loadScriptsClassic() {
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('xn-plugin-js', $this->url_assets.'js/plugin-scripts-classic.js', ['jquery-ui-datepicker'], $this->plugin_version);

        wp_enqueue_style('jquery-ui', 'https://cdn.jsdelivr.net/npm/jquery-ui-dist@1.12.1/jquery-ui.min.css', [], $this->plugin_version);
        wp_enqueue_style('jquery-ui-dtpicker-skin', $this->url_assets.'css/latoja.datepicker.css', ['jquery-ui'], $this->plugin_version);
    }

    public function addMetaBox() {
        add_meta_box('xn_box_expiration', __('Expires', 'wp-post-expires'),
            [$this, 'addBoxFields'], array_keys($this->settings['post_types']),
            'side', 'default', ['__back_compat_meta_box' => false]
        );
    }

    public function addBoxFields($post) {
        if(!empty($post->ID)) {
            $expires        = get_post_meta($post->ID, 'xn-wppe-expiration', true);
            $expires_select = get_post_meta($post->ID, 'xn-wppe-expiration-action', true);
            $expires_prefix = get_post_meta($post->ID, 'xn-wppe-expiration-prefix', false);
        } else {
            $expires        = '';
            $expires_select = '';
            $expires_prefix = '';
        }

        //$label  = !empty($expires)? date_i18n('M j, Y @ G:i', strtotime($expires)) : __('never', 'wp-post-expires');
        $date   = !empty($expires)? date_i18n('Y-m-d H:i', strtotime($expires)) : '';
        $select = !empty($expires_select)? $expires_select : $this->settings['action'];
        //Allow empty value
        $prefix = isset($expires_prefix[0])? esc_attr($expires_prefix[0]) : $this->settings['prefix'];
    ?>

        <div class="components-panel__row">
            <div><?php _e('DateTime', 'wp-post-expires'); ?></div>
            <div><input type="text" name="xn-wppe-expiration" id="xn-wppe-datetime" style="width:100%" value="<?php echo $date; ?>" placeholder="<?php _e('yyyy-mm-dd h:i', 'wp-post-expires'); ?>"></div>
        </div>

        <div class="components-panel__row">
            <div><?php _e('Action', 'wp-post-expires'); ?></div>
            <div>
                <select name="xn-wppe-expiration-action" id="xn-wppe-select-action">
                    <option <?php echo $select=='add_prefix'?'selected':'';?> value="add_prefix"><?php _e('Add Prefix', 'wp-post-expires'); ?></option>
                    <option <?php echo $select=='to_drafts'?'selected':'';?> value="to_drafts"><?php _e('Move to drafts', 'wp-post-expires'); ?></option>
                    <option <?php echo $select=='to_trash'?'selected':'';?> value="to_trash"><?php _e('Move to trash', 'wp-post-expires'); ?></option>
                </select>
            </div>
        </div>

        <div class="components-panel__row" id="xn-wppe-add-prefix-wrap">
            <div><?php _e('Prefix', 'wp-post-expires'); ?></div>
            <div>
                <input type="text" name="xn-wppe-expiration-prefix" id="xn-wppe-add-prefix" style="width:100%" value="<?php echo $prefix; ?>" placeholder="<?php _e('Prefix for post title', 'wp-post-expires'); ?>">
            </div>
        </div>
    <?php
    }

    public function saveBoxFields($post_id = 0) {

        if( defined('DOING_AUTOSAVE') || defined('DOING_AJAX') || wp_is_post_revision($post_id) ) {
            return false;
        }

        $expiration  = !empty($_POST['xn-wppe-expiration'])?        sanitize_text_field($_POST['xn-wppe-expiration'])        : false;
        $action_type = !empty($_POST['xn-wppe-expiration-action'])? sanitize_text_field($_POST['xn-wppe-expiration-action']) : false;
        $add_prefix  = isset($_POST['xn-wppe-expiration-prefix'])?  sanitize_text_field($_POST['xn-wppe-expiration-prefix']) : false;

        if($expiration !== false && $action_type !== false) {
            update_post_meta($post_id, 'xn-wppe-expiration', $expiration);
            update_post_meta($post_id, 'xn-wppe-expiration-action', $action_type);
            if($add_prefix !== false) {
                update_post_meta($post_id, 'xn-wppe-expiration-prefix', $add_prefix);
            }
        } else {
            delete_post_meta($post_id, 'xn-wppe-expiration');
            delete_post_meta($post_id, 'xn-wppe-expiration-action');
            delete_post_meta($post_id, 'xn-wppe-expiration-prefix');
        }
    }

    public function registerSettings() {
        register_setting('reading', 'xn_wppe_settings');

        add_settings_section("xn_wppe_section", __('Settings posts expires', 'wp-post-expires'), null, 'reading');

        add_settings_field('xn_wppe_settings_posttype', __('Supported post types', 'wp-post-expires'), [$this, 'settingsFieldPosttype'], 'reading', "xn_wppe_section");
        add_settings_field('xn_wppe_settings_action', __('Action by default', 'wp-post-expires'), [$this, 'settingsFieldAction'], 'reading', "xn_wppe_section");
        add_settings_field('xn_wppe_settings_prefix', __('Default Expired Item Prefix', 'wp-post-expires'), [$this, 'settingsFieldPrefix'], 'reading', "xn_wppe_section");
    }

    public function settingsFieldPosttype() {
        $all_post_types = get_post_types(['public' => true], 'objects');

        foreach($all_post_types as $post_type => $post_type_obj) {
            echo '<label>';
                echo '<input type="checkbox" name="xn_wppe_settings[post_types]['.$post_type.']" value="1"'.(isset($this->settings['post_types'][$post_type])?' checked':'').'>';
                echo $post_type_obj->labels->name;
            echo '</label> &nbsp;';
        }
    }

    public function settingsFieldAction() {
        echo '<select name="xn_wppe_settings[action]" id="xn_wppe_settings_action">';
            echo '<option '.($this->settings['action']=='add_prefix'?'selected':'').' value="add_prefix">'.__('Add Prefix', 'wp-post-expires').'</option>';
            echo '<option '.($this->settings['action']=='to_drafts'?'selected':'').' value="to_drafts">'.__('Move to drafts', 'wp-post-expires').'</option>';
            echo '<option '.($this->settings['action']=='to_trash'?'selected':'').' value="to_trash">'.__('Move to trash', 'wp-post-expires').'</option>';
        echo '</select>';
    }

    public function settingsFieldPrefix() {
        echo '<input id="xn_wppe_settings_prefix" type="text" name="xn_wppe_settings[prefix]" value="'.$this->settings['prefix'].'" class="regular-text">';
        echo '<p class="description">'.__('Enter the text you would like prepended to expired items.', 'wp-post-expires').'</p>';
    }

    public function textTitleFilter($title = '', $post_id = 0) {
        $expires_prefix = get_post_meta($post_id, 'xn-wppe-expiration-prefix', true);
        $prefix = !empty($expires_prefix)? esc_attr($expires_prefix).'&nbsp;' : '';

        return $prefix.$title;
    }

    public function cssClassFilter($classes) {
        $classes[] = 'post-expired';
        return $classes;
    }

    public function addPostState($post_states, $post) {
        $post_states[] = __('Expired', 'wp-post-expires');
        return $post_states;
    }

    public function expiredPost($post) {

        if(self::isExpired($post->ID)) {
            $expires_action = get_post_meta($post->ID, 'xn-wppe-expiration-action', true);
            $action = !empty($expires_action)? $expires_action : $this->settings['action'];

            if ($action == 'add_prefix') {

                add_filter('the_title', [$this, 'textTitleFilter'], 10, 2);
                add_filter('post_class', [$this, 'cssClassFilter']);

            } elseif (!in_array($post->post_status, ['draft', 'trash'])) {
                remove_action('save_post', [$this, 'saveBoxFields']);

                if ($action == 'to_drafts') {
                    wp_update_post(['ID' => $post->ID, 'post_status' => 'draft']);
                } elseif($action == 'to_trash') {
                    wp_trash_post($post->ID);
                }

                add_action('save_post', [$this, 'saveBoxFields']);
            } else {
                add_filter('display_post_states', [$this, 'addPostState'], 10, 2);
            }
        }
    }

    public static function dateExpiration($post_id = 0, $format = false) {
        $expires = get_post_meta($post_id, 'xn-wppe-expiration', true);
        if($format === false) {
            $format = get_option('date_format');
        }
        return !empty($expires)? date_i18n( $format, strtotime($expires) ) : __('never', 'wp-post-expires');
    }

    public static function isExpired($post_id = 0) {
        $expires = get_post_meta($post_id, 'xn-wppe-expiration', true);

        if(!empty($expires)) {
            $current = new DateTime();
            $current->setTimezone( self::getWpTimezone() );

            $expiration = DateTime::createFromFormat('Y-m-d H:i', $expires,
                self::getWpTimezone());

            if($expiration
                && $expiration->format('Y-m-d H:i') == $expires
                && $current >= $expiration) {
                return true;
            }
        }

        return false;
    }

    private static function getWpTimezone() {
        if (!empty(self::$timezone)) {
            return self::$timezone;
        }

        $timezone_string = get_option( 'timezone_string' );
        if (!empty($timezone_string)) {
            return self::$timezone = new DateTimeZone($timezone_string);
        }
        $offset  = get_option( 'gmt_offset' );
        $hours   = (int) $offset;
        $minutes = abs( ( $offset - (int) $offset ) * 60 );
        $offset  = sprintf( '%+03d:%02d', $hours, $minutes );
        return self::$timezone = new DateTimeZone($offset);
    }

}
add_action('plugins_loaded', ['XNPostExpires','init']);