<?php
function twcm_get_options()
{
	$options=array(
			'site_twitter_username'=>'WPDevTeam',
			'use_authors_twitter_account'=>0, 
			'use_image_from'=>"first_image",
			'image_custom_field'=>'image', 
			'default_image'=>'',
			'home_page_description'=>'',
			'default_card_type'=>'summary',
			'use_default_card_type_sitewide'=>0 
	
	);
	
	$saved_options=get_option('twcm_options',$options);
	if(!$saved_options['default_card_type']){$saved_options['default_card_type']="summary";}
	return $saved_options;
}



function twcm_options_page()
{
	global $wpdb;
	$twcm_options=twcm_get_options();
	
	if(isset($_POST['save_options']))
	{
		$options=array(
				'site_twitter_username'=>trim($_POST['site_twitter_username']),
				'use_authors_twitter_account'=>isset($_POST['use_authors_twitter_account']) ? intval($_POST['use_authors_twitter_account']) : '', 
				'use_image_from'=>$_POST['use_image_from'], 
				'image_custom_field'=>trim($_POST['image_custom_field']),
				'default_image'=>(trim($_POST['default_image'])=='Link to Default Image')? '' : trim($_POST['default_image']),
				'home_page_description'=>(trim($_POST['home_page_description'])=='Enter a description for home page, keep it under 200 characters')? '' : wp_filter_nohtml_kses(trim($_POST['home_page_description'])),  #wp_filter_nohtml_kses is smililar with strip_tags() function
				'default_card_type'=>$_POST['default_card_type'], 
				'use_default_card_type_sitewide'=>isset($_POST['use_default_card_type_sitewide']) ? $_POST['use_default_card_type_sitewide'] : ''
		
		);	
	update_option('twcm_options', apply_filters( 'tcm_options_post', $options ) );
	$twcm_options=$options;
	}#end if(isset($_POST['save_options']))
	//oneTarek
	global $current_user;	
	?>
	 <div style="width: 1010px; padding-left: 10px;" class="wrap">
		 <div style="width: 700px; float:left;">
		 <div id="icon-options-general" class="icon32"></div>
		 <h2>Twitter Cards Meta Options</h2>

		
			<script type="text/ecmascript">
            	function show_custom_field_name_row()
				{
				document.getElementById('image_custom_field').style.display='block';
				document.getElementById('image_custom_field_label').style.display='block';
				}
            	function hide_custom_field_name_row()
				{
				document.getElementById('image_custom_field').style.display='none';
				document.getElementById('image_custom_field_label').style.display='none';
				}				
            
            </script>
            <form action="" method="post">
            <table class="form-table">
            <tr><td  align="left" width="200">Site's Main Twitter Account:</td><td>@<input type="text" name="site_twitter_username" value="<?php echo ($twcm_options['site_twitter_username'])? $twcm_options['site_twitter_username'] :'WPDevTeam';?>" size="20"  onblur="javascript: if(this.value=='') {this.value='WPDevTeam';}" onclick="javascript: if(this.value=='WPDevTeam') {this.value='';}"  /></td></tr>
            
            <tr><td  align="left">Use Authors' Twitter Account</td><td><input type="checkbox" name="use_authors_twitter_account" value="1" <?php echo ($twcm_options['use_authors_twitter_account'])?' checked="checked"': '';?> /></td></tr>
            
            <tr><td  align="left">
            	Image Selection: </td><td>
            	<input type="radio" name="use_image_from" id="use_image_from_1" onclick="hide_custom_field_name_row()"  value="first_image" <?php echo ($twcm_options['use_image_from']=='first_image')?' checked="checked"': '';?> /> <label for="use_image_from_1">First image from content</label><br />
                <input type="radio" name="use_image_from" id="use_image_from_2" onclick="hide_custom_field_name_row()" value="featured_image" <?php echo ($twcm_options['use_image_from']=='featured_image')?' checked="checked"': '';?> /> <label for="use_image_from_2"> Featured image </label><br />
                <input type="radio" name="use_image_from" id="use_image_from_3" onclick="show_custom_field_name_row()" value="custom_field" <?php echo ($twcm_options['use_image_from']=='custom_field')?' checked="checked"': '';?> /> <label for="use_image_from_3"> From a custom field </label>           
            	</td></tr>
            <tr><td  align="left"> <span id="image_custom_field_label" <?php echo ($twcm_options['use_image_from']!='custom_field')?' style="display:none"': '';?>>Image Custom Field Name:</span> </td><td><input type="text" name="image_custom_field" id="image_custom_field" value="<?php echo $twcm_options['image_custom_field'];?>" size="20" <?php echo ($twcm_options['use_image_from']!='custom_field')?' style="display:none"': '';?> /></td></tr>
            <tr><td   align="left"> Default Image URL: </td><td><input type="text" name="default_image" value="<?php echo ($twcm_options['default_image'])? $twcm_options['default_image']:'Link to Default Image';?>" size="30"  style="width:300px;" onblur="javascript: if(this.value=='') {this.value='Link to Default Image';}" onclick="javascript: if(this.value=='Link to Default Image') {this.value='';}" /></td></tr>


            <tr><td valign="top" align="left">
	    
            	Twitter Cards Type Selection: </td><td>
		<div class="tmc_rad_opt">
            	<input type="radio" name="default_card_type" id="default_card_type_1" value="summary" <?php echo ($twcm_options['default_card_type']=='summary')?' checked="checked"': '';?> /> <label for="default_card_type_1">Summary Cards</label><br />

               <!-- <input type="radio" name="default_card_type" id="default_card_type_2" value="photo" <?php echo ($twcm_options['default_card_type']=='photo')?' checked="checked"': '';?> /> <label for="default_card_type_2"> Photo Cards </label><br /> -->
		
                <input type="radio" name="default_card_type" id="default_card_type_4" value="summary_large_image" <?php echo ($twcm_options['default_card_type']=='summary_large_image')?' checked="checked"': '';?> <?php if( ! ACTIVE_LARGE_PHOTO ) { ?>disabled="disabled"<?php } ?> /> <label for="default_card_type_4" <?php if( ! ACTIVE_LARGE_PHOTO ) { ?>style="color:#CCCCCC;"<?php } ?> > <b>Summary Card With Large Image</b> <?php if( ! ACTIVE_LARGE_PHOTO ) { ?>(<a href="https://wpdeveloper.net/go/TCM-SCLI" target="_blank"><b>available as premium addon</b></a>)<?php } ?></label><br />

               <!-- <input type="radio" name="default_card_type" id="default_card_type_6" value="product" <?php echo ($twcm_options['default_card_type']=='product')?' checked="checked"': '';?> <?php if( ! ACTIVE_PRODUCT_CARD ) { ?>disabled="disabled"<?php } ?> /> <label for="default_card_type_6" <?php if( ! ACTIVE_PRODUCT_CARD ) { ?>style="color:#CCCCCC;"<?php } ?> > <b>Product Card</b> <?php if( ! ACTIVE_PRODUCT_CARD ) { ?>(<a href="https://wpdeveloper.net/go/TCM-PC" target="_blank"><b>available as premium addon</b></a>)<?php } ?></label><br /> -->
		
		      <!--  <input type="radio" name="default_card_type" id="default_card_type_5" value="product_woo" <?php echo ($twcm_options['default_card_type']=='product_woo')?' checked="checked"': '';?> <?php if( ! ACTIVE_WOO_PRODUCT ) { ?>disabled="disabled"<?php } ?> /> <label for="default_card_type_5" <?php if( ! ACTIVE_WOO_PRODUCT ) { ?>style="color:#CCCCCC;"<?php } ?> > Product Card for WooCommerce <?php if( ! ACTIVE_WOO_PRODUCT ) { ?>(<a href="https://wpdeveloper.net/go/TCM-PCfWC" target="_blank"><b>available as premium addon</b></a>)<?php } ?></label><br /> -->
		
		       <!-- <input type="radio" name="default_card_type" id="default_card_type_7" value="gallery" <?php echo ($twcm_options['default_card_type']=='gallery')?' checked="checked"': '';?> <?php if( ! ACTIVE_GALLERY_CARD ) { ?>disabled="disabled"<?php } ?> /> <label for="default_card_type_7" <?php if( ! ACTIVE_GALLERY_CARD ) { ?>style="color:#CCCCCC;"<?php } ?> > Gallery Card <?php if( ! ACTIVE_GALLERY_CARD ) { ?>(<a href="https://wpdeveloper.net/go/TCM-GC" target="_blank"><b>available as premium addon</b></a>)<?php } ?></label><br /> -->

		        <input type="radio" name="default_card_type" id="default_card_type_8" value="app" <?php echo ($twcm_options['default_card_type']=='app')?' checked="checked"': '';?> <?php if( ! ACTIVE_APP_CARD ) { ?>disabled="disabled"<?php } ?> /> <label for="default_card_type_8" <?php if( ! ACTIVE_APP_CARD ) { ?>style="color:#CCCCCC;"<?php } ?> > App Card <?php if( ! ACTIVE_APP_CARD ) { ?>(<a href="https://wpdeveloper.net/go/TCM-Survey" target="_blank"><b>comming soon</b></a>)<?php } ?></label><br />
		
		        <input type="radio" name="default_card_type" id="default_card_type_9" value="player" <?php echo ($twcm_options['default_card_type']=='player')?' checked="checked"': '';?> <?php if( ! ACTIVE_PLAYER_CARD ) { ?>disabled="disabled"<?php } ?> /> <label for="default_card_type_9" <?php if( ! ACTIVE_PLAYER_CARD ) { ?>style="color:#CCCCCC;"<?php } ?> > Player Card <?php if( ! ACTIVE_PLAYER_CARD ) { ?>(<a href="https://wpdeveloper.net/go/TCM-Survey" target="_blank"><b>comming soon</b></a>)<?php } ?></label><br /><br /><br />
		

		<div class="tmc_card_extra_option_meta">
			<?php do_action( 'tmc_card_extra_option_meta' ); ?>
		</div>
	    </div>
            	</td>
            	</tr>


            <tr><td   align="left"> Description For Home Page :</td><td> <textarea  name="home_page_description" cols="10" rows="4" style="width:300px;" onblur="javascript: if(this.value=='') {this.value='Enter a description for home page, keep it under 200 characters';}"  onclick="javascript: if(this.value=='Enter a description for home page, keep it under 200 characters') {this.value='';}"  ><?php echo ($twcm_options['home_page_description'])? $twcm_options['home_page_description'] :'Enter a description for home page, keep it under 200 characters';?></textarea></td></tr>
 			
            <tr style="border:1px solid #CC0000; background:#FFEBE8;"><td  align="left">Use Default Card Type Sitewide</td>
            <td>
            <input type="checkbox" name="use_default_card_type_sitewide" value="1" <?php echo ($twcm_options['use_default_card_type_sitewide'])?' checked="checked"': '';?> />
            <span style="color:#FF0000;"> (<strong>*Caution! </strong> If you select this option, all posts will show the default Cards type you selected. Individual Card type selection per post will not work)</span>

            </td></tr> 		
                 
   
	      
            <tr><td>
            <br /><br /><br /><input type="submit" name="save_options" value="Save Options" class='button-primary'/></td><td>&nbsp;</td></tr>
            </table>
            </form>
           <div style=" text-align:center; margin-top:10px;">
			<strong>Do you need help in <a href="https://wpdeveloper.net/go/TCM-Setup" target="_blank">setting</a> up</strong> <strong><a href="https://wpdeveloper.net/go/TCM" target="_blank">Twitter Cards Meta</a>? </strong>Now we could help you <strong>Install, setup</strong>, file for card validation to Twitter, setup addon or help you fix any issue related to Twitter Card. We have a team of <strong>Avenger</strong>! <strong><a href="https://wpdeveloper.net/go/TCM-Setup" target="_blank">Click here!</a></strong>
            </div>

            <div style=" text-align:center; margin-top:60px;"><b>Photo Card + Summary Card = <a href="https://wpdeveloper.net/go/TCM-SCLI" target="_blank"><b>Summary Card with Large Image</b></a></b><b> [Must Have]</b><br /><a target="_blank" href="https://wpdeveloper.net/go/TCM-SCLI"><img style="border:2px solid #ffffff;" src="<?php echo TWCM_PLUGIN_URL."/example-summary-card-with-large-image.jpg" ?>" width="500" alt="Summery Card with Large Image" /></a></div>

<div style=" text-align:center; margin-top:100px;">
<center><a target="_blank" href="https://wpdeveloper.net/"><img src="<?php echo TWCM_PLUGIN_URL."/wpdeveloper-logo-2.png" ?>" alt="Summary Card with Large Image" /></a>
</center> 
 <br />
<b>Created With Love By  <a href="https://wpdeveloper.net/" target="_blank"><b>WPDeveloper.net</b></a></b>
</div>
<?php
		
		echo "</div>";
	
		include_once(TWCM_PLUGIN_PATH."twcm-sidebar.php");
		echo '<div style="clear:both"></div>';
	echo "</div>";

}

?>