<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Options
 *
 *
 * @property string $version
 *
 * @property bool $enabled
 * @property string $type
 * @property string $referrals_page
 * @property bool $advocate_must_paying_customer
 * @property bool $auto_approve
 * @property bool $use_credit_on_subscription_renewals
 *
 * @property string $widget_heading
 * @property string $widget_text
 * @property string|false $widget_on_order_confirmed
 * @property bool $widget_on_order_emails
 *
 * @property int $share_link_expiry
 * @property string $share_link_parameter
 *
 * @property bool $enable_facebook_share
 * @property bool $enable_twitter_share
 * @property string $social_share_text
 * @property string $social_share_text_twitter
 * @property string $social_share_url
 *
 * @property bool $enable_email_share
 * @property string $share_email_subject
 * @property string $share_email_heading
 * @property string $share_email_template
 * @property string $share_email_body
 *
 * @property string $offer_type
 * @property int $offer_amount
 * @property int $offer_min_purchase
 * @property int $offer_coupon_expiry
 *
 * @property string $reward_type
 * @property int $reward_amount
 * @property int $reward_min_purchase
 *
 * @property bool $allow_existing_customer_referrals
 * @property bool $reward_event
 */
class Options extends AutomateWoo\Options_API {

	/** @var string */
	public $prefix = 'aw_referrals_';


	function __construct() {
		$this->defaults = [
			'enabled' => 'no',
			'type' => 'coupon',
			'auto_approve' => 'yes',
			'advocate_must_paying_customer' => 'no',
			'use_credit_on_subscription_renewals' => 'no',

			'widget_heading' => __( 'Give $20, Get $20', 'automatewoo-referrals' ),
			'widget_text' => __( 'Invite a friend via Facebook, Twitter or email and they’ll get a $20 welcome credit, plus you’ll get $20 in your own account with their first order, as our little thank you.', 'automatewoo-referrals' ),
			'widget_on_order_confirmed' => 'bottom',
			'widget_on_order_emails' => 'no',

			'offer_type' => 'coupon_discount',
			'offer_amount' => 20,
			'offer_min_purchase' => 0,
			'offer_coupon_expiry' => 4,

			'share_link_expiry' => 4,
			'share_link_parameter' => 'awref',

			'reward_type' => 'credit',
			'reward_amount' => 20,
			'reward_min_purchase' => 0,

			'enable_facebook_share' => 'yes',
			'enable_twitter_share' => 'yes',
			'social_share_text' => sprintf( __( 'Get a free $20 credit for %s when you spend $100. Use coupon: {{ coupon_code }}', 'automatewoo-referrals' ), get_bloginfo( 'name' ) ),

			'enable_email_share' => 'yes',
			'share_email_template' => 'default',
			'share_email_subject' => __( 'Your friend gave you $20 off!', 'automatewoo-referrals' ),
			'share_email_heading' => __( '{{ advocate.first_name }} has sent you $20 to spend', 'automatewoo-referrals' ),
			'share_email_body' =>
				sprintf(__(
					"Hi there! \n\n You have been invited to shop at %s and you've got a $20 discount waiting for you when you spend $100. Use the coupon code below to claim your offer.",
					'automatewoo-referrals' ), get_bloginfo( 'name' )
				)
				. "<p style='text-align: center;'><strong class='aw-coupon-code'>{{ coupon_code }}</strong></p>"
				. "<p style='text-align: center;'><a class='aw-btn-1' href='" . home_url() . "'>Shop Now</a></p>",

			'allow_existing_customer_referrals' => 'no',
			'reward_event' => 'purchase'
		];
	}


	/**
	 * @return int
	 */
	function get_advocate_key_expiry() {

		switch ( AW_Referrals()->options()->type ) {
			case 'coupon':
				return absint( AW_Referrals()->options()->offer_coupon_expiry );
				break;

			case 'link':
				return absint( AW_Referrals()->options()->share_link_expiry );
				break;

			default:
				return 0;
				break;
		}
	}


	/**
	 * @return bool
	 */
	function is_advocate_key_expiry_enabled() {
		return $this->get_advocate_key_expiry() !== 0;
	}


	function filter_share_link_parameter( $value ) {
		return sanitize_key( $value );
	}


	/**
	 * @return string
	 */
	function get_reward_event() {
		if ( $this->type === 'coupon' ) {
			return 'purchase';
		}
		return $this->reward_event;
	}


}

