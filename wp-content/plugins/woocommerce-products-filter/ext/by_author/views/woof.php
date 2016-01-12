<?php

if (!defined('ABSPATH'))
    die('No direct access allowed');

global $WOOF;
if ($WOOF->settings['by_author']['show'])
{
    echo do_shortcode('[woof_author_filter]');
}


