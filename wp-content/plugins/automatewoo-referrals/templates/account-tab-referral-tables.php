<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * The Template for displaying referral credits in the my account area.
 * This template can be overridden by copying it to yourtheme/automatewoo/referrals/account-tab-referral-tables.php
 *
 * @see https://docs.woothemes.com/document/template-structure/
 *
 * @var AutomateWoo\Referrals\Referral[] $referrals
 * @var AutomateWoo\Referrals\Referral[] $used_referrals
 */

?>

<?php if ( ! empty( $referrals ) ): ?>

	<div class="referrals-container">

		<table class="shop_table shop_table_responsive my_account_referrals">

			<thead>
			<tr>
				<th><?php _e( 'Credit', 'automatewoo-referrals' ); ?></th>
				<th><?php _e( 'Remaining Balance', 'automatewoo-referrals' ); ?></th>
				<th><?php _e( 'Customer', 'automatewoo-referrals' ); ?></th>
				<th><?php _e( 'Date', 'automatewoo-referrals' ); ?></th>
			</tr>
			</thead>

			<tbody>
			<?php foreach ( $referrals as $referral ): ?>
				<tr>
					<td><strong><?php echo wc_price( $referral->get_reward_amount() ) ?></strong></td>
					<td><?php echo wc_price( $referral->get_reward_amount_remaining() ) ?></td>
					<td><?php echo ( $referral ? esc_html( $referral->get_customer_name() ) : '-' ) ?></td>
					<td><?php echo AutomateWoo\Format::date( $referral->date ) ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

<?php endif; ?>


<?php if ( ! empty( $used_referrals ) ): ?>

	<div class="used-referrals-container">

		<h3><?php _e( 'Used Referrals', 'automatewoo-referrals' ); ?></h3>

		<table class="shop_table shop_table_responsive my_account_referrals">

			<thead>
			<tr>
				<th><?php _e( 'Credit', 'automatewoo-referrals' ); ?></th>
				<th><?php _e( 'Remaining Balance', 'automatewoo-referrals' ); ?></th>
				<th><?php _e( 'Customer', 'automatewoo-referrals' ); ?></th>
				<th><?php _e( 'Date', 'automatewoo-referrals' ); ?></th>
			</tr>
			</thead>

			<tbody>
			<?php foreach ( $used_referrals as $referral ): ?>
				<tr>
					<td><strong><?php echo wc_price( $referral->reward_amount ) ?></strong></td>
					<td><?php echo wc_price( $referral->reward_amount_remaining ) ?></td>
					<td><?php echo ( $referral ? esc_html( $referral->get_customer_name() ) : '-' ) ?></td>
					<td><?php echo AutomateWoo\Format::date( $referral->date ); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>

<?php endif; ?>

