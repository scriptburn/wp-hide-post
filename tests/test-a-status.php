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

    public function deactivate_plugin($name)
    {

        $ret = $this->exec(['cmd' => 'wp plugin deactivate ' . $name]);
        if (!$ret[0])
        {
            return $ret[1];
        }
        else
        {
            $matches = array();
            preg_match_all('/success:(.+?)(deactivated)(.+?)/im', $ret[1], $matches, PREG_SET_ORDER, 0);
            return strtolower(@$matches[0][2]) == 'deactivated';
        }

    }
    public function activate_plugin($name)
    {

        $ret = $this->exec(['cmd' => 'wp plugin activate ' . $name]);
        if (!$ret[0])
        {
            return $ret[1];
        }
        else
        {
            $matches = array();
            preg_match_all('/success:(.+?)(activated)(.+?)/im', $ret[1], $matches, PREG_SET_ORDER, 0);
            return strtolower(@$matches[0][2]) == 'activated';
        }

    }
    public function test_plugin_can_be_activated()
    {

        $this->assertTrue($this->deactivate_plugin('wp-hide-post'));
        $this->assertTrue($this->activate_plugin('wp-hide-post'));

    }

}
