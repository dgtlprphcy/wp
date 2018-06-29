<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Cache;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class List_Table_Advocates
 * @since 2.3
 */
class List_Table_Advocates extends Admin_List_Table {

	public $_column_headers;
	public $max_items;


	function __construct() {
		parent::__construct([
			'singular' => __( 'Advocates', 'automatewoo-referrals' ),
			'plural' => __( 'Advocates', 'automatewoo-referrals' ),
			'ajax' => false
		]);
	}


	function filters() {
		$this->output_advocate_filter();
	}


	function get_columns() {
		$columns = [
			'advocate' => __( 'Advocate', 'automatewoo-referrals' ),
			'invites_sent' => __( 'Invites sent', 'automatewoo-referrals' ),
			'referral_count' => __( 'Referral count', 'automatewoo-referrals' ),
			'referral_revenue' => __( 'Referral revenue', 'automatewoo-referrals' ),
			'credit_current' => __( 'Current credit', 'automatewoo-referrals' ),
			'credit_total' => __( 'Total credit', 'automatewoo-referrals' ),
		];

		return $columns;
	}


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_advocate( $advocate ) {
		return $this->format_user( $advocate->get_user() );
	}

	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_invites_sent( $advocate ) {
		$link = esc_url( add_query_arg( '_advocate_user', $advocate->get_id(), AW_Referrals()->admin->page_url( 'invites' ) ) );
		$count = $advocate->get_invites_count();
		return "<a href='$link'>$count</a>";
	}


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_referral_count( $advocate ) {
		$link = esc_url( add_query_arg( '_advocate_user', $advocate->get_id(), AW_Referrals()->admin->page_url( 'referrals' ) ) );
		$count = $advocate->get_referral_count( 'approved' );
		return "<a href='$link'>$count</a>";
	}


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_referral_revenue( $advocate ) {
		return wc_price( $advocate->get_referral_revenue() );
	}


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_credit_current( $advocate ) {
		return wc_price( $advocate->get_current_credit() );
	}


	/**
	 * @param Advocate $advocate
	 * @return string
	 */
	function column_credit_total( $advocate ) {
		return wc_price( $advocate->get_total_credit() );
	}



	function prepare_items() {

		$this->_column_headers = [ $this->get_columns(), [], $this->get_sortable_columns() ];
		$current_page = absint( $this->get_pagenum() );
		$per_page = (int) apply_filters( 'automatewoo_report_items_per_page', 20 );

		$this->get_items( $current_page, $per_page );

		$this->set_pagination_args([
			'total_items' => $this->max_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		]);
	}


	/**
	 * @param int $current_page
	 * @param int $per_page
	 */
	function get_items( $current_page, $per_page ) {

		if ( ! $advocate_ids = $this->get_advocates() ) {
			return;
		}

		if ( ! empty( $_GET['_advocate_user'] ) ) {
			$advocate_ids = [ absint($_GET['_advocate_user']) ];
		}

		$this->items = [];
		$this->max_items = count( $advocate_ids );

		$advocate_ids = array_slice( $advocate_ids, $per_page * ( $current_page - 1 ), $per_page );

		foreach ( $advocate_ids as $advocate_id ) {
			if ( $advocate = new Advocate( $advocate_id ) ) {
				$this->items[] = $advocate;
			}
		}

	}


	/**
	 * @return array
	 */
	function get_advocates() {
		global $wpdb;

		if ( $cache = Cache::get_transient( 'current_advocates' ) ) {
			return $cache;
		}

		$referrals_table = AW()->database_tables()->get_table( 'referrals' );
		$advocates1 = $wpdb->get_results( "SELECT DISTINCT advocate_id FROM {$referrals_table->name}", ARRAY_N );

		$invites_table = AW()->database_tables()->get_table( 'referral-invites' );
		$advocates2 = $wpdb->get_results( "SELECT DISTINCT advocate_id FROM {$invites_table->name}", ARRAY_N );

		if ( ! is_array( $advocates1 ) || ! is_array( $advocates2 ) ) {
			return [];
		}

		$advocates = array_unique( array_merge( wp_list_pluck( $advocates1, 0 ), wp_list_pluck( $advocates2, 0 ) ) );

		Cache::set_transient( 'current_advocates', $advocates, 1 );

		return $advocates;
	}

}
