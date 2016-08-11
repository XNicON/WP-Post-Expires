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

new XN_WP_Post_Expires();
class XN_WP_Post_Expires {

	private $url_assets;

	public function __construct(){
		$this->url_assets = plugin_dir_url( __FILE__ ).'assets/';

		load_plugin_textdomain('xn-wppe', false, dirname(plugin_basename( __FILE__ ) ).'/languages');
		echo 'Yes';

		add_action('post_submitbox_misc_actions', array($this, 'xn_add_box_fields'));
		add_action('load-post-new.php', array($this, 'xn_wppe_scripts'));
		add_action('load-post.php', array($this, 'xn_wppe_scripts'));
	}

	public function xn_add_box_fields() {

		global $post;

		if(!empty($post->ID)) {
			$expires = get_post_meta( $post->ID, 'pw_spe_expiration', true );
		}

		$label = ! empty( $expires ) ? date_i18n( 'Y-n-d h:i', strtotime($expires)) : __('never', 'xn-wppe');
		$date  = ! empty( $expires ) ? date_i18n( 'Y-n-d h:i', strtotime($expires)) : '';
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
			<?php //<div id="xn-wppe-fields" class="hide-if-js"> ?>
			<div id="xn-wppe-fields" class="hide-if-js">
				<p>
					<label for="xn-wppe-datetime"><?php _e('DateTime:', 'xn-wppe'); ?></label>
					<input type="text" name="xn-wppe-expiration" id="xn-wppe-datetime" value="<?php echo esc_attr( $date ); ?>" placeholder="<?php _e('yyyy-mm-dd h:i','xn-wppe'); ?>">
				</p>
				<p>
					<label for="xn-wppe-action-end"><?php _e('Action:', 'xn-wppe'); ?></label>
					<select name="xn-wppe-expiration-action" id="xn-wppe-action-end">
						<option value="add_text"><?php _e('Add Text', 'xn-wppe'); ?></option>
						<option value="to_drafts"><?php _e('Move to drafts', 'xn-wppe'); ?></option>
						<option value="to_trash"><?php _e('Move to trash', 'xn-wppe'); ?></option>
					</select>
				</p>
				<p id="xn-wppe-select-add-text">
					<label for="xn-wppe-add-text"><?php _e('Text:', 'xn-wppe'); ?></label>
					<input type="text" name="xn-wppe-add-text" id="xn-wppe-add-text" value="<?php _e('Expired:', 'xn-wppe'); ?>" placeholder="<?php _e('Text add to post title', 'xn-wppe'); ?>">
				</p>
				<p>
					<a href="#" class="xn-wppe-hide-expiration button secondary"><?php _e('OK', 'xn-wppe'); ?></a>
					<a href="#" class="xn-wppe-hide-expiration cancel"><?php _e('Cancel', 'xn-wppe'); ?></a>
				</p>
			</div>
			<?php //wp_nonce_field( 'pw_spe_edit_expiration', 'pw_spe_expiration_nonce' ); ?>
		</div>
	<?php
	}

	public function xn_wppe_scripts() {
		wp_enqueue_style('datatimepicker-css', $this->url_assets.'css/datepicker.min.css');
		wp_enqueue_script('datatimepicker-js', $this->url_assets.'js/datepicker.min.js',array('jquery'));
		wp_enqueue_script('xn-wppe-plugin-js', $this->url_assets.'js/plugin-scripts.js',array('datatimepicker-js'));
	}
}
