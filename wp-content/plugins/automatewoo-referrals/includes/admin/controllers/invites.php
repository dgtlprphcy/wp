<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Admin_Controller_Abstract;
use AutomateWoo\Clean;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Admin_Invites_Controller
 * @since 2.3
 */
class Admin_Invites_Controller extends Admin_Controller_Abstract {

	/** @var string */
	protected static $nonce_action = 'referral-invites-action';


	static function output() {

		$action = self::get_current_action();

		switch ( $action ) {

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
		require_once AW_Referrals()->admin_path() . '/list-tables/invites.php';

		$table = new Invites_List_Table();
		$table->prepare_items();
		$table->nonce_action = self::$nonce_action;

		$sidebar_content = '<p>' . __( 'A record is made for each referral invite that is sent. Social shares are not tracked.', 'automatewoo-referrals' ) . '</p>';

		AW()->admin->get_view( 'page-table-with-sidebar', [
			'page' => 'referral-invites',
			'table' => $table,
			'heading' => get_admin_page_title(),
			'sidebar_content' => $sidebar_content,
			'messages' => self::get_messages()
		]);
	}


	/**
	 * @param $action
	 */
	private static function action_bulk_edit( $action ) {

		self::verify_nonce_action();

		$ids = Clean::ids( aw_request( 'referral_invite_ids' ) );

		if ( empty( $ids ) ) {
			self::$errors[] = __( 'Please select some referral invites to bulk edit.', 'automatewoo-referrals' );
			return;
		}

		foreach ( $ids as $id ) {

			$referral = AW_Referrals()->get_invite( $id );

			if ( ! $referral )
				continue;

			switch ( $action ) {
				case 'delete':
					$referral->delete();
					break;
			}
		}

		self::$messages[] = __( 'Bulk edit completed.', 'automatewoo-referrals' );
	}

}
