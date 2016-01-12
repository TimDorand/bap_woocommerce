<?php
if (!defined('ABSPATH'))
    die('No direct access allowed');

global $WOOF;
if (isset($WOOF->settings['by_text']) AND $WOOF->settings['by_text']['show'])
{
    if (isset($WOOF->settings['by_text']['title']) AND ! empty($WOOF->settings['by_text']['title']))
    {
        ?>
        <!-- <h4><?php echo $WOOF->settings['by_text']['title']; ?></h4> -->
        <?php
    }
    echo do_shortcode('[woof_text_filter]');
}


