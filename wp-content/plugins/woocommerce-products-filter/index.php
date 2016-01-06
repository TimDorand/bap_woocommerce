<?php
/*
  Plugin Name: WOOF - WooCommerce Products Filter
  Plugin URI: http://woocommerce-filter.com/
  Description: WOOF - WooCommerce Products Filter. Easy & Quick!
  Requires at least: WP 4.1.0
  Tested up to: WP 4.3
  Author: realmag777
  Author URI: http://pluginus.net/
  Version: 1.1.2
  Tags: filter,search,woocommerce,woocommerce filter,products filter,product filter,filter of products,filter for products
  Text Domain: woocommerce-products-filter
  Domain Path: /languages
  Forum URI: #
 */


if (!defined('ABSPATH'))
{
    exit; // Exit if accessed directly
}

//https://wordpress.org/support/topic/filtering-by-attributes-stopped-working-after-update-to-232
if (!defined('WOOF_PATH'))
{
    define('WOOF_PATH', plugin_dir_path(__FILE__));
}
define('WOOF_LINK', plugin_dir_url(__FILE__));
define('WOOF_PLUGIN_NAME', plugin_basename(__FILE__));
//classes
include plugin_dir_path(__FILE__) . 'classes/helper.php';
include plugin_dir_path(__FILE__) . 'classes/storage.php';
include plugin_dir_path(__FILE__) . 'classes/counter.php';
include plugin_dir_path(__FILE__) . 'classes/widgets.php';

//***
//14-09-2015
final class WOOF
{

    public $settings = array();
    public $version = '1.1.2';
    public $html_types = array(
        'radio' => 'Radio',
        'checkbox' => 'Checkbox',
        'select' => 'Drop-down',
        'mselect' => 'Multi drop-down',
        'color' => 'Color'
    );
    public static $query_cache_table = 'woof_query_cache';
    private $session_rct_key = 'woof_really_current_term';
    private $storage = null;
    private $storage_type = 'session';

    public function __construct()
    {
        $this->init_settings();

        if (isset($this->settings['storage_type']))
        {
            $this->storage_type = $this->settings['storage_type'];
        }

        $this->storage = new WOOF_STORAGE($this->storage_type);

        //+++
        if (!defined('DOING_AJAX'))
        {
            global $wp_query;
            if (isset($wp_query->query_vars['taxonomy']) AND in_array($wp_query->query_vars['taxonomy'], get_object_taxonomies('product')))
            {
                //unset($_SESSION['woof_really_current_term']);
                $this->set_really_current_term();
            }
        }
        //+++

        global $wpdb;
        $attribute_taxonomies = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "woocommerce_attribute_taxonomies");
        set_transient('wc_attribute_taxonomies', $attribute_taxonomies);
        if (!empty($attribute_taxonomies) AND is_array($attribute_taxonomies))
        {
            foreach ($attribute_taxonomies as $att)
            {
                //fixing for woo >= 2.3.2
                add_filter("woocommerce_taxonomy_args_pa_{$att->attribute_name}", array($this, 'change_woo_att_data'));
            }
        }
        //add_filter("woocommerce_taxonomy_args_pa_color", array($this, 'change_woo_att_data'));
        //add_action('init', array($this, 'price_filter_init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts_styles'));
        add_action('widgets_init', array($this, 'widgets_init'));
    }

    public function enqueue_scripts_styles()
    {
        //enqueue styles
        wp_enqueue_style('woof', WOOF_LINK . 'css/front.css');

        if ($this->is_woof_use_chosen())
        {
            wp_enqueue_style('chosen-drop-down', WOOF_LINK . 'js/chosen/chosen.min.css');
        }

        if ($this->settings['overlay_skin'] != 'default')
        {
            wp_enqueue_style('plainoverlay', WOOF_LINK . 'css/plainoverlay.css');
        }

        if (get_option('woof_use_beauty_scroll', 0))
        {
            wp_enqueue_style('malihu-custom-scrollbar', WOOF_LINK . 'js/malihu-custom-scrollbar/jquery.mCustomScrollbar.css');
        }

        $icheck_skin = $this->settings['icheck_skin'];
        if ($icheck_skin != 'none')
        {
            if (!$icheck_skin)
            {
                $icheck_skin = 'square_green';
            }

            if ($icheck_skin != 'none')
            {
                $icheck_skin = explode('_', $icheck_skin);
                wp_enqueue_style('icheck-jquery-color', WOOF_LINK . 'js/icheck/skins/' . $icheck_skin[0] . '/' . $icheck_skin[1] . '.css');
            }
        }
    }

    public function init()
    {
        if (!class_exists('WooCommerce'))
        {
            return;
        }


        //***
        $first_init = (int) get_option('woof_first_init', 0);
        if ($first_init != 1)
        {
            update_option('woof_first_init', 1);
            update_option('woof_set_automatically', 0);
            update_option('woof_autosubmit', 1);
            update_option('woof_show_count', 1);
            update_option('woof_show_count_dynamic', 1);
            update_option('woof_hide_dynamic_empty_pos', 0);
            update_option('woof_try_ajax', 0);
            update_option('woof_use_chosen', 1);
            update_option('woof_checkboxes_slide', 1);
            update_option('woof_use_beauty_scroll', 1);
            update_option('woof_show_title_search', 1);
            update_option('woof_show_sku_search', 0);
            update_option('woof_sku_conditions', 'LIKE');
            update_option('woof_show_in_stock_only', 0);
            update_option('woof_show_sales_only', 0);
            update_option('woof_show_price_search', 0);
            update_option('woof_show_price_search_button', 0);
            update_option('woof_hide_red_top_panel', 0);
            update_option('woof_filter_btn_txt', '');
            update_option('woof_reset_btn_txt', '');
        }
        //***
        load_plugin_textdomain('woocommerce-products-filter', false, dirname(plugin_basename(__FILE__)) . '/languages');
        add_filter('plugin_action_links_' . WOOF_PLUGIN_NAME, array($this, 'plugin_action_links'), 50);
        add_action('woocommerce_settings_tabs_array', array($this, 'woocommerce_settings_tabs_array'), 50);
        add_action('woocommerce_settings_tabs_woof', array($this, 'print_plugin_options'), 50);
        //add_action('woocommerce_update_options_settings_tab_woof', array($this, 'update_settings'), 50);
        add_action('admin_head', array($this, 'admin_head'), 1);
        //+++
        add_action('wp_head', array($this, 'wp_head'), 999);
        add_action('wp_footer', array($this, 'wp_footer'), 999);
        add_shortcode('woof', array($this, 'woof_shortcode'));

        //+++
        add_action('wp_ajax_woof_draw_products', array($this, 'woof_draw_products'));
        add_action('wp_ajax_nopriv_woof_draw_products', array($this, 'woof_draw_products'));
        add_action('wp_ajax_woof_redraw_woof', array($this, 'woof_redraw_woof'));
        add_action('wp_ajax_nopriv_woof_redraw_woof', array($this, 'woof_redraw_woof'));
        //+++
        add_filter('widget_text', 'do_shortcode');
        add_filter('parse_query', array($this, "parse_query"), 9999);
        add_filter('woocommerce_product_query', array($this, "woocommerce_product_query"), 9999);
        //add_filter('posts_where', array($this, 'woof_post_title_filter'), 9999); //for searching by title
        //+++
        add_action('woocommerce_before_shop_loop', array($this, 'woocommerce_before_shop_loop'));
        add_action('woocommerce_after_shop_loop', array($this, 'woocommerce_after_shop_loop'));
        add_shortcode('woof_products', array($this, 'woof_products'));
        add_shortcode('woof_price_filter', array($this, 'woof_price_filter'));
        add_shortcode('woof_title_filter', array($this, 'woof_title_filter'));
        add_shortcode('woof_sku_filter', array($this, 'woof_sku_filter'));
        //add_filter('woocommerce_pagination_args', array($this, 'woocommerce_pagination_args'));
        add_action('wp_ajax_woof_cache_count_data_clear', array($this, 'cache_count_data_clear'));

        add_filter('woof_exclude_tax_key', array($this, 'woof_exclude_tax_key'));
        add_filter('sidebars_widgets', array($this, 'sidebars_widgets'));
        //own filters of WOOF
        add_filter('woof_modify_query_args', array($this, 'woof_modify_query_args'), 1);
        //sheduling
        if (isset($this->settings['cache_count_data_auto_clean']) AND $this->settings['cache_count_data_auto_clean'])
        {
            add_action('woof_cache_count_data_auto_clean', array($this, 'cache_count_data_clear'));
            if (!wp_next_scheduled('woof_cache_count_data_auto_clean'))
            {
                wp_schedule_event(time(), $this->settings['cache_count_data_auto_clean'], 'woof_cache_count_data_auto_clean');
            }
        }

        //for pagination
        //http://docs.woothemes.com/document/change-number-of-products-displayed-per-page/
        add_filter('loop_shop_per_page', create_function('$cols', "return {$this->settings['per_page']};"), 9999);

        //cron
        add_filter('cron_schedules', array($this, 'cron_schedules'), 10, 1);
        //custom filters
        //add_filter('woof_before_term_name', array($this, 'woof_before_term_name'));
    }

    public function widgets_init()
    {
        register_widget('WOOF_Widget');
    }

    //fix for woo 2.3.2 and higher with attributes filtering
    public function change_woo_att_data($taxonomy_data)
    {
        $taxonomy_data['query_var'] = true;
        return $taxonomy_data;
    }

    public function sidebars_widgets($sidebars_widgets)
    {
        if (get_option('woof_show_price_search', 0))
        {
            $sidebars_widgets['sidebar-woof'] = array('woocommerce_price_filter');
        }

        return $sidebars_widgets;
    }

    public function cron_schedules($schedules)
    {
        // $schedules stores all recurrence schedules within WordPress
        for ($i = 2; $i <= 7; $i++)
        {
            $schedules['days' . $i] = array(
                'interval' => $i * DAY_IN_SECONDS,
                'display' => sprintf(__("each %s days", 'woocommerce-products-filter'), $i)
            );
        }

        return (array) $schedules;
    }

    /*
      public function price_filter_init()
      {
      if (get_option('woof_show_price_search'))
      {
      $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

      wp_register_script('wc-jquery-ui-touchpunch', WC()->plugin_url() . '/assets/js/frontend/jquery-ui-touch-punch' . $suffix . '.js', array('jquery-ui-slider'), WC_VERSION, true);
      wp_register_script('wc-price-slider', WC()->plugin_url() . '/assets/js/frontend/price-slider' . $suffix . '.js', array('jquery-ui-slider', 'wc-jquery-ui-touchpunch'), WC_VERSION, true);

      wp_localize_script('wc-price-slider', 'woocommerce_price_slider_params', array(
      'currency_symbol' => get_woocommerce_currency_symbol(),
      'currency_pos' => get_option('woocommerce_currency_pos'),
      'min_price' => isset($_GET['min_price']) ? esc_attr($_GET['min_price']) : '',
      'max_price' => isset($_GET['max_price']) ? esc_attr($_GET['max_price']) : ''
      ));

      global $woocommerce;
      add_filter('loop_shop_post_in', array($woocommerce->query, 'price_filter'));
      }
      }
     */

    /**
     * Show action links on the plugin screen
     */
    public function plugin_action_links($links)
    {
        return array_merge(array(
            '<a href="' . admin_url('admin.php?page=wc-settings&tab=woof') . '">' . __('Settings', 'woocommerce-products-filter') . '</a>',
            '<a target="_blank" href="' . esc_url('http://woocommerce-filter.com/documentation/') . '">' . __('Documentation', 'woocommerce-products-filter') . '</a>'
                ), $links);

        return $links;
    }

    public function get_swoof_search_slug()
    {
        $slug = 'swoof';


        return $slug;
    }

    public function woocommerce_product_query($q)
    {
        //http://docs.woothemes.com/wc-apidocs/class-WC_Query.html
        //wp-content\plugins\woocommerce\includes\class-wc-query.php -> public function product_query( $q )
        add_filter('posts_where', array($this, 'woof_post_title_filter'), 9999); //for searching by title
        $meta_query = $q->get('meta_query');
        $q->set('meta_query', $this->assemble_stock_sales_params($meta_query));
        $q->set('meta_query', $this->assemble_sku_params($meta_query)); //for searching by sku
        return $q;
    }

    public function parse_query($wp_query)
    {
        $_REQUEST['woof_parse_query'] = 1;

        if (!defined('DOING_AJAX'))
        {
            if (isset($_REQUEST['woof_products_doing']))
            {
                return $wp_query;
            }
        }

        $request = $this->get_request_data();

        //+++
        if ($wp_query->is_main_query())
        {
            if ($this->is_isset_in_request_data($this->get_swoof_search_slug()))
            {
                if (!is_page())
                {
                    $wp_query->set('post_type', 'product');
                    if (!$this->is_isset_in_request_data('stock'))
                    {
                        $wp_query->is_page = false;
                    }
                    $wp_query->is_post_type_archive = true;
                }

                $wp_query->is_tax = false;
                $wp_query->is_tag = false;
                $wp_query->is_home = false;
                $wp_query->is_single = false;
                $wp_query->is_posts_page = false;
                $wp_query->is_search = false; //!!!
                //+++
                $meta_query = array();
                if (isset($wp_query->query_vars['meta_query']))
                {
                    $meta_query = $wp_query->query_vars['meta_query'];
                }
                $meta_query['relation'] = 'AND';
                //+++
                $assemble_stock_sales_params = !is_page();
                if (!$assemble_stock_sales_params)
                {
                    $assemble_stock_sales_params = ($wp_query->query_vars['page_id'] == wc_get_page_id('shop'));
                }

                if (!$assemble_stock_sales_params)
                {
                    $assemble_stock_sales_params = $wp_query->is_post_type_archive;
                }

                if ($assemble_stock_sales_params)
                {
                    $this->assemble_stock_sales_params($meta_query);
                    $this->assemble_sku_params($meta_query);
                }
                //***
                //out of stock products
                //http://stackoverflow.com/questions/24480982/hide-out-of-stock-products-in-woocommerce
                /*
                  if (get_option('woof_exclude_out_stock_products', 0))
                  {
                  $meta_query[] = array(
                  'key' => '_stock_status',
                  'value' => array('instock'),
                  'compare' => 'IN'
                  );
                  }
                 */
                //***
                $wp_query->set('meta_query', $meta_query);
            }
        }

        return $wp_query;
    }

    private function assemble_stock_sales_params(&$meta_query)
    {
        //http://stackoverflow.com/questions/20990199/woocommerce-display-only-on-sale-products-in-shop
        $request = $this->get_request_data();
        if (isset($request['stock']))
        {
            if ($request['stock'] == 'instock')
            {
                $meta_query[] = array(
                    'key' => '_stock_status',
                    'value' => 'outofstock', //instock,outofstock
                    'compare' => 'NOT IN'
                );
            }

            if ($request['stock'] == 'outofstock')
            {
                $meta_query[] = array(
                    array(
                        'key' => '_stock_status',
                        'value' => 'outofstock', //instock,outofstock
                        'compare' => 'IN'
                    )
                );
            }
        }
        //+++
        if (isset($request['insales']) AND $request['insales'] == 'salesonly')
        {
            //http://stackoverflow.com/questions/20990199/woocommerce-display-only-on-sale-products-in-shop
            $meta_query[] = array(
                array(
                    'relation' => 'OR',
                    array(
                        'key' => '_sale_price',
                        'value' => 0,
                        'compare' => '>',
                        'type' => 'DECIMAL'
                    ),
                    array(
                        'key' => '_min_variation_sale_price',
                        'value' => 0,
                        'compare' => '>',
                        'type' => 'DECIMAL'
                    )
                )
            );
        }


        return $meta_query;
    }

    private function assemble_sku_params(&$meta_query)
    {
        $request = $this->get_request_data();
        if (isset($request['woof_sku']))
        {
            if (!empty($request['woof_sku']))
            {
                $meta_query[] = array(
                    'key' => '_sku',
                    'value' => $request['woof_sku'],
                    'compare' => get_option('woof_sku_conditions', '=')
                );
            }
        }

        return $meta_query;
    }

    private function assemble_price_params(&$meta_query)
    {
        $request = $this->get_request_data();
        if (isset($request['min_price']) AND isset($request['max_price']))
        {
            if ($request['min_price'] <= $request['max_price'])
            {
                $meta_query[] = array(
                    'key' => '_price',
                    'value' => array(floatval($request['min_price']), floatval($request['max_price'])),
                    'type' => 'DECIMAL',
                    'compare' => 'BETWEEN'
                );
            }
        }

        return $meta_query;
    }

    public function woof_post_title_filter($where = '')
    {

        global $wp_query;
        $request = $this->get_request_data();
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
            if ($this->is_isset_in_request_data('woof_title'))
            {
                $woof_title = strtolower($request['woof_title']);
                $where .= "AND post_title LIKE '%{$woof_title}%'";
            }
        }
        //***
        return $where;
    }

    public function woocommerce_settings_tabs_array($tabs)
    {
        $tabs['woof'] = __('Products Filter', 'woocommerce-products-filter');
        return $tabs;
    }

    public function admin_head()
    {
        if (isset($_GET['page']) AND isset($_GET['tab']))
        {
            if ($_GET['page'] == 'wc-settings' AND $_GET['tab'] == 'woof')
            {
                wp_enqueue_script('jquery');
                wp_enqueue_script('jquery-ui-core');
                wp_enqueue_script('woof-admin', WOOF_LINK . 'js/admin.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'));
            }
        }
    }

    public function wp_head()
    {
        global $wp_query;
        //***
        if (!isset($wp_query->query_vars['taxonomy']) AND ! defined('DOING_AJAX'))
        {
            $this->set_really_current_term();
        }

        //***
        ?>

        <?php //if (isset($this->settings['custom_css_code'])):                        ?>
        <style type="text/css">
        <?php
        if (isset($this->settings['custom_css_code']))
        {
            echo stripcslashes($this->settings['custom_css_code']);
        }
        ?>

        <?php
        if (isset($this->settings['overlay_skin_bg_img']))
        {
            if (!empty($this->settings['overlay_skin_bg_img']))
            {
                ?>
                    .plainoverlay {
                        background-image: url('<?php echo $this->settings['overlay_skin_bg_img'] ?>');
                    }
                <?php
            }
        }



//***
        if (isset($this->settings['plainoverlay_color']))
        {
            if (!empty($this->settings['plainoverlay_color']))
            {
                ?>
                    .jQuery-plainOverlay-progress {
                        border-top: 12px solid <?php echo $this->settings['plainoverlay_color'] ?> !important;
                    }
                <?php
            }
        }


//***



        if (isset($this->settings['title_submit_image']))
        {
            if (!empty($this->settings['title_submit_image']))
            {
                ?>
                    .woof_title_search_container .woof_title_search_go{
                        background: url(<?php echo $this->settings['title_submit_image'] ?>) !important;
                    }
                <?php
            }
        }


//***
//***


        if ((int) get_option('woof_autosubmit', 0))
        {
            /*
              ?>
              .woof_price_search_container .price_slider_amount button.button{
              display: none;
              }

              .woof_price_search_container .price_slider_amount .price_label{
              text-align: left !important;
              }
              <?php
             *
             */
        }
        ?>



        <?php if (get_option('woof_show_price_search_button', 0) == 1): ?>


        <?php else: ?>


                /***** START: hiding submit button of the price slider ******/
                .woof_price_search_container .price_slider_amount button.button{
                    display: none;
                }

                .woof_price_search_container .price_slider_amount .price_label{
                    text-align: left !important;
                }

                .woof .widget_price_filter .price_slider_amount .button {
                    float: left;
                }

                /***** END: hiding submit button of the price slider ******/


        <?php endif; ?>




        </style>
        <?php //endif;                             ?>

        <?php if (!current_user_can('create_users')): ?>
            <style type="text/css">
                .woof_edit_view{
                    display: none;
                }
            </style>
        <?php endif; ?>


        <?php
        //svg preloading
        if (isset($this->settings['overlay_skin'])):
            ?>
            <img style="display: none;" src="<?php echo WOOF_LINK ?>img/loading-master/<?php echo $this->settings['overlay_skin'] ?>.svg" alt="preloader" />
        <?php endif; ?>

        <script type="text/javascript">

            var woof_is_mobile = 0;
        <?php if (WOOF_HELPER::is_mobile_device()): ?>
                woof_is_mobile = 1;
        <?php endif; ?>


            var woof_show_price_search_button = 0;
        <?php if (get_option('woof_show_price_search_button', 0) == 1): ?>
                woof_show_price_search_button = 1;
        <?php endif; ?>

            var swoof_search_slug = "<?php echo $this->get_swoof_search_slug(); ?>";

        <?php $icheck_skin = $this->settings['icheck_skin']; ?>

            var icheck_skin = {};
        <?php if ($icheck_skin != 'none'): ?>
            <?php $icheck_skin = explode('_', $icheck_skin); ?>
                icheck_skin.skin = "<?php echo $icheck_skin[0] ?>";
                icheck_skin.color = "<?php echo $icheck_skin[1] ?>";
        <?php else: ?>
                icheck_skin = 'none';
        <?php endif; ?>

            var is_woof_use_chosen =<?php echo $this->is_woof_use_chosen() ?>;

            var woof_current_page_link = location.protocol + '//' + location.host + location.pathname;
            //***lets remove pagination from woof_current_page_link
            woof_current_page_link = woof_current_page_link.replace(/\page\/[0-9]/, "");
        <?php
        if (!isset($wp_query->query_vars['taxonomy']))
        {
            $page_id = get_option('woocommerce_shop_page_id');
            if ($page_id > 0)
            {
                $link = get_permalink($page_id);
            }

            if (is_string($link) AND ! empty($link))
            {
                ?>
                    woof_current_page_link = "<?php echo $link ?>";
                <?php
            }
        }


//code bone when filter child categories on the category page of parent
//like here: http://dev.pluginus.net/product-category/clothing/?swoof=1&product_cat=hoo1
        if (!defined('DOING_AJAX') AND ! is_page())
        {
            $request_data = $this->get_request_data();
            if (isset($wp_query->query_vars['taxonomy']) AND empty($request_data))
            {
                $queried_obj = get_queried_object();
                if (is_object($queried_obj))
                {
                    //$_SESSION['woof_really_current_term'] = $queried_obj;
                    $this->set_really_current_term($queried_obj);
                }
            }
        } else
        {
            if ($this->is_really_current_term_exists())
            {
                //unset($_SESSION['woof_really_current_term']);
                $this->set_really_current_term();
            }
        }
//+++
        $woof_use_beauty_scroll = (int) get_option('woof_use_beauty_scroll', 0);
        ?>
            var woof_link = '<?php echo WOOF_LINK ?>';
            var woof_current_values = '<?php echo json_encode($this->get_request_data()); ?>';
            //+++
            var woof_lang_loading = "<?php _e('Loading ...', 'woocommerce-products-filter') ?>";

        <?php if (isset($this->settings['default_overlay_skin_word']) AND ! empty($this->settings['default_overlay_skin_word'])): ?>
                woof_lang_loading = "<?php echo __($this->settings['default_overlay_skin_word'], 'woocommerce-products-filter') ?>";
        <?php endif; ?>

            var woof_lang_orderby = "<?php _e('orderby', 'woocommerce-products-filter') ?>";
            var woof_lang_title = "<?php _e('Title', 'woocommerce-products-filter') ?>";
            var woof_lang_insales = "<?php _e('In sales only', 'woocommerce-products-filter') ?>";
            var woof_lang_instock = "<?php _e('In stock only', 'woocommerce-products-filter') ?>";
            var woof_lang_perpage = "<?php _e('Per page', 'woocommerce-products-filter') ?>";
            var woof_lang_sku = "<?php _e('SKU', 'woocommerce-products-filter') ?>";
            var woof_lang_pricerange = "<?php _e('price range', 'woocommerce-products-filter') ?>";
            var woof_lang_show_products_filter = "<?php _e('show products filter', 'woocommerce-products-filter') ?>";
            var woof_lang_hide_products_filter = "<?php _e('hide products filter', 'woocommerce-products-filter') ?>";
            var woof_use_beauty_scroll =<?php echo $woof_use_beauty_scroll ?>;
            //+++
            var woof_autosubmit =<?php echo (int) get_option('woof_autosubmit', 0) ?>;
            var woof_ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var woof_submit_link = "";
            var woof_is_ajax = 0;
            var woof_ajax_page_num =<?php echo ((get_query_var('page')) ? get_query_var('page') : 1) ?>;
            var woof_ajax_first_done = false;
            var woof_checkboxes_slide_flag = <?php echo(((int) get_option('woof_checkboxes_slide') == 1 ? 'true' : 'false')); ?>;



            var woof_overlay_skin = "<?php echo(isset($this->settings['overlay_skin']) ? $this->settings['overlay_skin'] : 'default') ?>";
            jQuery(function () {
                woof_current_values = jQuery.parseJSON(woof_current_values);
                if (woof_current_values.length == 0) {
                    woof_current_values = {};
                }

            });
            //***
            function woof_js_after_ajax_done() {
        <?php echo(isset($this->settings['js_after_ajax_done']) ? stripcslashes($this->settings['js_after_ajax_done']) : ''); ?>
            }
        </script>
        <?php
        if ($icheck_skin != 'none')
        {
            wp_enqueue_script('icheck-jquery', WOOF_LINK . 'js/icheck/icheck.min.js', array('jquery'));
            //wp_enqueue_style('icheck-jquery', self::get_application_uri() . 'js/icheck/all.css');
        }
        /*
          if (is_shop())
          {
          add_action('woocommerce_before_shop_loop', array($this, 'woocommerce_before_shop_loop'));
          }
         */
        //***
        wp_enqueue_script('woof_front', WOOF_LINK . 'js/front.js', array('jquery'));
        wp_enqueue_script('woof_radio_html_items', WOOF_LINK . 'js/html_types/radio.js', array('jquery'));
        wp_enqueue_script('woof_checkbox_html_items', WOOF_LINK . 'js/html_types/checkbox.js', array('jquery'));
        wp_enqueue_script('woof_color_html_items', WOOF_LINK . 'js/html_types/color.js', array('jquery'));
        wp_enqueue_script('woof_select_html_items', WOOF_LINK . 'js/html_types/select.js', array('jquery'));
        wp_enqueue_script('woof_mselect_html_items', WOOF_LINK . 'js/html_types/mselect.js', array('jquery'));
        //+++
        wp_enqueue_script('woof_title_html_items', WOOF_LINK . 'js/html_types/title.js', array('jquery'));
        wp_enqueue_script('woof_sku_html_items', WOOF_LINK . 'js/html_types/sku.js', array('jquery'));
        //+++
        if ($this->is_woof_use_chosen())
        {
            wp_enqueue_script('chosen-drop-down', WOOF_LINK . 'js/chosen/chosen.jquery.min.js', array('jquery'));
        }

        if ($this->settings['overlay_skin'] != 'default')
        {
            wp_enqueue_script('plainoverlay', WOOF_LINK . 'js/plainoverlay/jquery.plainoverlay.min.js', array('jquery'));
        }

        if ($woof_use_beauty_scroll)
        {
            wp_enqueue_script('mousewheel', WOOF_LINK . 'js/malihu-custom-scrollbar/jquery.mousewheel.min.js', array('jquery'));
            wp_enqueue_script('malihu-custom-scrollbar', WOOF_LINK . 'js/malihu-custom-scrollbar/jquery.mCustomScrollbar.min.js', array('jquery'));
            wp_enqueue_script('malihu-custom-scrollbar-concat', WOOF_LINK . 'js/malihu-custom-scrollbar/jquery.mCustomScrollbar.concat.min.js', array('jquery'));
        }


        if (get_option('woof_show_price_search', 0) == 1)
        {
            wp_enqueue_script('jquery-ui-core', array('jquery'));
            wp_enqueue_script('jquery-ui-slider', array('jquery-ui-core'));
            wp_enqueue_script('wc-jquery-ui-touchpunch', array('jquery-ui-core', 'jquery-ui-slider'));
            wp_enqueue_script('wc-price-slider', array('jquery-ui-slider', 'wc-jquery-ui-touchpunch'));
        }
    }

    public function wp_footer()
    {

    }

    public function print_plugin_options()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        if (isset($_POST['woof_settings']))
        {
            WC_Admin_Settings::save_fields($this->get_options());
            //+++
            if (class_exists('SitePress'))
            {
                $lang = ICL_LANGUAGE_CODE;
                if (isset($_POST['woof_settings']['wpml_tax_labels']) AND ! empty($_POST['woof_settings']['wpml_tax_labels']))
                {
                    $translations_string = $_POST['woof_settings']['wpml_tax_labels'];
                    $translations_string = explode(PHP_EOL, $translations_string);
                    $translations = array();
                    if (!empty($translations_string) AND is_array($translations_string))
                    {
                        foreach ($translations_string as $line)
                        {
                            if (empty($line))
                            {
                                continue;
                            }

                            $line = explode(':', $line);
                            if (!isset($translations[$line[0]]))
                            {
                                $translations[$line[0]] = array();
                            }
                            $tmp = explode('^', $line[1]);
                            $translations[$line[0]][$tmp[0]] = $tmp[1];
                        }
                    }

                    $_POST['woof_settings']['wpml_tax_labels'] = $translations;
                }
            }
            //+++
            update_option('woof_settings', $_POST['woof_settings']);
            $this->init_settings();
        }
        //+++
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('woof', WOOF_LINK . 'js/plugin_options.js', array('jquery', 'jquery-ui-core', 'jquery-ui-sortable'));
        wp_enqueue_style('woof', WOOF_LINK . 'css/plugin_options.css');

        $args = array("woof_settings" => get_option('woof_settings', array()));
        echo $this->render_html(WOOF_PATH . 'views/plugin_options.php', $args);
    }

    private function init_settings()
    {
        $this->settings = get_option('woof_settings', array());
        if (!isset($this->settings['per_page']) OR empty($this->settings['per_page']))
        {
            $this->settings['per_page'] = 12;
        }

        $this->settings['hide_terms_count_txt'] = 0;
    }

    private function get_taxonomies()
    {
        static $taxonomies = array();
        if (empty($taxonomies))
        {
            $taxonomies = get_object_taxonomies('product', 'objects');
            unset($taxonomies['product_shipping_class']);
            unset($taxonomies['product_type']);
        }
        return $taxonomies;
    }

    public function get_options()
    {
        $options = array
            (array(
                'name' => '',
                'type' => 'title',
                'desc' => '',
                'id' => 'woof_general_settings'
            ),
            array(
                'name' => __('Set filter automatically', 'woocommerce-products-filter'),
                'desc' => __('Set filter automatically on the shop page', 'woocommerce-products-filter'),
                'id' => 'woof_set_automatically',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    1 => __('Yes', 'woocommerce-products-filter'),
                    0 => __('No', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Autosubmit', 'woocommerce-products-filter'),
                'desc' => __('Start searching just after changing any of the elements on the search form', 'woocommerce-products-filter'),
                'id' => 'woof_autosubmit',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Show count', 'woocommerce-products-filter'),
                'desc' => __('Show count of items near taxonomies terms on the front', 'woocommerce-products-filter'),
                'id' => 'woof_show_count',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Dynamic recount', 'woocommerce-products-filter'),
                'desc' => __('Show count of items near taxonomies terms on the front dynamically. Must be switched on "Show count"', 'woocommerce-products-filter'),
                'id' => 'woof_show_count_dynamic',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Hide empty terms', 'woocommerce-products-filter'),
                'desc' => __('Hide empty terms in "Dynamic recount" mode', 'woocommerce-products-filter'),
                'id' => 'woof_hide_dynamic_empty_pos',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No - Premium only', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Try to ajaxify the shop', 'woocommerce-products-filter'),
                'desc' => __('Select "Yes" if you want to TRY make filtering in your shop by AJAX. Not compatible for 100% of all wp themes.', 'woocommerce-products-filter'),
                'id' => 'woof_try_ajax',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Use chosen', 'woocommerce-products-filter'),
                'desc' => __('Use chosen javascript library on the front of your site for drop-downs.', 'woocommerce-products-filter'),
                'id' => 'woof_use_chosen',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Hide childs in checkboxes and radio', 'woocommerce-products-filter'),
                'desc' => __('Hide childs in checkboxes and radio. Near checkbox/radio which has childs will be plus icon to show childs.', 'woocommerce-products-filter'),
                'id' => 'woof_checkboxes_slide',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Use beauty scroll', 'woocommerce-products-filter'),
                'desc' => __('Use beauty scroll when you apply max height for taxonomy block on the front', 'woocommerce-products-filter'),
                'id' => 'woof_use_beauty_scroll',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Show "Search by title" textinput', 'woocommerce-products-filter'),
                'desc' => __('Show textinput for searching by products title', 'woocommerce-products-filter'),
                'id' => 'woof_show_title_search',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Show "Search by SKU" textinput', 'woocommerce-products-filter'),
                'desc' => __('Show textinput for searching by products sku', 'woocommerce-products-filter'),
                'id' => 'woof_show_sku_search',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No - Premium only', 'woocommerce-products-filter'),
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Conditions logic of "Search by SKU"', 'woocommerce-products-filter'),
                'desc' => __('LIKE or Equally', 'woocommerce-products-filter'),
                'id' => 'woof_sku_conditions',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    '=' => __('No - Premium only', 'woocommerce-products-filter'),
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Show "In stock only checkbox"', 'woocommerce-products-filter'),
                'desc' => __('Show "In stock only checkbox" on the front', 'woocommerce-products-filter'),
                'id' => 'woof_show_in_stock_only',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Show "In sales only checkbox"', 'woocommerce-products-filter'),
                'desc' => __('Show "In sales only checkbox" on the front', 'woocommerce-products-filter'),
                'id' => 'woof_show_sales_only',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Show "Filter by price"', 'woocommerce-products-filter'),
                'desc' => __('Show woocommerce filter by price inside woof search form', 'woocommerce-products-filter'),
                'id' => 'woof_show_price_search',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('As range-slider', 'woocommerce-products-filter'),
                    2 => __('As drop-down - Premium only', 'woocommerce-products-filter'),
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Show button for "Filter by price"', 'woocommerce-products-filter'),
                'desc' => __('Show button for woocommerce filter by price inside woof search form', 'woocommerce-products-filter'),
                'id' => 'woof_show_price_search_button',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Hide woof top panel buttons', 'woocommerce-products-filter'),
                'desc' => __('Red buttons on the top of the widget or shop when searching done', 'woocommerce-products-filter'),
                'id' => 'woof_hide_red_top_panel',
                'type' => 'select',
                'class' => 'chosen_select',
                'css' => 'min-width:300px;',
                'options' => array(
                    0 => __('No', 'woocommerce-products-filter'),
                    1 => __('Yes', 'woocommerce-products-filter')
                ),
                'desc_tip' => true
            ),
            array(
                'name' => __('Filter button text', 'woocommerce-products-filter'),
                'desc' => __('Filter button text in the search form', 'woocommerce-products-filter'),
                'id' => 'woof_filter_btn_txt',
                'type' => 'text',
                'class' => 'text',
                'css' => 'min-width:300px;',
                'desc_tip' => true
            ),
            array(
                'name' => __('Reset button text', 'woocommerce-products-filter'),
                'desc' => __('Reset button text in the search form. Write "none" to hide this button on front.', 'woocommerce-products-filter'),
                'id' => 'woof_reset_btn_txt',
                'type' => 'text',
                'class' => 'text',
                'css' => 'min-width:300px;',
                'desc_tip' => true
            ),
            array('type' => 'sectionend', 'id' => 'woof_general_settings')
        );

        return apply_filters('wc_settings_tab_woof_settings', $options);
    }

    //for dynamic count
    public function dynamic_count($curr_term, $type, $additional_taxes = '')
    {
        //global $wp_query;
        $request = $this->get_request_data();
        $opposition_terms = array();
        if (!empty($additional_taxes))
        {
            $opposition_terms = $this->_expand_additional_taxes_string($additional_taxes);
        }
        if (!empty($opposition_terms))
        {
            $tmp = array();
            foreach ($opposition_terms as $t)
            {
                $tmp[$t['taxonomy']] = $t['terms'];
            }
            $opposition_terms = $tmp;
            unset($tmp);
        }

        //***
        if ($this->is_really_current_term_exists())
        {
            //we need this when for dynamic recount on taxonomy page
            $o = $this->get_really_current_term();
            $opposition_terms[$o->taxonomy] = array($o->slug);
        }
        //$opposition_terms - all terms from $additional_taxes or/and from really_current_term
        //it is always in opposition
        $in_query_terms = array(); //terms from request
        static $product_taxonomies = null;
        if (!$product_taxonomies)
        {
            $product_taxonomies = $this->get_taxonomies();
            $product_taxonomies = array_keys($product_taxonomies);
        }
        if (!empty($request) AND is_array($request))
        {
            foreach ($request as $tax_slug => $terms_string)
            {
                if (in_array($tax_slug, $product_taxonomies))
                {
                    $in_query_terms[$tax_slug] = explode(',', $terms_string);
                }
            }
        }


        //$in_query_terms - terms we have in search query!!
        //***

        $term_is_in_query = false;
        if (isset($in_query_terms[$curr_term['taxonomy']]))
        {
            if (in_array($curr_term['slug'], $in_query_terms[$curr_term['taxonomy']]))
            {
                $term_is_in_query = true;
            }
        }


        //any way we not display count for the selected terms
        if ($term_is_in_query)
        {
            return 0;
        }

        //***

        $term_is_in_opposition = false;
        if (isset($opposition_terms[$curr_term['taxonomy']]))
        {
            if (in_array($curr_term['slug'], $opposition_terms[$curr_term['taxonomy']]))
            {
                $term_is_in_opposition = true;
            }
        }

        //***

        $terms_to_query = array();
        switch ($type)
        {
            case 'radio':
            case 'select':


                if (isset($in_query_terms[$curr_term['taxonomy']]))
                {
                    $in_query_terms[$curr_term['taxonomy']] = array($curr_term['slug']);
                } else
                {
                    $terms_to_query[$curr_term['taxonomy']] = array($curr_term['slug']);
                }


                break;

            case 'price2':
                //for drop-down price
                //leave it empty
                break;

            case 'checkbox':
            case 'color':
            case 'mselect':

                if (isset($in_query_terms[$curr_term['taxonomy']]))
                {
                    $in_query_terms[$curr_term['taxonomy']] = array($curr_term['slug']);
                } else
                {
                    $terms_to_query[$curr_term['taxonomy']][] = $curr_term['slug'];
                }


                break;
        }

        //***

        $taxonomies = array();
        if (!empty($opposition_terms))
        {
            foreach ($opposition_terms as $tax_slug => $terms)
            {
                if (!empty($terms))
                {
                    $taxonomies[] = array(
                        'taxonomy' => $tax_slug,
                        'terms' => $terms,
                        'field' => 'slug',
                        'operator' => 'IN',
                        'include_children' => 1
                    );
                }
            }
        }


        if (!empty($in_query_terms))
        {
            foreach ($in_query_terms as $tax_slug => $terms)
            {
                if (!empty($terms))
                {
                    $taxonomies[] = array(
                        'taxonomy' => $tax_slug,
                        'terms' => $terms,
                        'field' => 'slug',
                        'operator' => 'IN',
                        'include_children' => 1
                    );
                }
            }
        }

        if (!empty($terms_to_query))
        {
            foreach ($terms_to_query as $tax_slug => $terms)
            {
                if (!empty($terms))
                {
                    $taxonomies[] = array(
                        'taxonomy' => $tax_slug,
                        'terms' => $terms,
                        'field' => 'slug',
                        'operator' => 'IN',
                        'include_children' => 1
                    );
                }
            }
        }

        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

        if (!empty($taxonomies))
        {
            $taxonomies['relation'] = 'AND';
        }
        //***
        $args = array(
            'nopaging' => true,
            'fields' => 'ids',
            'post_type' => 'product'
        );

        $args['tax_query'] = $taxonomies;
        if (isset($wp_query->meta_query->queries))
        {
            $args['meta_query'] = $wp_query->meta_query->queries;
        } else
        {
            $args['meta_query'] = array();
        }

        //check for price
        if ($this->is_isset_in_request_data('min_price') AND $this->is_isset_in_request_data('max_price'))
        {
            $this->assemble_price_params($args['meta_query']);
            $args['meta_query']['relation'] = 'AND';
        }

        //WPML compatibility
        if (class_exists('SitePress'))
        {
            $args['lang'] = ICL_LANGUAGE_CODE;
        }

        //for dynamic recount cache working with title search
        if ($this->is_isset_in_request_data('woof_title'))
        {
            $args['woof_title'] = strtolower($request['woof_title']);
        }

        //***
        $atts = array();
        if (!isset($args['meta_query']))
        {
            $args['meta_query'] = array();
        }
        $this->assemble_stock_sales_params($args['meta_query']);
        $this->assemble_sku_params($args['meta_query']);
        $args = apply_filters('woocommerce_shortcode_products_query', $args, $atts);
        //***
        $_REQUEST['woof_dyn_recount_going'] = 1;
        remove_filter('posts_clauses', array(WC()->query, 'order_by_popularity_post_clauses'));
        remove_filter('posts_clauses', array(WC()->query, 'order_by_rating_post_clauses'));

        //out of stock products - remove from dyn recount
        //wp-admin/admin.php?page=wc-settings&tab=products&section=inventory
        if (get_option('woocommerce_hide_out_of_stock_items', 'no') == 'yes')
        {
            $args['meta_query'][] = array(
                'key' => '_stock_status',
                'value' => array('instock'),
                'compare' => 'IN'
            );
        }

        //***
        //$post_count = WOOF_HELPER::get_post_count($args);

        static $woof_post_title_filter_added = 0; //just a flag to not add this filter a lot of times
        if ($woof_post_title_filter_added == 0)
        {
            add_filter('posts_where', array($this, 'woof_post_title_filter'), 9999);
            $woof_post_title_filter_added++;
        }
        /*
          if ($type == 'price2')
          {
          echo '<pre>';
          print_r($args);
          echo '</pre>';
          }
         */
        $query = new WP_QueryWoofCounter($args);


        unset($_REQUEST['woof_dyn_recount_going']);
        return $query->found_posts;
    }

    //need for checkboxes and multi-selects dynamic recount
    private function _util_dynamic_count_add_term(&$taxonomies, $curr_term_taxonomy, $curr_term_slug)
    {
        $is_tax_inside_index = -1;
        if (!empty($taxonomies))
        {
            foreach ($taxonomies as $k => $t)
            {
                if ($t['taxonomy'] == $curr_term_taxonomy)
                {
                    $is_tax_inside_index = $k;
                }
            }
        }

        if ($is_tax_inside_index === -1)
        {
            $taxonomies[] = array(
                'taxonomy' => $curr_term_taxonomy,
                'terms' => array($curr_term_slug),
                'field' => 'slug',
                'operator' => 'IN',
                'include_children' => 1
            );
        } else
        {
            $terms = $taxonomies[$is_tax_inside_index]['terms'];
            $terms[] = $curr_term_slug;
            $terms = array_unique($terms);
            $taxonomies[$is_tax_inside_index]['terms'] = $terms;
        }
    }

    public function is_woof_use_chosen()
    {
        return (int) get_option('woof_use_chosen', 1);
    }

    public function woocommerce_before_shop_loop()
    {
        $woof_set_automatically = (int) get_option('woof_set_automatically', 0);
        //$_REQUEST['woof_before_shop_loop_done'] - is just key lock
        if ($woof_set_automatically == 1 AND ! isset($_REQUEST['woof_before_shop_loop_done']))
        {
            $_REQUEST['woof_before_shop_loop_done'] = true;
            $shortcode_hide = false;
            if (isset($this->settings['woof_auto_hide_button']))
            {
                $shortcode_hide = intval($this->settings['woof_auto_hide_button']);
            }

            $price_filter = (int) get_option('woof_show_price_search', 0);

            echo do_shortcode('[woof sid="auto_shortcode" autohide=' . $shortcode_hide . ' price_filter=' . $price_filter . ']');
        }
        ?>


        <?php if (get_option('woof_hide_red_top_panel', 0) == 0): ?>
            <div class="woof_products_top_panel"></div>
        <?php endif; ?>

        <?php
        //for ajax output
        if (get_option('woof_try_ajax', 0) AND ! isset($_REQUEST['woof_products_doing']))
        {
            //$_REQUEST['woocommerce_before_shop_loop_done']=true;
            echo '<div class="woocommerce woocommerce-page woof_shortcode_output">';
            $shortcode_txt = "woof_products is_ajax=1";
            if ($this->is_really_current_term_exists())
            {
                $o = $this->get_really_current_term();
                $shortcode_txt = "woof_products taxonomies={$o->taxonomy}:{$o->term_id} is_ajax=1";
                $_REQUEST['WOOF_IS_TAX_PAGE'] = $o->taxonomy;
            }
            echo '<div id="woof_results_by_ajax" data-shortcode="' . $shortcode_txt . '">';
        }
    }

    public function woocommerce_after_shop_loop()
    {
        //for ajax output
        if (get_option('woof_try_ajax', 0) AND ! isset($_REQUEST['woof_products_doing']))
        {
            echo '</div>';
            echo '</div>';
        }
    }

    public function get_request_data()
    {
        if (isset($_GET['s']))
        {
            $_GET['woof_title'] = $_GET['s'];
        }
        return $_GET;
    }

    public function is_isset_in_request_data($key)
    {
        $request = $this->get_request_data();
        return isset($request[$key]);
    }

    public function get_catalog_orderby($orderby = '', $order = 'ASC')
    {
        if (empty($orderby))
        {
            $orderby = get_option('woocommerce_default_catalog_orderby');
        }
        //D:\myprojects\woocommerce-filter\wp-content\plugins\woocommerce\includes\class-wc-query.php#588
        //$orderby_array = array('menu_order', 'popularity', 'rating',
        //'date', 'price', 'price-desc','rand');
        $meta_key = '';
        global $wpdb;
        switch ($orderby)
        {
            case 'price-desc':
                $orderby = "meta_value_num {$wpdb->posts}.ID";
                $order = 'DESC';
                $meta_key = '_price';
                break;
            case 'price':
                $orderby = "meta_value_num {$wpdb->posts}.ID";
                $order = 'ASC';
                $meta_key = '_price';
                break;
            case 'popularity' :
                $meta_key = 'total_sales';
                // Sorting handled later though a hook
                add_filter('posts_clauses', array(WC()->query, 'order_by_popularity_post_clauses'));
                break;
            case 'rating' :
                $orderby = '';
                $meta_key = '';
                // Sorting handled later though a hook
                add_filter('posts_clauses', array(WC()->query, 'order_by_rating_post_clauses'));
                break;
            case 'title' :
                $orderby = 'title';
                break;
            case 'rand' :
                $orderby = 'rand';
                break;
            case 'date' :
                $order = 'DESC';
                $orderby = 'date';
                break;
            default:
                break;
        }


        return compact('order', 'orderby', 'meta_key');
    }

    private function get_tax_query($additional_taxes = '')
    {
        $data = $this->get_request_data();
        $res = array();
        //static $woo_taxonomies = NULL;
        $woo_taxonomies = NULL;
        //if (!$woo_taxonomies)
        {
            $woo_taxonomies = get_object_taxonomies('product');
        }

        //+++

        if (!empty($data) AND is_array($data))
        {
            foreach ($data as $tax_slug => $value)
            {
                if (in_array($tax_slug, $woo_taxonomies))
                {
                    $value = explode(',', $value);
                    $res[] = array(
                        'taxonomy' => $tax_slug,
                        'field' => 'slug',
                        'terms' => $value
                    );
                }
            }
        }
        //+++
        //for shortcode
        //[woof_products is_ajax=1 per_page=8 dp=0 taxonomies=product_cat:9,12+locations:30,31]
        //dp - ID of shortcode of Display Product for WooCommerce
        $res = $this->_expand_additional_taxes_string($additional_taxes, $res);
        //+++
        if (!empty($res))
        {
            $res = array_merge(array('relation' => 'AND'), $res);
        }

        return $res;
    }

    private function _expand_additional_taxes_string($additional_taxes, $res = array())
    {
        if (!empty($additional_taxes))
        {
            $t = explode('+', $additional_taxes);
            if (!empty($t) AND is_array($t))
            {
                foreach ($t as $string)
                {
                    $tmp = explode(':', $string);
                    $tax_slug = $tmp[0];
                    $tax_terms = explode(',', $tmp[1]);
                    $slugs = array();
                    foreach ($tax_terms as $term_id)
                    {
                        $term = get_term(intval($term_id), $tax_slug);
                        $slugs[] = $term->slug;
                    }

                    //***
                    $res[] = array(
                        'taxonomy' => $tax_slug,
                        'field' => 'slug', //id
                        'terms' => $slugs
                    );
                }
            }
        }

        return $res;
    }

    //works only in shortcode [woof_products]
    private function get_meta_query($args = array())
    {
        //print_r(WC()->query); - will think about it
        $meta_query = WC()->query->get_meta_query();
        $meta_query = array_merge(array('relation' => 'AND'), $meta_query);
        //+++
        $this->assemble_price_params($meta_query);
        $this->assemble_stock_sales_params($meta_query);
        //for sku searching in [woof_products] shortcode
        $this->assemble_sku_params($meta_query);

        return $meta_query;
    }

    //plugins\woocommerce\includes\class-wc-shortcodes.php#295
    //[woof_products is_ajax=1 per_page=8 dp=0 taxonomies=product_cat:9,12+locations:30,31]
    public function woof_products($atts)
    {
        $_REQUEST['woof_products_doing'] = 1;
        add_filter('posts_where', array($this, 'woof_post_title_filter'), 9999);
        $shortcode_txt = 'woof_products';
        if (!empty($atts))
        {
            foreach ($atts as $key => $value)
            {
                $shortcode_txt.=' ' . $key . '=' . $value;
            }
        }
        //***
        $data = $this->get_request_data();
        $catalog_orderby = $this->get_catalog_orderby(isset($data['orderby']) ? $data['orderby'] : '');


        extract(shortcode_atts(array(
            'columns' => apply_filters('loop_shop_columns', 4),
            'orderby' => $catalog_orderby['orderby'],
            'order' => $catalog_orderby['order'],
            'page' => 1,
            'per_page' => 0,
            'is_ajax' => 0,
            'taxonomies' => '',
            'sid' => '',
            'dp' => 0
                        ), $atts));

        //***
        //this needs just for AJAX mode for shortcode [woof] in woof_draw_products()
        $_REQUEST['woof_additional_taxonomies_string'] = $taxonomies;

        //+++
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            //'posts_per_page' => $per_page,
            'orderby' => $orderby,
            'order' => $order,
            'meta_query' => $this->get_meta_query(),
            'tax_query' => $this->get_tax_query($taxonomies)
        );



        $args['posts_per_page'] = $this->settings['per_page'];


        if ($per_page > 0)
        {
            $args['posts_per_page'] = $per_page;
        } else
        {
            //compatibility for woocommerce-products-per-page
            if (WC()->session->__isset('products_per_page'))
            {
                $args['posts_per_page'] = WC()->session->__get('products_per_page');
            }
        }

        //Display Product for WooCommerce compatibility
        if (isset($_REQUEST['perpage']))
        {
            //if (is_integer($_REQUEST['perpage']))
            {
                $args['posts_per_page'] = $_REQUEST['perpage'];
            }
        }

        //if smth wrong, set default per page option
        if (!$args['posts_per_page'])
        {
            $args['posts_per_page'] = $this->settings['per_page'];
        }

        //***

        if (!empty($catalog_orderby['meta_key']))
        {
            $args['meta_key'] = $catalog_orderby['meta_key'];
            $args['orderby'] = $catalog_orderby['orderby'];
            if (!empty($catalog_orderby['order']))
            {
                $args['order'] = $catalog_orderby['order'];
            }
        } else
        {
            $args['orderby'] = $catalog_orderby['orderby'];
            if (!empty($catalog_orderby['order']))
            {
                $args['order'] = $catalog_orderby['order'];
            }
        }
        //print_r($args);
        //+++
        $pp = $page;
        if (get_query_var('page'))
        {
            $pp = get_query_var('page');
        }
        if (get_query_var('paged'))
        {
            $pp = get_query_var('paged');
        }

        if ($pp > 1)
        {
            $args['paged'] = $pp;
        } else
        {
            $args['paged'] = ((get_query_var('page')) ? get_query_var('page') : $page);
        }
        //+++

        $wr = apply_filters('woocommerce_shortcode_products_query', $args, $atts);
        global $products, $wp_query;

        //print_r($wr);
        $_REQUEST['woof_wp_query'] = $wp_query = $products = new WP_Query($wr);
        $_REQUEST['woof_wp_query_args'] = $wr;
        //***
        ob_start();
        global $woocommerce_loop;
        $woocommerce_loop['columns'] = $columns;
        $woocommerce_loop['loop'] = 0;
        ?>

        <?php if ($is_ajax == 1): ?>
            <?php //if (!get_option('woof_try_ajax')):                                                                                   ?>
            <div id="woof_results_by_ajax" class="woof_results_by_ajax_shortcode" data-shortcode="<?php echo $shortcode_txt ?>">
            <?php //endif;                            ?>
            <?php endif; ?>
            <?php
            if ($products->have_posts()) :
                add_filter('post_class', array($this, 'woo_post_class'));
                $_REQUEST['woof_before_shop_loop_done'] = true;
                ?>

                <div class="woocommerce columns-<?php echo $columns ?> woocommerce-page woof_shortcode_output">

            <?php
            if ($dp == 0)
            {//Display Product for WooCommerce compatibility
                do_action('woocommerce_before_shop_loop');
            }
            ?>


            <?php
            if (function_exists('woocommerce_product_loop_start'))
            {
                woocommerce_product_loop_start();
            }
            ?>

                    <?php
                    global $woocommerce_loop;
                    $woocommerce_loop['columns'] = $columns;
                    $woocommerce_loop['loop'] = 0;
                    //+++
                    wc_get_template('loop/loop-start.php');

                    //WOOCS compatibility
                    global $WOOCS;
                    if (!method_exists($WOOCS, 'woocs_convert_currency'))
                    {
                        if (class_exists('WOOCS') AND defined('DOING_AJAX'))
                        {
                            //IT IS OBSOLETE AND NOT RIGHT ALREADY FROM X.0.9 VERSIONS
                            //add_filter('raw_woocommerce_price', array($this, 'raw_woocommerce_price'), 1001);
                            //add_filter('woocommerce_currency_symbol', array($this, 'woocommerce_currency_symbol'), 1001);
                        }
                    }
                    ?>



            <?php
            //products output
            if ($dp == 0)
            {//Display Product for WooCommerce compatibility
                while ($products->have_posts()) : $products->the_post();
                    wc_get_template_part('content', 'product');
                endwhile; // end of the loop.
            } else
            {
                echo do_shortcode('[displayProduct id="' . $dp . '"]');
            }
            ?>



            <?php wc_get_template('loop/loop-end.php'); ?>

                    <?php
                    if (function_exists('woocommerce_product_loop_end'))
                    {
                        woocommerce_product_loop_end();
                    }
                    ?>

                    <?php do_action('woocommerce_after_shop_loop'); ?>

                </div>


            <?php
        else:
            if ($is_ajax == 1)
            {
                //if (!get_option('woof_try_ajax'))
                {
                    ?>
                        <div id="woof_results_by_ajax" class="woof_results_by_ajax_shortcode" data-shortcode="<?php echo $shortcode_txt ?>">
                        <?php
                    }
                }
                ?>
                    <div class="woocommerce woocommerce-page woof_shortcode_output">

            <?php
            if (!$is_ajax)
            {
                wc_get_template('loop/no-products-found.php');
            } else
            {
                ?>
                            <div id="woof_results_by_ajax" class="woof_results_by_ajax_shortcode" data-shortcode="<?php echo $shortcode_txt ?>">
                            <?php
                            wc_get_template('loop/no-products-found.php');
                            ?>
                            </div>
                                <?php
                            }
                            ?>

                    </div>
            <?php
            if ($is_ajax == 1)
            {
                if (!get_option('woof_try_ajax', 0))
                {
                    echo '</div>';
                }
            }
        endif;
        ?>

                <?php if ($is_ajax == 1): ?>
                    <?php if (!get_option('woof_try_ajax', 0)): ?>
                    </div>
                    <?php endif; ?>
            <?php endif; ?>
            <?php
            wp_reset_postdata();
            wp_reset_query();

            unset($_REQUEST['woof_products_doing']);

            return ob_get_clean();
        }

        public function raw_woocommerce_price($price)
        {
            if (class_exists('WOOCS'))
            {
                global $WOOCS;
                $currencies = $WOOCS->get_currencies();
                return $price * $currencies[$WOOCS->current_currency]['rate'];
            }

            return $price;
        }

        public function woocommerce_currency_symbol($currency)
        {
            if (class_exists('WOOCS'))
            {
                global $WOOCS;
                $currencies = $WOOCS->get_currencies();
                return $currencies[$WOOCS->current_currency]['symbol'];
            }

            return $currency;
        }

        //for shortcode woof_products
        public function woo_post_class($classes)
        {
            global $post;
            $classes[] = 'product';
            $classes[] = 'type-product';
            $classes[] = 'status-publish';
            $classes[] = 'has-post-thumbnail';
            $classes[] = 'post-' . $post->ID;
            return $classes;
        }

        //shortcode, works when ajax mode only for shop/category page
        public function woof_draw_products()
        {
            $link = parse_url($_REQUEST['link'], PHP_URL_QUERY);
            parse_str($link, $_GET); //$_GET data init
            //add_filter('posts_where', array($this, 'woof_post_title_filter'), 9999);
            $products = do_shortcode("[" . $_REQUEST['shortcode'] . " page=" . $_REQUEST['page'] . "]");
            //+++
            $form = '';
            if (isset($_REQUEST['woof_shortcode']))//if search form on the page exists
            {
                if (empty($_REQUEST['woof_additional_taxonomies_string']))
                {
                    $form = do_shortcode("[" . $_REQUEST['woof_shortcode'] . "]");
                } else
                {
                    $form = do_shortcode("[" . $_REQUEST['woof_shortcode'] . " taxonomies={$_REQUEST['woof_additional_taxonomies_string']}]");
                }
            }
            wp_die(json_encode(compact('products', 'form')));
        }

        //[woof taxonomies="product_cat:9" sid="auto_shortcode"]
        public function woof_shortcode($atts)
        {
            $args = array();
            //this for synhronizating shortcode woof_products if its has attribute taxonomies
            if (isset($atts['taxonomies']))
            {
                //$args['additional_taxes'] = $this->get_tax_query($atts['taxonomies']);
                $args['additional_taxes'] = $atts['taxonomies'];
            } else
            {
                $args['additional_taxes'] = '';
            }

            //+++
            $taxonomies = $this->get_taxonomies();
            $allow_taxonomies = (array) $this->settings['tax'];
            $args['taxonomies'] = array();
            if (!empty($taxonomies))
            {
                foreach ($taxonomies as $tax_key => $tax)
                {
                    if (!in_array($tax_key, array_keys($allow_taxonomies)))
                    {
                        continue;
                    }
                    //+++
                    $args['woof_settings'] = get_option('woof_settings', array());
                    $args['taxonomies_info'][$tax_key] = $tax;
                    $hide_empty = false;
                    $args['taxonomies'][$tax_key] = WOOF_HELPER::get_terms($tax_key, $hide_empty);
                }
            }
            //***
            if (isset($atts['skin']))
            {
                wp_enqueue_style('woof_skin_' . $atts['skin'], WOOF_LINK . 'css/shortcode_skins/' . $atts['skin'] . '.css');
            }
            //***

            if (isset($atts['sid']))
            {
                $args['sid'] = $atts['sid'];
                wp_enqueue_script('woof_sid', WOOF_LINK . 'js/woof_sid.js');
            }


            if (isset($atts['autohide']))
            {
                $args['autohide'] = $atts['autohide'];
            } else
            {
                $args['autohide'] = 0;
            }


            $args['price_filter'] = get_option('woof_show_price_search', 0);


            //***
            $args['show_woof_edit_view'] = 0;
            if (current_user_can('create_users'))
            {
                $args['show_woof_edit_view'] = 1;
                //wp_enqueue_script('jquery');
                //wp_enqueue_script('jquery-ui-core', array('jquery'));
                //wp_enqueue_script('jquery-ui-dialog', array('jquery', 'jquery-ui-core'));
                //wp_enqueue_style('jquery-ui-dialog',includes_url('css/jquery-ui-dialog.min.css'));
                //wp_enqueue_style('jquery-ui-dialog', 'http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
            }
            return $this->render_html(WOOF_PATH . 'views/woof.php', $args);
        }

        //shortcode
        public function woof_price_filter($args = array())
        {
            return $this->render_html(WOOF_PATH . 'views/shortcodes/woof_price_filter.php', $args);
        }

        //shortcode
        public function woof_title_filter($args = array())
        {
            return $this->render_html(WOOF_PATH . 'views/shortcodes/woof_title_filter.php', $args);
        }

        //shortcode
        public function woof_sku_filter($args = array())
        {
            return $this->render_html(WOOF_PATH . 'views/shortcodes/woof_sku_filter.php', $args);
        }

        //redraw search form
        public function woof_redraw_woof()
        {
            $_REQUEST['woof_shortcode_txt'] = $_REQUEST['shortcode'];
            wp_die(do_shortcode("[" . $_REQUEST['shortcode'] . "]"));
        }

        public function woocommerce_pagination_args($args)
        {
            return $args;
        }

        //for relevant excluding terms in shortcode [woof]
        public function woof_exclude_tax_key($terms)
        {
            //if (!defined('DOING_AJAX')) - fixed, commented for AJAX mode because widget redraw was bad (18-08-2015)
            {
                if ($this->is_really_current_term_exists())
                {
                    /*
                      $queried_obj = get_queried_object();
                      $current_term_id = $queried_obj->term_id;
                      $terms = $terms[$current_term_id]['childs'];
                     *
                     */

                    $queried_obj = $this->get_really_current_term();
                    $current_term_id = $queried_obj->term_id;
                    $parent_id = $queried_obj->parent;
                    //search for childs in cycle
                    if ($parent_id == 0)
                    {
                        $terms = $terms[$current_term_id]['childs'];
                    } else
                    {
                        foreach ($terms as $top_tid => $value)
                        {
                            if (!empty($value['childs']))
                            {
                                $terms = $this->_woof_exclude_tax_key_util1($current_term_id, $top_tid, $value['childs']);
                                if (!empty($terms))
                                {
                                    break;
                                }
                            }
                        }

                        //woocommerce-products-filter-bk21 - old code is here
                    }
                }
                //+++
            }

            return $terms;
        }

        ///just utilita for woof_exclude_tax_key
        private function _woof_exclude_tax_key_util1($current_term_id, $top_tid, $child_terms)
        {
            $terms = array();
            if (!empty($child_terms))
            {
                if (isset($child_terms[$current_term_id]['childs']))
                {
                    $terms = $child_terms[$current_term_id]['childs'];
                } else
                {
                    foreach ($child_terms as $tid => $value)
                    {
                        $parent_keys[] = $top_tid;
                        $terms = $this->_woof_exclude_tax_key_util1($current_term_id, $tid, $value['childs']);
                        if (!empty($terms))
                        {
                            break;
                        }
                    }
                }
            }

            return $terms;
        }

        //if we are on the category products page, or any another product taxonomy page
        private function get_really_current_term()
        {
            $res = NULL;
            $key = $this->session_rct_key;
            /*
              if (isset($_SESSION['woof_really_current_term']))
              {
              $res = $_SESSION['woof_really_current_term'];
              }
             */



            /*
              if (WC()->session->__isset($key))
              {
              $res = WC()->session->__get($key);
              }
             */


            if ($this->storage->is_isset($key))
            {
                $res = $this->storage->get_val($key);
            }


            return $res;
        }

        private function is_really_current_term_exists()
        {
            return (bool) $this->get_really_current_term();
        }

        //we need it when making search on the category page == any taxonomy term page
        private function set_really_current_term($queried_obj = NULL)
        {
            if (defined('DOING_AJAX'))
            {
                return false;
            }

            $key = $this->session_rct_key;
            /*
              if ($queried_obj === NULL)
              {
              //unset($_SESSION['woof_really_current_term']);
              WC()->session->__unset($key);
              } else
              {
              //$_SESSION['woof_really_current_term'] = $queried_obj;
              WC()->session->set($key, $queried_obj);
              }
             *
             */

            if ($queried_obj === NULL)
            {
                $this->storage->unset_val($key);
            } else
            {
                $this->storage->set_val($key, $queried_obj);
            }

            return $queried_obj;
        }

        //ajax + wp_cron
        public function cache_count_data_clear()
        {
            //WOOF_HELPER::log('cache_count_data_clear ' . date('d-m-Y H:i:s'));
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE " . self::$query_cache_table);
            //wp_die('done');
        }

        //Display Product for WooCommerce compatibility
        public function woof_modify_query_args($query_args)
        {

            if (isset($_REQUEST[$this->get_swoof_search_slug()]))
            {
                if (isset($_REQUEST['woof_wp_query_args']))
                {
                    $query_args['meta_query'] = $_REQUEST['woof_wp_query_args']['meta_query'];
                    $query_args['tax_query'] = $_REQUEST['woof_wp_query_args']['tax_query'];
                    $query_args['paged'] = $_REQUEST['woof_wp_query_args']['paged'];
                }
            }

            return $query_args;
        }

        public function render_html($pagepath, $data = array())
        {
            @extract($data);
            ob_start();
            include($pagepath);
            return ob_get_clean();
        }

    }

//***

    $WOOF = new WOOF();
    $GLOBALS['WOOF'] = $WOOF;
    add_action('init', array($WOOF, 'init'), 1);

