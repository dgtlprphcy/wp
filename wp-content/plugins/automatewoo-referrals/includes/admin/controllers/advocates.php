<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Admin_Controller_Abstract;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Admin_Advocates_Controller
 * @since 2.3
 */
class Admin_Advocates_Controller extends Admin_Controller_Abstract {

	/** @var string */
	protected static $nonce_action = 'referral-advocate-action';


	static function output() {

		$action = self::get_current_action();

		switch ( $action ) {
			default:
				self::output_view_list();
				break;
		}
	}


	private static function output_view_list() {

		require_once AW_Referrals()->admin_path() . '/list-tables/abstract.php';
		require_once AW_Referrals()->admin_path() . '/list-tables/advocates.php';

		$table = new List_Table_Advocates();
		$table->prepare_items();
		$table->nonce_action = self::$nonce_action;

		$sidebar_content = '<p>' . __( 'Advocates are users that are promoting your site through referrals. Current credit is the amount of earned credit the advocate has yet to spend and the total credit is the total amount of credit earned. These figures include approved referrals only.', 'automatewoo-referrals' ) . '</p>';

		AW()->admin->get_view( 'page-table-with-sidebar', [
			'page' => 'advocates',
			'table' => $table,
			'heading' => get_admin_page_title(),
			'sidebar_content' => $sidebar_content,
			'messages' => self::get_messages()
		]);
	}

}
