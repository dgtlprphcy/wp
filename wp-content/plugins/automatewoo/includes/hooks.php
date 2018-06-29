<?php

namespace AutomateWoo;

/**
 * @class Hooks
 * @since 2.6.7
 */
class Hooks {

	/**
	 * Add 'init' actions here means we can load less files at 'init'
	 */
	static function init() {

		// general
		add_action( 'automatewoo/background_process', [ 'AutomateWoo\Hooks', 'background_process' ], 10, 3 );

		// addons
		add_action( 'automatewoo/addons/activate', [ 'AutomateWoo\Hooks' , 'activate_addon' ] );

		// unsubscribes
		add_action( 'user_register', [ 'AutomateWoo\Hooks', 'schedule_unsubscribe_consolidate_user' ] );
		add_action( 'automatewoo/unsubscribe/consolidate_user', [ 'AutomateWoo\Unsubscribe_Manager', 'consolidate_user' ] );

		// workflows
		add_action( 'delete_post', [ 'AutomateWoo\Hooks', 'maybe_cleanup_workflow_data' ] );

		// frontend action endpoints
		add_action( 'wp_loaded', [ 'AutomateWoo\Hooks', 'check_for_action_endpoint' ] );

		// email
		add_filter( 'automatewoo_email_content', 'wptexturize' );
		add_filter( 'automatewoo_email_content', 'convert_smilies');
		add_filter( 'automatewoo_email_content', 'wpautop' );

		// pre-submit
		if ( AW()->options()->abandoned_cart_enabled ) {
			add_action( 'wp_footer', [ 'AutomateWoo\Hooks', 'maybe_print_presubmit_js' ] );
			add_action( 'automatewoo/ajax/capture_email', [ 'AutomateWoo\PreSubmit', 'ajax_capture_email' ] );
			add_action( 'automatewoo/ajax/capture_checkout_field', [ 'AutomateWoo\PreSubmit', 'ajax_capture_checkout_field' ] );
		}

		// conversions
		add_action( 'woocommerce_checkout_order_processed', [ 'AutomateWoo\Conversions', 'check_order_for_conversion' ], 20 );

		// tools
		add_action( 'automatewoo/tools/background_process', [ 'AutomateWoo\Tools', 'handle_background_process' ], 10, 2 );

		// queue
		add_action( 'automatewoo_five_minute_worker', [ 'AutomateWoo\Queue_Manager', 'check_for_queued_events' ] );
		add_action( 'automatewoo_four_hourly_worker', [ 'AutomateWoo\Queue_Manager', 'check_for_failed_queued_events' ] );

		// coupons
		add_action( 'automatewoo_four_hourly_worker', [ 'AutomateWoo\Coupons', 'schedule_clean_expired' ] );
		add_action( 'automatewoo/coupons/clean_expired', [ 'AutomateWoo\Coupons', 'clean_expired' ] );

		add_action( 'get_header', [ 'AutomateWoo\Language', 'make_language_persistent' ] );

		// object caching
		add_action( 'automatewoo/object/save', [ 'AutomateWoo\Factories', 'update_object_cache' ] );
		add_action( 'automatewoo/object/load', [ 'AutomateWoo\Factories', 'update_object_cache' ] );
		add_action( 'automatewoo/object/delete', [ 'AutomateWoo\Factories', 'clean_object_cache' ] );

		// license
		add_action( 'admin_init', [ 'AutomateWoo\Licenses', 'maybe_check_status' ] );
		add_action( 'automatewoo_license_reset_status_check_timer', [ 'AutomateWoo\Licenses', 'reset_status_check_timer' ] );

		// carts
		if ( AW()->options()->abandoned_cart_enabled ) {
			add_action( 'automatewoo_two_minute_worker', [ 'AutomateWoo\Carts', 'check_for_abandoned_carts' ] );
			add_action( 'automatewoo_two_days_worker', [ 'AutomateWoo\Carts', 'clean_stored_carts' ] );
			add_action( 'woocommerce_cart_updated', [ 'AutomateWoo\Carts', 'maybe_store_cart' ], 100 );
			add_action( 'woocommerce_cart_emptied', [ 'AutomateWoo\Carts', 'cart_emptied' ] );
			add_action( 'woocommerce_checkout_order_processed', [ 'AutomateWoo\Carts', 'empty_after_order_created' ] );
			add_action( 'woocommerce_thankyou', [ 'AutomateWoo\Carts', 'empty_after_order_created' ] );
		}

	}


	/**
	 * @param $hook
	 * @param $batch
	 * @param $args
	 */
	static function background_process( $hook, $batch, $args ) {
		Legacy_Background_Process_Handler::handle( $hook, $batch, $args );
	}


	/**
	 * @param $addon_id
	 */
	static function activate_addon( $addon_id ) {
		if ( $addon = Addons::get( $addon_id ) ) {
			$addon->activate();
		}
	}


	/**
	 * @param $user_id
	 */
	static function schedule_unsubscribe_consolidate_user( $user_id ) {
		wp_schedule_single_event( time() + 30, 'automatewoo/unsubscribe/consolidate_user', [ $user_id ] );
	}


	/**
	 * @param $id
	 */
	static function maybe_cleanup_workflow_data( $id ) {
		if ( get_post_type( $id ) !== 'aw_workflow' ) return;
		Workflow_Manager::delete_related_data( $id );
	}


	/**
	 * Action endpoints
	 */
	static function check_for_action_endpoint() {
		if ( empty( $_GET[ 'aw-action' ] ) || is_ajax() || is_admin() )
			return;

		Frontend_Endpoints::handle( sanitize_key( aw_request( 'aw-action' ) ) );
	}


	/**
	 * Maybe print pre-submit js
	 */
	static function maybe_print_presubmit_js() {

		if ( is_user_logged_in() ) return;

		switch( AW()->options()->guest_email_capture_scope ) {
			case 'none':
				return;
				break;
			case 'checkout':
				if ( ! is_checkout() ) return;
				break;
		}

		PreSubmit::print_js();
	}

}
