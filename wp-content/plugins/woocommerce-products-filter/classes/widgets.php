<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

class WOOF_Widget extends WP_Widget
{

//Widget Setup
    public function __construct()
    {
        parent::__construct(__CLASS__, __('WOOF - WooCommerce Products Filter', 'woocommerce-products-filter'), array(
            'classname' => __CLASS__,
            'description' => __('WooCommerce Products Filter by realmag777', 'woocommerce-products-filter')
                )
        );
    }

//Widget view
    public function widget($args, $instance)
    {
        $args['instance'] = $instance;
        $args['sidebar_id'] = $args['id'];
        $args['sidebar_name'] = $args['name'];
        //+++
        $price_filter = (int) get_option('woof_show_price_search', 0);

        if (isset($args['before_widget']))
        {
            echo $args['before_widget'];
        }
        ?>
        <div class="widget widget-woof">
            <?php
            if (!empty($instance['title']))
            {
                if (isset($args['before_title']))
                {
                    echo $args['before_title'];
                    echo $instance['title'];
                    echo $args['after_title'];
                } else
                {
                    ?>
                    <h3 class="widget-title"><?php echo $instance['title'] ?></h3>
                    <?php
                }
            }
            ?>


            <?php if (get_option('woof_hide_red_top_panel', 0) == 0): ?>
                <div class="woof_products_top_panel"></div>
            <?php endif; ?>


            <?php echo do_shortcode('[woof sid="widget"  price_filter=' . $price_filter . ']'); ?>
        </div>
        <?php
        if (isset($args['after_widget']))
        {
            echo $args['after_widget'];
        }
    }

//Update widget
    public function update($new_instance, $old_instance)
    {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }

//Widget form
    public function form($instance)
    {
//Defaults
        $defaults = array(
            'title' => __('WooCommerce Products Filter', 'woocommerce-products-filter')
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $args = array();
        $args['instance'] = $instance;
        $args['widget'] = $this;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'woocommerce-products-filter') ?>:</label>
            <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
        </p>
        <?php
    }

}
