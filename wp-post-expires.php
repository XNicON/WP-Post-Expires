<?php
/*
Plugin Name: WP Post Expires
Plugin URI:  http://wordpress.org
Description: A simple plugin that allows you to set an expiration date on posts. Once a post is expired, "Expired" will be prefixed to the post title. Or move to trash or drafts.
Version:     1.0
Author:      X-NicON
Author URI:  http://xnicon.ru
License:     GPL2

Copyright 2016  X-NicON  (x-icon@ya.ru)

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

add_action('plugins_loaded','xn_init_wp_post_expires');

function xn_init_wp_post_expires() {
	new XN_WP_Post_Expires();
}

class XN_WP_Post_Expires {

	private $url_assets;
	public static $settings = array();

	public function __construct() {
		$this->url_assets = plugin_dir_url( __FILE__ ).'assets/';
		$this->settings   = $this->load_settings();

		load_plugin_textdomain('xn-wppe', false, dirname(plugin_basename( __FILE__ ) ).'/languages');

		if(current_user_can('edit_posts')) {
			add_action('load-post-new.php', array($this, 'xn_wppe_scripts'));
			add_action('load-post.php', array($this, 'xn_wppe_scripts'));
			add_action('save_post', array($this, 'xn_wppe_save_box_fields'));
			add_action('admin_init', array($this,'xn_wppe_register_settings'));
			add_action('current_screen', array($this, 'xn_wppe_submitbox_add'));
		}
	}

	public function xn_wppe_submitbox_add(){
		$screen = get_current_screen();

		if(array_key_exists($screen->post_type, $this->settings['post_types'])){

			if($this->settings['cats_type'] == 'disabled' || empty($this->settings['cats'])){
				add_action('post_submitbox_misc_actions', array($this, 'xn_wppe_add_box_fields'));

			}elseif($this->settings['cats_type'] != 'disabled' && !empty($this->settings['cats'])){

				$cats_trim = str_replace(' ', '', $this->settings['cats']);
				$cats      = explode(',', $cats_trim);

				// TODO: Fix check category
				//var_dump(get_queried_object()->term_id);
				if($this->settings['cats_type'] == 'include' && in_array(get_queried_object()->term_id, $cats)){
					add_action('post_submitbox_misc_actions', array($this, 'xn_wppe_add_box_fields'));

				}elseif($this->settings['cats_type'] == 'exclude' && !in_array(get_queried_object()->term_id, $cats)){
					add_action('post_submitbox_misc_actions', array($this, 'xn_wppe_add_box_fields'));
				}
			}
		}
	}

	public function xn_wppe_add_box_fields() {

		global $post;

		if(!empty($post->ID)) {
			$expires        = get_post_meta($post->ID, 'xn-wppe-expiration', true);
			$expires_sction = get_post_meta($post->ID, 'xn-wppe-expiration-action', true);
			$expires_prefix = get_post_meta($post->ID, 'xn-wppe-expiration-prefix', true);
		}

		$label  = !empty($expires)? date_i18n('Y-n-d h:i', strtotime($expires)) : __('never', 'xn-wppe');
		$date   = !empty($expires)? date_i18n('Y-n-d h:i', strtotime($expires)) : '';
		$prefix = !empty($expires_prefix)? esc_attr($expires_prefix) : $this->settings['prefix'];
	?>
		<div id="xn-wppe" class="misc-pub-section">
			<span>
				<span class="wp-media-buttons-icon dashicons dashicons-clock"></span>&nbsp;
				<?php _e('Expires:', 'xn-wppe'); ?>
				<b id="xn-wppe-currentsetdt" data-never="<?php _e('never', 'xn-wppe'); ?>"><?php echo $label; ?></b>
			</span>
			<a href="#" id="xn-wppe-edit" class="xn-wppe-edit hide-if-no-js">
				<span aria-hidden="true"><?php _e('Edit', 'xn-wppe'); ?></span>
				<span class="screen-reader-text">(<?php _e('Edit date and time', 'xn-wppe'); ?>)</span>
			</a>
			<div id="xn-wppe-fields" class="hide-if-js">
				<p>
					<label for="xn-wppe-datetime"><?php _e('DateTime:', 'xn-wppe'); ?></label>
					<input type="text" name="xn-wppe-expiration" id="xn-wppe-datetime" value="<?php echo $date; ?>" placeholder="<?php _e('yyyy-mm-dd h:i','xn-wppe'); ?>">
				</p>
				<p>
					<label for="xn-wppe-action-end"><?php _e('Action:', 'xn-wppe'); ?></label>
					<select name="xn-wppe-expiration-action" id="xn-wppe-action-end">
						<option <?php echo $expires_sction=='add_text'?'selected':'';?> value="add_text"><?php _e('Add Text', 'xn-wppe'); ?></option>
						<option <?php echo $expires_sction=='to_drafts'?'selected':'';?> value="to_drafts"><?php _e('Move to drafts', 'xn-wppe'); ?></option>
						<option <?php echo $expires_sction=='to_trash'?'selected':'';?> value="to_trash"><?php _e('Move to trash', 'xn-wppe'); ?></option>
					</select>
				</p>
				<p id="xn-wppe-select-add-text">
					<label for="xn-wppe-add-text"><?php _e('Text:', 'xn-wppe'); ?></label>
					<input type="text" name="xn-wppe-expiration-prefix" id="xn-wppe-add-text" value="<?php echo $prefix; ?>" placeholder="<?php _e('Prefix for post title', 'xn-wppe'); ?>">
				</p>
				<p>
					<a href="#" class="xn-wppe-hide-expiration button secondary"><?php _e('OK', 'xn-wppe'); ?></a>
					<a href="#" class="xn-wppe-hide-expiration cancel"><?php _e('Cancel', 'xn-wppe'); ?></a>
				</p>
			</div>
		</div>
	<?php
	}

	public function xn_wppe_save_box_fields($post_id = 0) {

		if( defined('DOING_AUTOSAVE') ||
				defined('DOING_AJAX') ||
				isset($_REQUEST['bulk_edit']) ||
				!current_user_can('edit_post', $post_id)
		) {
			return;
		}

		$expiration  = !empty($_POST['xn-wppe-expiration'])?        sanitize_text_field($_POST['xn-wppe-expiration'])        : false;
		$action_type = !empty($_POST['xn-wppe-expiration-action'])? sanitize_text_field($_POST['xn-wppe-expiration-action']) : false;
		$add_text    = !empty($_POST['xn-wppe-expiration-prefix'])? sanitize_text_field($_POST['xn-wppe-expiration-prefix']) : false;

		if($expiration && $action_type) {
			update_post_meta($post_id, 'xn-wppe-expiration', $expiration);
			update_post_meta($post_id, 'xn-wppe-expiration-action', $action_type);

			if($add_text) {
				update_post_meta($post_id, 'xn-wppe-expiration-prefix', $add_text);
			}
		}else{
			delete_post_meta($post_id, 'xn-wppe-expiration');
			delete_post_meta($post_id, 'xn-wppe-expiration-action');
			delete_post_meta($post_id, 'xn-wppe-expiration-prefix');
		}
	}

	public function xn_wppe_register_settings() {

		register_setting('reading', 'xn_wppe_settings');

		add_settings_section("xn_wppe_section", __('Настройки срока давности записей', 'xn-wppe'), null, 'reading');

		add_settings_field('xn_wppe_settings_posttype', __('Supported post types', 'xn-wppe'), array($this, 'xn_wppe_settings_field_posttype'), 'reading', "xn_wppe_section");
		add_settings_field('xn_wppe_settings_prefix', __('Default Expired Item Prefix', 'xn-wppe'), array($this, 'xn_wppe_settings_field_prefix'), 'reading', "xn_wppe_section");
		add_settings_field('xn_wppe_settings_cats_type', __('Include/Exclude Categories', 'xn-wppe'), array($this, 'xn_wppe_settings_field_cats_type'), 'reading', "xn_wppe_section");
	}

	public function xn_wppe_settings_field_posttype() {
		$all_post_types = get_post_types(false, 'objects');
		unset($all_post_types['nav_menu_item']);
		unset($all_post_types['revision']);
		unset($all_post_types['attachment']);

		foreach($all_post_types as $post_type => $post_type_obj) {
			echo '<label>';
				echo '<input type="checkbox" name="xn_wppe_settings[post_types]['.$post_type.']" value="1"'.(isset($this->settings['post_types'][$post_type])?' checked':'').'>';
				echo $post_type_obj->labels->name;
			echo '</label> &nbsp;';
		}
	}

	public function xn_wppe_settings_field_prefix() {
		echo '<input id="xn_wppe_settings_prefix" type="text" name="xn_wppe_settings[prefix]" value="'.$this->settings['prefix'].'" class="regular-text">';
		echo '<p class="description">'.__('Enter the text you would like prepended to expired items.', 'pw-spe').'</p>';
	}

	public function xn_wppe_settings_field_cats_type() {
		echo '<fieldset>';
			echo '<label><input type="radio" name="xn_wppe_settings[cats_type]" value="disabled"'.($this->settings['cats_type'] == 'disabled'?' checked':'').'> '.__('Disabled', 'xn-wppe').'</label><br>';
			echo '<label><input type="radio" name="xn_wppe_settings[cats_type]" value="include"'.($this->settings['cats_type'] == 'include'?' checked':'').'> '.__('Include only', 'xn-wppe').'</label><br>';
			echo '<label><input type="radio" name="xn_wppe_settings[cats_type]" value="exclude"'.($this->settings['cats_type'] == 'exclude'?' checked':'').'> '.__('Exclude', 'xn-wppe').'</label><br>';
		echo '</fieldset>';
		echo '<p><input class="regular-text" type="text" name="xn_wppe_settings[cats]" value="'.$this->settings['cats'].'" placeholder="'.__('Include/Exclude Categories','xn-wppe').'"></p>';
		echo '<p class="description">'.__('Check Include/Exclude and set category/term ids of comma separate','xn-wppe').'</p>';
	}

	public function xn_wppe_scripts() {
		wp_enqueue_style('datatimepicker-css', $this->url_assets.'css/datepicker.min.css');
		wp_enqueue_script('datatimepicker-js', $this->url_assets.'js/datepicker.min.js', array('jquery'));
		wp_enqueue_script('xn-wppe-plugin-js', $this->url_assets.'js/plugin-scripts.js', array('datatimepicker-js'));
	}

	private function load_settings() {
		$settings_load = get_option('xn_wppe_settings');

		if (empty($settings_load) || !is_array($settings_load)) {
			$settings_load = array();
		}

		if(!isset($settings_load['post_types'])) {
			$settings_load['post_types']['post'] = 1;
			$settings_load['post_types']['page'] = 1;
		}

		if(!isset($settings_load['prefix'])) {
			$settings_load['prefix'] = __('Expired:', 'xn-wppe');
		}else{
			$settings_load['prefix'] = esc_attr($settings_load['prefix']);
		}

		if(!isset($settings_load['cats_type']) || empty($settings_load['cats_type'])) {
			$settings_load['cats_type'] = 'disabled';
		}

		if(!isset($settings_load['cats'])) {
			$settings_load['cats'] = '';
		}

		return $settings_load;
	}
}
