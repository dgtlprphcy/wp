<?php
/**
 * Request a Review email template
 *
 * @package 	Woocommerce Partial Orders
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
$base 		= get_option( 'woocommerce_email_base_color' );
$base_text 	= wc_light_or_dark( $base, '#202020', '#ffffff' );
?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( "Hello ", 'woocommerce' ), get_option( 'blogname' ) ); ?></p>

<p><?php print( __( "Your meals are on their way to you.", 'woocommerce' ) ); ?></p>

<p><?php print( __( "Please <a href='".get_site_url()."/my-account'>login</a> to your <a href='".get_site_url()."/my-account'>Lean Lunch account</a> to view full details.", 'woocommerce' ) ); ?></p>

<p><?php print( __( "Just pop it in the fridge when it arrives if you’re not ready for it straightaway. And eat it within 24 hours for maximum freshness and flavour.", 'woocommerce' ) ); ?></p>

<p><?php print( __( "Have a lovely lunch!", 'woocommerce' ) ); ?></p>


<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_footer' ); ?>