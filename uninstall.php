<?php

if(!defined('WP_UNINSTALL_PLUGIN')) {
  exit;
}

delete_option('xn_wppe_settings');
delete_post_meta_by_key('xn-wppe-expiration');
delete_post_meta_by_key('xn-wppe-expiration-action');
delete_post_meta_by_key('xn-wppe-expiration-prefix');
