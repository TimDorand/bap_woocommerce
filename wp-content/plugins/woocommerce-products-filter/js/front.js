jQuery(function () {
    jQuery('body').append('<div id="woof_html_buffer" class="woof_info_popup" style="display: none;"></div>');
    jQuery.fn.life = function (types, data, fn) {
        jQuery(this.context).on(types, this.selector, data, fn);
        return this;
    };
    //+++

    if (jQuery('#woof_results_by_ajax').length > 0) {
        woof_is_ajax = 1;
    }

    //fix for native woo price range
    jQuery('.widget_price_filter form').submit(function () {
        var min_price = jQuery(this).find('.price_slider_amount #min_price').val();
        var max_price = jQuery(this).find('.price_slider_amount #max_price').val();
        woof_current_values.min_price = min_price;
        woof_current_values.max_price = max_price;
        woof_ajax_page_num = 1;
        woof_submit_link(woof_get_submit_link());
        return false;
    });

    jQuery('body').bind('price_slider_change', function (event, min, max) {
        if (woof_autosubmit && !woof_show_price_search_button) {
            jQuery('.widget_price_filter form').trigger('submit');
        } else {
            var min_price = jQuery(this).find('.price_slider_amount #min_price').val();
            var max_price = jQuery(this).find('.price_slider_amount #max_price').val();
            woof_current_values.min_price = min_price;
            woof_current_values.max_price = max_price;
        }
    });

    jQuery('.woof_price_filter_dropdown').life('change', function () {
        var val = jQuery(this).val();
        if (parseInt(val, 10) == -1) {
            delete woof_current_values.min_price;
            delete woof_current_values.max_price;
        } else {
            var val = val.split("-");
            woof_current_values.min_price = val[0];
            woof_current_values.max_price = val[1];
        }

        woof_submit_link(woof_get_submit_link());
    });

    //***

    woof_init_show_auto_form();
    woof_init_hide_auto_form();

    //***
    woof_remove_empty_elements();

    woof_init_search_form();
    woof_init_pagination();
    woof_init_orderby();
    woof_init_reset_button();
    woof_init_beauty_scroll();
    //+++
    woof_draw_products_top_panel();
    woof_shortcode_observer();

});

function woof_init_orderby() {
    jQuery('form.woocommerce-ordering').life('submit', function () {
        return false;
    });
    jQuery('form.woocommerce-ordering select.orderby').life('change', function () {
        woof_current_values.orderby = jQuery(this).val();
        woof_ajax_page_num = 1;
        woof_submit_link(woof_get_submit_link());
        return false;
    });
}

function woof_init_reset_button() {
    jQuery('.woof_reset_search_form').life('click', function () {
        //var link = jQuery(this).data('link');
        woof_current_values = {};
        woof_ajax_page_num = 1;
        woof_submit_link(woof_get_submit_link().split("page/")[0]);
        //woof_submit_link(woof_get_submit_link());
        return false;
    });
}

function woof_init_pagination() {
    if (woof_is_ajax === 1) {
        jQuery('.woocommerce-pagination ul.page-numbers a.page-numbers').life('click', function () {
            var l = jQuery(this).attr('href');

            if (woof_ajax_first_done) {
                //http://woocommerce-filter.pluginus.net/wp-admin/admin-ajax.php?paged=2
                var res = l.split("paged=");
                if (res[1] !== undefined) {
                    woof_ajax_page_num = parseInt(res[1]);
                } else {
                    woof_ajax_page_num = 1;
                }
            } else {
                //http://woocommerce-filter.pluginus.net/tester/page/2/
                var res = l.split("page/");
                if (res[1] !== undefined) {
                    woof_ajax_page_num = parseInt(res[1]);
                } else {
                    woof_ajax_page_num = 1;
                }
            }

            //+++

            //if (woof_autosubmit) - pagination doesn need pressing any submit button!!
            {
                woof_submit_link(woof_get_submit_link());
            }

            return false;
        });
    }
}

function woof_init_search_form() {
    woof_init_checkboxes();
    woof_init_colors();
    woof_init_mselects();
    woof_init_radios();
    woof_init_selects();
    try {
        woof_init_title();
    } catch (e) {

    }
    try {
        woof_init_sku();
    } catch (e) {

    }
    //+++
    var containers = jQuery('.woof_container');
    //+++
    try {
        jQuery.each(containers, function (index, value) {

            var remove = false;

            if (jQuery(value).find('ul.woof_list_radio').size() === 1) {
                remove = true;
            }

            if (jQuery(value).find('ul.woof_list_checkbox').size() === 1) {
                remove = true;
            }

            if (remove) {
                if (jQuery(value).find('ul.woof_list li').size() === 0) {
                    jQuery(value).remove();
                }
            }

        });
    } catch (e) {

    }
    //+++
    jQuery('.woof_submit_search_form').click(function () {
        woof_submit_link(woof_get_submit_link());
        return false;
    });



    //***
    jQuery('ul.woof_childs_list').parent('li').addClass('woof_childs_list_li');
    //***

    if (icheck_skin != 'none') {
        jQuery('.woof_checkbox_instock').on('ifChecked', function (event) {
            jQuery(this).attr("checked", true);
            woof_current_values.stock = 'instock';
            woof_ajax_page_num = 1;
            if (woof_autosubmit) {
                woof_submit_link(woof_get_submit_link());
            }
        });

        jQuery('.woof_checkbox_instock').on('ifUnchecked', function (event) {
            jQuery(this).attr("checked", false);
            delete woof_current_values.stock;
            woof_ajax_page_num = 1;
            if (woof_autosubmit) {
                woof_submit_link(woof_get_submit_link());
            }
        });

        //+++


        jQuery('.woof_checkbox_sales').on('ifChecked', function (event) {
            jQuery(this).attr("checked", true);
            woof_current_values.insales = 'salesonly';
            woof_ajax_page_num = 1;
            if (woof_autosubmit) {
                woof_submit_link(woof_get_submit_link());
            }
        });

        jQuery('.woof_checkbox_sales').on('ifUnchecked', function (event) {
            jQuery(this).attr("checked", false);
            delete woof_current_values.insales;
            woof_ajax_page_num = 1;
            if (woof_autosubmit) {
                woof_submit_link(woof_get_submit_link());
            }
        });

    } else {
        jQuery('.woof_checkbox_instock').on('change', function (event) {
            if (jQuery(this).is(':checked')) {
                jQuery(this).attr("checked", true);
                woof_current_values.stock = 'instock';
                woof_ajax_page_num = 1;
                if (woof_autosubmit) {
                    woof_submit_link(woof_get_submit_link());
                }
            } else {
                jQuery(this).attr("checked", false);
                delete woof_current_values.stock;
                woof_ajax_page_num = 1;
                if (woof_autosubmit) {
                    woof_submit_link(woof_get_submit_link());
                }
            }
        });
        //+++
        jQuery('.woof_checkbox_sales').on('change', function (event) {
            if (jQuery(this).is(':checked')) {
                jQuery(this).attr("checked", true);
                woof_current_values.insales = 'salesonly';
                woof_ajax_page_num = 1;
                if (woof_autosubmit) {
                    woof_submit_link(woof_get_submit_link());
                }
            } else {
                jQuery(this).attr("checked", false);
                delete woof_current_values.insales;
                woof_ajax_page_num = 1;
                if (woof_autosubmit) {
                    woof_submit_link(woof_get_submit_link());
                }
            }
        });
    }

    //***

    woof_remove_class_widget();
    woof_checkboxes_slide();
}


function woof_submit_link(link) {
    woof_show_info_popup(woof_lang_loading);
    if (woof_is_ajax === 1) {
        woof_ajax_first_done = true;
        var data = {
            action: "woof_draw_products",
            link: link,
            page: woof_ajax_page_num,
            shortcode: jQuery('#woof_results_by_ajax').data('shortcode'),
            woof_shortcode: jQuery('div.woof').attr('shortcode')
        };
        jQuery.post(woof_ajaxurl, data, function (content) {
            content = jQuery.parseJSON(content);
            if (jQuery('.woof_results_by_ajax_shortcode').length) {
                jQuery('#woof_results_by_ajax').replaceWith(content.products);
            } else {
                jQuery('.woof_shortcode_output').replaceWith(content.products);
            }

            jQuery('div.woof_redraw_zone').replaceWith(jQuery(content.form).find('.woof_redraw_zone'));
            woof_remove_empty_elements();
            woof_init_search_form();
            woof_hide_info_popup();
            woof_draw_products_top_panel();
            woof_init_beauty_scroll();
            //removing id woof_results_by_ajax - multi in ajax mode sometimes
            //when uses shorcode woof_products in ajax and in settings try ajaxify shop is Yes
            jQuery.each(jQuery('#woof_results_by_ajax'), function (index, item) {
                if (index == 0) {
                    return;
                }

                jQuery(item).removeAttr('id');
            });

            //*** script after ajax loading here
            woof_js_after_ajax_done();
        });
    } else {
        window.location = link;
    }
}

function woof_remove_empty_elements() {
    // lets check for empty drop-downs
    jQuery.each(jQuery('.woof_container select'), function (index, select) {
        var size = jQuery(select).find('option').size();
        if (size === 0) {
            jQuery(select).parents('.woof_container').remove();
        }
    });
    //+++
    // lets check for empty checkboxes, radio, color conatiners
    jQuery.each(jQuery('ul.woof_list_checkbox, ul.woof_list_color, ul.woof_list_radio'), function (index, ch) {
        var size = jQuery(ch).find('li').size();
        if (size === 0) {
            jQuery(ch).parents('.woof_container').remove();
        }
    });
}

function woof_get_submit_link() {
    //filter woof_current_values values
    if (woof_is_ajax) {
        woof_current_values.page = woof_ajax_page_num;
    }
    //+++
    if (Object.keys(woof_current_values).length > 0) {
        jQuery.each(woof_current_values, function (index, value) {
            if (index == swoof_search_slug) {
                delete woof_current_values[index];
            }
            if (index == 's') {
                delete woof_current_values[index];
            }
        });
    }
    //***
    if (Object.keys(woof_current_values).length === 2) {
        if (('min_price' in woof_current_values) && ('max_price' in woof_current_values)) {
            var l = woof_current_page_link + '?min_price=' + woof_current_values.min_price + '&max_price=' + woof_current_values.max_price;
            history.pushState({}, "", l);
            return l;
        }
    }


    if (Object.keys(woof_current_values).length === 1) {
        if ('stock' in woof_current_values) {
            //return woof_current_page_link;
        }
    }

    if (Object.keys(woof_current_values).length === 0) {
        history.pushState({}, "", woof_current_page_link);
        return woof_current_page_link;
    }
    //+++
    var link = woof_current_page_link + "?" + swoof_search_slug + "=1";
    if (Object.keys(woof_current_values).length > 0) {
        jQuery.each(woof_current_values, function (index, value) {
            if (index == 'page' && woof_is_ajax) {
                index = 'paged';//for right pagination if copy/paste this link and send somebody another by email for example
            }
            link = link + "&" + index + "=" + value;
        });
    }

    //+++
    //remove wp pagination like 'page/2'
    link = link.replace(new RegExp(/page\/(\d+)\//), "");
    history.pushState({}, "", link);
    return link;
}

function woof_show_info_popup(text) {
    if (woof_overlay_skin == 'default') {
        jQuery("#woof_html_buffer").text(text);
        jQuery("#woof_html_buffer").fadeTo(200, 0.9);
    } else {
        //http://jxnblk.com/loading/
        switch (woof_overlay_skin) {
            case 'loading-balls':
            case 'loading-bars':
            case 'loading-bubbles':
            case 'loading-cubes':
            case 'loading-cylon':
            case 'loading-spin':
            case 'loading-spinning-bubbles':
            case 'loading-spokes':
                jQuery('body').plainOverlay('show', {progress: function () {
                        return jQuery('<div id="woof_svg_load_container"><img style="height: 100%;width: 100%" src="' + woof_link + 'img/loading-master/' + woof_overlay_skin + '.svg" alt=""></div>');
                    }});
                break;
            default:
                jQuery('body').plainOverlay('show', {duration: -1});
                break;
        }
    }
}


function woof_hide_info_popup() {
    if (woof_overlay_skin == 'default') {
        window.setTimeout(function () {
            jQuery("#woof_html_buffer").fadeOut(400);
        }, 200);
    } else {
        jQuery('body').plainOverlay('hide');
    }
}

function woof_draw_products_top_panel() {
    if (jQuery('.woof_products_top_panel').length) {

        if (woof_is_ajax) {
            var panel = jQuery('.woof_products_top_panel').eq(0);
        } else {
            var panel = jQuery('.woof_products_top_panel');
        }

        panel.html('');
        if (Object.keys(woof_current_values).length > 0) {
            panel.show();
            panel.html('<ul></ul>');
            var is_price_in = false;
            jQuery.each(woof_current_values, function (index, value) {
                if (index == swoof_search_slug) {
                    return;
                }

                //fix for layout constructor plugin
                if (index == 'cs_preview') {
                    return;
                }
                
                
                //fix for woocs
                if (index == 'currency') {
                    return;
                }


                if ((index == 'min_price' || index == 'max_price') && is_price_in) {
                    return;
                }

                if ((index == 'min_price' || index == 'max_price') && !is_price_in) {
                    is_price_in = true;
                    index = 'price';
                    value = woof_lang_pricerange;
                }
                //+++
                value = value.toString().trim();
                if (value.search(',')) {
                    value = value.split(',');
                }
                //+++
                jQuery.each(value, function (i, v) {
                    if (index == 'page') {
                        return;
                    }

                    if (index == 'post_type') {
                        return;
                    }

                    var txt = v;
                    if (index == 'orderby') {
                        txt = woof_lang_orderby + ': ' + v;
                    } else if (index == 'woof_title') {
                        txt = woof_lang_title + ': ' + v;
                    } else if (index == 'insales') {
                        txt = woof_lang_insales;
                    } else if (index == 'stock') {
                        txt = woof_lang_instock;
                    } else if (index == 'perpage') {
                        txt = woof_lang_perpage;
                    } else if (index == 'woof_sku') {
                        txt = woof_lang_sku;
                    } else {
                        txt = jQuery('.woof_n_' + index + '_' + v).val();
                        if (txt === undefined) {
                            txt = v;
                        }
                    }

                    panel.find('ul').append(
                            jQuery('<li>').append(
                            jQuery('<a>').attr('href', v).attr('data-tax', index).append(
                            jQuery('<span>').attr('class', 'woof_remove_ppi').append(txt)
                            )));

                });


            });
        }


        if (jQuery(panel).find('li').size() == 0) {
            panel.hide();
        }

        //+++
        panel.find('ul li a').click(function () {
            var tax = jQuery(this).data('tax');
            var name = jQuery(this).attr('href');

            //***
            if (tax != 'price') {
                values = woof_current_values[tax];
                values = values.split(',');
                var tmp = [];
                jQuery.each(values, function (index, value) {
                    if (value != name) {
                        tmp.push(value);
                    }
                });
                values = tmp;
                if (values.length) {
                    woof_current_values[tax] = values.join(',');
                } else {
                    delete woof_current_values[tax];
                }
            } else {
                delete woof_current_values['min_price'];
                delete woof_current_values['max_price'];
            }

            woof_ajax_page_num = 1;
            //if (woof_autosubmit)
            {
                woof_submit_link(woof_get_submit_link());
            }
            jQuery('.woof_products_top_panel').find("[data-tax='" + tax + "'][href='" + name + "']").hide(333);
            return false;
        });
    }
}

//control conditions if proucts shortcode uses on the page
function woof_shortcode_observer() {
    if (jQuery('.woof_shortcode_output').length) {
        woof_current_page_link = location.protocol + '//' + location.host + location.pathname;
    }

    if (jQuery('#woof_results_by_ajax').length) {
        woof_is_ajax = 1;
    }
}



function woof_init_beauty_scroll() {
    if (woof_use_beauty_scroll) {
        try {
            jQuery(".woof_section_scrolled").mCustomScrollbar('destroy');
            jQuery(".woof_section_scrolled").mCustomScrollbar({
                scrollButtons: {
                    enable: true
                },
                advanced: {
                    updateOnContentResize: true,
                    updateOnBrowserResize: true
                },
                theme: "dark-2",
                horizontalScroll: false,
                mouseWheel: true,
                scrollType: 'pixels',
                contentTouchScroll: true
            });
        } catch (e) {
            console.log(e);
        }
    }
}

//just for inbuilt price range widget
function woof_remove_class_widget() {
    jQuery('.woof_container_inner').find('.widget').removeClass('widget');
}

function woof_init_show_auto_form() {
    jQuery('.woof_show_auto_form').unbind('click');
    jQuery('.woof_show_auto_form').click(function () {
        var _this = this;
        jQuery(_this).addClass('woof_hide_auto_form').removeClass('woof_show_auto_form');
        jQuery(".woof_auto_show").show().animate(
                {
                    height: (jQuery(".woof_auto_show_indent").height() + 20) + "px",
                    opacity: 1
                }, 377, function () {
            //jQuery(_this).text(woof_lang_hide_products_filter);
            woof_init_hide_auto_form();
            jQuery('.woof_auto_show').removeClass('woof_overflow_hidden');
            jQuery('.woof_auto_show_indent').removeClass('woof_overflow_hidden');
            jQuery(".woof_auto_show").height('auto');
        });


        return false;
    });


}

function woof_init_hide_auto_form() {
    jQuery('.woof_hide_auto_form').unbind('click');
    jQuery('.woof_hide_auto_form').click(function () {
        var _this = this;
        jQuery(_this).addClass('woof_show_auto_form').removeClass('woof_hide_auto_form');
        jQuery(".woof_auto_show").show().animate(
                {
                    height: "1px",
                    opacity: 0
                }, 377, function () {
            //jQuery(_this).text(woof_lang_show_products_filter);
            jQuery('.woof_auto_show').addClass('woof_overflow_hidden');
            jQuery('.woof_auto_show_indent').addClass('woof_overflow_hidden');
            woof_init_show_auto_form();
        });

        return false;
    });


}

//if we have mode - child checkboxes closed - append openers buttons by js
function woof_checkboxes_slide() {
    if (woof_checkboxes_slide_flag == true) {
        var childs = jQuery('ul.woof_childs_list');
        if (childs.size()) {
            jQuery.each(childs, function (index, ul) {
                var span_class = 'woof_is_closed';
                if (jQuery(ul).find('input[type=checkbox],input[type=radio]').is(':checked')) {
                    jQuery(ul).show();
                    span_class = 'woof_is_opened';
                }

                jQuery(ul).before('<a href="javascript:void(0);" class="woof_childs_list_opener"><span class="' + span_class + '"></span></a>');
            });

            jQuery.each(jQuery('a.woof_childs_list_opener'), function (index, a) {
                jQuery(a).click(function () {
                    var span = jQuery(this).find('span');
                    if (span.hasClass('woof_is_closed')) {
                        //lets open
                        jQuery(this).parent().find('ul.woof_childs_list').first().show(333);
                        span.removeClass('woof_is_closed');
                        span.addClass('woof_is_opened');
                    } else {
                        //lets close
                        jQuery(this).parent().find('ul.woof_childs_list').first().hide(333);
                        span.removeClass('woof_is_opened');
                        span.addClass('woof_is_closed');
                    }

                    return false;
                });
            });
        }
    }
}

