<?php
/*
Plugin Name: WP Post Expires
Description: A simple plugin allow to set the posts, the time after which will be performed one of 3 actions: "Add prefix to title", "Move to drafts", "Move to trash".
Version:     1.1
Author:      X-NicON
Author URI:  https://xnicon.ru
License:     GPL2
Text Domain: wp-post-expires
Domain Path: /languages

Copyright 2016-2018  X-NicON  (x-icon@ya.ru)

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

//Legacy
class XN_WP_Post_Expires {
	public static function xn_wppe_is_expired($post_id = 0){
		return XNPostExpires::isExpired($post_id);
	}
}

class XNPostExpires {
	private $plugin_version = '1.1';
	private $url_assets;
	public $settings = array();

	public static function init(){
		return new self;
	}

	public function __construct(){
		load_plugin_textdomain('wp-post-expires', false, dirname(plugin_basename( __FILE__ ) ).'/languages');

		$this->settings   = $this->getSettings();
		$this->url_assets = plugin_dir_url( __FILE__ ).'assets/';

		add_action('the_post', [$this, 'xnExpiredPost']);

		if(current_user_can('manage_options')){
			add_action('admin_init', array($this,'xn_wppe_register_settings'));
		}

		if(current_user_can('edit_posts')){
			add_action('load-post-new.php', array($this, 'xnScripts'));
			add_action('load-post.php', array($this, 'xnScripts'));
			add_action('post_submitbox_misc_actions', array($this, 'xnAddBoxFields'));
			add_action('save_post', [$this, 'xnSaveBoxFields']);
		}
	}

	private function getSettings() {
		$settings_load = get_option('xn_wppe_settings');

		if(empty($settings_load) || !is_array($settings_load)) {
			$settings_load = array();
		}

		if(!isset($settings_load['post_types'])) {
			$settings_load['post_types']['post'] = 1;
			$settings_load['post_types']['page'] = 0;
		}

		if(!isset($settings_load['action'])) {
			$settings_load['action'] = 'add_prefix';
		}

		if(!isset($settings_load['prefix'])) {
			$settings_load['prefix'] = __('Expired:', 'wp-post-expires');
		}else{
			$settings_load['prefix'] = esc_attr($settings_load['prefix']);
		}

		return $settings_load;
	}

	public function xnScripts() {
		$wplang 	 = explode('-', get_bloginfo('language'));
		$supported = array('cs', 'da', 'de', 'es', 'fi', 'fr', 'hu', 'nl', 'pl', 'pt','ro','zh', 'it', 'sk');
		$dtplang	 = 'en';

		if($wplang[0] == 'ru'){
			$dtplang = false;
		}elseif(in_array($wplang[0], $supported)){
			$dtplang = $wplang[0];
		}

		wp_enqueue_style('xn-wppe-dtpicker', $this->url_assets.'css/datepicker.min.css', [], $this->plugin_version);

		wp_enqueue_script('xn-wppe-dtpicker-js', $this->url_assets.'js/datepicker.min.js', ['jquery'], $this->plugin_version);
		if($dtplang != false){
			wp_enqueue_script('xn-wppe-dtpicker-lang-js', $this->url_assets.'js/i18n/datepicker.'.$dtplang.'.js', ['xn-wppe-dtpicker-js'], $this->plugin_version);
		}
		wp_enqueue_script('xn-wppe-plugin-js', $this->url_assets.'js/plugin-scripts.js', ['xn-wppe-dtpicker-js'], $this->plugin_version);
	}

	public function xnAddBoxFields() {
		global $post;

		if(!array_key_exists($post->post_type, $this->settings['post_types'])){
			return;
		}

		if(!empty($post->ID)) {
			$expires        = get_post_meta($post->ID, 'xn-wppe-expiration', true);
			$expires_select = get_post_meta($post->ID, 'xn-wppe-expiration-action', true);
			$expires_prefix = get_post_meta($post->ID, 'xn-wppe-expiration-prefix', false);
		}

		$label  = !empty($expires)? date_i18n('M j, Y @ G:i', strtotime($expires)) : __('never', 'wp-post-expires');
		$date   = !empty($expires)? date_i18n('Y-m-d H:i', strtotime($expires)) : '';
		$select = !empty($expires_select)? $expires_select : $this->settings['action'];
		//Allow empty value
		$prefix = isset($expires_prefix[0])? esc_attr($expires_prefix[0]) : $this->settings['prefix'];
	?>

		<div id="xn-wppe" class="misc-pub-section">
			<span>
				<span class="wp-media-buttons-icon dashicons dashicons-clock"></span>&nbsp;
				<?php _e('Expires:', 'wp-post-expires'); ?>
				<b id="xn-wppe-currentsetdt" data-never="<?php _e('never', 'wp-post-expires'); ?>"><?php echo $label; ?></b>
			</span>
			<a href="#" id="xn-wppe-edit" class="xn-wppe-edit hide-if-no-js">
				<span aria-hidden="true"><?php _e('Edit', 'wp-post-expires'); ?></span>
				<span class="screen-reader-text">(<?php _e('Edit date and time', 'wp-post-expires'); ?>)</span>
			</a>
			<div id="xn-wppe-fields" class="hide-if-js">
				<p>
					<label for="xn-wppe-datetime"><?php _e('DateTime:', 'wp-post-expires'); ?></label>
					<input type="text" name="xn-wppe-expiration" id="xn-wppe-datetime" value="<?php echo $date; ?>" placeholder="<?php _e('yyyy-mm-dd h:i','wp-post-expires'); ?>">
				</p>
				<p>
					<label for="xn-wppe-select-action"><?php _e('Action:', 'wp-post-expires'); ?></label>
					<select name="xn-wppe-expiration-action" id="xn-wppe-select-action">
						<option <?php echo $select=='add_prefix'?'selected':'';?> value="add_prefix"><?php _e('Add Prefix', 'wp-post-expires'); ?></option>
						<option <?php echo $select=='to_drafts'?'selected':'';?> value="to_drafts"><?php _e('Move to drafts', 'wp-post-expires'); ?></option>
						<option <?php echo $select=='to_trash'?'selected':'';?> value="to_trash"><?php _e('Move to trash', 'wp-post-expires'); ?></option>
					</select>
				</p>
				<p id="xn-wppe-add-prefix-wrap">
					<label for="xn-wppe-add-prefix"><?php _e('Prefix:', 'wp-post-expires'); ?></label>
					<input type="text" name="xn-wppe-expiration-prefix" id="xn-wppe-add-prefix" value="<?php echo $prefix; ?>" placeholder="<?php _e('Prefix for post title', 'wp-post-expires'); ?>">
				</p>
				<p>
					<a href="#" class="xn-wppe-hide-expiration button secondary"><?php _e('OK', 'wp-post-expires'); ?></a>
					<a href="#" class="xn-wppe-hide-expiration cancel"><?php _e('Cancel', 'wp-post-expires'); ?></a>
				</p>
			</div>
		</div>
	<?php
	}

	public function xnSaveBoxFields($post_id = 0){

		if( defined('DOING_AUTOSAVE') || defined('DOING_AJAX') || wp_is_post_revision($post_id) ){
			return false;
		}

		$expiration  = !empty($_POST['xn-wppe-expiration'])?        sanitize_text_field($_POST['xn-wppe-expiration'])        : false;
		$action_type = !empty($_POST['xn-wppe-expiration-action'])? sanitize_text_field($_POST['xn-wppe-expiration-action']) : false;
		$add_prefix  = isset($_POST['xn-wppe-expiration-prefix'])?  sanitize_text_field($_POST['xn-wppe-expiration-prefix']) : false;

		if($expiration !== false && $action_type !== false){
			update_post_meta($post_id, 'xn-wppe-expiration', $expiration);
			update_post_meta($post_id, 'xn-wppe-expiration-action', $action_type);
			if($add_prefix !== false){
				update_post_meta($post_id, 'xn-wppe-expiration-prefix', $add_prefix);
			}
		}else{
			delete_post_meta($post_id, 'xn-wppe-expiration');
			delete_post_meta($post_id, 'xn-wppe-expiration-action');
			delete_post_meta($post_id, 'xn-wppe-expiration-prefix');
		}
	}

	public function xn_wppe_register_settings() {

		register_setting('reading', 'xn_wppe_settings');

		add_settings_section("xn_wppe_section", __('Settings posts expires', 'wp-post-expires'), null, 'reading');

		add_settings_field('xn_wppe_settings_posttype', __('Supported post types', 'wp-post-expires'), array($this, 'xn_wppe_settings_field_posttype'), 'reading', "xn_wppe_section");
		add_settings_field('xn_wppe_settings_action', __('Action by default', 'wp-post-expires'), array($this, 'xn_wppe_settings_field_action'), 'reading', "xn_wppe_section");
		add_settings_field('xn_wppe_settings_prefix', __('Default Expired Item Prefix', 'wp-post-expires'), array($this, 'xn_wppe_settings_field_prefix'), 'reading', "xn_wppe_section");
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

	public function xn_wppe_settings_field_action() {
		echo '<select name="xn_wppe_settings[action]" id="xn_wppe_settings_action">';
			echo '<option '.($this->settings['action']=='add_prefix'?'selected':'').' value="add_prefix">'.__('Add Prefix', 'wp-post-expires').'</option>';
			echo '<option '.($this->settings['action']=='to_drafts'?'selected':'').' value="to_drafts">'.__('Move to drafts', 'wp-post-expires').'</option>';
			echo '<option '.($this->settings['action']=='to_trash'?'selected':'').' value="to_trash">'.__('Move to trash', 'wp-post-expires').'</option>';
		echo '</select>';
	}

	public function xn_wppe_settings_field_prefix() {
		echo '<input id="xn_wppe_settings_prefix" type="text" name="xn_wppe_settings[prefix]" value="'.$this->settings['prefix'].'" class="regular-text">';
		echo '<p class="description">'.__('Enter the text you would like prepended to expired items.', 'wp-post-expires').'</p>';
	}

	public static function isExpired($post_id = 0){

		$expires = get_post_meta($post_id, 'xn-wppe-expiration', true);

		if(!empty($expires)) {
			$current = new DateTime();
			$current->setTimestamp(current_time('timestamp'));
			$expiration = new DateTime($expires);

			if($current >= $expiration){
				return true;
			}
		}
		return false;
	}

	public static function dateExpiration($post_id = 0, $format = false){
		$expires = get_post_meta($post_id, 'xn-wppe-expiration', true);
		if($format === false){
			$format = get_option('date_format');
		}
		return !empty($expires)? date_i18n( $format, strtotime($expires) ) : __('never', 'wp-post-expires');
	}

	public function xnFilterTitle($title = '', $post_id = 0) {
		$expires_prefix = get_post_meta($post_id, 'xn-wppe-expiration-prefix', true);
		$prefix = !empty($expires_prefix)? esc_attr($expires_prefix).'&nbsp;' : '';

		return $prefix.$title;
	}

	public function xnFilterClass($classes) {
		$classes[] = 'post-expired';
		return $classes;
	}

	public function xnExpiredPost($post) {
		$post_id = $post->ID;

		if(self::isExpired($post_id)){

			$expires_action = get_post_meta($post_id, 'xn-wppe-expiration-action', true);
			$action = !empty($expires_action)? $expires_action : $this->settings['action'];

			switch($action) {
				case 'add_prefix':
					add_filter('the_title', [$this, 'xnFilterTitle'], 10, 2);
					add_filter('post_class', [$this, 'xnFilterClass']);
				break;
				case 'to_drafts':
					remove_action('save_post', [$this, 'xnSaveBoxFields']);
					wp_update_post(['ID' => $post_id, 'post_status' => 'draft']);
					update_post_meta($post_id, 'xn-wppe-expiration-action', 'add_prefix');
					update_post_meta($post_id, 'xn-wppe-expiration-prefix', $this->settings['prefix']);
					add_action('save_post', [$this, 'xnSaveBoxFields']);
				break;
				case 'to_trash':
					remove_action('save_post', [$this, 'xnSaveBoxFields']);
					$del_post = wp_trash_post($post_id);
					//check post to trash, or deleted
					if($del_post['post_status'] == 'trash'){
						update_post_meta($post_id, 'xn-wppe-expiration-action', 'add_prefix');
						update_post_meta($post_id, 'xn-wppe-expiration-prefix', $this->settings['prefix']);
					}
					add_action('save_post', [$this, 'xnSaveBoxFields']);
				break;
			}
		}
	}
}
add_action('plugins_loaded', ['XNPostExpires','init']);