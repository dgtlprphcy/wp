<?php
/**
 * Request a Review email template
 *
 * @package 	Woocommerce Partial Orders
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
$base 		= get_option( 'woocommerce_email_base_color' );
$base_text 	= wc_light_or_dark( $base, '#202020', '#ffffff' );
?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( "Hi there.", 'woocommerce' ), get_option( 'blogname' ) ); ?></p>

<p><?php print( __( "The following items have just been shipped and are on their way to you!", 'woocommerce' ) ); ?></p>

<?php do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text ); ?>

<table cellspacing="0" cellpadding="6" style="width: 100%;">
	<thead>
		<tr>
			<th>Product</th>
			<th width="20%" style="text-align: center;">Date Shipped</th>
			<th width="20%" style="text-align: center;">Quantity Shipped</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach( $shipped_items as $item ){
			$_product     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
			$item_meta    = new WC_Order_Item_Meta( $item['item_meta'], $_product );
		?>
			
		<tr>
			<td style="text-align:left; vertical-align:top; border: 1px solid #eee; border-right: none; word-wrap:break-word;">
				<?php 
				// Show title/image etc
				echo apply_filters( 'woocommerce_order_item_thumbnail', '<img src="' . ( $_product->get_image_id() ? current( wp_get_attachment_image_src( $_product->get_image_id(), 'thumbnail') ) : wc_placeholder_img_src() ) .'" height="32" width="32" style="vertical-align:middle; margin-right: 10px;" />', $item );

				// Product name
				echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item );

				// Variation
				if ( $item_meta->meta ) {
					echo '<br/><small>' . nl2br( $item_meta->display( true, true ) ) . '</small>';
				}
				?>
				<small>Ordered on {order_date}</small>
			</td>
			
			<td colspan="2" width="40%" style="border: 1px solid #eee; border-left: none; text-align:center;">
				<table width="100%">
					<?php foreach($item['shipped_info'] as $info){ ?>				
					<tr>
						<td width="50%" style="text-align:center;">
							<?php echo $info['date_shipped']; ?>	
						</td>
						<td style="text-align:center;">
							<?php echo $info['quantity_shipped']; ?>
						</td>
					</tr>
					<?php } ?>
				</table>
			</td>
		</tr>
		<?php	
		}
		?>
	</tbody>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text ); ?>

<?php do_action( 'woocommerce_email_footer' ); ?>