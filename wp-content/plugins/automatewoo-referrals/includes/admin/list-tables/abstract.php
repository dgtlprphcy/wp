<?php

namespace AutomateWoo\Referrals;

use AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Admin_List_Table
 */
abstract class Admin_List_Table extends AutomateWoo\Admin_List_Table {


	function output_advocate_filter() {

		$advocate_string = '';
		$advocate_id = absint( aw_request( '_advocate_user' ) );

		if ( $advocate_id ) {
			$advocate = get_user_by( 'id', $advocate_id );
			$advocate_string = esc_html( $advocate->display_name ) . ' (#' . absint( $advocate->ID ) . ' &ndash; ' . esc_html( $advocate->user_email );
		}

		if ( version_compare( WC()->version, '3.0', '<' ) ): ?>
			<input type="hidden" class="wc-customer-search" name="_advocate_user"
					 data-placeholder="<?php esc_attr_e( 'Search for an advocate&hellip;', 'automatewoo-referrals' ); ?>"
					 data-selected="<?php echo htmlspecialchars( $advocate_string ); ?>" value="<?php echo $advocate_id ? $advocate_id : ''; ?>"
					 data-allow_clear="true">
		<?php else: ?>
			<select class="wc-customer-search" style="width:203px;" name="_advocate_user"
					  data-placeholder="<?php esc_attr_e( 'Search for an advocate&hellip;', 'automatewoo-referrals' ); ?>"
					  data-allow_clear="true">
				<?php if ( $advocate_id ) { echo '<option value="' . $advocate_id . '"' . selected( true, true, false ) . '>' . wp_kses_post( $advocate_string ) . '</option>'; } ?>
			</select>
		<?php endif;
	}


}