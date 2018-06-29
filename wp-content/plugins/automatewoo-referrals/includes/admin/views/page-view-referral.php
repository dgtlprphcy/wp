<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Fields;
use AutomateWoo\Compat;
use AutomateWoo\Format;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @view Edit Referral Page
 *
 * @var $referral Referral
 * @var $status_field \AW_Field_Select
 * @var $reward_amount_field Fields\Price
 * @var $reward_amount_remaining_field Fields\Price
 */

$advocate = $referral->get_advocate();
$customer = $referral->get_customer();
$order = $referral->get_order();

?>

<div class="wrap automatewoo-referral-page automatewoo-page automatewoo-page--referrals">

	<h1><?php _e( 'Referral Details', 'automatewoo-referrals' ) ?></h1>

	<?php Admin_Referrals_Controller::output_messages(); ?>

	<form method="post" action="<?php echo Admin_Referrals_Controller::get_route_url( 'save', $referral ) ?>" id="aw_referrals_edit_referral">

		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">

				<div id="postbox-container-1">

					<div class="postbox automatewoo-metabox no-drag">

						<table class="automatewoo-table">

							<tr class="automatewoo-table__row">
								<td class="automatewoo-table__col">

									<label class="automatewoo-label"><?php echo $status_field->get_title(); ?></label>

									<?php $status_field->render( $referral->get_status() ); ?>

									<?php if ( $status_field->get_description() ): ?>
										<p class="aw-field-description"><?php echo $status_field->get_description(); ?></p>
									<?php endif; ?>

								</td>
							</tr>

						</table>

						<div class="automatewoo-metabox-footer submitbox">
							<div id="delete-action"><a class="submitdelete deletion" href="<?php echo Admin_Referrals_Controller::get_route_url( 'delete', $referral ); ?>"><?php _e( 'Delete permanently', 'automatewoo-referrals' ) ?></a></div>
							<input type="submit" class="button save_order button-primary" name="save" value="<?php _e( 'Update', 'automatewoo-referrals' ) ?>">
						</div>

					</div>
				</div>


				<div id="postbox-container-2">

					<?php if ( $referral->ip_addresses_match() ): ?>
						<div class="aw-referral-info-boxes">

							<div class="automatewoo-info-box">
								<span class="dashicons dashicons-shield-alt"></span> <strong><?php _e( 'Potential Fraud Detected', 'automatewoo' ) ?></strong> -
								<?php _e( 'The IP address of the advocate matched the customer IP when the order was placed.', 'automatewoo' ); ?>
							</div>

						</div>
					<?php endif ?>


					<div class="postbox automatewoo-metabox no-drag">
						<div class="inside">

							<table class="automatewoo-table automatewoo-table--two-column">

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e( 'Referral ID', 'automatewoo-referrals' ) ?></td>
									<td class="automatewoo-table__col">#<?php echo $referral->get_id() ?></td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Date created', 'automatewoo-referrals') ?></td>
									<td class="automatewoo-table__col"><?php echo Format::datetime( $referral->date ) ?></td>
								</tr>


								<?php if ( $order ): ?>
									<tr>
										<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Order', 'automatewoo-referrals') ?></td>
										<td class="automatewoo-table__col">
											<a href="<?php echo get_edit_post_link( Compat\Order::get_id( $order ) ) ?>">#<?php echo $order->get_order_number() ?></a>
											<?php printf(__('(Status:  %s)', 'automatewoo-referrals'), wc_get_order_status_name( $order->get_status() ) ) ?>
										</td>
									</tr>
								<?php endif; ?>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Advocate', 'automatewoo-referrals') ?></td>
									<td class="automatewoo-table__col">
										<a href="<?php echo get_edit_profile_url( $referral->get_advocate_id() ) ?>"><?php echo $referral->get_advocate_name() ?></a>
										<a href="mailto:<?php echo $advocate->get_email() ?>"><?php echo $advocate->get_email() ?></a>
									</td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Advocate IP address', 'automatewoo-referrals') ?></td>
									<td class="automatewoo-table__col"><?php echo $referral->get_advocate_ip_address() ?></td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Advocate reward type', 'automatewoo-referrals') ?></td>
									<td class="automatewoo-table__col"><?php echo AW_Referrals()->get_reward_types()[ $referral->get_reward_type() ] ?></td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Advocate reward amount', 'automatewoo-referrals') ?></td>
									<td class="automatewoo-table__col automatewoo-table__col--field">
										<?php echo get_woocommerce_currency_symbol() ?><?php $reward_amount_field->render( Format::decimal( $referral->get_reward_amount() ) ) ?>
									</td>
								</tr>


								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Advocate reward amount remaining', 'automatewoo-referrals') ?></td>
									<td class="automatewoo-table__col automatewoo-table__col--field">
										<?php echo get_woocommerce_currency_symbol() ?><?php $reward_amount_remaining_field->render( Format::decimal( $referral->get_reward_amount_remaining() ) ) ?>
									</td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Customer', 'automatewoo-referrals') ?></td>
									<td class="automatewoo-table__col">
										<?php if ( $customer ): ?>
											<a href="<?php echo get_edit_profile_url( $customer->ID ) ?>"><?php echo AW_Referrals()->admin->get_formatted_customer_name( $customer ) ?></a>
										<?php else: ?>
											<?php echo AW_Referrals()->admin->get_formatted_customer_name_from_order( $order ) ?>
										<?php endif; ?>

										<?php if ( $order ): $email = Compat\Order::get_billing_email( $order ) ?>
											<a href="mailto:<?php echo esc_attr( $email ) ?>"><?php echo esc_attr( $email ) ?></a>
										<?php endif; ?>
									</td>
								</tr>

								<tr>
									<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Customer IP address', 'automatewoo-referrals') ?></td>
									<td class="automatewoo-table__col"><?php echo $referral->get_customer_ip_address() ?></td>
								</tr>


								<?php if ( $referral->offer_type ): ?>

									<tr>
										<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Customer discount type', 'automatewoo-referrals') ?></td>
										<td class="automatewoo-table__col"><?php echo AW_Referrals()->get_offer_types()[$referral->offer_type] ?></td>
									</tr>

									<tr>
										<td class="automatewoo-table__col automatewoo-table__col--label"><?php _e('Customer discount amount', 'automatewoo-referrals') ?></td>
										<td class="automatewoo-table__col">
											<?php
											if ( $referral->offer_type == 'coupon_percentage_discount' ) {
												echo wc_price( $referral->get_discounted_amount() ) . ' (' . $referral->offer_amount . '%)';
											}
											elseif ( $referral->offer_type == 'coupon_discount') {
												echo wc_price( $referral->offer_amount );
											}
											?>
										</td>

									</tr>
								<?php endif ?>


							</table>

						</div>

					</div>

				</div>

			</div>
		</div>
	</form>

</div>
