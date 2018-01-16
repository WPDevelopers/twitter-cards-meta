<?php
/**
 *
 */
if( ! class_exists( 'TWCM_Twitter_Cards_Preview' ) ) {

	/**
	 * Twitter Cards Meta Perview
	 *
	 * @since  2.5.5
	 */
	class TWCM_Twitter_Cards_Preview {

		public function __construct() {

			add_action( 'add_meta_boxes', array( $this, 'twcm_core_add_metabox' ) );
			add_action( 'save_post', array( $this, 'twcm_save_post_page_metabox' ) );

		}

		/**
		 * This method will create metaboxes
		 *
		 * @since  2.5.5
		 */
		public function twcm_core_add_metabox() {
			$screens = ['post', 'page'];
				add_meta_box(
					'twcm_core_page_settings',
					'Twitter Cards Meta',
					array( $this, 'twcm_core_metabox_html' ),
					$screens,
					'normal',
					'high'
				);
		}

		/**
		 * This method will save twcm card type otpions
		 *
		 * @since  2.5.5
		 */
		public function twcm_save_post_page_metabox( $post_id ) {

			if( !isset( $_POST['twitter_card_type'] ) ) { return $post_id; }

			//check nonce
			if ( !isset( $_POST['twcm_nonce'] ) || !wp_verify_nonce( $_POST['twcm_nonce'], 'twcm_nonce' ) ) { return $post_id; }

			//check capabilities
			if ( !current_user_can( 'edit_post', $post_id ) ) { return $post_id; }

			//exit on autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) { return $post_id; }

			//saving data
			if( $_POST['twitter_card_type']=='default' ) {
				delete_post_meta(
					$post_id,
					'_twcm_twitter_card_type'
				);
			}else {
				update_post_meta(
					$post_id,
					'_twcm_twitter_card_type',
					$_POST['twitter_card_type']
				);
			}

		}

		/**
		 * This method will generate twcm card types markup. It is called into 'twcm_core_metabox_html' method.
		 *
		 * @since  2.5.5
		 */
		public function twcm_card_type_metaboxes() {
			global $post;
			$twcm_options=twcm_get_options();
			$twitter_card_type=get_post_meta($post->ID,'_twcm_twitter_card_type', true);
			if( $twitter_card_type == "" ) {
				$twitter_card_type = 'default';
			}
			?>
			<div class="tcm_card_options" id="tcm_card_options">
				<p>
					<input type="radio" name="twitter_card_type" id="twitter_card_type_default" value="default" <?php echo ($twitter_card_type=="default")?' checked="checked"':''; ?>/> <label for="twitter_card_type_default">Default<span style="color:#CCCCCC"> (<?php echo $twcm_options['default_card_type'];?>)</span></label>
				</p>
				<p>
					<input type="radio" name="twitter_card_type" id="twitter_card_type_summary" value="summary" <?php echo ($twitter_card_type=="summary")?' checked="checked"':''; ?>/> <label for="twitter_card_type_summary">Summary Card</label>
				</p>
				<?php if( !function_exists( 'tcm_addon_cmb_cb' ) ) : ?>
				<p class="option-muted">
			    	<input type="radio" name="twitter_card_type_disabled" id="twitter_card_type_large_photo" value="summary_large_image" disabled/> <label for="twitter_card_type_large_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-SCLI">Summary Card with Large Image (Pro)</a></label>
			    </p>
				<?php endif; ?>
				<?php
					/**
					 * Custom TWCM hook to add more options.
					 */
					do_action( 'tcm_addon_cmb' );
				?>

				<!--<p><input type="radio" name="twitter_card_type" id="twitter_card_type_photo" value="photo" <?php echo ($twitter_card_type=="photo")?' checked="checked"':''; ?>/> <label for="twitter_card_type_photo">Photo Card</label><br /></p> -->

				<?php if( ! ACTIVE_LARGE_PHOTO ) { ?>
				<!-- <p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-SCLI"><b>Photo + Summary Card (Addon)</b></a></label><br /></p> -->
				<?php } ?>

				<!--<?php if( ! ACTIVE_PRODUCT_CARD ) { ?>
				<p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-PC"><b>Product Card (Addon)</b></a></label><br /></p>
				<?php } ?>

				<?php if( ! ACTIVE_WOO_PRODUCT ) { ?>
				<p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-PCfWC">WooCommerce Product Card (Addon)</a></label><br /></p>
				<?php } ?>

				<?php if( ! ACTIVE_GALLERY_CARD ) { ?>
				<p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-GC">Gallery Card (Addon)</a></label><br /></p>
				<?php } ?> -->

				<?php if( ! ACTIVE_APP_CARD ) { ?>
				<!-- <p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-Survey">App Card (Addon - Coming Soon)</a></label><br /></p> -->
				<?php } ?>

				<?php if( ! ACTIVE_PLAYER_CARD ) { ?>
				<!-- <p><input type="radio" disabled="disabled"/> <label for="twitter_card_type_photo"><a style="color:#CCCCCC;" target="blank" href="https://wpdeveloper.net/go/TCM-Survey">Player Card (Addon - Coming Soon)</a></label><br /></p> -->
				<?php } ?>

				<div id="tcm_addon_extra_field">
				  	<table width="100%">
					    <?php do_action( 'tcm_addon_extra_field' ); ?>
				  	</table>
				</div>
				<input type="hidden" name="twcm_nonce" value="<?php echo wp_create_nonce('twcm_nonce')?>" />
			</div>
			<?php
		}

		/**
		 * This method will generate metabox markup
		 *
		 * @since  2.5.5
		 */
		public function twcm_core_metabox_html( $post ) {

			// TWCM Meta Options
			$twitter_card_type = get_post_meta( $post->ID,'_twcm_twitter_card_type', true );

			// TWCM Settings Options
			$twcm_options = twcm_get_options();

			// Get the post feature image
			$images = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'full' );
			$image  = $images[0];

			// Striped Content
			$striped_content = strip_tags( $post->post_content );
			$striped_content = trim( addslashes( preg_replace( '/\s+/', ' ', $striped_content ) ) );
			?>
			<div class="twcm-core-metabox-wrapper">
				<?php $this->twcm_card_type_metaboxes(); ?>
				<h2 class="section-title">Twitter Card Preview</h2>
				<div id="twcm-append-preview"></div>
			</div>

			<script>
				jQuery(document).ready(function($){
					var appendTo = $( '#twcm-append-preview' );
					var twitterCardType = '<?php echo $twitter_card_type; ?>';

					// Default State
					if( twitterCardType == '' ) {
						<?php if( $twcm_options['default_card_type'] == 'summary' ) : ?>
							appendTo.html( twcm_summary_html() );
						<?php elseif( $twcm_options['default_card_type'] == 'summary_large_image' ) : ?>
							appendTo.html( twcm_summary_large_image_html() );
						<?php endif; ?>
					}else if( twitterCardType == 'summary' ) {
						appendTo.html( twcm_summary_html() );
					}else if( twitterCardType == 'summary_large_image' ) {
						appendTo.html( twcm_summary_large_image_html() );
					}

					// On Change State
					$( 'input[name=twitter_card_type]' ).on( 'change', function() {
						var cardType = $(this).val();
						if( cardType == 'default' ) {
							<?php if( $twcm_options['default_card_type'] == 'summary' ) : ?>
								appendTo.html( twcm_summary_html() );
							<?php elseif( $twcm_options['default_card_type'] == 'summary_large_image' ) : ?>
								appendTo.html( twcm_summary_large_image_html() );
							<?php endif; ?>
						}else if( cardType == 'summary' ) {
							appendTo.html( twcm_summary_html() );
						}else if( cardType == 'summary_large_image' ) {
							appendTo.html( twcm_summary_large_image_html() );
						}
					});

					// Summary Card Style
					function twcm_summary_html() {

						var html = '<div class="twcm-summary-card">';
								html += '<div class="twcm-summary-card-left" style="background:url('+twcm_get_the_image()+');"></div>';
								html += '<div class="twcm-summary-card-right">';
									html += '<h2 class="summary-card-title">'+twcm_get_the_title()+'</h2>';
									html += '<p class="summary-card-desc">'+twcm_get_the_content()+'</p>';
									html += '<small class="summary-card-link"><a href="'+twcm_get_permalink()+'">'+twcm_get_permalink()+'</a></small>';
								html += '</div>';
						html += '</div>';

						return html;
					}

					// Summary with Large Image Card Style
					function twcm_summary_large_image_html() {

						var html = '<div class="twcm-summary-card card-lg">';
								html += '<div class="twcm-summary-card-left" style="background:url('+twcm_get_the_image()+');"></div>';
								html += '<div class="twcm-summary-card-right">';
									html += '<h2 class="summary-card-title">'+twcm_get_the_title()+'</h2>';
									html += '<p class="summary-card-desc">'+twcm_get_the_content()+'</p>';
									html += '<small class="summary-card-link"><a href="'+twcm_get_permalink()+'">'+twcm_get_permalink()+'</a></small>';
								html += '</div>';
						html += '</div>';

						return html;
					}

					// Get Post Title
					function twcm_get_the_title() {
						var getTheTitle = '<?php echo $post->post_title; ?>';
						$( 'input[name=post_title]' ).on( 'keyup kyepress keydown', function() {
							getTheTitle = $(this).val();
							$( '.summary-card-title' ).html( getTheTitle );
						});
						return getTheTitle;
					}

					// Get Post Content
					function twcm_get_the_content() {
						var getExcerpt = '<?php echo $post->post_excerpt; ?>';
						if( getExcerpt == '' ) {
							var getContent = '<?php echo $striped_content; ?>';
						}else {
							var getContent = getExcerpt;
						}
						// Checking if the content length exceding 180 characters
						if( getContent.length > 180 ) {
							getContent = getContent.substr( 0, 180 ) + '...';
						}else {
							return getContent;
						}

						return getContent;
					}

					// Get Post Image
					function twcm_get_the_image() {
						var getImage = '<?php echo $image; ?>';
						var getFirstImage = '<?php echo twcm_get_first_image( $post->post_content ); ?>';
						<?php if( $twcm_options['default_image'] == '' ) : ?>
							<?php if( $twcm_options['use_image_from'] == 'first_image' ) : ?>
								getImage = getFirstImage;
							<?php elseif( $twcm_options['use_image_from'] == 'featured_image' ) : ?>
								return getImage;
							<?php elseif( $twcm_options['use_image_from'] == 'custom_field' ) : ?>
								getImage = '<?php echo get_post_meta( $post->ID, $twcm_options['image_custom_field'] , true ) ?>';
							<?php endif; ?>
						<?php else: ?>
							getImage = '<?php echo $twcm_options['default_image']; ?>';
						<?php endif; ?>

						return getImage;
					}

					// Get Post Permalink
					function twcm_get_permalink() {
						var getPerma = '<?php echo get_the_permalink(); ?>';
						return getPerma;
					}

				});
			</script>
			<?php
		}

	}

	$twcm_metaboxes = new TWCM_Twitter_Cards_Preview();

}