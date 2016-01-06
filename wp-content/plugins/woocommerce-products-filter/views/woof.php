<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>


<?php
//+++
$args = array();
$args['show_count'] = get_option('woof_show_count', 0);
$args['show_count_dynamic'] = get_option('woof_show_count_dynamic', 0);
$args['hide_dynamic_empty_pos'] = false;
$args['woof_autosubmit'] = get_option('woof_autosubmit', 0);
?>

<?php if ($autohide): ?>
    <div>
        <?php
        if (isset($this->settings['woof_auto_hide_button_img']) AND ! empty($this->settings['woof_auto_hide_button_img']))
        {
            if ($this->settings['woof_auto_hide_button_img'] != 'none')
            {
                ?>
                <style type="text/css">
                    .woof_show_auto_form,.woof_hide_auto_form
                    {
                        background-image: url('<?php echo $this->settings['woof_auto_hide_button_img'] ?>') !important;
                    }
                </style>
                <?php
            } else
            {
                ?>
                <style type="text/css">
                    .woof_show_auto_form,.woof_hide_auto_form
                    {
                        background-image: none !important;
                    }
                </style>
                <?php
            }
        }
        //***
        $woof_auto_hide_button_txt = '';
        if (isset($this->settings['woof_auto_hide_button_txt']))
        {
            $woof_auto_hide_button_txt = $this->settings['woof_auto_hide_button_txt'];
        }
        ?>
        <a href="javascript:void(0);" class="woof_show_auto_form <?php if (isset($this->settings['woof_auto_hide_button_img']) AND $this->settings['woof_auto_hide_button_img'] == 'none') echo 'woof_show_auto_form_txt'; ?>"><?php echo $woof_auto_hide_button_txt ?></a><br />
        <div class="woof_auto_show woof_overflow_hidden" style="opacity: 0; height: 1px;">
            <div class="woof_auto_show_indent woof_overflow_hidden">
            <?php endif; ?>




            <div class="woof <?php if (!empty($sid)): ?>woof_sid woof_sid_<?php echo $sid ?><?php endif; ?>" <?php if (!empty($sid)): ?>data-sid="<?php echo $sid; ?>"<?php endif; ?> shortcode="<?php echo(isset($_REQUEST['woof_shortcode_txt']) ? $_REQUEST['woof_shortcode_txt'] : 'woof') ?>">

                <?php if ($show_woof_edit_view AND ! empty($sid)): ?>
                    <a href="#" class="woof_edit_view" data-sid="<?php echo $sid ?>"><?php _e('edit view helper', 'woocommerce-products-filter') ?></a>
                    <div></div>
                <?php endif; ?>


                <?php if ($price_filter == 1): ?>
                    <div data-css-class="woof_price_search_container" class="woof_price_search_container woof_container">
                        <div class="woof_container_overlay_item"></div>
                        <div class="woof_container_inner">
                            <div class="woocommerce widget_price_filter">
                                <?php //the_widget('WC_Widget_Price_Filter', array('title' => ''));   ?>
                                <?php if (isset($this->settings['price_filter_title_txt']) AND ! empty($this->settings['price_filter_title_txt'])): ?>
                                    <h4><?php echo $this->settings['price_filter_title_txt']; ?></h4>
                                <?php endif; ?>
                                <?php WOOF_HELPER::price_filter(); ?>
                            </div>
                        </div>
                    </div>
                   <div style="clear:both;"></div>
                <?php endif; ?>


                <div class="woof_redraw_zone">

                    <?php if ($price_filter == 2): ?>
                        <div data-css-class="woof_price2_search_container" class="woof_price2_search_container woof_container">
                            <div class="woof_container_overlay_item"></div>
                            <div class="woof_container_inner">
                                <?php if (isset($this->settings['price_filter_title_txt']) AND ! empty($this->settings['price_filter_title_txt'])): ?>
                                    <h4><?php echo $this->settings['price_filter_title_txt']; ?></h4>
                                <?php endif; ?>


                                <?php echo do_shortcode('[woof_price_filter additional_taxes="' . $additional_taxes . '"]'); ?>


                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (get_option('woof_show_in_stock_only')): ?>
                        <div data-css-class="woof_checkbox_instock_container" class="woof_checkbox_instock_container woof_container">
                            <div class="woof_container_overlay_item"></div>
                            <div class="woof_container_inner">
                                <input type="checkbox" class="woof_checkbox_instock" id="woof_checkbox_instock" name="stock" value="0" <?php checked('instock', $this->is_isset_in_request_data('stock') ? 'instock' : '', true) ?> />&nbsp;&nbsp;<label for="woof_checkbox_instock"><?php _e('In stock only', 'woocommerce-products-filter') ?></label><br />
                            </div>
                        </div>
                    <?php endif; ?>


                    <?php if (get_option('woof_show_sales_only')): ?>
                        <div data-css-class="woof_checkbox_sales_container" class="woof_checkbox_sales_container woof_container">
                            <div class="woof_container_overlay_item"></div>
                            <div class="woof_container_inner">
                                <input type="checkbox" class="woof_checkbox_sales" id="woof_checkbox_sales" name="sales" value="0" <?php checked('salesonly', $this->is_isset_in_request_data('insales') ? 'salesonly' : '', true) ?> />&nbsp;&nbsp;<label for="woof_checkbox_sales"><?php _e('In sales only', 'woocommerce-products-filter') ?></label><br />
                            </div>
                        </div>
                    <?php endif; ?>


                    <?php
                    if (get_option('woof_show_title_search', 0))
                    {
                        echo do_shortcode('[woof_title_filter]');
                    }
                    ?>


                    <?php
                    if (get_option('woof_show_sku_search', 0))
                    {
                        echo do_shortcode('[woof_sku_filter]');
                    }
                    ?>





                    <?php
                    global $wp_query;
//+++
                    if (!empty($taxonomies))
                    {
                        $exclude_tax_key = '';
                        //code-bone for pages like
                        //http://dev.pluginus.net/product-category/clothing/ with GET params
                        //another way when GET is actual no possibility get current taxonomy
                        if ($this->is_really_current_term_exists())
                        {
                            $o = $this->get_really_current_term();
                            $exclude_tax_key = $o->taxonomy;
                        }
                        //***
                        if (!empty($wp_query->query))
                        {
                            if (isset($wp_query->query_vars['taxonomy']) AND in_array($wp_query->query_vars['taxonomy'], get_object_taxonomies('product')))
                            {
                                $taxes = $wp_query->query;
                                if (isset($taxes['paged']))
                                {
                                    unset($taxes['paged']);
                                }

                                foreach ($taxes as $key => $value)
                                {
                                    if (in_array($key, array_keys($this->get_request_data())))
                                    {
                                        unset($taxes[$key]);
                                    }
                                }
                                //***
                                if (!empty($taxes))
                                {
                                    $t = array_keys($taxes);
                                    $v = array_values($taxes);
                                    //***
                                    $exclude_tax_key = $t[0];
                                    $_REQUEST['WOOF_IS_TAX_PAGE'] = $exclude_tax_key;
                                }
                            }
                        } else
                        {
                            //***
                        }

                        //***
                        $taxonomies_tmp = $taxonomies;
                        $taxonomies = array();
                        //sort them as in options
                        foreach ($this->settings['tax'] as $key => $value)
                        {
                            $taxonomies[$key] = $taxonomies_tmp[$key];
                        }
                        //check for absent
                        foreach ($taxonomies_tmp as $key => $value)
                        {
                            if (!in_array(@$taxonomies[$key], $taxonomies_tmp))
                            {
                                $taxonomies[$key] = $taxonomies_tmp[$key];
                            }
                        }
                        //+++
                        $counter = 0;
                        foreach ($taxonomies as $tax_slug => $terms)
                        {
                            if ($exclude_tax_key == $tax_slug)
                            {
                                $terms = apply_filters('woof_exclude_tax_key', $terms);
                                if (empty($terms))
                                {
                                    continue;
                                }
                            }


                            $args['taxonomy_info'] = $taxonomies_info[$tax_slug];
                            $args['tax_slug'] = $tax_slug;
                            $args['terms'] = $terms;
                            $args['all_terms_hierarchy'] = $taxonomies[$tax_slug];
                            $args['additional_taxes'] = $additional_taxes;

                            //***
                            $woof_container_styles = "";
                            if ($woof_settings['tax_type'][$tax_slug] == 'radio' OR $woof_settings['tax_type'][$tax_slug] == 'checkbox')
                            {
                                if ($this->settings['tax_block_height'][$tax_slug] > 0)
                                {
                                    $woof_container_styles = "max-height:{$this->settings['tax_block_height'][$tax_slug]}px; overflow-y: auto;";
                                }
                            }
                            //***
                            //https://wordpress.org/support/topic/adding-classes-woof_container-div
                            $primax_class = sanitize_key(WOOF_HELPER::wpml_translate($taxonomies_info[$tax_slug]));
                            ?>
                            <div data-css-class="woof_container_<?php echo $tax_slug ?>" class="woof_container woof_container_<?php echo $woof_settings['tax_type'][$tax_slug] ?> woof_container_<?php echo $tax_slug ?> woof_container_<?php echo $counter++ ?> woof_container_<?php echo $primax_class ?>">
                                <div class="woof_container_overlay_item"></div>
                                <div class="woof_container_inner woof_container_inner_<?php echo $primax_class ?>">
                                    <?php
                                    switch ($woof_settings['tax_type'][$tax_slug])
                                    {
                                        case 'checkbox':
                                            if ($this->settings['show_title_label'][$tax_slug])
                                            {
                                                ?>
                                                <h4><?php echo WOOF_HELPER::wpml_translate($taxonomies_info[$tax_slug]) ?></h4>
                                                <?php
                                            }
                                            ?>
                                            <div <?php if (!empty($woof_container_styles)): ?>style="<?php echo $woof_container_styles ?>" class="woof_section_scrolled"<?php endif; ?>>
                                                <?php
                                                echo $this->render_html(WOOF_PATH . 'views/html_types/checkbox.php', $args);
                                                ?>
                                            </div>
                                            <?php
                                            break;
                                        case 'select':
                                            if ($this->settings['show_title_label'][$tax_slug])
                                            {
                                                ?>
                                                <h4><?php echo WOOF_HELPER::wpml_translate($taxonomies_info[$tax_slug]) ?></h4>
                                                <?php
                                            }
                                            echo $this->render_html(WOOF_PATH . 'views/html_types/select.php', $args);
                                            break;
                                        case 'mselect':
                                            if ($this->settings['show_title_label'][$tax_slug])
                                            {
                                                ?>
                                                <h4><?php echo WOOF_HELPER::wpml_translate($taxonomies_info[$tax_slug]) ?></h4>
                                                <?php
                                            }
                                            echo $this->render_html(WOOF_PATH . 'views/html_types/mselect.php', $args);
                                            break;
                                        case 'color':
                                            //http://code.tutsplus.com/articles/how-to-use-wordpress-color-picker-api--wp-33067
                                            if ($this->settings['show_title_label'][$tax_slug])
                                            {
                                                ?>
                                                <h4><?php echo WOOF_HELPER::wpml_translate($taxonomies_info[$tax_slug]) ?></h4>
                                                <?php
                                            }
                                            $args['colors'] = isset($woof_settings['color'][$tax_slug]) ? $woof_settings['color'][$tax_slug] : array();
                                            echo $this->render_html(WOOF_PATH . 'views/html_types/color.php', $args);
                                            break;
                                        default:
                                            if ($this->settings['show_title_label'][$tax_slug])
                                            {
                                                ?>
                                                <h4><?php echo WOOF_HELPER::wpml_translate($taxonomies_info[$tax_slug]) ?></h4>
                                                <?php
                                            }
                                            ?>
                                            <div <?php if (!empty($woof_container_styles)):
                                                ?>style="<?php echo $woof_container_styles ?>" class="woof_section_scrolled"<?php endif; ?>>
                                                <?php
                                                echo $this->render_html(WOOF_PATH . 'views/html_types/radio.php', $args);
                                                ?>
                                            </div>
                                            <?php
                                            break;
                                    }
                                    ?>

                                </div>
                            </div>
                            <?php
                        }
                    }

//***
                    $woof_autosubmit = (int) get_option('woof_autosubmit');
                    ?>

                    <div class="woof_submit_search_form_container">

                        <?php if ($this->is_isset_in_request_data($this->get_swoof_search_slug())): global $woof_link; ?>

                            <?php
                            $woof_reset_btn_txt = __('Reset', 'woocommerce-products-filter');
                            ?>

                            <?php if ($woof_reset_btn_txt != 'none'): ?>
                                <button style="float: right;" class="button woof_reset_search_form" data-link="<?php echo $woof_link ?>"><?php echo $woof_reset_btn_txt ?></button>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (!$woof_autosubmit): ?>
                            <?php
                            $woof_filter_btn_txt = __('Filter', 'woocommerce-products-filter');
                            ?>
                            <button style="float: left;" class="button woof_submit_search_form"><?php echo $woof_filter_btn_txt ?></button>
                        <?php endif; ?>

                    </div>

                </div>

            </div>



            <?php if ($autohide): ?>
            </div>
        </div>
    </div>
<?php endif; ?>
