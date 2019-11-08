<?php
/**
 * Class SampleTest
 *
 * @package Wp_Hide_Post
 */

/**
 * Sample test case.
 */

class TestStatus extends WP_UnitTestCase
{
	use SCBExec;

	public function test_plugin_can_be_activated()
	{
		$base = rtrim(realpath(dirname(__FILE__)."/../../"), "/");
 		if (file_exists($base."/wp-hide-post-pro/wp-hide-post.php") && file_exists($base."/wp-hide-post/wp-hide-post.php"))
		{
			if (WPHP_PLUGIN_DIR == $base."/wp-hide-post-pro/")
			{
				$this->setPluginStatus('wp-hide-post', 'deactivate');
				$this->setPluginStatus('wp-hide-post-pro', 'deactivate');

				$this->assertTrue($this->setPluginStatus('wp-hide-post', 'activate'), 'Unable to activate wp-hide-post(1)');
				$this->assertFalse($this->setPluginStatus('wp-hide-post-pro', 'activate'), 'pro must not activate if lite is active');
			}
			if (WPHP_PLUGIN_DIR == $base."/wp-hide-post/")
			{
				$this->setPluginStatus('wp-hide-post', 'deactivate');
				$this->setPluginStatus('wp-hide-post-pro', 'deactivate');

				$this->assertTrue($this->setPluginStatus('wp-hide-post-pro', 'activate'), 'Unable to activate wp-hide-post-pro(1)');
				$this->assertFalse($this->setPluginStatus('wp-hide-post', 'activate'), 'lite must not activate if pro is active');
			}
		}
		else
		{
			$this->setPluginStatus('wp-hide-post', 'deactivate');
			$this->setPluginStatus('wp-hide-post-pro', 'deactivate');

			if (WPHP_PLUGIN_DIR == $base."/wp-hide-post-pro/")
			{
				$this->setPluginStatus('wp-hide-post', 'deactivate');

				$plugin = 'wp-hide-post-pro';

				$this->setPluginStatus($plugin, 'activate');

				$this->assertTrue($this->setPluginStatus($plugin, 'deactivate'), " Unable to deactivate $plugin (2)");
				$this->assertTrue($this->setPluginStatus($plugin, 'activate'), "Unable to activate $plugin (2)");
			}
			if (WPHP_PLUGIN_DIR == $base."/wp-hide-post/")
			{
				$plugin = 'wp-hide-post';

				$this->setPluginStatus($plugin, 'activate');

				$this->assertTrue($this->setPluginStatus($plugin, 'deactivate'), " Unable to deactivate $plugin (2)");
				$this->assertTrue($this->setPluginStatus($plugin, 'activate'), "Unable to activate $plugin (2)");

				$this->setPluginStatus($plugin, 'deactivate');
				$this->setPluginStatus($plugin, 'activate');
			}
		}
	}
}
