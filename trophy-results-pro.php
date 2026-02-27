<?php
/*
Plugin Name: Trophy Results Pro
Description: Professional trophy racing results management for seasons, events, categories, participants, points tables, and standings.
Version: 3.0.0
Author: Trophy Results Team
*/

if (!defined('ABSPATH')) {
    exit;
}

define('TRP_PLUGIN_VERSION', '3.0.0');
define('TRP_PLUGIN_FILE', __FILE__);
define('TRP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TRP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once TRP_PLUGIN_PATH . 'includes/class-trp-db.php';
require_once TRP_PLUGIN_PATH . 'includes/class-trp-roles.php';
require_once TRP_PLUGIN_PATH . 'includes/class-trp-admin.php';
require_once TRP_PLUGIN_PATH . 'includes/class-trp-scoring.php';
require_once TRP_PLUGIN_PATH . 'includes/class-trp-shortcode.php';

register_activation_hook(TRP_PLUGIN_FILE, ['TRP_DB', 'install']);
register_activation_hook(TRP_PLUGIN_FILE, ['TRP_Roles', 'add_roles']);
register_deactivation_hook(TRP_PLUGIN_FILE, ['TRP_Roles', 'remove_roles']);

add_action('plugins_loaded', function () {
    TRP_Admin::init();
    TRP_Shortcode::init();
});
