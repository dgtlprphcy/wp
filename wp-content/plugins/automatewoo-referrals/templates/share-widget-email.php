<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/share-widget.php
 *
 * @see 	    https://docs.woothemes.com/document/template-structure/
 * @package  AutomateWoo Referrals/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @var $advocate AutomateWoo\Referrals\Advocate
 * @var $widget_heading string
 * @var $widget_text string
 * @var $enable_facebook_share bool
 * @var $enable_twitter_share bool
 * @var $enable_email_share bool
 */

?>

<div class="aw-referrals-share-widget">

	<div class="aw-referrals-share-widget-text">
		<h2><?php echo esc_attr( $widget_heading ) ?></h2>
		<?php echo wpautop( esc_attr( $widget_text ) ) ?>
	</div>

	<div class="aw-referrals-widget__buttons">

		<?php if ( $enable_email_share ): ?>
			<a href="<?php echo esc_url( AW_Referrals()->get_share_page_url() ) ?>" class="aw-referrals-share-widget__btn-email"><?php _e( 'Share via Email', 'automatewoo-referrals' ) ?></a>
		<?php endif; ?>

		<?php if ( $enable_facebook_share ): ?>
			<a href="<?php echo esc_url( $advocate ? $advocate->get_facebook_share_url() : AW_Referrals()->get_share_page_url() ) // send to share page if user is guest ?>"
				target="_blank" class="aw-referrals-share-widget__btn-facebook"><?php _e( 'Share via Facebook', 'automatewoo-referrals' ) ?></a>
		<?php endif; ?>

		<?php if ( $enable_twitter_share ): ?>
			<a href="<?php echo esc_url( $advocate ? $advocate->get_twitter_share_url() : AW_Referrals()->get_share_page_url() ) // send to share page if user is guest ?>"
				target="_blank" class="aw-referrals-share-widget__btn-twitter"><?php _e( 'Share via Twitter', 'automatewoo-referrals' ) ?></a>
		<?php endif; ?>

	</div>

</div>