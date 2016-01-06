jQuery(function () {
    try {
        jQuery("#tabs").tabs();
        jQuery("#woof_options").sortable();
        jQuery('.woof-color-picker').wpColorPicker();
    } catch (e) {

    }
    //+++
    jQuery('.js_cache_count_data_clear').click(function () {
        jQuery(this).next('span').html('clearing ...');
        var _this = this;
        var data = {
            action: "woof_cache_count_data_clear"
        };
        jQuery.post(ajaxurl, data, function () {
            jQuery(_this).next('span').html('cleared!');
        });

        return false;
    });
    
    //free
    jQuery('#woof_filter_btn_txt').prop('disabled', true);
    jQuery('#woof_filter_btn_txt').val('premium version');
    jQuery('#woof_reset_btn_txt').prop('disabled', true);
    jQuery('#woof_reset_btn_txt').val('premium version');
    jQuery('#woof_hide_dynamic_empty_pos').prop('disabled', true);
    jQuery('#woof_show_sku_search').prop('disabled', true);
    jQuery('#woof_sku_conditions').prop('disabled', true);

});
