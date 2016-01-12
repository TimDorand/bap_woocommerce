<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

final class WOOF_EXT_BY_TEXT extends WOOF_EXT
{

    public $type = 'by_html_type';
    public $html_type = 'by_text'; //your custom key here
    public $index = 'woof_text';
    public $html_type_dynamic_recount_behavior = null;

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    public function get_ext_path()
    {
        return plugin_dir_path(__FILE__);
    }

    public function get_ext_link()
    {
        return plugin_dir_url(__FILE__);
    }

    public function woof_add_items_keys($keys)
    {
        $keys[] = $this->html_type;
        return $keys;
    }

    public function init()
    {

        if ((int) get_option('woof_first_init', 0) != 1)
        {
            update_option('woof_show_text_search', 0);
        }

        add_filter('woof_add_items_keys', array($this, 'woof_add_items_keys'));
        add_filter('woof_get_request_data', array($this, 'woof_get_request_data'));
        add_action('woof_print_html_type_options_' . $this->html_type, array($this, 'woof_print_html_type_options'), 10, 1);
        add_action('woof_print_html_type_' . $this->html_type, array($this, 'print_html_type'), 10, 1);
        add_action('wp_head', array($this, 'wp_head'), 1);

        self::$includes['js']['woof_' . $this->html_type . '_html_items'] = $this->get_ext_link() . 'js/' . $this->html_type . '.js';
        self::$includes['css']['woof_' . $this->html_type . '_html_items'] = $this->get_ext_link() . 'css/' . $this->html_type . '.css';
        self::$includes['js_init_functions'][$this->html_type] = 'woof_init_text'; //we have no init function in this case
        //***
        add_shortcode('woof_text_filter', array($this, 'woof_text_filter'));
    }

    public function woof_get_request_data($request)
    {
        if (isset($request['s']))
        {
            $request['woof_text'] = $request['s'];
            //unset($request['s']);
        }

        return $request;
    }

    public function wp_head()
    {
        global $WOOF;
        ?>
        <style type="text/css">
        <?php
        if (isset($WOOF->settings['by_text']['image']))
        {
            if (!empty($WOOF->settings['by_text']['image']))
            {
                ?>
                    .woof_text_search_container .woof_text_search_go{
                        background: url(<?php echo $WOOF->settings['by_text']['image'] ?>) !important;
                    }
                <?php
            }
        }
        ?>
        </style>
        <script type="text/javascript">
            if (typeof woof_lang_custom == 'undefined') {
                var woof_lang_custom = {};//!!important
            }
            woof_lang_custom.<?php echo $this->index ?> = "<?php _e('By text', 'woocommerce-products-filter') ?>";
        </script>
        <?php
    }

    //shortcode
    public function woof_text_filter($args = array())
    {
        global $WOOF;
        return $WOOF->render_html($this->get_ext_path() . 'views/shortcodes/woof_text_filter.php', $args);
    }

    //settings page hook
    public function woof_print_html_type_options()
    {
        global $WOOF;
        echo $WOOF->render_html($this->get_ext_path() . 'views/options.php', array(
            'key' => $this->html_type,
            "woof_settings" => get_option('woof_settings', array())
                )
        );
    }

    public function assemble_query_params(&$meta_query)
    {
        add_filter('posts_where', array($this, 'woof_post_text_filter'), 9999); //for searching by title
        return $meta_query;
    }

    public function woof_post_text_filter($where = '')
    {

        global $wp_query;
        global $WOOF;
        $request = $WOOF->get_request_data();
        if (defined('DOING_AJAX'))
        {
            $conditions = (isset($wp_query->query_vars['post_type']) AND $wp_query->query_vars['post_type'] == 'product') OR isset($_REQUEST['woof_products_doing']);
        } else
        {
            $conditions = isset($_REQUEST['woof_products_doing']);
        }
        //***
        //if ($conditions)
        {
            if ($WOOF->is_isset_in_request_data('woof_text'))
            {
                $woof_text = strtolower($request['woof_text']);

                //just for compatibility when migrating from 2.1.2 to 2.1.3
                if (isset($WOOF->settings['search_by_title_behavior']))
                {
                    $behavior = $WOOF->settings['search_by_title_behavior'];
                }

                $behavior='';
                if (isset($WOOF->settings['by_text']['behavior']))
                {
                    $behavior = $WOOF->settings['by_text']['behavior'];
                }

                //***

                switch ($behavior)
                {
                    case 'content':
                        $where .= "AND post_content LIKE '%{$woof_text}%'";
                        break;




                    case 'excerpt':
                        $where .= "AND post_excerpt LIKE '%{$woof_text}%'";
                        break;


                    case 'content_or_excerpt':
                        $where .= "AND (post_excerpt LIKE '%{$woof_text}%' OR post_content LIKE '%{$woof_text}%')";
                        break;



                    default:
                        $where .= "AND post_title LIKE '%{$woof_text}%'";
                        break;
                }
            }
        }
        //***
        return $where;
    }

}

WOOF_EXT::$includes['html_type_objects']['by_text'] = new WOOF_EXT_BY_TEXT();
