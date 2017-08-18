<?php
/**
 * Class SampleTest
 *
 * @package Wp_Hide_Post
 */

/**
 * Sample test case.
 */

class WPBASE_UnitTestCase extends WP_UnitTestCase
{

    protected $posts, $hiddens, $staticPage, $currentUser;
    public function __construct()
    {
        $this->mysql_dump_bin = 'mysqldump';
        $this->mysql_bin      = 'mysql';

        parent::__construct();
    }
    public function setUp()
    {
        parent::setUp();
        wphp_set_setting('wphp_gen', 'wphp_post_types', array('post', 'page'));

        $this->currentUser = self::factory()->user->create(array('role' => 'administrator'));

        wp_set_current_user($this->currentUser);

        add_shortcode('pw_sample', array($this, 'shortcode_render_items_home_page'), 10, 3);
        add_action('pre_get_posts', array($this, 'action_display_all_posts'), 1);

        // for displaying all posts in feed page
        add_filter('post_limits', array($this, 'filter_set_no_limit'), 10, 2);

    }
    public function filter_set_no_limit($limit, $query)
    {
        if (!is_admin() && $query->is_main_query() && $query->is_feed())
        {
            return '';
        }

        return $limit;
    }
    public function action_display_all_posts($query)
    {
        $query->set( 'post_type', array(
'post', 'page' ) );
        // no affect on admin or other queries
        if (is_admin() || !$query->is_main_query())
        {
            return;
        }

        // if it's an author query
        // if ($query->is_author() || $query->is_archive() || $query->is_feed () )
        {
            // change order and orderby parameters
            $query->set('posts_per_page', -1);
        }

    }
    public function shortcode_render_items_home_page($static = false)
    {

        p_l("in short code");

        $query_opt = array('posts_per_page' => -1, 'post_type' => ['page', 'post'], 'orderby' => 'ID');
        if ($this->staticPage)
        {
            $query_opt['post__not_in'] = array($this->staticPage);
        }
        $the_query = new WP_Query($query_opt);

        //p_l($the_query->request);
        $cnt   = ['start'];
        $cnt[] = "from short code";
        while ($the_query->have_posts())
        {
            $meta = [];

            $the_query->the_post();
            $metas = (get_post_meta(get_the_ID()));
            foreach ($metas as $m => $v)
            {
                $meta[] = "$m=" . $v[0];
            }
            $cnt[] = "[{" . get_the_title() . "-" . get_post_type() . "}-" . get_the_ID() . "-" . get_the_content() . "-" . implode("&", $meta) . "]";

        }
        wp_reset_postdata();
        p_l($the_query->request);
        //p_l(implode("\n", $cnt));
        return implode("\n", $cnt);

    }
    public function create_posts_if_not_created($post_type, $recreate = false)
    {

        if (!$this->posts)
        {

            $this->hiddens = array_keys(wp_hide_post::getInstance()->pluginAdmin()->get_visibility_types($post_type));
            //  p_l($this->hiddens);
            $this->posts = [];
            $common      = [
                'post_type'   => $post_type,
                'post_status' => 'publish',

            ];
            $cat           = array('cat_name' => "common_cat name", 'category_description' => "common_cat cat desc");
            $common_cat_id = wp_insert_category($cat);

            $loop        = 1;
            $res         = $this->create_x_posts($post_type, $common_cat_id, $loop, 5);
            $loop        = $res[0];
            $this->posts = array_merge($this->posts, $res[1]);

            $res         = $this->create_x_posts($post_type == 'post' ? 'page' : 'post', $common_cat_id, $loop, 5);
            $loop        = $res[0];
            $this->posts = array_merge($this->posts, $res[1]);

            foreach ($this->hiddens as $hidden)
            {
                $loop_index = "{$post_type}_{$loop}";

                $cat                          = array('cat_name' => "{$loop_index} cat name", 'category_description' => "{$loop_index} cat desc");
                $my_cat_id                    = wp_insert_category($cat);
                $this->posts["{$loop_index}"] = [
                    'post_title'    => "{$loop_index} title",
                    'post_category' => array($my_cat_id, $common_cat_id),
                    'tags_input'    => array("{$loop_index}_tag"),

                ];

                $loop++;
                $loop_index                   = ($post_type == 'post' ? 'page' : 'post') . "_{$loop}";
                $cat                          = array('cat_name' => "{$loop_index} cat name", 'category_description' => "{$loop_index} cat desc");
                $my_cat_id                    = wp_insert_category($cat);
                $this->posts["{$loop_index}"] = [
                    'post_title'    => "{$loop_index} title",
                    'post_category' => array($my_cat_id, $common_cat_id),
                    'tags_input'    => array("{$loop_index}_tag"),
                    'post_type'     => $post_type == 'post' ? 'page' : 'post',

                ];

                $cat = array('cat_name' => "{$hidden} cat name", 'category_description' => "{$hidden} cat desc");

// Create the category
                $my_cat_id = wp_insert_category($cat);

                $this->posts[$hidden . "_1"] = [
                    'post_title'    => "Hide on the $hidden",
                    'post_category' => array($my_cat_id, $common_cat_id),
                    'tags_input'    => array("{$hidden}_tag"),
                    'extra'         => [
                        'index' => $hidden,
                        'value' => 1,
                    ],
                ];

                $loop++;
                $loop_index = "{$post_type}_{$loop}";

                $cat                          = array('cat_name' => "{$loop_index} cat name", 'category_description' => "{$loop_index} cat desc");
                $my_cat_id                    = wp_insert_category($cat);
                $this->posts["{$loop_index}"] = [
                    'post_title'    => "{$loop_index} title",
                    'post_category' => array($my_cat_id, $common_cat_id),
                    'tags_input'    => array("{$loop_index}_tag"),

                ];

                $loop++;
                $loop_index                   = ($post_type == 'post' ? 'page' : 'post') . "_{$loop}";
                $cat                          = array('cat_name' => "{$loop_index} cat name", 'category_description' => "{$loop_index} cat desc");
                $my_cat_id                    = wp_insert_category($cat);
                $this->posts["{$loop_index}"] = [
                    'post_title'    => "{$loop_index} title",
                    'post_category' => array($my_cat_id, $common_cat_id),
                    'tags_input'    => array("{$loop_index}_tag"),
                    'post_type'     => $post_type == 'post' ? 'page' : 'post',

                ];

                $loop++;
            }
            $res         = $this->create_x_posts($post_type, $common_cat_id, $loop++, 5);
            $loop        = $res[0];
            $this->posts = array_merge($this->posts, $res[1]);

            $res         = $this->create_x_posts($post_type == 'post' ? 'page' : 'post', $common_cat_id, $loop, 5);
            $loop        = $res[0];
            $this->posts = array_merge($this->posts, $res[1]);

            foreach ($this->posts as $index => $post)
            {

                $skip = false;

                if (!$skip)
                {
                    if (isset($post['extra']))
                    {
                        $_POST[WPHP_VISIBILITY_NAME][$post['extra']['index']] = $post['extra']['value'];
                        //p_l($_POST);
                    }

                    //unset($post['extra']);

                    if (empty($post['post_type']))
                    {
                        $post['post_type'] =$common['post_type'];
                        $post['post_status'] =$common['post_status'];


                          
                    }
                    if (empty($post['post_status']))
                    {
                         $post['post_status'] =$common['post_status'];


                          
                    }
                    if (empty($post['post_content']))
                    {
                        $post['post_content'] = $post['post_title'];
                    }
 
                    if (isset($post['extra']))
                    {
                        $_POST["wphp_" . $post['post_type'] . "_edit_nonce"] = wp_create_nonce("wphp_" . $post['post_type'] . "_edit_nonce");
                    }
                    if ($post['post_type'] == 'page')
                    {
                        $post['tax_input']['post_tag'] = $post['tags_input'];
                        //         $post['tax_input']['category']=$post['post_tag'];

                    }

                    $this->posts[$index] = $post;
 
                    $post_id = wp_insert_post($this->posts[$index]);
                    if (is_wp_error($post_id))
                    {
                        throw new Exception($post_id->get_error_message());
                    }
                    if ($this->posts[$index]['post_type'] == 'page')
                    {
                        wp_set_object_terms($post_id, $this->posts[$index]['post_category'], 'category');

                    }
                    p_l("Created " . $post['post_type'] . ":" . $post['post_title'] . "@" . $post_id);
                    unset($_POST[WPHP_VISIBILITY_NAME]);
                    unset($_POST[WPHP_VISIBILITY_NAME . "_old"]);
                    unset($_POST["wphp_" . $post['post_type'] . "_edit_nonce"]);

                }
                $this->posts[$index]['ID'] = $post_id;

            }
            p_l($this->posts);

        }
        return $this->posts;
    }
    private function create_x_posts($post_type, $common_cat_id, $index_start, $nums = 10)
    {
        $posts = array();
        for ($loop = $index_start; $loop < $index_start + $nums; $loop++)
        {
            $loop_index = "{$post_type}_{$loop}";
            //if ($post_type == 'post')
            {
                $cat       = array('cat_name' => "$loop_index cat name", 'category_description' => "$loop_index cat desc");
                $my_cat_id = wp_insert_category($cat);
            }
            //elseif ($post_type == 'page')
            {
                // $posts[$loop_index]['tax_input'] = array(
                //     'category' => array(
                //       "$loop_index cat name",
                //   ),
                // );
            }

            $posts[$loop_index] = [
                'post_title' => "{$loop_index} title",
                'tags_input' => array("{$loop_index}_tag"),
                'post_type'  => $post_type,
                'auto'       => 1,
                'post_status'=>'publish'

            ];
            $posts[$loop_index]['post_category'] = array($my_cat_id, $common_cat_id);

        }
        return [$loop, $posts];
    }

    public function get_page_content($url, $title = "")
    {
        if (is_array($title) || is_object($title))
        {
            $title = (array) $title;
            $title = "{$title['post_title']}-{$title['post_type']}";
        }
        p_l("get_page_content $url " . ($title ? "[$title]" : ''));
        $this->go_to($url);
        global $wp_query;
        $wp_query->set('posts_per_page', -1);

        // $the_query = new WP_Query('posts_per_page=15&post_type=page&orderby=ID');
        //p_l($wp_query);
        // we are on home page and now run the query so shortcode will be called and return all pages on homepage

        $post_content = "";
        while ($wp_query->have_posts())
        {
            $wp_query->the_post();
            $post_content .= ("{" . get_the_title() . "-" . get_post_type() . "}" . get_the_ID() . "-" . apply_filters('the_content', get_the_content()));
        }
        p_l($wp_query->request);
        ob_start();
        wp_footer();
        $foot = ob_get_clean();
        wp_reset_postdata();
        return $post_content . $foot;
    }
    public function set_static_home_page($boolTrue)
    {
        p_l("setting static home page:".($boolTrue?'yes':'no'));
        if (!$this->staticPage && $boolTrue)
        {
            $this->staticPage = $this->factory->post->create(['post_type' => 'page', 'post_title' => 'I am static home page', 'post_content' => "[pw_sample]"]);
            // set the page as home page
        }
        if ($boolTrue)
        {
            update_option('page_on_front', $this->staticPage);
            update_option('show_on_front', 'page');
        }
        else
        {
            delete_option('page_on_front');
            update_option('show_on_front', 'posts');
            $this->staticPage = false;
        }
        return $this->staticPage;
    }
    public function get_home_page_content($static = false)
    {
        $this->set_static_home_page($static);
        return $this->get_page_content(home_url());

    }
    public function textBoundry($post)
    {
        return "{" . $post['post_title'] . "-" . $post['post_type'] . "}";
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
