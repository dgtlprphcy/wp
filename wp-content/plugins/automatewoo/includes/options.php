<?php

namespace AutomateWoo;

/**
 * @class Options
 * @since 2.0.2
 *
 * @property string $version
 *
 * @property bool $abandoned_cart_enabled
 * @property int $abandoned_cart_timeout
 * @property string $guest_email_capture_scope (checkout,all,none)
 * @property bool $clean_expired_coupons
 *
 * @property bool $twilio_integration_enabled
 * @property string $twilio_from
 * @property string $twilio_auth_id
 * @property string $twilio_auth_token
 *
 * @property bool $mailchimp_integration_enabled
 * @property bool $mailchimp_api_key
 *
 * @property bool $active_campaign_integration_enabled
 * @property string $active_campaign_api_url
 * @property string $active_campaign_api_key
 *
 * @property int $queue_batch_size
 * @property int $conversion_window
 *
 * @property bool $enable_background_system_check
 */

class Options extends Options_API {

	/** @var string */
	public $prefix = 'automatewoo_';

	/** @var array */
	public $defaults = [

		'abandoned_cart_enabled' => 'yes',
		'abandoned_cart_timeout' => 15,
		'guest_email_capture_scope' => 'checkout',
		'clean_expired_coupons' => 'yes',

		'twilio_integration_enabled' => 'no',
		'active_campaign_integration_enabled' => false,
		'mailchimp_integration_enabled' => false,
		'queue_batch_size' => 50,
		'conversion_window' => 14,
		'enable_background_system_check' => true,
	];
}

