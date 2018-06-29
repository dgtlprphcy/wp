<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/share-page-form.php
 *
 * @see 	    https://docs.woothemes.com/document/template-structure/
 * @package  AutomateWoo Referrals/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;


?>

<form class="aw-email-referral-form" action="" accept-charset="UTF-8" method="post">

	<input type="hidden" name="action" value="aw-referrals-email-share">

	<?php for( $i = 0; $i < 5; $i++ ): ?>
		<p class="form-row form-row-wide">
			<input autocomplete="off" placeholder="<?php esc_attr_e( 'Enter email address', 'automatewoo-referrals' ) ?>" type="email" name="emails[]" class="woocommerce-Input input-text">
		</p>
	<?php endfor; ?>

	<div class="email-button"><button class="woocommerce-Button button btn btn-success" type="submit"><?php esc_attr_e( 'Send', 'automatewoo-referrals' ) ?></button></div>

</form>
