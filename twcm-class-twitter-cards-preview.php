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
					'Twitter Cards Preview',
					array( $this, 'twcm_core_metabox_html' ),
					$screens,
					'normal',
					'high'
				);
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
			$striped_content = trim( preg_replace( '/\s+/', ' ', $striped_content ) );
			?>
			<div class="twcm-core-metabox-wrapper">
				<div id="twcm-append-preview"></div>
			</div>

			<script>
				jQuery(document).ready(function($){
					var appendTo = $( '#twcm-append-preview' );
					var twitterCardType = '<?php echo $twitter_card_type; ?>';

					// Default State
					if( twitterCardType == '' ) {
						appendTo.html( twcm_default_html() );
					}else if( twitterCardType == 'summary' ) {
						appendTo.html( twcm_summary_html() );
					}else if( twitterCardType == 'summary_large_image' ) {
						appendTo.html( twcm_summary_large_image_html() );
					}

					// On Change State
					$( 'input[name=twitter_card_type]' ).on( 'change', function() {
						var cardType = $(this).val();
						if( cardType == 'default' ) {
							appendTo.html( twcm_default_html() );
						}else if( cardType == 'summary' ) {
							appendTo.html( twcm_summary_html() );
						}else if( cardType == 'summary_large_image' ) {
							appendTo.html( twcm_summary_large_image_html() );
						}
					});

					// Default Card Style
					function twcm_default_html() {

						var html = '<div class="twcm-summary-card card-lg">';
								html += '<div class="twcm-summary-card-right">';
									html += '<h2 class="summary-card-title">'+twcm_get_the_title()+'</h2>';
									html += '<p class="summary-card-desc">'+twcm_get_the_content()+'</p>';
									html += '<small class="summary-card-link"><a href="'+twcm_get_permalink()+'">'+twcm_get_permalink()+'</a></small>';
								html += '</div>';
						html += '</div>';

						return html;
					}

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