<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

final class WOOF_HELPER
{

    static $notices = array();

    //log test data while makes debbuging
    public static function log($string)
    {
        $handle = fopen(WOOF_PATH . 'log.txt', 'a+');
        $string.= PHP_EOL;
        fwrite($handle, $string);
        fclose($handle);
    }

    public static function get_terms($taxonomy, $hide_empty = true, $get_childs = true, $selected = 0, $category_parent = 0)
    {
        static $collector = array();
        global $WOOF;

        if (isset($collector[$taxonomy]))
        {
            return $collector[$taxonomy];
        }

        //***
        if (isset($WOOF->settings['cache_terms']) AND $WOOF->settings['cache_terms'] == 1)
        {
            $cache_key = 'woof_terms_cache_' . md5($taxonomy . '-' . (int) $hide_empty . '-' . (int) $get_childs . '-' . (int) $selected . '-' . (int) $category_parent);
            if (false !== ( $cats = get_transient($cache_key) ))
            {
                return $cats;
            }
        }
        //***

        $args = array(
            'orderby' => 'name',
            'order' => 'ASC',
            'style' => 'list',
            'show_count' => 0,
            'hide_empty' => $hide_empty,
            'use_desc_for_title' => 1,
            'child_of' => 0,
            'hierarchical' => true,
            'title_li' => '',
            'show_option_none' => '',
            'number' => '',
            'echo' => 0,
            'depth' => 0,
            'current_category' => $selected,
            'pad_counts' => 0,
            'taxonomy' => $taxonomy,
            'walker' => 'Walker_Category');


        //WPML compatibility
        if (class_exists('SitePress'))
        {
            $args['lang'] = ICL_LANGUAGE_CODE;
        }

        $cats_objects = get_categories($args);

        $cats = array();
        if (!empty($cats_objects))
        {
            foreach ($cats_objects as $value)
            {
                if (is_object($value) AND $value->category_parent == $category_parent)
                {
                    $cats[$value->term_id] = array();
                    $cats[$value->term_id]['term_id'] = $value->term_id;
                    $cats[$value->term_id]['slug'] = $value->slug;
                    $cats[$value->term_id]['taxonomy'] = $value->taxonomy;
                    $cats[$value->term_id]['name'] = $value->name;
                    $cats[$value->term_id]['count'] = $value->count;
                    $cats[$value->term_id]['parent'] = $value->parent;
                    if ($get_childs)
                    {
                        $cats[$value->term_id]['childs'] = self::assemble_terms_childs($cats_objects, $value->term_id);
                    }
                }
            }
        }

        //***
        if (isset($WOOF->settings['cache_terms']) AND $WOOF->settings['cache_terms'] == 1)
        {
            $period = 0;

            $periods = array(
                0 => 0,
                'hourly' => HOUR_IN_SECONDS,
                'twicedaily' => 12 * HOUR_IN_SECONDS,
                'daily' => DAY_IN_SECONDS,
                'days2' => 2 * DAY_IN_SECONDS,
                'days3' => 3 * DAY_IN_SECONDS,
                'days4' => 4 * DAY_IN_SECONDS,
                'days5' => 5 * DAY_IN_SECONDS,
                'days6' => 6 * DAY_IN_SECONDS,
                'days7' => 7 * DAY_IN_SECONDS
            );

            if (isset($WOOF->settings['cache_terms_auto_clean']))
            {
                $period = $WOOF->settings['cache_terms_auto_clean'];

                if (!$period)
                {
                    $period = 0;
                }
            }

            set_transient($cache_key, $cats, $periods[$period]);
        }
        //***

        $collector[$taxonomy] = $cats;
        return $cats;
    }

    //just for get_terms
    private static function assemble_terms_childs($cats_objects, $parent_id)
    {
        $res = array();
        foreach ($cats_objects as $value)
        {
            if ($value->category_parent == $parent_id)
            {
                $res[$value->term_id]['term_id'] = $value->term_id;
                $res[$value->term_id]['name'] = $value->name;
                $res[$value->term_id]['slug'] = $value->slug;
                $res[$value->term_id]['count'] = $value->count;
                $res[$value->term_id]['taxonomy'] = $value->taxonomy;
                $res[$value->term_id]['parent'] = $value->parent;
                $res[$value->term_id]['childs'] = self::assemble_terms_childs($cats_objects, $value->term_id);
            }
        }

        return $res;
    }

    //https://wordpress.org/support/topic/translated-label-with-wpml
    //for taxonomies labels translations
    public static function wpml_translate($taxonomy_info, $string = '', $index = -1)
    {
        global $WOOF;

        if (empty($string))
        {
            $string = $WOOF->settings['custom_tax_label'][$taxonomy_info->name];
        }


        if (empty($string))
        {
            $string = $taxonomy_info->label;
        }


        //***
        $check_for_custom_label = false;
        if (class_exists('SitePress'))
        {
            $lang = ICL_LANGUAGE_CODE;
            $woof_settings = get_option('woof_settings');
            if (isset($woof_settings['wpml_tax_labels']) AND ! empty($woof_settings['wpml_tax_labels']))
            {
                $translations = $woof_settings['wpml_tax_labels'];
                //$translations = unserialize($translations);
                /*
                  $translations = array(
                  'es' => array(
                  'Locations' => 'Ubicaciones',
                  'Size' => 'Tamaño'
                  ),
                  'de' => array(
                  'Locations' => 'Lage',
                  'Size' => 'Größe'
                  ),
                  );
                 */

                if (isset($translations[$lang]))
                {
                    if (isset($translations[$lang][$string]))
                    {
                        $string = $translations[$lang][$string];
                    } else
                    {
                        $check_for_custom_label = TRUE;
                    }
                } else
                {
                    $check_for_custom_label = TRUE;
                }
            } else
            {
                $check_for_custom_label = TRUE;
            }
        } else
        {
            $check_for_custom_label = TRUE;
        }

        //+++
        if (empty($string))
        {
            $check_for_custom_label = FALSE;
        }


        //for hierarchy titles type of: name1+name2+name3
        if ($index != -1)
        {
            if (stripos($string, '+'))
            {
                $tmp = explode('+', $string);
                if (isset($tmp[$index]))
                {
                    $string = $tmp[$index];
                }
            }
        }

        return $string;
    }

    //drawing of native woo price filter
    public static function price_filter()
    {
        global $_chosen_attributes, $wpdb, $wp, $WOOF;
        $request = $WOOF->get_request_data();
        /*
          if (!is_post_type_archive('product') && !is_tax(get_object_taxonomies('product')))
          {
          return;
          }

          if (sizeof(WC()->query->unfiltered_product_ids) == 0)
          {
          return; // None shown - return
          }
         */
        $min_price = $WOOF->is_isset_in_request_data('min_price') ? esc_attr($request['min_price']) : '';
        $max_price = $WOOF->is_isset_in_request_data('max_price') ? esc_attr($request['max_price']) : '';

        //wp_enqueue_script('wc-price-slider');
        // Remember current filters/search
        $fields = '';

        if (get_search_query())
        {
            $fields .= '<input type="hidden" name="s" value="' . get_search_query() . '" />';
        }

        if (!empty($_GET['post_type']))
        {
            $fields .= '<input type="hidden" name="post_type" value="' . esc_attr($_GET['post_type']) . '" />';
        }

        if (!empty($_GET['product_cat']))
        {
            $fields .= '<input type="hidden" name="product_cat" value="' . esc_attr($_GET['product_cat']) . '" />';
        }

        if (!empty($_GET['product_tag']))
        {
            $fields .= '<input type="hidden" name="product_tag" value="' . esc_attr($_GET['product_tag']) . '" />';
        }

        if (!empty($_GET['orderby']))
        {
            $fields .= '<input type="hidden" name="orderby" value="' . esc_attr($_GET['orderby']) . '" />';
        }

        if ($_chosen_attributes)
        {
            foreach ($_chosen_attributes as $attribute => $data)
            {
                $taxonomy_filter = 'filter_' . str_replace('pa_', '', $attribute);

                $fields .= '<input type="hidden" name="' . esc_attr($taxonomy_filter) . '" value="' . esc_attr(implode(',', $data['terms'])) . '" />';

                if ('or' == $data['query_type'])
                {
                    $fields .= '<input type="hidden" name="' . esc_attr(str_replace('pa_', 'query_type_', $attribute)) . '" value="or" />';
                }
            }
        }

        if (0 === sizeof(WC()->query->layered_nav_product_ids))
        {
            $min = floor($wpdb->get_var(
                            $wpdb->prepare('
					SELECT min(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE meta_key IN ("' . implode('","', apply_filters('woocommerce_price_filter_meta_keys', array('_price', '_min_variation_price'))) . '")
					AND meta_value != ""
				', $wpdb->posts, $wpdb->postmeta)
            ));
            $max = ceil($wpdb->get_var(
                            $wpdb->prepare('
					SELECT max(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE meta_key IN ("' . implode('","', apply_filters('woocommerce_price_filter_meta_keys', array('_price'))) . '")
				', $wpdb->posts, $wpdb->postmeta, '_price')
            ));
        } else
        {
            $min = floor($wpdb->get_var(
                            $wpdb->prepare('
					SELECT min(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE meta_key IN ("' . implode('","', apply_filters('woocommerce_price_filter_meta_keys', array('_price', '_min_variation_price'))) . '")
					AND meta_value != ""
					AND (
						%1$s.ID IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
						OR (
							%1$s.post_parent IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
							AND %1$s.post_parent != 0
						)
					)
				', $wpdb->posts, $wpdb->postmeta
            )));
            $max = ceil($wpdb->get_var(
                            $wpdb->prepare('
					SELECT max(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE meta_key IN ("' . implode('","', apply_filters('woocommerce_price_filter_meta_keys', array('_price'))) . '")
					AND (
						%1$s.ID IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
						OR (
							%1$s.post_parent IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
							AND %1$s.post_parent != 0
						)
					)
				', $wpdb->posts, $wpdb->postmeta
            )));
        }

        if ($min == $max)
        {
            return;
        }


        if ('' == get_option('permalink_structure'))
        {
            $form_action = remove_query_arg(array('page', 'paged'), add_query_arg($wp->query_string, '', home_url($wp->request)));
        } else
        {
            $form_action = preg_replace('%\/page/[0-9]+%', '', home_url(trailingslashit($wp->request)));
        }

        echo '<form method="get" action="' . esc_url($form_action) . '">
			<div class="price_slider_wrapper">
				<div class="price_slider" style="display:none;"></div>
				<div class="price_slider_amount">
					<input type="text" id="min_price" name="min_price" value="' . esc_attr($min_price) . '" data-min="' . esc_attr(apply_filters('woocommerce_price_filter_widget_amount', $min)) . '" placeholder="' . __('Min price', 'woocommerce') . '" />
					<input type="text" id="max_price" name="max_price" value="' . esc_attr($max_price) . '" data-max="' . esc_attr(apply_filters('woocommerce_price_filter_widget_amount', $max)) . '" placeholder="' . __('Max price', 'woocommerce') . '" />
					<button type="submit" class="button">' . __('Filter', 'woocommerce') . '</button>
					<div class="price_label" style="display:none;">
						' . __('Price:', 'woocommerce') . ' <span class="from"></span> &mdash; <span class="to"></span>
					</div>
					' . $fields . '
					<div class="clear"></div>
				</div>
			</div>
		</form>';
    }

    //for drop-down price filter
    public static function get_price2_filter_data($additional_taxes = '')
    {
        $woof_settings = get_option('woof_settings', array());
        if (isset($woof_settings['by_price']['ranges']) AND ! empty($woof_settings['by_price']['ranges']))
        {
            global $WOOF;
            $res = array();
            $request = $WOOF->get_request_data();
            $res['selected'] = '';
            if (isset($request['min_price']) AND isset($request['max_price']))
            {
                $res['selected'] = $request['min_price'] . '-' . $request['max_price'];
            }

            //+++
            $r = array(); //drop-doen options
            $rc = array(); //count of items
            $ranges = explode(',', $woof_settings['by_price']['ranges']);
            $get = $request;
            $max = self::get_max_price();
            $show_count = get_option('woof_show_count', 0);
            foreach ($ranges as $value)
            {
                $key = str_replace('i', $max, $value);
                //+++
                $tmp = explode('-', $value);
                $wc_price_args = array();
                /*
                  $wc_price_args = array(
                  'currency' => 'default'
                  );
                 */
                //$_REQUEST['woof_price_filter_working'] = TRUE;

                $tmp[0] = wc_price(floatval($tmp[0]), $wc_price_args);

                if ($tmp[1] != 'i')
                {
                    $tmp[1] = wc_price(floatval($tmp[1]), $wc_price_args);
                } else
                {
                    $tmp[1] = '&#8734;';
                }
                //$_REQUEST['woof_price_filter_working'] = FALSE;


                $value = $tmp[0] . ' - ' . $tmp[1];
                //+++

                $r[$key] = $value;
                //***
                $v = explode('-', $key);
                $_GET['min_price'] = $v[0];
                $_GET['max_price'] = $v[1];
                if ($show_count)
                {
                    $rc[$key] = $WOOF->dynamic_count(NULL, 0, $additional_taxes);
                } else
                {
                    $rc[$key] = 0;
                }
            }
            $_GET = $get;
            $ranges['options'] = $r;
            $ranges['count'] = $rc;
            //+++
            $res['ranges'] = $ranges;
            return $res;
        }


        return array();
    }

    public static function get_max_price()
    {
        global $wpdb;
        if (0 === sizeof(WC()->query->layered_nav_product_ids))
        {

            $max = ceil($wpdb->get_var(
                            $wpdb->prepare('
					SELECT max(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE meta_key IN ("' . implode('","', apply_filters('woocommerce_price_filter_meta_keys', array('_price'))) . '")
				', $wpdb->posts, $wpdb->postmeta, '_price')
            ));
        } else
        {

            $max = ceil($wpdb->get_var(
                            $wpdb->prepare('
					SELECT max(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE meta_key IN ("' . implode('","', apply_filters('woocommerce_price_filter_meta_keys', array('_price'))) . '")
					AND (
						%1$s.ID IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
						OR (
							%1$s.post_parent IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
							AND %1$s.post_parent != 0
						)
					)
				', $wpdb->posts, $wpdb->postmeta
            )));
        }


        return $max;
    }

    public static function get_min_price()
    {
        global $wpdb;

        if (0 === sizeof(WC()->query->layered_nav_product_ids))
        {
            $min = floor($wpdb->get_var(
                            $wpdb->prepare('
					SELECT min(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE meta_key IN ("' . implode('","', apply_filters('woocommerce_price_filter_meta_keys', array('_price', '_min_variation_price'))) . '")
					AND meta_value != ""
				', $wpdb->posts, $wpdb->postmeta)
            ));
        } else
        {
            $min = floor($wpdb->get_var(
                            $wpdb->prepare('
					SELECT min(meta_value + 0)
					FROM %1$s
					LEFT JOIN %2$s ON %1$s.ID = %2$s.post_id
					WHERE meta_key IN ("' . implode('","', apply_filters('woocommerce_price_filter_meta_keys', array('_price', '_min_variation_price'))) . '")
					AND meta_value != ""
					AND (
						%1$s.ID IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
						OR (
							%1$s.post_parent IN (' . implode(',', array_map('absint', WC()->query->layered_nav_product_ids)) . ')
							AND %1$s.post_parent != 0
						)
					)
				', $wpdb->posts, $wpdb->postmeta
            )));
        }
        return $min;
    }

    //is customer look the site from mobile device
    public static function is_mobile_device()
    {
        if (isset($_SERVER["HTTP_USER_AGENT"]))
        {
            return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
        }

        return false;
    }

    public static function show_admin_notice($key)
    {
        global $WOOF;
        echo $WOOF->render_html(WOOF_PATH . 'views/notices/' . $key . '.php');
    }

    public static function add_notice($key)
    {
        //update_option('woof_notices', array());
        $notices = get_option('woof_notices', array());
        $is_hidden = false;
        if (isset($notices[$key]) AND $notices[$key] == 'hidden')
        {
            $is_hidden = true;
        }

        if (!$is_hidden)
        {
            self::$notices[] = $key;
            $notices[] = $key;
            update_option('woof_notices', $notices);
        }
    }

    public static function hide_admin_notices()
    {
        if (isset($_GET['woof_hide_notice']) && isset($_GET['_wpnonce']))
        {
            if (!wp_verify_nonce($_GET['_wpnonce']))
            {
                wp_die(__('Action failed. Please refresh the page and retry.', 'woocommerce-products-filter'));
            }

            if (!current_user_can('manage_woocommerce'))
            {
                wp_die(__('Cheatin&#8217; huh?', 'woocommerce-products-filter'));
            }

            $key = sanitize_text_field($_GET['woof_hide_notice']);
            unset(self::$notices[$key]);
            $notices = get_option('woof_notices', array());
            $notices[$key] = 'hidden';
            update_option('woof_notices', $notices);
        }
    }

}
