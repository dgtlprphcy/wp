<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/share-page-login.php
 *
 * @see https://docs.woothemes.com/document/template-structure/
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="aw-referrals-well">

   <?php if ( is_user_logged_in() ): ?>

	    <p><?php esc_attr_e( 'You must be a paying customer to refer a friend.', 'automatewoo-referrals' ) ?></p>
	    <p><a href="<?php echo esc_url( wc_get_page_permalink('shop') ) ?>" class="woocommerce-Button button"><?php esc_attr_e( 'Go to shop', 'automatewoo-referrals' ) ?></a></p>

    <?php else: ?>

	    <h4><?php esc_attr_e( 'Please Login', 'automatewoo-referrals' ) ?></h4>

        <?php if ( AW_Referrals()->options()->advocate_must_paying_customer ): ?>
	        <p><?php esc_attr_e( 'You must be a paying customer to refer a friend.', 'automatewoo-referrals' ) ?></p>
	        <p><a href="<?php echo esc_url( wc_get_page_permalink('myaccount') ) ?>" class="woocommerce-Button button"><?php esc_attr_e( 'Login', 'automatewoo-referrals' ) ?></a></p>
        <?php else: ?>
	        <p><?php esc_attr_e( 'You must have an account to refer a friend.', 'automatewoo-referrals' ) ?></p>
	        <p><a href="<?php echo esc_url( wc_get_page_permalink('myaccount') ) ?>" class="woocommerce-Button button"><?php esc_attr_e( 'Login or register', 'automatewoo-referrals' ) ?></a></p>
        <?php endif ?>

    <?php endif ?>

</div>
