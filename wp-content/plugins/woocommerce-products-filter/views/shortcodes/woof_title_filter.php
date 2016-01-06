<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<div data-css-class="woof_title_search_container" class="woof_title_search_container woof_container">
    <div class="woof_container_overlay_item"></div>
    <div class="woof_container_inner">
        <?php
        $woof_title = '';
        $request = $this->get_request_data();

        if (isset($request['woof_title']))
        {
            $woof_title = $request['woof_title'];
        }
        //+++
        if (!isset($placeholder))
        {
            $p = __('enter a product title here ...', 'woocommerce-products-filter');
        }

        if (isset($this->settings['search_by_title_placeholder_txt']) AND ! isset($placeholder))
        {
            if (!empty($this->settings['search_by_title_placeholder_txt']))
            {
                $p = $this->settings['search_by_title_placeholder_txt'];
                $p = WOOF_HELPER::wpml_translate(null, $p);
                $p = __($p, 'woocommerce-products-filter');
            }


            if ($this->settings['search_by_title_placeholder_txt'] == 'none')
            {
                $p = '';
            }
        }
        //***
        $unique_id = uniqid('woof_title_search_');
        ?>

        <table class="woof_title_table">
            <tr>
                <td style="width: 100%;">
                    <input type="search" class="woof_show_title_search <?php echo $unique_id ?>" data-uid="<?php echo $unique_id ?>" placeholder="<?php echo(isset($placeholder) ? $placeholder : $p) ?>" name="woof_title" value="<?php echo $woof_title ?>" />
                </td>
                <td>
                    <a href="javascript:void(0);" data-uid="<?php echo $unique_id ?>" class="woof_title_search_go <?php echo $unique_id ?>"></a>
                </td>
            </tr>
        </table>


    </div>
</div>