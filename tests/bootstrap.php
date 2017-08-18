<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Wp_Hide_Post
 */
define('WP_USE_THEMES', true);

$_tests_dir = getenv('WP_TESTS_DIR');
if (!$_tests_dir)
{
    $_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin()
{
    require dirname(dirname(__FILE__)) . '/wp-hide-post.php';
}
tests_add_filter('muplugins_loaded', '_manually_load_plugin');

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
require __DIR__ . '/../bin/trait_exec.php';

require __DIR__ . '/../bin/base_class.php';

if (file_exists(__DIR__ . '/../bin/vendor/autoload.php'))
{
    require __DIR__ . '/../bin/vendor/autoload.php';

}
