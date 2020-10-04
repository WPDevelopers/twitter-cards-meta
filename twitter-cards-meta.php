<?php
/*
 * Plugin Name: Twitter Cards Meta
 * Plugin URI: https://wpdeveloper.net/go/TCM
 * Description: The Most Advanced Twitter Cards Plugin in WordPress. Supports Summary Card with Large Image. Advance Automated settings with Preview.
 * Version: 2.9.1
 * Author: WPDeveloper
 * Author URI: https://wpdeveloper.net
 * License: GPL-3.0+
 * Text Domain: twitter-cards-meta
 * Min WP Version: 2.5.0
 * Max WP Version: 5.5.1
 */

define("TWCM_PLUGIN_SLUG",'twitter-cards-meta');
define("TWCM_PLUGIN_URL",plugins_url("",__FILE__ ));#without trailing slash (/)
define("TWCM_PLUGIN_PATH",plugin_dir_path(__FILE__)); #with trailing slash (/)
define('TWCM_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('TWCM_PLUGIN_VERSION', '2.9.1');

define( 'ACTIVE_LARGE_PHOTO', apply_filters( 'active_large_photo', false ) );
define( 'ACTIVE_WOO_PRODUCT', apply_filters( 'active_woo_product', false ) );
define( 'ACTIVE_PRODUCT_CARD', apply_filters( 'active_product_card', false ) );
define( 'ACTIVE_GALLERY_CARD', apply_filters( 'active_gallery_card', false ) );
define( 'ACTIVE_APP_CARD', apply_filters( 'active_app_card', false ) );
define( 'ACTIVE_PLAYER_CARD', apply_filters( 'active_player_card', false ) );

include_once(TWCM_PLUGIN_PATH.'twcm-options.php');
include_once(TWCM_PLUGIN_PATH.'wpdev-dashboard-widget.php');
include_once(TWCM_PLUGIN_PATH.'twcm-class-twitter-cards-preview.php');
include_once(TWCM_PLUGIN_PATH.'includes/class-plugin-usage-tracker.php');

/**
 * This fucntion will add a menu page
 */
function add_twcm_menu_pages() {
	add_menu_page( "Twitter Cards Meta", "Twitter Cards" ,'manage_options', TWCM_PLUGIN_SLUG, 'twcm_options_page');
}
add_action('admin_menu', 'add_twcm_menu_pages');

/**
 * This fucntion will add admin style file
 */
function twcm_add_styles_scripts() {
	wp_enqueue_style( 'twcm-admin-style', TWCM_PLUGIN_URL.'/assets/css/twcm-admin.css' );
}
add_action( 'admin_init', 'twcm_add_styles_scripts' );

function twitter_cards_meta() {
	global $post;
	$twcm_options = twcm_get_options();
	//twitter cards
	if(is_single() || is_page()) {
		$site_twitter_username = $twcm_options['site_twitter_username'];

		// if use_authors_twitter_account set
		if( $twcm_options['use_authors_twitter_account'] ) {
		  	//get $creator_twitter_username
			$creator_twitter_username = get_the_author_meta( 'twcm_twitter', $post->post_author );
			if( $creator_twitter_username == "" ) {
				$creator_twitter_username = $twcm_options['site_twitter_username'];
			}
		}else {
			$creator_twitter_username = $twcm_options['site_twitter_username'];
		}

		// if use_default_card_type_sitewide set
		if( $twcm_options['use_default_card_type_sitewide'] ) {
		    $twitter_card_type = $twcm_options['default_card_type'];
		}else{
		    $twitter_card_type = get_post_meta( $post->ID, '_twcm_twitter_card_type', true );
		    if( $twitter_card_type == "" ) {
		    	$twitter_card_type = $twcm_options['default_card_type'];
		    }
		}

		$twitter_url    = get_permalink();
		$twitter_title  = get_the_title();
		$twitter_thumbs = twcm_get_image();

		$cards_meta_data=array(
		  	"twitter:card" 		=> $twitter_card_type,
			"twitter:site" 		=> "@".$site_twitter_username,
			"twitter:creator"	=> "@".$creator_twitter_username,
			"twitter:url"		=> $twitter_url,
			"twitter:title"		=> $twitter_title,
			"twitter:description" => twcm_get_description(),
			"twitter:image"		=> $twitter_thumbs
		);
		$cards_meta_data = apply_filters( 'tcm_cards_meta_data', $cards_meta_data );
		twcm_render_meta_data( $cards_meta_data );

    }elseif( is_home() ) {
		$cards_meta_data=array(
		  	"twitter:card" 		=> $twcm_options['default_card_type'],
			"twitter:site" 		=> "@".$twcm_options['site_twitter_username'],
			"twitter:creator"	=> "@".$twcm_options['site_twitter_username'],
			"twitter:url"		=> get_bloginfo( 'url' ),
			"twitter:title"		=> get_bloginfo( 'name' ),
			"twitter:description" => twcm_sub_string( esc_attr( $twcm_options['home_page_description'] ) ),
			"twitter:image"		=> $twcm_options['default_image']
		);
		$cards_meta_data = apply_filters( 'tcm_cards_meta_data', $cards_meta_data );
		twcm_render_meta_data( $cards_meta_data );
	}

}
add_action('wp_head','twitter_cards_meta');

/**
 * This function will render meta data
 *
 * @since  v1.0.0
 */
function twcm_render_meta_data( $cards_meta_data ) {

	echo "\r\n<!-- Twitter Cards Meta - V 2.5.4 -->\r\n";
	foreach( $cards_meta_data as $name=>$content ) {
		echo '<meta name="'.esc_attr( $name ).'" content="'.esc_attr( $content ).'" />'; echo "\r\n";
	}
	echo "<!-- Twitter Cards Meta By WPDeveloper.net -->\r\n\r\n";

}

/**
 * This function will get description
 *
 * @since  v1.0.0
 */
function twcm_get_description() {

   	global $post;
	$twcm_options = twcm_get_options();
	$post_content = $post->post_content;
	$desc = '';
	if( empty( $desc ) ) {
		// Try Yoast metadesc first
		$desc = get_post_meta( get_the_ID(), '_yoast_wpseo_metadesc', true );
		if( empty( $desc ) ) {
			$desc = trim( get_the_excerpt() );
		}
	}

	// If still empty, grab the content
	if( empty( $desc ) ) {
		$desc = $post_content;
	}

	$desc = strip_tags( $desc );
	//$desc=wp_filter_nohtml_kses( $desc ); #smililar with strip_tags() function
	$desc = esc_attr( $desc );
	$desc = str_replace( ']]>',']]&gt;', $desc );
	$desc = strip_shortcodes( $desc );
	$desc = trim(preg_replace( "/\s+/", " ", $desc ) ); #to maintain a space between words in description. Since version 1.1.2
	$desc = twcm_sub_string( $desc, 160 );
	return $desc;
}

/**
 * This function will generate sub string of along string
 *
 * @since  v1.0.0
 */
function twcm_sub_string( $text, $charlength=160 ) {
	$charlength++;
	$retext="";
	if ( mb_strlen( $text ) > $charlength ) {
		$subex = mb_substr( $text, 0, $charlength - 5 );
		$exwords = explode( ' ', $subex );
		$excut = - ( mb_strlen( $exwords[ count( $exwords ) - 1 ] ) );
		if ( $excut < 0 ) {
			$retext .= mb_substr( $subex, 0, $excut );
		} else {
			$retext .= $subex;
		}
		$retext .= '[...]';
	} else {
		$retext .= $text;
	}

	return $retext;
}

/**
 * This function will get the image
 *
 * @since  v1.0.0
 */
function twcm_get_image() {
	global $post;
	$twcm_options=twcm_get_options();
	$image='';
	if( $twcm_options['use_image_from'] == 'custom_field' && $twcm_options['image_custom_field'] != '' ) {
		$image = get_post_meta( $post->ID, $twcm_options['image_custom_field'] , true);
	}elseif( $twcm_options['use_image_from'] == 'featured_image' ) {
		$images = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
		$image  = $images[0];
	}

	// if image is blank get first image
	if( $image == "" ) {
	  	//get first image form post content
	  	$image = twcm_get_first_image($post->post_content);
	}

	// if still image is blank get default image
	if( $image == "" ) {
		$image = $twcm_options['default_image'];
	}

	return $image;
}

/**
 * This function will fetch the first iamge form the content
 *
 * @since  v1.0.0
 */
function twcm_get_first_image($text){
    $first_img = '';
    ob_start();
    ob_end_clean();
    $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $text, $matches);
    $first_img = isset($matches [1] [0]) ? $matches [1] [0] : '';

    return $first_img;
}

/**
 * This function will fetch the settings link
 *
 * @since  v1.0.0
 */
function twcm_setting_links( $links, $file ) {
    static $twcm_setting;
    if ( !$twcm_setting ) {
        $twcm_setting = plugin_basename( __FILE__ );
    }
    if ( $file == $twcm_setting ) {
        $twcm_settings_link = '<a href="options-general.php?page='.TWCM_PLUGIN_SLUG.'">Settings</a>';
        array_unshift( $links, $twcm_settings_link );
    }

    return $links;
}
add_filter('plugin_action_links', 'twcm_setting_links', 10, 2);

//================== Add Extra TWITTER Field with user profile =========================

add_action( 'show_user_profile', 'twcm_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'twcm_extra_user_profile_fields' );
add_action( 'personal_options_update', 'twcm_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'twcm_save_extra_user_profile_fields' );

/**
 * This function will save extar user profile field value
 *
 * @since  v1.0.0
 */
function twcm_save_extra_user_profile_fields( $user_id ) {
	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
	update_user_meta( $user_id, 'twcm_twitter', $_POST['twcm_twitter'] );
}

/**
 * This function will save extar user profile field value
 *
 * @since  v1.0.0
 */
function twcm_extra_user_profile_fields( $user ) {
	?>
	<h3>For Twitter Cards Meta</h3>
	<table class="form-table">
		<tr>
			<th><label for="twcm_twitter">Twitter  User Name</label></th>
			<td>
				<input type="text" id="twcm_twitter" name="twcm_twitter" size="20" value="<?php echo esc_attr( get_the_author_meta( 'twcm_twitter', $user->ID )); ?>">
				<span class="description">Please enter your Twitter Account User name, eg: oneTarek</span>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * This function will show an admin notice
 *
 * @since  v1.0.0
 */

function twcm_admin_notice() {
	if ( current_user_can( 'install_plugins' ) ) {
		global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */
		if ( ! get_user_meta($user_id, 'twcm_ignore_notice290') ) {
	        echo '<div class="updated"><p>';
	        printf(__('Introducing <strong>Twitter Card Preview! </strong>Go to any post edit screen and look at the end of the post. Share your opinion <a href="https://wpdeveloper.net/in/TCM-Feedback">here</a>. We are actively working!
	        	 <a href="%1$s">[Hide]</a>'),  admin_url( 'admin.php?page=twitter-cards-meta&twcm_nag_ignore=0' ));
	        echo "</p></div>";
		}
    }
}
/** add_action('admin_notices', 'twcm_admin_notice');

/**
 * This function will generate nag ignore
 *
 * @since  v1.0.0
 */
function twcm_nag_ignore() {
	global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['twcm_nag_ignore']) && '0' == $_GET['twcm_nag_ignore'] ) {
             add_user_meta($user_id, 'twcm_ignore_notice290', 'true', true);
	}
}
add_action('admin_init', 'twcm_nag_ignore');


/**
 * This function will generate editor scripts
 *
 * @since  v1.0.0
 */
function tcm_post_editor_script() {
	?>
	<style type="text/css">
		#tcm_addon_extra_field th{text-align: left; width: 100px;}
	  	#tcm_addon_extra_field > table > tbody > tr{display: none}
	  	#tcm_addon_extra_field input[type=text]{width: 90%}
	</style>
	<script type="text/javascript">
	  	jQuery(function($) {
		    $('#tcm_card_options input[name=twitter_card_type]').each(function() {
			    if( $(this).is(':checked') ){
					var id = $(this).attr('id');
					if( ! $('.' + id).is(':visible') ){
						$('#tcm_addon_extra_field > table > tbody > tr').hide();
						$('.' + id).show();
					}
			    }
		    });
		    $('#tcm_card_options input[name=twitter_card_type]').click(function() {
			    var id = $(this).attr('id');
			    if( ! $('.' + id).is(':visible') ){
					$('#tcm_addon_extra_field > table > tbody > tr').hide();
					$('.' + id).show();
			    }
		    });
		});
	</script>
	<?php
	do_action( "tcm_post_editor_script" );
}
add_action( 'admin_head', 'tcm_post_editor_script' );

// Optional usage tracker

if( ! class_exists( 'Twcm_Plugin_Usage_Tracker') ) {
    require_once dirname( __FILE__ ) . '/includes/class-plugin-usage-tracker.php';
}
if( ! function_exists( 'twitter_cards_meta_start_plugin_tracking' ) ) {
    function twitter_cards_meta_start_plugin_tracking() {
        $tracker = Twcm_Plugin_Usage_Tracker::get_instance( __FILE__, [
			'opt_in'       => true,
			'goodbye_form' => true,
			'item_id'      => '499fd55e8666c802e28a'
		] );
		$tracker->set_notice_options(array(
			'notice' => __( 'Want to help make <strong>Twitter Cards Meta</strong> even more awesome? You can get a 25% discount coupon for premium addons if you allow.', 'twitter-cards-meta' ),
			'extra_notice' => __( 'We collect non-sensitive diagnostic data and plugin usage information. Your site URL, WordPress & PHP version, plugins & themes and email address to send you the discount coupon. This data lets us make sure this plugin always stays compatible with the most popular plugins and themes. No spam, I promise.', 'twitter-cards-meta' ),
		));
		$tracker->init();
    }
    twitter_cards_meta_start_plugin_tracking();
}

if( ! class_exists( 'WPDeveloper_TCM_Notice' ) ) {
	require_once dirname( __FILE__ ) . '/includes/class-wpdev-notices.php';

	$notice = new WPDeveloper_TCM_Notice(TWCM_PLUGIN_BASENAME, TWCM_PLUGIN_VERSION);
	/**
	 * Current Notice End Time.
	 * Notice will dismiss in 3 days if user does nothing.
	 */
	$notice->cne_time = '3 Day';
	/**
	 * Current Notice Maybe Later Time.
	 * Notice will show again in 7 days
	 */
	$notice->maybe_later_time = '7 Day';

	$notice->text_domain = 'twitter-cards-meta';

	$notice->options_args = array(
		'notice_will_show' => [
			'opt_in' => $notice->timestamp,
		],
	);

	$notice->init();
}