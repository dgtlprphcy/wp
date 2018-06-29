<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Admin_Settings_Tab_Abstract;
use AutomateWoo\Integrations;
use AutomateWoo\Emails;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Settings_Tab
 */
class Settings_Tab extends Admin_Settings_Tab_Abstract {

	/** @var bool */
	public $show_tab_title = false;

	/** @var string  */
	public $prefix = 'aw_referrals_';

	function __construct() {
		$this->id = 'referrals';
		$this->name = __( 'Refer A Friend', 'automatewoo-referrals' );
	}


	function load_settings() {

		if ( ! empty( $this->settings ) )
			return;

		$this->section_start( 'main', __( 'Referral campaign options', 'automatewoo-referrals' ) );

		$this->add_setting( 'enabled', [
			'title' => __( 'Enable referrals', 'automatewoo-referrals' ),
			'type' => 'checkbox',
			'autoload' => true,
		]);

		$this->add_setting( 'type', [
			'title' => __( 'Share type', 'automatewoo-referrals' ),
			'desc' => __( 'Choose whether you would like to offer a coupon incentive in your referral campaign. PLEASE NOTE: If you choose link based you must adjust the default text below to suit.', 'automatewoo-referrals' ),
			'type' => 'select',
			'autoload' => true,
			'options' => [
				'coupon' => __( 'Coupon based', 'automatewoo-referrals' ),
				'link' => __( 'Link based', 'automatewoo-referrals' )
			]
		]);

		$this->add_setting( 'referrals_page', [
			'title' => __( 'Share page', 'automatewoo-referrals' ),
			'desc' => __( 'Ensure you add the shortcode <code>[automatewoo_referrals_page]</code> to the content of the page you select.', 'automatewoo-referrals' ),
			'tooltip' => __( 'This is the main page that advocates can use to refer people to your store. This should not be set to the checkout as the referral widget will automatically be displayed on the order recieved page.', 'automatewoo-referrals' ),
			'type' => 'single_select_page',
			'class' => 'wc-enhanced-select-nostd',
			'required' => true
		]);

		$this->add_setting( 'advocate_must_paying_customer', [
			'title' => __( "Limit sharing to paying customers", 'automatewoo-referrals' ),
			'desc' => __( "If unchecked any customer with an account can share.", 'automatewoo-referrals' ),
			'type' => 'checkbox',
		]);

		$this->add_setting( 'auto_approve', [
			'title' => __( 'Enable auto-approval', 'automatewoo-referrals' ),
			'desc' => __( "Automatically approve referrals that don't appear fraudulent once their order is marked as complete.", 'automatewoo-referrals' ),
			'type' => 'checkbox',
		]);


		if ( Integrations::subscriptions_enabled() ) {
			$this->add_setting( 'use_credit_on_subscription_renewals', [
				'title' => __( 'Use store credit on subscription renewal payments', 'automatewoo-referrals' ),
				'desc' => sprintf(
					__( 'If checked any store credit earned by a subscriber will be automatically applied to WooCommerce Subscription renewal payments. This will only work with payment gateway extensions that support %s recurring total modifications%s.', 'automatewoo-referrals' ),
					'<a href="https://docs.woothemes.com/document/subscriptions/payment-gateways/#advanced-features" target="_blank">', '</a>' ),
				'type' => 'checkbox',
			]);
		}

		$this->section_end('main');



		$this->section_start( 'offer',
			__( 'Referral offer (for the friend)', 'automatewoo-referrals' ),
			__( 'The coupon discount that advocates can offer to their friends. This coupon will only be valid for new customers. By default referral coupons expire after 4 weeks which means that a single advocate may own many different referral coupons at one time. If you would like advocates to have a single coupon for all time set the <b>Coupon Expiry</b> to 0.', 'automatewoo-referrals' )
		);

		$this->add_setting( 'offer_type', [
			'title' => __( 'Offer type', 'automatewoo-referrals' ),
			'type' => 'select',
			'options' => AW_Referrals()->get_offer_types(),
		]);

		$this->add_setting( 'offer_amount', [
			'title' => __( 'Offer amount', 'automatewoo-referrals' ),
			'type' => 'number',
		]);

		$this->add_setting( 'offer_min_purchase', [
			'title' => __( 'Minimum purchase amount', 'automatewoo-referrals' ),
			'tooltip' => __( 'The minimum purchase amount that the referral offer is valid for.', 'automatewoo-referrals' ),
			'type' => 'number',
		]);

		$this->add_setting( 'offer_coupon_expiry', [
			'title' => __( 'Coupon expiry', 'automatewoo-referrals' ),
			'desc' => __( 'weeks after creation', 'automatewoo-referrals' ),
			'tooltip' => __( "Set this to '0' if you do not want coupons to expire.", 'automatewoo-referrals' ),
			'type' => 'number'
		]);

		$this->section_end( 'offer' );


		$this->section_start( 'link',
			__( 'Link options', 'automatewoo-referrals' ),
			__( 'By default share links expire after 4 weeks which means that a single advocate may own many different unique share links at one time. If you would like advocates to have a single share link for all time set the <b>Share Link Expiry</b> to 0.', 'automatewoo-referrals' )
		);

		$this->add_setting( 'share_link_parameter', [
			'title' => __( 'Share link parameter', 'automatewoo-referrals' ),
			'tooltip' => sprintf( __( 'This parameter is used when generating unique share links e.g. %s', 'automatewoo-referrals' ),
				home_url() . '?[link-parameter]=[advocate-share-key]' ),
			'type' => 'text',
		]);

		$this->add_setting( 'share_link_expiry', [
			'title' => __( 'Share link expiry', 'automatewoo-referrals' ),
			'desc' => __( 'weeks after share', 'automatewoo-referrals' ),
			'tooltip' => __( "Set this to '0' if you do not want links to expire.", 'automatewoo-referrals' ),
			'type' => 'number',
		]);

		$this->section_end( 'link' );



		$this->section_start( 'reward', __( 'Referral reward (for the advocate)', 'automatewoo-referrals' ),
			__( 'The reward given to the advocate for each time they successfully refer a customer. This reward is only granted for the friends first purchase. '
			. 'If you would like to notify advocates each time they receive a referral reward you should create a workflow with the <strong>New Referral</strong> trigger.', 'automatewoo-referrals' )
		);

		$this->add_setting( 'reward_type', [
			'title' => __( 'Reward type', 'automatewoo-referrals' ),
			'type' => 'select',
			'options' => AW_Referrals()->get_reward_types(),
			'tooltip' => __( 'By selecting no reward you can instead reward them by using the AutomateWoo referral triggers and for example you could generate a coupon for the advocate.', 'automatewoo-referrals' ),
		]);

		$this->add_setting( 'reward_amount', [
			'title' => __( 'Reward amount', 'automatewoo-referrals' ),
			'type' => 'number',
		]);

		$this->add_setting( 'reward_min_purchase', [
			'title' => __( 'Minimum purchase amount', 'automatewoo-referrals' ),
			'tooltip' => __( 'The minimum purchase amount required for store credit to be used.', 'automatewoo-referrals' ),
			'type' => 'number',
		]);

		$this->section_end( 'reward' );



		$this->section_start( 'widget',
			__( 'Share widget', 'automatewoo-referrals' ),
			sprintf( __( 'The share widget is a mini version of the share page that can added to the order confirmation page, order emails and be inserted in a workflow email with %s. Please note that customers will be prompted to create an account before they can refer their friends.', 'automatewoo-referrals' ),
				'<code>{{ customer.referral_widget }}</code>'
				)
		);

		$this->add_setting( 'widget_on_order_confirmed', [
			'title' => __( 'Show widget on order confirmation page', 'automatewoo-referrals' ),
			'type' => 'select',
			'options' => [
				'bottom' => __( 'Bottom Of Page', 'automatewoo-referrals' ),
				'top' => __( 'Top Of Page', 'automatewoo-referrals' ),
				'no' => __( 'Do Not Display', 'automatewoo-referrals' ),
			]
		]);

		$this->add_setting( 'widget_on_order_emails', [
			'title' => __( 'Show widget on order emails', 'automatewoo-referrals' ),
			'type' => 'checkbox',
		]);

		$this->add_setting( 'widget_heading', [
			'title' => __( 'Widget heading', 'automatewoo-referrals' ),
			'type' => 'text',
		]);

		$this->add_setting( 'widget_text', [
			'title' => __( 'Widget paragraph text', 'automatewoo-referrals' ),
			'type' => 'textarea',
			'custom_attributes' => [
				'rows' => 5
			]
		]);

		$this->section_end( 'widget' );



		$this->section_start( 'social', __( 'Social sharing', 'automatewoo-referrals' ) );

		$this->add_setting( 'enable_facebook_share', [
			'title' => __( 'Enable sharing via Facebook', 'automatewoo-referrals' ),
			'type' => 'checkbox',
		]);

		$this->add_setting( 'enable_twitter_share', [
			'title' => __( 'Enable sharing via Twitter', 'automatewoo-referrals' ),
			'type' => 'checkbox',
		]);

		$this->add_setting( 'social_share_text', [
			'title' => __( 'Default share text', 'automatewoo-referrals' ),
			'type' => 'textarea',
			'tooltip' => __( "This is the default text used when an advocate shares via Twitter or Facebook. If you chose 'Coupon Based' you must include the variable {{ coupon_code }}. A link to your shop will be added automatically.", 'automatewoo-referrals' ),
		]);

		$this->add_setting( 'social_share_text_twitter', [
			'title' => __( 'Twitter default share text (optional)', 'automatewoo-referrals' ),
			'type' => 'textarea',
			'tooltip' => __( "Optionally specify different text for Twitter shares as they are limited to 140 characters. If left blank the default share text will be used.", 'automatewoo-referrals' ),
		]);

		$this->add_setting( 'social_share_url', [
			'title' => __( 'Share URL (optional)', 'automatewoo-referrals' ),
			'tooltip' => __( 'This URL used when an shares via Facebook or Twitter. Defaults to the home page URL. You can also add analytics tracking parameters to the URL is you wish.', 'automatewoo-referrals' ),
			'placeholder' => home_url(),
			'type' => 'text',
		]);

		$this->section_end( 'social' );



		$this->section_start(
			'email',
			__( 'Email sharing', 'automatewoo-referrals' ),
			__( 'The email template that is sent when an advocate refers a friend view email. ', 'automatewoo-referrals' )
			. __( 'You can insert dynamic content with the following variables: ', 'automatewoo-referrals' )
			. '<br><code>{{ coupon_code }}</code> <code>{{ advocate.first_name }}</code> <code>{{ advocate.full_name }}</code>'
			. '<br><br><a href="#" class="button js-aw-referrals-preview-share-email">' . __( 'Preview email', 'automatewoo-referrals' ) . '</a>'
		);

		$this->add_setting( 'enable_email_share', [
			'title' => __( 'Enable sharing via Email', 'automatewoo-referrals' ),
			'type' => 'checkbox',
		]);

		$this->add_setting( 'share_email_subject', [
			'title' => __( 'Email subject', 'automatewoo-referrals' ),
			'type' => 'text',
		]);

		$this->add_setting( 'share_email_heading', [
			'title' => __( 'Email heading', 'automatewoo-referrals' ),
			'type' => 'text',
		]);

		$this->add_setting( 'share_email_template', [
			'title' => __( 'Email template', 'automatewoo-referrals' ),
			'type' => 'select',
			'options' => Emails::get_email_templates(),
			'tooltip' => __( 'The template that will be used to send referral share emails. For info on creating custom templates please refer to the AutomateWoo documentation.', 'automatewoo-referrals' ),
		]);

		$this->add_setting( 'share_email_body', [
			'title' => __( 'Email body', 'automatewoo-referrals' ),
			'type' => 'tinymce',
			'desc' => __( "If you chose 'Coupon Based' you must include the variable {{ coupon_code }}. If you chose 'Link Based' ensure you have at least one link in the email body. Tracking parameters will be automatically added to all links in the email.", 'automatewoo-referrals' ),
		]);

		$this->section_end( 'email' );


		$this->section_start( 'advanced', __( 'Advanced', 'automatewoo-referrals' ) );

		$this->add_setting( 'allow_existing_customer_referrals', [
			'title' => __( 'Allow existing customer referrals', 'automatewoo-referrals' ),
			'desc' => __( 'If this is unchecked, existing customers will be blocked from receiving referrals (default). If checked, existing customers can be referred but they can only be referred a single time.', 'automatewoo-referrals' ),
			'type' => 'checkbox',
		]);


		$this->add_setting( 'reward_event', [
			'title' => __( 'Reward event', 'automatewoo-referrals' ),
			'desc' => __( "The reward event determines when a referral should be created. E.g. when setting to 'Purchase' (default) referrals will be created when a friend makes a purchase. When setting to 'Sign Up' referrals will be created when a friend creates an account.", 'automatewoo-referrals' ),
			'type' => 'select',
			'autoload' => true,
			'options' => [
				'purchase' => __( 'Purchase', 'automatewoo-referrals' ),
				'signup' => __( 'Sign up', 'automatewoo-referrals' )
			]
		]);

		$this->section_end( 'advanced' );
	}


	/**
	 * @return array
	 */
	function get_settings() {
		$this->load_settings();
		return $this->settings;
	}


	/**
	 * @param $id
	 * @return mixed
	 */
	protected function get_default( $id ) {
		return isset( AW_Referrals()->options()->defaults[ $id ] ) ? AW_Referrals()->options()->defaults[ $id ] : false;
	}


}

return new Settings_Tab();
