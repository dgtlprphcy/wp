<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Admin_Controller_Abstract;
use AutomateWoo\Cache;
use AutomateWoo\Clean;
use AutomateWoo\Fields;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Admin_Referrals_Controller
 */
class Admin_Referrals_Controller extends Admin_Controller_Abstract {

	/** @var string */
	protected static $nonce_action = 'referral-action';


	static function output() {

		$action = self::get_current_action();

		switch ( $action ) {

			case 'view':
				self::output_view_single();
				break;

			case 'save':
				self::action_save();
				self::output_view_single();
				break;

			case 'delete':
				self::action_delete();
				self::output_view_list();
				break;

			case 'reject':
				self::action_reject();
				self::output_view_list();
				break;

			case 'approve':
				self::action_approve();
				self::output_view_list();
				break;

			case 'bulk_approved':
			case 'bulk_rejected':
			case 'bulk_pending':
			case 'bulk_potential-fraud':
			case 'bulk_delete':
				self::action_bulk_edit( str_replace( 'bulk_', '', $action ) );
				self::output_view_list();
				break;

			default:

				self::output_view_list();
				break;
		}
	}


	private static function output_view_list() {

		require_once AW_Referrals()->admin_path() . '/list-tables/abstract.php';
		require_once AW_Referrals()->admin_path() . '/list-tables/referrals.php';

		$table = new Referrals_List_Table();
		$table->nonce_action = self::$nonce_action;
		$table->sections = self::get_list_sections();
		$table->section_totals = self::get_section_totals();

		AW_Referrals()->admin->get_view( 'page-list-referrals.php', [
			'table' => $table
		]);
	}


	/**
	 *
	 */
	private static function output_view_single() {

		$referral = self::get_referral();
		$field_name_base = 'referral_data';

		$status_field = new Fields\Select( false );
		$status_field
			->set_name_base( $field_name_base )
			->set_name('status')
			->set_title( __('Status', 'automatewoo-referrals' ) )
			->set_options( AW_Referrals()->get_referral_statuses() )
			->set_description( __( 'The referral status controls whether the advocate can use any reward credit. Store credit can only be used when the referral is approved.', 'automatewoo-referrals' ) )
			->set_required()
			;

		$reward_amount_field = ( new Fields\Price() )
			->set_name_base( $field_name_base )
			->set_name( 'reward_amount' )
			->set_title( __( 'Amount', 'automatewoo-referrals' ) );

		$reward_amount_remaining_field = ( new Fields\Price() )
			->set_name_base( $field_name_base )
			->set_name( 'reward_amount_remaining' )
			->set_title( __( 'Amount', 'automatewoo-referrals' ) );


		AW_Referrals()->admin->get_view( 'page-view-referral.php', [
			'referral' => $referral,
			'status_field' => $status_field,
			'reward_amount_field' => $reward_amount_field,
			'reward_amount_remaining_field' => $reward_amount_remaining_field
		]);
	}


	/**
	 *
	 */
	private static function action_delete() {

		self::verify_nonce_action();

		$referral = self::get_referral();

		if ( ! $referral ) {
			self::referral_missing_error();
			return;
		}

		$referral->delete();

		self::$messages[] = __( 'Referral successfully deleted.', 'automatewoo-referrals');
	}


	/**
	 *
	 */
	private static function action_save() {

		self::verify_nonce_action();

		$referral = self::get_referral();

		if ( ! $referral ) {
			self::referral_missing_error();
			return;
		}

		if ( ! isset( $_POST[ 'referral_data' ] ) ) {
			return;
		}

		$data = $_POST[ 'referral_data' ];

		if ( isset( $data['status'] ) ) {
			$referral->update_status( Clean::string( $data['status'] ) );
		}

		if ( isset( $data[ 'reward_amount' ] ) ) {
			$referral->reward_amount = Clean::string( $data[ 'reward_amount' ] );
		}

		if ( isset( $data[ 'reward_amount_remaining' ] ) ) {
			$referral->reward_amount_remaining = Clean::string( $data[ 'reward_amount_remaining' ] );
		}

		$referral->save();

		self::$messages[] = __( 'Referral successfully updated.', 'automatewoo-referrals');
	}


	/**
	 *
	 */
	private static function action_approve() {

		self::verify_nonce_action();

		$referral = self::get_referral();

		if ( ! $referral ) {
			self::referral_missing_error();
			return;
		}

		if ( $referral->has_status( 'approved' ) )
			return;

		$referral->update_status( 'approved' );

		self::$messages[] = __( 'Referral marked as approved.', 'automatewoo-referrals');
	}


	/**
	 *
	 */
	private static function action_reject() {

		self::verify_nonce_action();

		$referral = self::get_referral();

		if ( ! $referral ) {
			self::referral_missing_error();
			return;
		}

		if ( $referral->has_status( 'rejected' ) )
			return;

		$referral->update_status( 'rejected' );

		self::$messages[] = __( 'Referral marked as rejected.', 'automatewoo-referrals');
	}


	/**
	 * @param $action
	 */
	private static function action_bulk_edit( $action ) {

		self::verify_nonce_action();

		$ids = Clean::ids( aw_request( 'referral_ids' ) );

		if ( empty( $ids ) ) {
			self::$errors[] = __( 'Please select some referrals to bulk edit.', 'automatewoo-referrals' );
			return;
		}

		foreach ( $ids as $id ) {

			$referral = AW_Referrals()->get_referral( $id );

			if ( ! $referral )
				continue;

			switch ( $action ) {
				case 'approved':
				case 'rejected':
				case 'pending':
				case 'potential-fraud':
					$referral->update_status( $action );
					break;

				case 'delete':
					$referral->delete();
					break;
			}
		}

		self::$messages[] = __( 'Bulk edit completed.', 'automatewoo-referrals' );
	}


	/**
	 * @return false|Referral
	 */
	private static function get_referral() {

		$referral_id = absint( aw_request( 'referral_id' ) );

		if ( ! $referral_id )
			return false;

		return AW_Referrals()->get_referral( $referral_id );
	}


	/**
	 *
	 */
	private static function referral_missing_error() {
		self::$errors[] = __( 'Referral could not be found.', 'automatewoo-referrals' );
	}


	/**
	 * @param $route
	 * @param Referral|bool $referral
	 * @return string
	 */
	static function get_route_url( $route = false, $referral = false ) {

		$base_url = admin_url('admin.php?page=automatewoo-referrals');

		if ( ! $route ) {
			return $base_url;
		}

		$args = [
			'action' => sanitize_title( $route ),
			'referral_id' => $referral ? $referral->get_id() : false
		];

		switch ( $args['action'] ) {
			case 'view':
				return add_query_arg( $args, $base_url );
				break;

			case 'delete':
			case 'reject':
			case 'approve':
			case 'save':
				return wp_nonce_url( add_query_arg( $args, $base_url ), self::$nonce_action );
				break;
		}

		return '';
	}


	/**
	 * @return array
	 */
	static function get_list_sections() {
		return [
			'' => __( 'All', 'automatewoo-referrals' ),
			'approved' => __( 'Approved', 'automatewoo-referrals' ),
			'rejected' => __( 'Rejected', 'automatewoo-referrals' ),
			'pending' => __( 'Pending', 'automatewoo-referrals' ),
			'potential-fraud' => __( 'Potential Fraud', 'automatewoo-referrals' )
		];
	}


	/**
	 * @return array
	 */
	static function get_section_totals() {

		$counts = [];

		foreach ( self::get_list_sections() as $section_id => $section ) {
			$counts[ $section_id ] = Referral_Manager::get_referrals_count( $section_id );
		}

		return $counts;
	}

}
