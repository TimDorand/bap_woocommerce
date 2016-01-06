<?php if (!defined('ABSPATH')) die('No direct access allowed'); ?>
<?php
$collector = array();
$_REQUEST['additional_taxes'] = $additional_taxes;
$woof_hide_dynamic_empty_pos = false;
if (!function_exists('woof_draw_select_prepare_data'))
{

    function woof_draw_select_prepare_data($all_terms_hierarchy, $tax_slug)
    {
        global $WOOF;
        $current_request = array();
        $request = $WOOF->get_request_data();
        if ($WOOF->is_isset_in_request_data($tax_slug))
        {
            $current_request = $request[$tax_slug];
            $current_request = explode(',', urldecode($current_request));
        }
        
        if(!empty($current_request)){
            $term_slug=$current_request[0];
            $parents=array();
            //lets get all parents and childs of current $term_slug
            
            
            
        }
    }

}
if (!function_exists('woof_draw_select_childs2'))
{

    function woof_draw_select_childs2(&$collector, $taxonomy_info, $tax_slug, $current_term, $level, $show_count, $show_count_dynamic, $hide_dynamic_empty_pos)
    {
        global $WOOF;
        $request = $WOOF->get_request_data();
        $woof_hide_dynamic_empty_pos = false;
        //***
        $current_request = array();
        $selected_option_data = array();
        if ($WOOF->is_isset_in_request_data($tax_slug))
        {
            $current_request = $request[$tax_slug];
            $current_request = explode(',', urldecode($current_request));
        }
        ?>
        <select class="woof_select woof_select_<?php echo $tax_slug ?> woof_select_<?php echo $tax_slug ?>_<?php echo $current_term['term_id'] ?>" name="<?php echo $tax_slug ?>">
            <option value="0">select me</option>
            <?php foreach ($current_term['childs'] as $term) : ?>           
                <?php
                $count_string = "";
                $count = 0;
                $is_selected = false;
                if (!in_array($term['slug'], $current_request))
                {
                    if ($show_count)
                    {
                        if ($show_count_dynamic)
                        {
                            $count = $WOOF->dynamic_count($term, 'select', $_REQUEST['additional_taxes']);
                        } else
                        {
                            $count = $term['count'];
                        }
                        $count_string = '(' . $count . ')';
                    }
                    //+++
                    if ($hide_dynamic_empty_pos AND $count == 0)
                    {
                        continue;
                    }
                } else
                {
                    $is_selected = true;
                }
                ?>
                <option <?php if ($show_count AND $count == 0 AND ! in_array($term['slug'], $current_request)): ?>disabled=""<?php endif; ?> value="<?php echo $term['slug'] ?>" <?php echo selected(in_array($term['slug'], $current_request)) ?>><?php
                    if (has_filter('woof_before_term_name'))
                        echo apply_filters('woof_before_term_name', $term, $taxonomy_info);
                    else
                        echo $term['name'];
                    ?> <?php echo $count_string ?></option>
                <?php
                if (!isset($collector[$tax_slug]))
                {
                    $collector[$tax_slug] = array();
                }

                $collector[$tax_slug][] = array('name' => $term['name'], 'slug' => $term['slug']);

                //+++

                if ($is_selected)
                {
                    $selected_option_data['tax_slug'] = $tax_slug;
                    $selected_option_data['collector'] = $collector;
                    $selected_option_data['taxonomy_info'] = $taxonomy_info;
                    $selected_option_data['term'] = $term;
                    $selected_option_data['show_count'] = $show_count;
                    $selected_option_data['show_count_dynamic'] = $show_count_dynamic;
                    $selected_option_data['hide_dynamic_empty_pos'] = $hide_dynamic_empty_pos;
                }
                ?>
            <?php endforeach; ?>
        </select>
        <?php
        //***
        if (!empty($selected_option_data))
        {
            //woof_draw_select_childs2($selected_option_data['collector'], $selected_option_data['taxonomy_info'], $selected_option_data['tax_slug'], $selected_option_data['term'], 1, $selected_option_data['show_count'], $selected_option_data['show_count_dynamic'], $selected_option_data['hide_dynamic_empty_pos']);
        }
    }

}

woof_draw_select_prepare_data($all_terms_hierarchy, $tax_slug);
?>


<?php
/*
if (!empty($selected_option_data))
{
    woof_draw_select_childs2($selected_option_data['collector'], $selected_option_data['taxonomy_info'], $selected_option_data['tax_slug'], $selected_option_data['term'], 1, $selected_option_data['show_count'], $selected_option_data['show_count_dynamic'], $selected_option_data['hide_dynamic_empty_pos']);
}
 * 
 */
?>



<?php
//this is for woof_products_top_panel
if (!empty($collector))
{
    foreach ($collector as $ts => $values)
    {
        if (!empty($values))
        {
            foreach ($values as $value)
            {
                ?>
                <input type="hidden" value="<?php echo $value['name'] ?>" class="woof_n_<?php echo $ts ?>_<?php echo $value['slug'] ?>" />
                <?php
            }
        }
    }
}

//we need it only here, and keep it in $_REQUEST for using in function for child items
unset($_REQUEST['additional_taxes']);


