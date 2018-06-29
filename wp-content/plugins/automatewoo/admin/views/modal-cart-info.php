<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @var $cart Cart
 */

$cart->calculate_totals();
$tax_display = get_option( 'woocommerce_tax_display_cart' );

?>

	<div class="automatewoo-modal__header">
		<h1><?php printf(__( "Cart #%s", 'automatewoo' ), $cart->get_id() ) ?></h1>
	</div>

	<div class="automatewoo-modal__body">
		<div class="automatewoo-modal__body-inner">

			<?php if ( $cart->has_items() ): ?>

				<table cellspacing="0" cellpadding="6" border="1" class="automatewoo-cart-table">
					<thead>
					<tr>
						<th><?php _e( 'Product', 'automatewoo' ); ?></th>
						<th><?php _e( 'Quantity', 'automatewoo' ); ?></th>
						<th><?php _e( 'Price', 'automatewoo' ); ?></th>
					</tr>
					</thead>
					<tbody>

					<?php foreach ( $cart->get_items() as $cart_item_key => $cart_item ):

						$product = wc_get_product( $cart_item['product_id'] );
						$line_total = $tax_display === 'excl' ? $cart_item[ 'line_subtotal' ] : $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'];

						?>

						<tr>
							<td><a href="<?php echo $product->get_permalink() ?>"><?php echo $product->get_title(); ?></a></td>
							<td><?php echo $cart_item['quantity'] ?></td>
							<td><?php echo wc_price( $line_total ); ?></td>
						</tr>

					<?php endforeach; ?>

					</tbody>

					<tfoot>

					<?php if ( $cart->has_coupons() ): ?>
						<tr>
							<th scope="row" colspan="2">
								<?php _e('Subtotal', 'automatewoo'); ?>
								<?php if ( $tax_display !== 'excl' ): ?>
									<small><?php _e( '(incl. tax)','automatewoo' ) ?></small>
								<?php endif; ?>
							</th>
							<td><?php echo wc_price( $cart->calculated_subtotal ); ?></td>
						</tr>
					<?php endif; ?>

					<?php foreach ( $cart->get_coupons() as $coupon_code => $coupon_data ):

						$coupon_discount = $tax_display === 'excl' ? $coupon_data['discount_excl_tax'] : $coupon_data['discount_incl_tax'];
						?>

						<tr>
							<th scope="row" colspan="2"><?php printf(__('Coupon: %s', 'automatewoo'), $coupon_code ); ?></th>
							<td><?php echo wc_price( - $coupon_discount ); ?></td>
						</tr>
					<?php endforeach; ?>

					<?php if ( $tax_display === 'excl' ): ?>
						<tr>
							<th scope="row" colspan="2"><?php _e( 'Tax', 'automatewoo' ); ?></th>
							<td><?php echo wc_price( $cart->calculated_tax_total ); ?></td>
						</tr>
					<?php endif; ?>

					<tr>
						<th scope="row" colspan="2">
							<?php _e( 'Total', 'automatewoo' ); ?>
							<?php if ( $tax_display !== 'excl' ): ?>
								<small><?php printf( __( '(includes %s tax)','automatewoo' ), wc_price( $cart->calculated_tax_total ) ) ?></small>
							<?php endif; ?>
						</th>
						<td><?php echo wc_price( $cart->calculated_total ); ?></td>
					</tr>
					</tfoot>
				</table>

			<?php endif; ?>


			<ul>
				<li><strong><?php _e( 'Cart token', 'automatewoo' ) ?>:</strong> <?php echo esc_attr( $cart->get_token() ) ?></li>
                <li><strong><?php _e( 'Cart created', 'automatewoo' ) ?>:</strong> <?php echo esc_attr( Format::datetime( $cart->get_date_created(), 0 ) ) ?></li>
            </ul>

		</div>
	</div>
