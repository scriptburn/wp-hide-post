<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://www.scriptburn.com
 * @since             1.0.0
 * @package           wp_hide_post
 *
 * @wordpress-plugin
 * Plugin Name:       WP Hide Post
 * Plugin URI:        http://scriptburn.com/wp-hide-post
 * Description:       Control the visibility of items on your blog by making posts/pages hidden on some parts , while still visible in other parts and search engines.
 * Version:           2.0.12
 * Author:            scriptburn.com
 * Author URI:        http://www.scriptburn.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp_hide_post
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC'))
{
	die;
}
global $table_prefix;
if (!defined('WPHP_VER'))
{
	define('WPHP_VER', "2.0.12");
	define('WPHP_DB_VER', "2");

	define('WPHP_PLUGIN_DIR', __DIR__.DIRECTORY_SEPARATOR);
	define('WPHP_PLUGIN_FILE', ltrim(str_replace(str_Replace("/", DIRECTORY_SEPARATOR, WP_PLUGIN_DIR), "", __FILE__), DIRECTORY_SEPARATOR));
	define('WPHP_PLUGIN_URL', plugin_dir_url(WPHP_PLUGIN_FILE));

	define('WPHP_TABLE_NAME', "${table_prefix}postmeta");

	define('WP_POSTS_TABLE_NAME', "${table_prefix}posts");

	define('WPHP_DEBUG', defined('WP_DEBUG') && WP_DEBUG ? 1 : 0);

	define('WPHP_META_VALUE_PREFIX', '_wplp_');

	define('WPHP_VISIBILITY_NAME', 'wphp_visibility_type');
}
if (!class_exists('SCB_wphp'))
{
	class SCB_wphp
	{
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-hide-post-activator.php
 */
		public static function activate_wp_hide_post()
		{
			if (is_plugin_active('wp-hide-post-pro/wp-hide-post.php'))
			{
				
				$redirect = self_admin_url('plugins.php?supress_activate_lite=1');
				wp_redirect($redirect);
				exit;
			}
			require_once plugin_dir_path(__FILE__).'includes/class-wp-hide-post-activator.php';
			$wphp = new wp_hide_post_Activator();
			$wphp->activate();
		}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-hide-post-deactivator.php
 */
		public static function deactivate_wp_hide_post()
		{
			require_once plugin_dir_path(__FILE__).'includes/class-wp-hide-post-deactivator.php';
			$wphp = new wp_hide_post_Deactivator();
			$wphp->deactivate();
		}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.2.2
 */
		public function run_wp_hide_post()
		{
			$plugin = wp_hide_post::getInstance();
			$plugin->run();
		}
	}
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__).'includes/class-wp-hide-post.php';
register_activation_hook(__FILE__, array('SCB_wphp', 'activate_wp_hide_post'));
register_activation_hook(__FILE__, array('SCB_wphp', 'deactivate_wp_hide_post'));

wp_hide_post::getInstance()->run();
