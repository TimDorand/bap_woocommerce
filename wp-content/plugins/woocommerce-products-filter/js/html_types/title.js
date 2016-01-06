jQuery(function () {
    //woof_init_title();
});

var woof_title_do_submit = false;
function woof_init_title() {
    jQuery('.woof_show_title_search').keyup(function (e) {
        var val = jQuery(this).val();
        var uid = jQuery(this).data('uid');

        if (e.keyCode == 13 /*&& val.length > 0*/) {
            woof_title_do_submit = true;
            woof_title_direct_search('woof_title', val);
            return true;
        }

        //save new word into woof_current_values
        if (woof_autosubmit) {
            woof_current_values['woof_title'] = val;
        } else {
            woof_title_direct_search('woof_title', val);
        }


        //if (woof_is_mobile == 1) {
        if (val.length > 0) {
            jQuery('.woof_title_search_go.' + uid).show(222);
        } else {
            jQuery('.woof_title_search_go.' + uid).hide();
        }
        //}
    });
    //+++
    jQuery('.woof_title_search_go').life('click', function () {
        var uid = jQuery(this).data('uid');
        woof_title_do_submit = true;
        woof_title_direct_search('woof_title', jQuery('.woof_show_title_search.' + uid).val());
    });
}

function woof_title_direct_search(name, slug) {

    jQuery.each(woof_current_values, function (index, value) {
        if (index == name) {
            delete woof_current_values[name];
            return;
        }
    });

    if (slug != 0) {
        woof_current_values[name] = slug;
    }

    woof_ajax_page_num = 1;
    if (woof_autosubmit || woof_title_do_submit) {
        woof_title_do_submit = false;
        woof_submit_link(woof_get_submit_link());
    }
}


