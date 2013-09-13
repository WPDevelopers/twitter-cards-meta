<?php
/*
 * Plugin Name: Twitter Cards Meta
 * Plugin URI: http://wpdeveloper.net/free-plugin/twitter-cards-meta/
 * Description: The Best Way to Add Twitter Cards Metadata in WordPress Site. Enable Summary and Photo Cards Easily, With Control.
 * Version: 1.1.2
 * Author: WPDeveloper.net
 * Author URI: http://wpdeveloper.net
 * License: GPLv2+
 * Text Domain: twitter-cards-meta
 * Min WP Version: 2.5.0
 * Max WP Version: 3.5.2
 */


define("TWCM_PLUGIN_SLUG",'twitter-cards-meta');
define("TWCM_PLUGIN_URL",plugins_url("",__FILE__ ));#without trailing slash (/)
define("TWCM_PLUGIN_PATH",plugin_dir_path(__FILE__)); #with trailing slash (/)

include_once(TWCM_PLUGIN_PATH.'twcm-options.php');
include_once(TWCM_PLUGIN_PATH.'wpdev-dashboard-widget.php');

function add_twcm_menu_pages()

{
add_options_page( "Twitter Cards Meta", "Twitter Cards Meta" ,'manage_options', TWCM_PLUGIN_SLUG, 'twcm_options_page');
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
		  $twitter_url    = get_permalink();
		  $twitter_title  = get_the_title();
		  $twitter_thumbs = twcm_get_image();
	 	  if($twcm_options['use_default_card_type_sitewide'])
		  {
		  $twitter_card_type=$twcm_options['default_card_type'];
		  }
		  else
		  {
		  $twitter_card_type=get_post_meta($post->ID,'_twcm_twitter_card_type',true);
		  if($twitter_card_type==""){$twitter_card_type=$twcm_options['default_card_type'];}
		  }	  
		?>

<!-- Twitter Cards Meta By WPDeveloper.net -->
<meta name="twitter:card" value="<?php echo $twitter_card_type ?>"/>
<meta name="twitter:site" value="@<?php echo $site_twitter_username;?>" />
<meta name="twitter:creator" value="@<?php echo $creator_twitter_username; ?>" />
<meta name="twitter:url" value="<?php echo $twitter_url; ?>"/>
<meta name="twitter:title" value="<?php echo $twitter_title;?>"/>
<meta name="twitter:description" value="<?php echo twcm_get_description(); ?>"/>
<meta name="twitter:image" value="<?php echo $twitter_thumbs; ?>" />
<!-- Twitter Cards Meta By WPDeveloper.net -->

		<?php
      
    }
	elseif(is_home()) # elseif of if(is_single() || is_page())
	{
	
	?>

<!-- Twitter Cards Meta By WPDeveloper.net -->
<meta name="twitter:card" value="<?php echo $twcm_options['default_card_type'];?>"/>
<meta name="twitter:site" value="@<?php echo $twcm_options['site_twitter_username'];?>" />
<meta name="twitter:creator" value="@<?php echo $twcm_options['site_twitter_username'];?>" />
<meta name="twitter:url" value="<?php echo get_bloginfo('url'); ?>"/>
<meta name="twitter:title" value="<?php bloginfo('name'); ?>"/>
<meta name="twitter:description" value="<?php echo twcm_sub_string(esc_attr($twcm_options['home_page_description'])); ?>"/>
<meta name="twitter:image" value="<?php echo $twcm_options['default_image']; ?>" />
<!-- Twitter Cards Meta By WPDeveloper.net -->

    <?php
	}#end of if(is_single() || is_page())

}#end function twitter_cards_meta()


add_action('wp_head','twitter_cards_meta');



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
	//$desc=strip_tags( $desc );
	$desc=wp_filter_nohtml_kses( $desc ); #smililar with strip_tags() function
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
	  ob_start();
	  ob_end_clean();
	  $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches);
	  $image = $matches [1] [0];	  
	}
	if($image==""){$image=$twcm_options['default_image'];}
	return $image;
}


function twcm_get_first_image($text){
      $first_img = '';
      ob_start();
      ob_end_clean();
      $output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $text, $matches);
      $first_img = $matches [1] [0];
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
		add_meta_box('twcm_twitter_card_type', 'Twitter Card Type', 'twcm_card_type_metabox', $post_type, 'side', 'low');
		}

}


function twcm_card_type_metabox()
{
global $post;

	$twcm_options=twcm_get_options();
	$twitter_card_type=get_post_meta($post->ID,'_twcm_twitter_card_type', true);
	if($twitter_card_type==""){$twitter_card_type='default';}
	?>
	<div style="padding:5px 10px;">
	<input type="radio" name="twitter_card_type" id="twitter_card_type_default" value="default" <?php echo ($twitter_card_type=="default")?' checked="checked"':''; ?>/> <label for="twitter_card_type_default">Default<span style="color:#CCCCCC"> (<?php echo $twcm_options['default_card_type'];?>)</span></label><br />
	<input type="radio" name="twitter_card_type" id="twitter_card_type_summary" value="summary" <?php echo ($twitter_card_type=="summary")?' checked="checked"':''; ?>/> <label for="twitter_card_type_summary">Summary</label><br />
	<input type="radio" name="twitter_card_type" id="twitter_card_type_photo" value="photo" <?php echo ($twitter_card_type=="photo")?' checked="checked"':''; ?>/> <label for="twitter_card_type_photo">Photo</label><br />
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


?>