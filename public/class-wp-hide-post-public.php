<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://scriptburn.com
 * @since      2.0
 *
 * @package    wp_hide_post
 * @subpackage wp_hide_post/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    wp_hide_post
 * @subpackage wp_hide_post/public
 * @author     ScriptBurn <support@scriptburn.com>
 */
if (!class_exists('wp_hide_post_Public'))
{
    class wp_hide_post_Public
    {
        /**
         * The version of this plugin.
         *
         * @since    2.0.11
         * @access   private
         * @var      array    $hiddenPages    Holds all hidden pages.
         */
        private $hiddenPages;

        /**
         * The ID of this plugin.
         *
         * @since    1.2.2
         * @access   private
         * @var      string    $wp_hide_post    The ID of this plugin.
         */
        private $wp_hide_post;

        /**
         * The version of this plugin.
         *
         * @since    1.2.2
         * @access   private
         * @var      string    $version    The current version of this plugin.
         */
        private $version;

        /**
         * Initialize the class and set its properties.
         *
         * @since    1.2.2
         * @param      string    $wp_hide_post       The name of the plugin.
         * @param      string    $version    The version of this plugin.
         */
        public function __construct($wp_hide_post, $version)
        {

            $this->wp_hide_post = $wp_hide_post;
            $this->version      = $version;

        }

        /**
         * Register the stylesheets for the public-facing side of the site.
         *
         * @since    1.2.2
         */
        public function enqueue_styles()
        {

            /**
             * This function is provided for demonstration purposes only.
             *
             * An instance of this class should be passed to the run() function
             * defined in wp_hide_post_Loader as all of the hooks are defined
             * in that particular class.
             *
             * The wp_hide_post_Loader will then create the relationship
             * between the defined hooks and the functions defined in this
             * class.
             */

            //  wp_enqueue_style($this->wp_hide_post, plugin_dir_url(__FILE__) . 'css/wp-hide-post-public.css', array(), $this->version, 'all');

        }

        /**
         * Register the JavaScript for the public-facing side of the site.
         *
         * @since    1.2.2
         */
        public function enqueue_scripts()
        {

            /**
             * This function is provided for demonstration purposes only.
             *
             * An instance of this class should be passed to the run() function
             * defined in wp_hide_post_Loader as all of the hooks are defined
             * in that particular class.
             *
             * The wp_hide_post_Loader will then create the relationship
             * between the defined hooks and the functions defined in this
             * class.
             */

            wp_enqueue_script($this->wp_hide_post, plugin_dir_url(__FILE__) . 'js/wp-hide-post-public.js', array('jquery'), $this->version, false);

        }
        private function exclude_low_profile_items($item_type, $posts)
        {
            p_l("called: exclude_low_profile_items:$item_type");
            if ($item_type != 'page')
            {
                return $posts;
            }
            // regular posts & search results are filtered in wphp_query_posts_join
            else
            {
                if (wphp_is_applicable('page'))
                {
                    global $wpdb;
                    // now loop over the pages, and exclude the ones with low profile in this context
                    $result     = array();
                    $page_flags = $this->getHiddenPages();

                    if ($posts)
                    {
                        foreach ($posts as $post)
                        {
                            $check = isset($page_flags[$post->ID]) ? $page_flags[$post->ID]->meta_value : null;
                            $isAll = isset($page_flags[$post->ID]) ? $page_flags[$post->ID]->meta_key == '_wplp_hide_always' && $page_flags[$post->ID]->meta_value : false;
                            $isAll = $isAll || $check == 'all';
                            if (($check == 'front' && wphp_is_front_page()) || $isAll)
                            {
                                // exclude page
                            }
                            else
                            {
                                $result[] = $post;
                            }

                        }
                    }
                    return $result;
                }
                else
                {
                    return $posts;
                }

            }
        }
        /**
         * returns system info data for support requests
         * @param $posts
         * @return unknown_type
         */

        public function support_info()
        {
            if (isset($_REQUEST['support_info']))
            {
                if (isset($_REQUEST['hash']) && get_option('wphp_support_hash') == $_REQUEST['hash'])
                {
                    echo (scb_systems_info("</br>"));
                }

                wp_die();
            }
        }
        /**
         * Hook function to filter out hidden pages (get_pages)
         * @param $posts
         * @return unknown_type
         */

        public function exclude_low_profile_pages($posts)
        {
            p_l("called: wphp_exclude_low_profile_pages");
            return $this->exclude_low_profile_items('page', $posts);
        }

        /**
         *Remove item from rel post link
         * @param $where
         * @return unknown_type
         *
         */
        public function query_posts_where_rel_exclude($where)
        {
            // filter posts on one of the three kinds of contexts: front, category, feed
            $where .= ' AND ' . WPHP_TABLE_NAME . '.post_id IS NULL ';

            //echo "\n<!-- WPHP: ".$where." -->\n";
            return $where;
        }

        /**
         *
         * @param $where
         * @return unknown_type
         */
        public function query_posts_where($where, $wp_query = null)
        {
            p_l("query_posts_where");
            // filter posts on one of the three kinds of contexts: front, category, feed
            if (wphp_is_applicable('post', $wp_query) && wphp_is_applicable('page', $wp_query))
            {
                //  $where .= ' AND ' . WPHP_TABLE_NAME . '.post_id IS NULL ';
                $where .= ' AND c.post_id IS NULL ';

            }
            //echo "\n<!-- WPHP: ".$where." -->\n";
            return $where;
        }

        //add our custom variablein this filter so in query_posts_join wer will know that the Wp-query
        // is being called from recent posts widget
        public function widget_posts_args($posts_args)
        {
            p_l("in widget_posts_args");
            $posts_args['wphp_inside_recent_post_sidebar'] = 1;

            return $posts_args;
        }

        private function get_exclude_join($params)
        {

            if (empty($params['table']))
            {
                $params['table'] = WP_POSTS_TABLE_NAME;
            }
            $params['wp_query']       = isset($params['wp_query']) ? $params['wp_query'] : null;
            $params['from_rel_query'] = isset($params['from_rel_query']) ? $params['from_rel_query'] : null;
            $join                     = "";
            //  $join .= ' -' . WPHP_TABLE_NAME . ' wphptbl ON ' . WP_POSTS_TABLE_NAME . ".ID = wphptbl.post_id and wphptbl.meta_key like '" . WPHP_META_VALUE_PREFIX . "%'";
            //p_n($join);
            //p_d(stripos( "JOIN " . WPHP_TABLE_NAME,$join),1);
            $alias = "c";
            if (stripos($join, "JOIN " . WPHP_TABLE_NAME) === false)
            {
                $join .= ' LEFT JOIN ' . WPHP_TABLE_NAME . " $alias   ON " . $params['table'] . ".ID = " . $alias . ".post_id ";
            }
            $join .= " and " . $alias . ".meta_key like '" . WPHP_META_VALUE_PREFIX . "%' and (";
            // filter posts
            $can_display                   = null;
            $keys                          = array();
            $visibility_types_data['post'] = wp_hide_post::getInstance()->pluginAdmin()->get_visibility_types('post');
            $visibility_types_data['page'] = wp_hide_post::getInstance()->pluginAdmin()->get_visibility_types('page');
            $post_joins                    = array();
            // p_l($visibility_types_data);
            $args    = $params;
            $sidebar = wphp_is_post_sidebar($params['wp_query']);

            foreach ($visibility_types_data as $post_type => $visibility_types)
            {
                if ($post_type == 'page')
                {
                    $post_joins[$post_type][] = $params['table'] . '.' . "post_type='page'";

                }
                else
                {
                    $post_joins[$post_type][] = $params['table'] . '.' . wphp_allowed_post_types(true, 'page');
                }
                if ($post_type == 'page' && is_search())
                {
                    // $keys[] = '_wplp_page_search';
                }
                foreach ($visibility_types as $visibility_type => $detail)
                {

                    if ($sidebar && $visibility_type != 'post_recent')
                    {
                        continue;
                    }
                    $condition = !empty($detail['condition']) ? $detail['condition'] : $visibility_type;
                    $callbacks = array();

                    if (!empty($detail['condition_callback']))
                    {
                        $callbacks[] = $detail['condition_callback'];
                    }
                    else
                    {
                        // p_n('wphp_is_' . $condition . "_" . $post_type);
                        $callbacks[] = 'wphp_is_' . $condition . "_" . $post_type;
                    }

                    foreach ($callbacks as $callback)
                    {
                        if ($callback)
                        {

                            if (is_callable($callback))
                            {

                                $ret = call_user_func_array($callback, array(($args)));
                                p_l("Callback: $callback=" . ((int) $ret));
                                if ($ret)
                                {
                                    $keys[] = sprintf('%1$s%2$s', WPHP_META_VALUE_PREFIX, $condition);
                                    break;
                                }
                            }
                        }
                    }
                }

                if (!count($keys))
                {
                    $post_joins[$post_type][] = sprintf($alias . '.meta_key not like  %1$s', "'" . WPHP_META_VALUE_PREFIX . "'");
                }
                else
                {
                    $pre = "";

                    $post_joins[$post_type][] = sprintf("(" . $alias . '.meta_key  in ( %1$s) %2$s)', "'" . implode("','", $keys) . "'", '  ' . $pre);

                }

            }
            //   p_l($post_joins);
            //      p_l($callbacks);
            foreach ($post_joins as $post_type => $joins)
            {
                $post_joins[$post_type] = "( " . implode(" and ", $joins) . ")";
            }

            $join .= implode("  OR ", $post_joins) . ")";
            p_l($join);
            return $join;
        }
        /**
         * add join clauss in main filter query
         *
         * @since    1.2.2
         */

        public function query_posts_join($join, $wp_query)
        {
            if (isset($wp_query->query['wphp_inside_recent_post_sidebar']))
            {
                //p_n($wp_query);
            }

            // p_n("called: wphp_query_posts_join");
            if (wphp_is_applicable('post', $wp_query) && wphp_is_applicable('page', $wp_query))
            {

                if (!$join)
                {
                    $join = '';
                }
                $params = array('table' => WP_POSTS_TABLE_NAME, 'wp_query' => $wp_query);
                $join .= $this->get_exclude_join($params);

            }

            return $join;
        }

        /**
         * add join clauss in main filter query ,This partiucular function only runs when runnign in unit test, Needed to create seperate function due to weird phpunit bug that was throwing warning "Parameter 2 to wp_hide_post_Public::query_posts_join() expected to be a reference, value given" only when running in unit tests
         *
         * @since    2.0.11
         */

        public function query_posts_join_unit($join, $wp_query)
        {
            if (isset($wp_query->query['wphp_inside_recent_post_sidebar']))
            {
                //p_n($wp_query);
            }

            // p_n("called: wphp_query_posts_join");
            if (wphp_is_applicable('post', $wp_query) && wphp_is_applicable('page', $wp_query))
            {

                if (!$join)
                {
                    $join = '';
                }
                $params = array('table' => WP_POSTS_TABLE_NAME, 'wp_query' => $wp_query);
                $join .= $this->get_exclude_join($params);

            }

            return $join;
        }

        public function post_excluded_terms_join_rel($join)
        {
            // p_n("post_excluded_terms_join_rel");
            //SELECT p.ID FROM scbposts AS p (join) WHERE p.post_date < '2016-07-25 12:58:19' AND p.post_type = 'post'  AND ( p.post_status = 'publish' OR p.post_status = 'private' ) ORDER BY p.post_date DESC LIMIT 1
            $params = array('table' => 'p', 'wp_query' => null, 'from_rel_query' => true);

            $join .= ' LEFT JOIN ' . WPHP_TABLE_NAME . '   ON ' . $params['table'] . ".ID = " . WPHP_TABLE_NAME . ".post_id  and " . WPHP_TABLE_NAME . ".meta_key ='" . WPHP_META_VALUE_PREFIX . "post_rel'";

            return $join;
        }
        public function test_enable_allposts_everywhere($query)
        {

            // Only filter the main query on the front-end
            if (is_admin() || !$query->is_main_query())
            {
                return;
            }

            global $wp;
            $front = false;

            // If the latest posts are showing on the home page
            if ((is_home() && empty($wp->query_string)))
            {
                $front = true;
            }

            // If a static page is set as the home page
            if (($query->get('page_id') == get_option('page_on_front') && get_option('page_on_front')) || empty($wp->query_string))
            {
                $front = true;
            }

            if ($front || is_archive())
            {
                p_d(wphp_allowed_post_types());
                $query->set('post_type', wphp_allowed_post_types());

            }
            //endif;
            return $query;

        }

        /**
         * Filter to filter out hidden pages from menu
         *
         * @since    2.0.11
         */

        public function wp_get_nav_menu_items($menus, $menu, $args)
        {

            $pages = $this->getHiddenPages();
            $ids   = array();
            // p_l($pages);
            foreach ($pages as $k => $v)
            {
                if ((($v->meta_key == '_wplp_nohide_search' && $v->meta_value) || ($v->meta_key == '_wplp_hide_always' && $v->meta_value) || ($v->meta_key == '_wplp_page_flags' && $v->meta_value == 'all')))
                {
                    $ids[] = $k;
                }
            }
            $current_menus = array();
            foreach ($menus as $m)
            {
                $current_menus[] = (array) $m;
            }
            /*
            echo ("<!--");
            p_n($ids);
            p_n($current_menus);
            p_n(array_column($current_menus,'object_id'));
             */
            $ids = array_intersect($ids, array_column($current_menus, 'object_id'));
            /*
            p_n($ids);

            echo ("-->");
             */

            if (count($ids))
            {
                foreach ($menus as $k => $v)
                {
                    if (in_array($v->object_id, $ids))
                    {
                        unset($menus[$k]);
                    }
                }
            }
            return $menus;
            global $wpdb;

            $sql        = "SELECT a.post_type,b.post_id, b.meta_key, b.meta_value FROM " . WP_POSTS_TABLE_NAME . " a inner join " . WPHP_TABLE_NAME . " b on a.ID=b.post_id WHERE  b.meta_key = '_wplp_hide_always' and post_id in('" . implode("','", $ids) . "')";
            $page_flags = $wpdb->get_results($sql, OBJECT_K);

            return $menus;

        }
        /**
         * Sets noindex meta tag for searchengines
         *
         * @since    2.0.11
         */
        public function add_noindex()
        {
            if (is_singular())
            {

                $meta = get_post_meta(get_the_ID(), WPHP_META_VALUE_PREFIX . 'no_index', true);
                p_l("add_noindex " . get_the_ID() . "-" . WPHP_META_VALUE_PREFIX . 'no_index-' . $meta);
                if ($meta)
                {
                    echo ('<meta name="robots" content="noindex"/>' . "\n");
                }

            }
        }
        /**
         * Returns all hidden pages
         *
         * @since    2.0.11
         */

        public function getHiddenPages()
        {
            global $wpdb;
            //if (!($this->hiddenPages))
            {
                $sql = "SELECT b.post_id,a.post_title,a.post_type, b.meta_key,b.meta_value FROM " . WP_POSTS_TABLE_NAME . " a inner join " . WPHP_TABLE_NAME . " b on a.ID=b.post_id WHERE (b.meta_key = '" . WPHP_META_VALUE_PREFIX . "page_flags' or b.meta_key = '" . WPHP_META_VALUE_PREFIX . "nohide_search'  or b.meta_key = '" . WPHP_META_VALUE_PREFIX . "hide_always') and a.post_type='page' ";
                p_l($sql);
                $this->hiddenPages = $wpdb->get_results($sql, OBJECT_K);
            }
            return $this->hiddenPages;

        }
    }
}
