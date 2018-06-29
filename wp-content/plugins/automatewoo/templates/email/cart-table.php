<?php
/**
 * Cart table. Can only be used with the cart.items variable
 *
 * Override this template by copying it to yourtheme/automatewoo/email/cart-table.php
 *
 * @var array $cart_items
 * @var AutomateWoo\Cart $cart
 * @var AutomateWoo\Workflow $workflow
 * @var string $variable_name
 * @var string $data_type
 * @var string $data_field
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$cart->calculate_totals();
$tax_display = get_option( 'woocommerce_tax_display_cart' );

?>

<?php if ( is_array( $cart_items ) ): ?>

	<table cellspacing="0" cellpadding="6" border="1" class="aw-order-table">
		<thead>
		<tr>
			<th class="td" scope="col" colspan="2" style="text-align:left;"><?php _e( 'Product', 'woocommerce' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php _e( 'Quantity', 'woocommerce' ); ?></th>
			<th class="td" scope="col" style="text-align:left;"><?php _e( 'Price', 'woocommerce' ); ?></th>
		</tr>
		</thead>
		<tbody>

		<?php foreach ( $cart_items as $cart_item_key => $cart_item ):

			$product = wc_get_product( $cart_item['product_id'] );
			$line_total = $tax_display === 'excl' ? $cart_item[ 'line_subtotal' ] : $cart_item['line_subtotal'] + $cart_item['line_subtotal_tax'];

			?>

			<tr>
				<td width="115">
					<a href="<?php echo $product->get_permalink() ?>"><?php echo AW_Mailer_API::get_product_image( $product, 'thumbnail' ) ?></a>
				</td>
				<td><a href="<?php echo $product->get_permalink() ?>"><?php echo $product->get_title(); ?></a></td>
				<td><?php echo $cart_item['quantity'] ?></td>
				<td><?php echo wc_price( $line_total ); ?></td>
			</tr>

		<?php endforeach; ?>

		</tbody>

		<tfoot>

			<?php if ( $cart->has_coupons() ): ?>
				<tr>
					<th scope="row" colspan="3">
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
					<th scope="row" colspan="3"><?php printf(__('Coupon: %s', 'automatewoo'), $coupon_code ); ?></th>
					<td><?php echo wc_price( - $coupon_discount ); ?></td>
				</tr>
			<?php endforeach; ?>

			<?php if ( $tax_display === 'excl' ): ?>
				<tr>
					<th scope="row" colspan="3"><?php _e( 'Tax', 'automatewoo' ); ?></th>
					<td><?php echo wc_price( $cart->calculated_tax_total ); ?></td>
				</tr>
			<?php endif; ?>

			<tr>
				<th scope="row" colspan="3">
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