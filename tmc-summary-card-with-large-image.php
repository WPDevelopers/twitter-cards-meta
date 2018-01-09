<?php
/*
 * Plugin Name: Twitter Cards Meta - Summary Card with Large Image
 * Plugin URI: http://wpdeveloper.net/addons/twitter-cards-meta/summary-card-with-large-image/
 * Description: Summary Card with Large Image Addons for Twitter Cards Meta plugin.
 * Version: 1.0.1
 * Author: WPDeveloper.net
 * Author URI: http://wpdeveloper.net
 * License: GPLv2+
 * Text Domain: twitter-cards-meta
 * Min WP Version: 2.5.0
 * Max WP Version: 4.2
 */

if( ! defined( 'ABSPATH' ) ) wp_die( 'This is not your place!' );


add_filter( 'active_large_photo', 'active_large_photo_cb' );
function active_large_photo_cb() {
    return true;
}

add_action( 'tcm_addon_cmb', 'tcm_addon_cmb_cb' );
function tcm_addon_cmb_cb() {
    global $post;
    $twcm_options=twcm_get_options();
    $twitter_card_type=get_post_meta($post->ID,'_twcm_twitter_card_type', true);
    ?>
    <input type="radio" name="twitter_card_type" id="twitter_card_type_large_photo" value="summary_large_image" <?php echo ($twitter_card_type=="summary_large_image")?' checked="checked"':''; ?>/> <label for="twitter_card_type_large_photo">Summary Card with Large Image</label><br />
    <?php
}