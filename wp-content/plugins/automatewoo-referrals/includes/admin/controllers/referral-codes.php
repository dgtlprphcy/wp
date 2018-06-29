<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Admin_Controller_Abstract;
use AutomateWoo\Clean;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Admin_Referral_Codes_Controller
 */
class Admin_Referral_Codes_Controller extends Admin_Controller_Abstract {

	/** @var string */
	protected static $nonce_action = 'referral-referral-code-action';


	static function output() {

		$action = self::get_current_action();

		switch ( $action ) {
			case 'bulk_delete':
				self::action_bulk_delete();
				self::output_view_list();
				break;

			default:
				self::output_view_list();
				break;
		}
	}


	private static function output_view_list() {

		require_once AW_Referrals()->admin_path() . '/list-tables/abstract.php';
		require_once AW_Referrals()->admin_path() . '/list-tables/referral-codes.php';

		$table = new Referral_Codes_List_Table();
		$table->prepare_items();
		$table->nonce_action = self::$nonce_action;

		$sidebar_content = '<p>' . __( 'Referral codes are unique keys that are used to identify advocates in the referral process. Depending on whether you are using coupon or link based tracking the referral code will be used as a part of the shared URL or coupon. These codes are automatically deleted if they expire.', 'automatewoo-referrals' ) . '</p>';

		AW()->admin->get_view( 'page-table-with-sidebar', [
			'page' => 'referral-codes',
			'table' => $table,
			'heading' => get_admin_page_title(),
			'sidebar_content' => $sidebar_content,
			'messages' => self::get_messages()
		]);
	}


	/**
	 * Bulk delete keys
	 */
	private static function action_bulk_delete() {

		self::verify_nonce_action();

		$ids = Clean::ids( aw_request( 'advocate_key_ids' ) );

		if ( empty( $ids ) ) {
			self::$errors[] = __( 'Please select some items to bulk edit.', 'automatewoo-referrals' );
			return;
		}

		foreach ( $ids as $id ) {
			$key = AW_Referrals()->get_advocate_key( $id );

			if ( $key ) {
				$key->delete();
			}
		}

		self::$messages[] = __( 'Bulk edit completed.', 'automatewoo-referrals' );
	}

}
