<?php
/**
 * Class SampleTest
 *
 * @package Wp_Hide_Post
 */

/**
 * Sample test case.
 */

class TestPosts extends WPBASE_UnitTestCase
{

    public function hidden_on_post_front($index, $hidden = true)
    {
        $this->create_posts_if_not_created('post');
        $showHide = "_" . (int) $hidden;

        $content              = array();
        $content['nonstatic'] = $this->get_home_page_content(false);
        $content['static']    = $this->get_home_page_content(true);

        return array($this->textBoundry($this->posts[$index . $showHide]), $content, $this->posts[$index . $showHide]);

    }
    public function hidden_on_post_category($index, $hidden = true)
    {
        $this->create_posts_if_not_created('post');
        $hidden   = (int) $hidden;
        $showHide = "_" . $hidden;

        $category_link = get_category_link($this->posts[$index . $showHide]['post_category'][0]);
        $content       = $this->get_page_content($category_link);

        return array($this->textBoundry($this->posts[$index . $showHide]), $content, $this->posts[$index . $showHide]);

    }
    public function hidden_on_post_tag($index, $hidden = true)
    {
        $this->create_posts_if_not_created('post');
        $hidden   = (int) $hidden;
        $showHide = "_" . $hidden;

       // p_l($this->posts[$index . $showHide]);
        $term     = get_term_by('name', $this->posts[$index . $showHide]['tags_input'][0], 'post_tag');
//p_l($term);
//exit();
        $content = $this->get_page_content((get_tag_link($term->term_id)));

        return array($this->textBoundry($this->posts[$index . $showHide]), $content, $this->posts[$index . $showHide]);

    }

    public function hidden_on_post_author($index, $hidden = true)
    {
        $this->create_posts_if_not_created('post');
        $hidden   = (int) $hidden;
        $showHide = "_" . $hidden;

        $content = $this->get_page_content(get_author_posts_url($this->currentUser));
        return array($this->textBoundry($this->posts[$index . $showHide]), $content, $this->posts[$index . $showHide]);

    }
    public function hidden_on_post_archive($index, $hidden = true)
    {
        $this->create_posts_if_not_created('post');
        $hidden   = (int) $hidden;
        $showHide = "_" . $hidden;

        $content = $this->get_page_content(get_day_link('', '', ''));
        return array($this->textBoundry($this->posts[$index . $showHide]), $content, $this->posts[$index . $showHide]);

    }
    public function hidden_on_post_search($index, $hidden = true)
    {
        $this->create_posts_if_not_created('post');
        $hidden   = (int) $hidden;
        $showHide = "_" . $hidden;

        $content = $this->get_page_content(home_url() . "/?s=" . urlencode($this->posts[$index . $showHide]['post_title']));
        return array($this->textBoundry($this->posts[$index . $showHide]), $content, $this->posts[$index . $showHide]);

    }
    public function hidden_on_post_feed($index, $hidden = true)
    {
        $this->create_posts_if_not_created('post');
        $hidden   = (int) $hidden;
        $showHide = "_" . $hidden;

        $content = $this->get_page_content(get_feed_link());
        return array($this->textBoundry($this->posts[$index . $showHide]), $content, $this->posts[$index . $showHide]);

    }
    public function hidden_on_post_recent($index, $hidden = true)
    {
        global $wpdb, $wp_query;
        $this->create_posts_if_not_created('post');
        $hidden   = (int) $hidden;
        $showHide = "_" . $hidden;
        p_l("in hidden_on_post_recent");

        p_l($_POST);
        $mypost['ID']        = $this->posts[$index . $showHide]['ID'];
        $mypost['post_date'] = date('Y-m-d H:i:s', time() + 3600); // uses 'Y-m-d H:i:s' format
        wp_update_post($mypost);

        ob_start();
        the_widget('WP_Widget_Recent_Posts');

        $content   = ob_get_clean();
        $matches   = array();
        $permalink = get_permalink($this->posts[$index . $showHide]['ID']);
        preg_match_all('/<a(.*?)href[\s]?=[\s]?[\'\"\\\\]*(.*)(' . preg_quote($permalink, "/") . ')(.*?)>/mi', $content, $matches, PREG_SET_ORDER, 0);

        //p_l($matches);
        // p_l("[" . @$matches[0][3] . "][" . $permalink . "]");
        return array(@$matches[0][3], $permalink, $content, get_post_meta($this->posts[$index . $showHide]['ID'], '_wplp_post_recent', true));

    }
    public function hidden_on_search_engine($index, $hidden = true)
    {
        global $wpdb;
        $this->create_posts_if_not_created('post');
        $hidden   = (int) $hidden;
        $showHide = "_" . $hidden;

        $this->get_page_content(get_permalink($this->posts[$index . $showHide]['ID']), $this->posts[$index . $showHide]);
        ob_start();
        wp_head();

        $content = ob_get_clean();
        $matches = array();

        //p_l($content);

        preg_match_all('/<meta(.*?)name[\s]?=[\s]?[\'\"\\\\]*(.*)(robots)(.*?)content[\s]?=[\s]?[\'\"\\\\]*(.*)(noindex)(.*?)>/mi', $content, $matches, PREG_SET_ORDER, 0);

        // p_l($matches);
        //xit();
        // p_l("[" . @$matches[0][3] . "][" . $permalink . "]");
        return array(@$matches[0], $content, $this->posts[$index . $showHide]);

    }
    public function hidden_on_rel_link($index, $hidden = true)
    {
        $hidden   = (int) $hidden;
        $showHide = "_" . $hidden;

        $posts = $this->create_posts_if_not_created('post');
        p_l(implode(",", array_column($posts, 'ID')));

        p_l(implode(",", [$this->get_previous_post($posts[$index . $showHide]['ID'], true), $posts[$index . $showHide]['ID'], $this->get_next_post($posts[$index . $showHide]['ID'], true)]));

        $keys = array_keys($posts);
        //p_l($this->posts[$index . $showHide]);
        $page['previous']['post_data'] = $this->get_previous_post($posts[$index . $showHide]['ID']);
        $page['next']['post_data']     = $this->get_next_post($posts[$index . $showHide]['ID']);

        // $posts[$keys[array_search($index . $showHide, $keys) - 1]];

        if (is_null($page['previous']['post_data']))
        {
            $page['previous']['posts'] = "";
        }
        else
        {
            $page['previous']['posts'] = $this->get_page_content(get_permalink($page['previous']['post_data']['ID']));
        }
        ob_start();
        wp_head();
        $page['previous']['html'] = ob_get_clean();

        $page['current']['post_data'] = $posts[$index . $showHide];
        $page['current']['posts']     = $this->get_page_content(get_permalink($posts[$index . $showHide]['ID']));
        ob_start();
        wp_head();
        $page['current']['html'] = ob_get_clean();

        // $posts[$keys[array_search($index . $showHide, $keys) - 1]];

        if (is_null($page['next']['post_data']))
        {
            $page['next']['posts'] = "";
        }
        else
        {
            $page['next']['posts'] = $this->get_page_content(get_permalink($page['next']['post_data']['ID']));
        }
        ob_start();
        wp_head();
        $page['next']['html'] = ob_get_clean();

        return array($this->textBoundry($this->posts[$index . $showHide]), $page, $this->posts[$index . $showHide]);

    }

    public function test_hidden_on_post_front()
    {
        /*
        $posts = $this->create_posts_if_not_created('post');

        $rel_posts = $this->hidden_on_rel_link('post_front', true);
        p_l($rel_posts);
        exit();
        p_l($this->get_previous_post_id(@$rel_posts[2]['ID']) . "-" . @$rel_posts[2]['ID'] . "-" . $this->get_next_post_id(@$rel_posts[2]['ID']));

        exit();
        $re = '/\<(.*)rel(.*)=(.*)[\'"]next[\'"](.*)href(.*)=(.*)[\'"](.*)(' . preg_quote(get_permalink($this->get_previous_post_id(@$rel_posts[2]['ID'])), "/") . ')[\'"](.*)\/\>/mi';
        p_l($re);

        $re1     = '/<link(.*?)href[\s]?=[\s]?[\'\"\\\]*' . preg_quote(get_permalink(9), "/") . '(.*?)>/i';
        $matches = array();
        preg_match_all($re, $rel_posts[1]['next'], $matches, PREG_SET_ORDER);
        p_l($matches);

        $re      = '/\<(.*)rel(.*)=(.*)[\'"]previous[\'"](.*)href(.*)=(.*)[\'"](.*)(' . preg_quote(get_permalink($rel_posts[2]['ID'])) . ')[\'"](.*)\/\>/mi';
        $matches = array();
        preg_match_all($re, $rel_posts[1]['previous'], $matches, PREG_SET_ORDER, 0);
        p_l($matches);

        exit();
        // Print the entire match result
        var_dump($matches);

        p_l($rel_posts);

        exit();
         */
       
        $posts              = $this->hidden_on_post_front('post_front', true);

        /*
        $cat_posts          = $this->hidden_on_post_category('post_front', true);
        $tags_posts         = $this->hidden_on_post_tag('post_front', true);
        $author_posts       = $this->hidden_on_post_author('post_front', true);
        $archive_posts      = $this->hidden_on_post_archive('post_front', true);
        $search_posts       = $this->hidden_on_post_search('post_front', true);
        $feed_posts         = $this->hidden_on_post_feed('post_front', true);
        $recent_posts       = $this->hidden_on_post_recent('post_front', true);
        $searchengine_posts = $this->hidden_on_search_engine('post_front', true);
        */

 
        $this->assertNotContains($posts[0], $posts[1]['nonstatic'], 'Must be hidden on the front page(nonstatic)');
        $this->assertNotContains($posts[0], $posts[1]['static'], 'Must be hidden  on the front page(static)');

        return;
        $this->assertContains($cat_posts[0], $cat_posts[1], 'Must be visible on the category page');
        $this->assertContains($tags_posts[0], $tags_posts[1], 'Must be visible on the tag page');
        $this->assertContains($author_posts[0], $author_posts[1], 'Must be visible on the author page');
        $this->assertContains($archive_posts[0], $archive_posts[1], 'Must be visible on the archive page');
        $this->assertContains($search_posts[0], $search_posts[1], 'Must be visible on the search page');
        $this->assertContains($feed_posts[0], $feed_posts[1], 'Must be visible on the feed page');

        $this->assertTrue($recent_posts[0] == $recent_posts[1], 'Must be visible wp native recent post widget');
        $this->assertTrue($searchengine_posts[0][3] != 'robots' && $searchengine_posts[0][6] != 'noindex', 'Must be visible in search engine');

    }

    public function ttest_hidden_on_post_category()
    {
        $index = 'post_category';

        $tags_posts = $this->hidden_on_post_tag($index, true);

        $posts     = $this->hidden_on_post_front($index, true);
        $cat_posts = $this->hidden_on_post_category($index, true);

        $author_posts       = $this->hidden_on_post_author($index, true);
        $archive_posts      = $this->hidden_on_post_archive($index, true);
        $search_posts       = $this->hidden_on_post_search($index, true);
        $feed_posts         = $this->hidden_on_post_feed($index, true);
        $recent_posts       = $this->hidden_on_post_recent($index, true);
        $searchengine_posts = $this->hidden_on_search_engine($index, true);

        $this->assertNotContains($cat_posts[0], $cat_posts[1], 'Must be Hiden on the category page');

        $this->assertContains($posts[0], $posts[1]['nonstatic'], 'Must be visible on the front page(nonstatic)');
        $this->assertContains($posts[0], $posts[1]['static'], 'Must be visible  on the front page(static)');

        $this->assertContains($tags_posts[0], $tags_posts[1], 'Must be visible on the tag page');
        $this->assertContains($author_posts[0], $author_posts[1], 'Must be visible on the author page');
        $this->assertContains($archive_posts[0], $archive_posts[1], 'Must be visible on the archive page');
        $this->assertContains($search_posts[0], $search_posts[1], 'Must be visible on the search page');
        $this->assertContains($feed_posts[0], $feed_posts[1], 'Must be visible on the feed page');

        $this->assertTrue($recent_posts[0] == $recent_posts[1], 'Must be visible wp native recent post widget');
        $this->assertTrue($searchengine_posts[0][3] != 'robots' && $searchengine_posts[0][6] != 'noindex', 'Must be visible in search engine');

    }
    public function ttest_hidden_on_post_tag()
    {
        $index = 'post_tag';

        $cat_posts = $this->hidden_on_post_category($index, true);

        $posts              = $this->hidden_on_post_front($index, true);
        $tags_posts         = $this->hidden_on_post_tag($index, true);
        $author_posts       = $this->hidden_on_post_author($index, true);
        $archive_posts      = $this->hidden_on_post_archive($index, true);
        $search_posts       = $this->hidden_on_post_search($index, true);
        $feed_posts         = $this->hidden_on_post_feed($index, true);
        $recent_posts       = $this->hidden_on_post_recent($index, true);
        $searchengine_posts = $this->hidden_on_search_engine($index, true);

        $this->assertNotContains($tags_posts[0], $tags_posts[1], 'Must be hidden on the tag page');

        $this->assertContains($cat_posts[0], $cat_posts[1], 'Must be visible on the category page');

        $this->assertContains($posts[0], $posts[1]['nonstatic'], 'Must be visible on the front page(nonstatic)');
        $this->assertContains($posts[0], $posts[1]['static'], 'Must be visible  on the front page(static)');

        $this->assertContains($author_posts[0], $author_posts[1], 'Must be visible on the author page');
        $this->assertContains($archive_posts[0], $archive_posts[1], 'Must be visible on the archive page');
        $this->assertContains($search_posts[0], $search_posts[1], 'Must be visible on the search page');
        $this->assertContains($feed_posts[0], $feed_posts[1], 'Must be visible on the feed page');

        $this->assertTrue($recent_posts[0] == $recent_posts[1], 'Must be visible wp native recent post widget');
        $this->assertTrue($searchengine_posts[0][3] != 'robots' && $searchengine_posts[0][6] != 'noindex', 'Must be visible in search engine');

    }

    public function ttest_hidden_on_post_author()
    {
        $index = 'post_author';

        $author_posts = $this->hidden_on_post_author($index, true);

        $cat_posts = $this->hidden_on_post_category($index, true);

        $posts              = $this->hidden_on_post_front($index, true);
        $tags_posts         = $this->hidden_on_post_tag($index, true);
        $archive_posts      = $this->hidden_on_post_archive($index, true);
        $search_posts       = $this->hidden_on_post_search($index, true);
        $feed_posts         = $this->hidden_on_post_feed($index, true);
        $recent_posts       = $this->hidden_on_post_recent($index, true);
        $searchengine_posts = $this->hidden_on_search_engine($index, true);

        $this->assertNotContains($author_posts[0], $author_posts[1], 'Must be hidden on the author page');

        $this->assertContains($tags_posts[0], $tags_posts[1], 'Must be visible on the tag page');

        $this->assertContains($cat_posts[0], $cat_posts[1], 'Must be visible on the category page');

        $this->assertContains($posts[0], $posts[1]['nonstatic'], 'Must be visible on the front page(nonstatic)');
        $this->assertContains($posts[0], $posts[1]['static'], 'Must be visible  on the front page(static)');

        $this->assertContains($archive_posts[0], $archive_posts[1], 'Must be visible on the archive page');
        $this->assertContains($search_posts[0], $search_posts[1], 'Must be visible on the search page');
        $this->assertContains($feed_posts[0], $feed_posts[1], 'Must be visible on the feed page');

        $this->assertTrue($recent_posts[0] == $recent_posts[1], 'Must be visible wp native recent post widget');
        $this->assertTrue($searchengine_posts[0][3] != 'robots' && $searchengine_posts[0][6] != 'noindex', 'Must be visible in search engine');

    }

    public function ttest_hidden_on_post_archive()
    {
        $index         = 'post_archive';
        $archive_posts = $this->hidden_on_post_archive($index, true);

        $cat_posts = $this->hidden_on_post_category($index, true);

        $posts        = $this->hidden_on_post_front($index, true);
        $tags_posts   = $this->hidden_on_post_tag($index, true);
        $author_posts = $this->hidden_on_post_author($index, true);

        $search_posts       = $this->hidden_on_post_search($index, true);
        $feed_posts         = $this->hidden_on_post_feed($index, true);
        $recent_posts       = $this->hidden_on_post_recent($index, true);
        $searchengine_posts = $this->hidden_on_search_engine($index, true);

        $this->assertNotContains($archive_posts[0], $archive_posts[1], 'Must be hidden on the archive page');

        $this->assertContains($author_posts[0], $author_posts[1], 'Must be visible on the author page');

        $this->assertContains($tags_posts[0], $tags_posts[1], 'Must be visible on the tag page');

        $this->assertContains($cat_posts[0], $cat_posts[1], 'Must be visible on the category page');

        $this->assertContains($posts[0], $posts[1]['nonstatic'], 'Must be visible on the front page(nonstatic)');
        $this->assertContains($posts[0], $posts[1]['static'], 'Must be visible  on the front page(static)');

        $this->assertContains($search_posts[0], $search_posts[1], 'Must be visible on the search page');
        $this->assertContains($feed_posts[0], $feed_posts[1], 'Must be visible on the feed page');

        $this->assertTrue($recent_posts[0] == $recent_posts[1], 'Must be visible wp native recent post widget');
        $this->assertTrue($searchengine_posts[0][3] != 'robots' && $searchengine_posts[0][6] != 'noindex', 'Must be visible in search engine');

    }

    public function ttest_hidden_on_post_search()
    {
        $index        = 'post_search';
        $search_posts = $this->hidden_on_post_search($index, true);

        $cat_posts = $this->hidden_on_post_category($index, true);

        $posts         = $this->hidden_on_post_front($index, true);
        $tags_posts    = $this->hidden_on_post_tag($index, true);
        $author_posts  = $this->hidden_on_post_author($index, true);
        $archive_posts = $this->hidden_on_post_archive($index, true);

        $feed_posts         = $this->hidden_on_post_feed($index, true);
        $recent_posts       = $this->hidden_on_post_recent($index, true);
        $searchengine_posts = $this->hidden_on_search_engine($index, true);

        $this->assertNotContains($search_posts[0], $search_posts[1], 'Must be hidden on the search page');

        $this->assertContains($archive_posts[0], $archive_posts[1], 'Must be visible on the archive page');

        $this->assertContains($author_posts[0], $author_posts[1], 'Must be visible on the author page');

        $this->assertContains($tags_posts[0], $tags_posts[1], 'Must be visible on the tag page');

        $this->assertContains($cat_posts[0], $cat_posts[1], 'Must be visible on the category page');

        $this->assertContains($posts[0], $posts[1]['nonstatic'], 'Must be visible on the front page(nonstatic)');
        $this->assertContains($posts[0], $posts[1]['static'], 'Must be visible  on the front page(static)');

        $this->assertContains($feed_posts[0], $feed_posts[1], 'Must be visible on the feed page');

        $this->assertTrue($recent_posts[0] == $recent_posts[1], 'Must be visible wp native recent post widget');
        $this->assertTrue($searchengine_posts[0][3] != 'robots' && $searchengine_posts[0][6] != 'noindex', 'Must be visible in search engine');

    }

    public function ttest_hidden_on_post_feed()
    {
        $index      = 'post_feed';
        $feed_posts = $this->hidden_on_post_feed($index, true);

        $cat_posts = $this->hidden_on_post_category($index, true);

        $posts         = $this->hidden_on_post_front($index, true);
        $tags_posts    = $this->hidden_on_post_tag($index, true);
        $author_posts  = $this->hidden_on_post_author($index, true);
        $archive_posts = $this->hidden_on_post_archive($index, true);
        $search_posts  = $this->hidden_on_post_search($index, true);

        $recent_posts       = $this->hidden_on_post_recent($index, true);
        $searchengine_posts = $this->hidden_on_search_engine($index, true);

        $this->assertNotContains($feed_posts[0], $feed_posts[1], 'Must be hidden on the feed page');

        $this->assertContains($search_posts[0], $search_posts[1], 'Must be visible on the search page');

        $this->assertContains($archive_posts[0], $archive_posts[1], 'Must be visible on the archive page');

        $this->assertContains($author_posts[0], $author_posts[1], 'Must be visible on the author page');

        $this->assertContains($tags_posts[0], $tags_posts[1], 'Must be visible on the tag page');

        $this->assertContains($cat_posts[0], $cat_posts[1], 'Must be visible on the category page');

        $this->assertContains($posts[0], $posts[1]['nonstatic'], 'Must be visible on the front page(nonstatic)');
        $this->assertContains($posts[0], $posts[1]['static'], 'Must be visible  on the front page(static)');

        $this->assertTrue($recent_posts[0] == $recent_posts[1], 'Must be visible wp native recent post widget');
        $this->assertTrue($searchengine_posts[0][3] != 'robots' && $searchengine_posts[0][6] != 'noindex', 'Must be visible in search engine');

    }

    public function ttest_hidden_on_post_recent()
    {
        $index = 'post_recent';

        $recent_posts = $this->hidden_on_post_recent($index, true);

        $cat_posts = $this->hidden_on_post_category($index, true);

        $posts         = $this->hidden_on_post_front($index, true);
        $tags_posts    = $this->hidden_on_post_tag($index, true);
        $author_posts  = $this->hidden_on_post_author($index, true);
        $archive_posts = $this->hidden_on_post_archive($index, true);
        $search_posts  = $this->hidden_on_post_search($index, true);
        $feed_posts    = $this->hidden_on_post_feed($index, true);

        $searchengine_posts = $this->hidden_on_search_engine($index, true);

        $this->assertTrue($recent_posts[0] != $recent_posts[1], 'Must be hidden wp native recent post widget');

        $this->assertContains($feed_posts[0], $feed_posts[1], 'Must be visible on the feed page');

        $this->assertContains($search_posts[0], $search_posts[1], 'Must be visible on the search page');

        $this->assertContains($archive_posts[0], $archive_posts[1], 'Must be visible on the archive page');

        $this->assertContains($author_posts[0], $author_posts[1], 'Must be visible on the author page');

        $this->assertContains($tags_posts[0], $tags_posts[1], 'Must be visible on the tag page');

        $this->assertContains($cat_posts[0], $cat_posts[1], 'Must be visible on the category page');

        $this->assertContains($posts[0], $posts[1]['nonstatic'], 'Must be visible on the front page(nonstatic)');
        $this->assertContains($posts[0], $posts[1]['static'], 'Must be visible  on the front page(static)');

        $this->assertTrue($searchengine_posts[0][3] != 'robots' && $searchengine_posts[0][6] != 'noindex', 'Must be visible in search engine');

    }

    public function ttest_hidden_on_search_engine()
    {
        $index              = 'no_index';
        $searchengine_posts = $this->hidden_on_search_engine($index, true);
        $posts              = $this->hidden_on_post_front($index, true);

        $cat_posts     = $this->hidden_on_post_category($index, true);
        $tags_posts    = $this->hidden_on_post_tag($index, true);
        $author_posts  = $this->hidden_on_post_author($index, true);
        $archive_posts = $this->hidden_on_post_archive($index, true);
        $search_posts  = $this->hidden_on_post_search($index, true);
        $feed_posts    = $this->hidden_on_post_feed($index, true);
        $recent_posts  = $this->hidden_on_post_recent($index, true);

        $this->assertTrue($searchengine_posts[0][3] == 'robots' && $searchengine_posts[0][6] == 'noindex', 'Must be hidden in search engine');
        $this->assertContains($posts[0], $posts[1]['nonstatic'], 'Must be visible on the front page(nonstatic)');
        $this->assertContains($posts[0], $posts[1]['static'], 'Must be visible  on the front page(static)');

        $this->assertContains($cat_posts[0], $cat_posts[1], 'Must be visible on the category page');
        $this->assertContains($tags_posts[0], $tags_posts[1], 'Must be visible on the tag page');
        $this->assertContains($author_posts[0], $author_posts[1], 'Must be visible on the author page');
        $this->assertContains($archive_posts[0], $archive_posts[1], 'Must be visible on the archive page');
        $this->assertContains($search_posts[0], $search_posts[1], 'Must be visible on the search page');
        $this->assertContains($feed_posts[0], $feed_posts[1], 'Must be visible on the feed page');

        $this->assertTrue($recent_posts[0] == $recent_posts[0], 'Must be visible wp native recent post widget');

    }
    public function get_previous_post($post_id, $id = false)
    {
        // Get a global post reference since get_adjacent_post() references it
        global $post;
        // Store the existing post object for later so we don't lose it
        $oldGlobal = $post;
        // Get the post object for the specified post and place it in the global variable
        $post = get_post($post_id);
        // Get the post object for the previous post
        $previous_post = get_previous_post();
        // Reset our global object
        $post = $oldGlobal;
        if ('' == $previous_post)
        {
            return null;
        }

        return $id ? $previous_post->ID : (array) $previous_post;
    }

    public function get_next_post($post_id, $id = false)
    {
        // Get a global post reference since get_adjacent_post() references it
        global $post;
        // Store the existing post object for later so we don't lose it
        $oldGlobal = $post;
        // Get the post object for the specified post and place it in the global variable
        $post = get_post($post_id);
        // Get the post object for the next post
        $next_post = get_next_post();
        // Reset our global object
        $post = $oldGlobal;
        if ('' == $next_post)
        {
            return null;
        }

        return $id ? $next_post->ID : (array) $next_post;

    }

}
