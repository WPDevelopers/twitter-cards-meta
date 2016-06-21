<?php
/*
 * Plugin Name: Twitter Cards Meta
 * Plugin URI: https://wpdeveloper.net/go/TCM
 * Description: The Only Complete Twitter Cards Plugin in WordPress. Supports Summary Card with Large Image. Advance Automated settings.
 * Version: 2.4.3
 * Author: WPDeveloper.net
 * Author URI: https://wpdeveloper.net/
 * License: GPLv2+
 * Text Domain: twitter-cards-meta
 * Min WP Version: 2.5.0
 * Max WP Version: 4.5.3
 */


define("TWCM_PLUGIN_SLUG",'twitter-cards-meta');
define("TWCM_PLUGIN_URL",plugins_url("",__FILE__ ));#without trailing slash (/)
define("TWCM_PLUGIN_PATH",plugin_dir_path(__FILE__)); #with trailing slash (/)

define( 'ACTIVE_LARGE_PHOTO', apply_filters( 'active_large_photo', false ) );
define( 'ACTIVE_WOO_PRODUCT', apply_filters( 'active_woo_product', false ) );
define( 'ACTIVE_PRODUCT_CARD', apply_filters( 'active_product_card', false ) );
define( 'ACTIVE_GALLERY_CARD', apply_filters( 'active_gallery_card', false ) );
define( 'ACTIVE_APP_CARD', apply_filters( 'active_app_card', false ) );
define( 'ACTIVE_PLAYER_CARD', apply_filters( 'active_player_card', false ) );

include_once(TWCM_PLUGIN_PATH.'twcm-options.php');
include_once(TWCM_PLUGIN_PATH.'wpdev-dashboard-widget.php');

function add_twcm_menu_pages()
{
	  add_menu_page( "Twitter Cards Meta", "Twitter Cards" ,'manage_options', TWCM_PLUGIN_SLUG, 'twcm_options_page');
}

add_action('admin_menu', 'add_twcm_menu_pages'); 


function twitter_cards_meta()
{
global $post;
$twcm_options=twcm_get_options();
	#twitter cards
	if(is_single() || is_page()) {
		  $site_twitter_username=$twcm_options['site_twitter_username'];
		  if($twcm_options['use_authors_twitter_account'])
		  {
		  	//get $creator_twitter_username
			 $creator_twitter_username=get_the_author_meta('twcm_twitter', $post->post_author);
			 if($creator_twitter_username==""){$creator_twitter_username=$twcm_options['site_twitter_username'];}		
		  
		  }
		  else
		  {
			$creator_twitter_username=$twcm_options['site_twitter_username'];
		  }
		  
		  if($twcm_options['use_default_card_type_sitewide']){
		    $twitter_card_type=$twcm_options['default_card_type'];
		  }
		  else{
		    $twitter_card_type=get_post_meta($post->ID,'_twcm_twitter_card_type',true);
		    if($twitter_card_type==""){$twitter_card_type=$twcm_options['default_card_type'];}
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
		  $cards_meta_data = apply_filters( 'tcm_cards_meta_data',$cards_meta_data);
		  twcm_render_meta_data($cards_meta_data);
		  	 	  	
			  

      
    }
	elseif(is_home()) # elseif of if(is_single() || is_page())
	{
	
	

			  $cards_meta_data=array(
		  		"twitter:card" 		=> $twcm_options['default_card_type'],
				"twitter:site" 		=> "@".$twcm_options['site_twitter_username'],
				"twitter:creator"	=> "@".$twcm_options['site_twitter_username'],
				"twitter:url"		=> get_bloginfo('url'),
				"twitter:title"		=> get_bloginfo('name'),
				"twitter:description" => twcm_sub_string(esc_attr($twcm_options['home_page_description'])), 
				"twitter:image"		=> $twcm_options['default_image']
		  
		  );
		 $cards_meta_data = apply_filters( 'tcm_cards_meta_data',$cards_meta_data);
		 twcm_render_meta_data($cards_meta_data);

	}#end of if(is_single() || is_page())

}#end function twitter_cards_meta()


add_action('wp_head','twitter_cards_meta');

function twcm_render_meta_data($cards_meta_data){
	echo "\r\n<!-- Twitter Cards Meta By WPDeveloper.net -->\r\n";
	foreach($cards_meta_data as $name=>$content){
	echo '<meta name="'.esc_attr($name).'" content="'.esc_attr($content).'" />'; echo "\r\n";
	}
	echo "<!-- Twitter Cards Meta By WPDeveloper.net -->\r\n\r\n";

}

function twcm_get_description()
{

	global $post;
	$twcm_options=twcm_get_options();
	
	$desc=trim(get_the_excerpt());
	if($desc=="")
	{
	//$desc=$post->post_content;
	$desc=strip_shortcodes( $post->post_content ); #avoid shortcode content
	//$desc=apply_filters('the_content',$post->post_content);#using this method to keep shortcode gentrated texts.
	//$desc=get_the_content(); 
	}
	// a failback by Asif for excerpt if null returned by other method.
	if ( $desc == null ) {
		$desc = get_the_content();
		$desc = str_replace(']]>',']]&gt;', $desc);
        $desc=strip_shortcodes( $desc );
     }

	$desc=strip_tags( $desc );
	//$desc=wp_filter_nohtml_kses( $desc ); #smililar with strip_tags() function
	$desc=esc_attr($desc);
	$desc = trim(preg_replace("/\s+/", " ", $desc)); #to maintain a space between words in description. Since version 1.1.2
	$desc=twcm_sub_string($desc, 200);
	return $desc;
}


function twcm_sub_string($text, $charlength=200) {
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

function twcm_get_image()
{
	global $post;
	$twcm_options=twcm_get_options();
	$image='';
	if($twcm_options['use_image_from']=='custom_field' && $twcm_options['image_custom_field']!='')
	{
	$image = get_post_meta($post->ID, $twcm_options['image_custom_field'] , true);
	}
	elseif($twcm_options['use_image_from']=='featured_image')
	{
		$images = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' );
		$image  = $images[0];
	}
	
	if($image=="")
	{
	  #get first image form post content
	  $image = twcm_get_first_image($post->post_content);	  
	}
	if($image==""){$image=$twcm_options['default_image'];}
	return $image;
}


function twcm_get_first_image($text){
      $first_img = '';
      ob_start();
      ob_end_clean();
      $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $text, $matches);
      $first_img = isset($matches [1] [0]) ? $matches [1] [0] : '';	
     return $first_img;
    }

function twcm_setting_links($links, $file) {
    static $twcm_setting;
    if (!$twcm_setting) {
        $twcm_setting = plugin_basename(__FILE__);
    }
    if ($file == $twcm_setting) {
        $twcm_settings_link = '<a href="options-general.php?page='.TWCM_PLUGIN_SLUG.'">Settings</a>';
        array_unshift($links, $twcm_settings_link);
    }
    return $links;
}
add_filter('plugin_action_links', 'twcm_setting_links', 10, 2);	

//================== Add Extra TWITTER Field with user profile =========================

add_action( 'show_user_profile', 'twcm_extra_user_profile_fields' );
add_action( 'edit_user_profile', 'twcm_extra_user_profile_fields' );
add_action( 'personal_options_update', 'twcm_save_extra_user_profile_fields' );
add_action( 'edit_user_profile_update', 'twcm_save_extra_user_profile_fields' );

function twcm_save_extra_user_profile_fields( $user_id ) {
 
if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }
 
update_user_meta( $user_id, 'twcm_twitter', $_POST['twcm_twitter'] );
}

function twcm_extra_user_profile_fields( $user ) { ?>
<h3>For Twitter Cards Meta</h3>
 
<table class="form-table">
<tr>
<th><label for="twcm_twitter">Twitter  User Name</label></th>
<td>
<input type="text" id="twcm_twitter" name="twcm_twitter" size="20" value="<?php echo esc_attr( get_the_author_meta( 'twcm_twitter', $user->ID )); ?>">
<span class="description">Please enter your Twitter Account User name, eg: oneTarek</span>
</td>
</table>
<?php }

#=============================Add Card Type Selection Option on Post Edit page ======================

function twcm_add_meta_boxes()
{

	$post_types=get_post_types('','names'); 
	$rempost = array('attachment','revision','nav_menu_item');#exclude these post_types
	$post_types = array_diff($post_types,$rempost);
	foreach($post_types as $post_type)
		{
		add_meta_box('twcm_twitter_card_type', 'Select a Twitter Card Type', 'twcm_card_type_metabox', $post_type, 'side', 'high');
		}

}


function twcm_card_type_metabox()
{
global $post;

	$twcm_options=twcm_get_options();
	$twitter_card_type=get_post_meta($post->ID,'_twcm_twitter_card_type', true);
	if($twitter_card_type==""){$twitter_card_type='default';}
	?>
	<div style="padding:5px 10px;" id="tcm_card_options">
	  <p>
	<input type="radio" name="twitter_card_type" id="twitter_card_type_default" value="default" <?php echo ($twitter_card_type=="default")?' checked="checked"':''; ?>/> <label for="twitter_card_type_default">Default<span style="color:#CCCCCC"> (<?php echo $twcm_options['default_card_type'];?>)</span></label><br /></p>
	<p><input type="radio" name="twitter_card_type" id="twitter_card_type_summary" value="summary" <?php echo ($twitter_card_type=="summary")?' checked="checked"':''; ?>/> <label for="twitter_card_type_summary">Summary Card</label><br /></p>
<!--	<p><input type="radio" name="twitter_card_type" id="twitter_card_type_photo" value="photo" <?php echo ($twitter_card_type=="photo")?' checked="checked"':''; ?>/> <label for="twitter_card_type_photo">Photo Card</label><br /></p> -->
	
	<?php do_action( 'tcm_addon_cmb' ); ?>
	
	<?php if( ! ACTIVE_LARGE_PHOTO ) { ?>
	<p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-SCLI"><b>Photo + Summary Card (Addon)</b></a></label><br /></p>
	<?php } ?>

<!--	<?php if( ! ACTIVE_PRODUCT_CARD ) { ?>
	<p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-PC"><b>Product Card (Addon)</b></a></label><br /></p>
	<?php } ?>

	<?php if( ! ACTIVE_WOO_PRODUCT ) { ?>
	<p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-PCfWC">WooCommerce Product Card (Addon)</a></label><br /></p>
	<?php } ?>

	<?php if( ! ACTIVE_GALLERY_CARD ) { ?>
	<p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-GC">Gallery Card (Addon)</a></label><br /></p>
	<?php } ?> -->

	<?php if( ! ACTIVE_APP_CARD ) { ?>
	<p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-Survey">App Card (Addon - Coming Soon)</a></label><br /></p>
	<?php } ?>

	<?php if( ! ACTIVE_PLAYER_CARD ) { ?>
	<p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-Survey">Player Card (Addon - Coming Soon)</a></label><br /></p>
	<?php } ?>


	<p><a target="blank" href="https://wpdeveloper.net/go/TCM-Setup"><b> Let us help setting up your Twitter Card</b></a></p>
	
	

	<div id="tcm_addon_extra_field">
	  <table width="100%">
		    <?php do_action( 'tcm_addon_extra_field' ); ?>
	  </table>
	  
	</div>
	<input type="hidden" name="twcm_nonce" value="<?php echo wp_create_nonce('twcm_nonce')?>" />
	</div>
	<?php
}#end function twcm_page_metabox()
	
function twcm_save_post_page_metabox($post_id)
{
	if(!isset($_POST['twitter_card_type'])){return $post_id;}
	#check nonce
	if (!isset($_POST['twcm_nonce']) || !wp_verify_nonce($_POST['twcm_nonce'], 'twcm_nonce')) {return $post_id;}
	#check capabilities
	if (!current_user_can('edit_post', $post_id)) {return $post_id;}
	#exit on autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {return $post_id;}
	#saving data
	if($_POST['twitter_card_type']=='default')
	{
	delete_post_meta($post_id,'_twcm_twitter_card_type');
	}
	else
	{
	update_post_meta($post_id,'_twcm_twitter_card_type', $_POST['twitter_card_type']);
	}
}#end function twcm_save_post_page_metabox()

add_action('add_meta_boxes', 'twcm_add_meta_boxes');
add_action('save_post', 'twcm_save_post_page_metabox');

/* Display a notice that can be dismissed */

add_action('admin_notices', 'twcm_admin_notice');

function twcm_admin_notice() {
if ( current_user_can( 'install_plugins' ) )
   {
	global $current_user ;
        $user_id = $current_user->ID;
        /* Check that the user hasn't already clicked to ignore the message */
	if ( ! get_user_meta($user_id, 'twcm_ignore_notice241') ) {
        echo '<div class="updated"><p>';
        printf(__('If you enjoyed <strong><a href="https://wpdeveloper.net/go/TCM" target="_blank">Twitter Cards Meta</a></strong>, share your Love by <a href="https://wpdeveloper.net/TCM-Tweet-Main" target="_blank">Tweeting to us</a> or <a href="https://wpdeveloper.net/go/twmc-rating" target="_blank">reviewing us</a> on WordPress.org!
        	 <a href="%1$s">[Hide]</a>'),  admin_url( 'admin.php?page=twitter-cards-meta&twcm_nag_ignore=0' ));
        echo "</p></div>";
	}
    }
}

add_action('admin_init', 'twcm_nag_ignore');

function twcm_nag_ignore() {
	global $current_user;
        $user_id = $current_user->ID;
        /* If user clicks to ignore the notice, add that to their user meta */
        if ( isset($_GET['twcm_nag_ignore']) && '0' == $_GET['twcm_nag_ignore'] ) {
             add_user_meta($user_id, 'twcm_ignore_notice241', 'true', true);
	}
}

add_action( 'admin_head', 'tcm_post_editor_script' );
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
	  do_action("tcm_post_editor_script");
}