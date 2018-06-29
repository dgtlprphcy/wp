<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/share-widget.php
 *
 * @see https://docs.woothemes.com/document/template-structure/
 */

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @var $advocate AutomateWoo\Referrals\Advocate
 * @var $widget_heading string
 * @var $widget_text string
 * @var $enable_facebook_share bool
 * @var $enable_twitter_share bool
 * @var $enable_email_share bool
 * @var $position string
 */

$button_count = 0;

if ( $enable_email_share ) $button_count++;
if ( $enable_facebook_share ) $button_count++;
if ( $enable_twitter_share ) $button_count++;

?>

<div class="aw-referrals-share-widget aw-referrals-well aw-referrals-share-container aw-referrals-share-widget--position-<?php echo $position ?>">

	<div class="aw-referrals-share-widget-text">
		<h3><?php echo esc_attr( $widget_heading ) ?></h3>
		<?php echo wpautop( esc_attr( $widget_text ) ) ?>
	</div>


	<div class="aw-referrals-share-buttons button-count-<?php echo $button_count ?>">

		<?php if ( $enable_email_share ): ?>
			<a href="<?php echo esc_url( AW_Referrals()->get_share_page_url() ) ?>" class="btn btn-email"><?php _e( 'Share via Email', 'automatewoo-referrals' ) ?></a>
		<?php endif; ?>

		<?php if ( $advocate ): // user is logged in ?>

			<?php if ( $enable_facebook_share ): ?>
				<a href="<?php echo esc_url( $advocate->get_facebook_share_url() ) ?>" class="btn btn-facebook js-automatewoo-open-share-box"><?php _e( 'Share via Facebook', 'automatewoo-referrals' ) ?></a>
			<?php endif; ?>

			<?php if ( $enable_twitter_share ): ?>
				<a href="<?php echo esc_url( $advocate->get_twitter_share_url() ) ?>" class="btn btn-twitter js-automatewoo-open-share-box"><?php _e( 'Share via Twitter', 'automatewoo-referrals' ) ?></a>
			<?php endif; ?>

		<?php else: // send to share page if no user account  ?>

			<?php if ( $enable_facebook_share ): ?>
				<a href="<?php echo esc_url( AW_Referrals()->get_share_page_url() ) ?>" class="btn btn-facebook"><?php _e( 'Share via Facebook', 'automatewoo-referrals' ) ?></a>
			<?php endif; ?>

			<?php if ( $enable_twitter_share ): ?>
				<a href="<?php echo esc_url( AW_Referrals()->get_share_page_url() ) ?>" class="btn btn-twitter"><?php _e( 'Share via Twitter', 'automatewoo-referrals' ) ?></a>
			<?php endif; ?>

		<?php endif; ?>

	</div>


</div>