<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/share-page.php
 *
 * @see 	    https://docs.woothemes.com/document/template-structure/
 * @package  AutomateWoo Referrals/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * @var $advocate AutomateWoo\Referrals\Advocate
 * @var $enable_facebook_share bool
 * @var $enable_twitter_share bool
 * @var $enable_email_share bool
 */

$button_count = 0;

if ( $enable_facebook_share ) $button_count++;
if ( $enable_twitter_share ) $button_count++;

?>

<div class="aw-referrals-share-container aw-referrals-share-page">

	<?php wc_print_notices(); ?>

	<?php if ( $advocate ): // user must be logged in  ?>

		<?php if ( $button_count > 0 ): ?>

			<div class="aw-referrals-share-buttons button-count-<?php echo $button_count ?>">

				<?php if ( $enable_facebook_share ): ?>
					<a href="<?php echo esc_url( $advocate->get_facebook_share_url() ) ?>" class="btn btn-facebook js-automatewoo-open-share-box"><?php _e( 'Share via Facebook', 'automatewoo-referrals' ) ?></a>
				<?php endif; ?>

				<?php if ( $enable_twitter_share ): ?>
					<a href="<?php echo esc_url( $advocate->get_twitter_share_url() ) ?>" class="btn btn-twitter js-automatewoo-open-share-box"><?php _e( 'Share via Twitter', 'automatewoo-referrals' ) ?></a>
				<?php endif; ?>

			</div>

		<?php endif; ?>

		<?php if ( $enable_email_share ): ?>

			<?php if ( $button_count > 0 ): ?>
				<div class="aw-referrals-share-or"><?php esc_attr_e( 'Or', 'automatewoo-referrals' ) ?></div>
			<?php endif; ?>

			<?php AW_Referrals()->get_template( 'share-page-form.php' ) ?>

		<?php endif; ?>

	<?php else: ?>

		<?php AW_Referrals()->get_template( 'share-page-login.php' ) ?>

	<?php endif; ?>

</div>
