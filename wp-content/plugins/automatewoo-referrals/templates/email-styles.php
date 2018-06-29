<?php
/**
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/email-styles.php
 *
 * @see 	    https://docs.woothemes.com/document/template-structure/
 * @package  AutomateWoo Referrals/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;


$bg = get_option( 'woocommerce_email_background_color' );
$bg = get_option( 'woocommerce_email_background_color' );

?>

.aw-referrals-share-widget {
	background: <?php echo esc_attr( $bg ) ?>;
	margin: 25px 0;
	padding: 15px 40px 30px;
}

.aw-referrals-share-widget-text h2,
.aw-referrals-share-widget-text p {
	text-align: center;
}

.aw-referrals-widget__buttons p {
	margin-bottom: 0 !important;
}

.aw-referrals-share-widget__btn-email,
.aw-referrals-share-widget__btn-facebook,
.aw-referrals-share-widget__btn-twitter {
	display: block;
	border: none;
	background: #43454b;
	color: #fff;
	padding: 15px 20px;
	line-height: 1.1;
	text-decoration: none;
	font-weight: bold;
	border-radius: 0;
	text-align: center;
	max-width: 280px;
	margin: 0 auto 6px;
}


.aw-referrals-share-widget__btn-facebook {
	background: #3B5998;
}

.aw-referrals-share-widget__btn-twitter {
	background: #55acee;
}

