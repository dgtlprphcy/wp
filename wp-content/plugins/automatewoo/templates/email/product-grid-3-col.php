<?php
/**
 * Products items list
 *
 * Override this template by copying it to yourtheme/automatewoo/email/product-list.php
 *
 * @version 1.0.4
 *
 * @var array $products
 * @var AutomateWoo\Workflow $workflow
 * @var string $variable_name
 * @var string $data_type
 * @var string $data_field
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$n = 1;

?>

	<?php if ( is_array( $products ) ): ?>

		<style>
			/** don't inline this css - hack for gmail */
			.aw-product-grid .aw-product-grid-item-3-col img {
				height: auto !important;
			}
		</style>

		<table cellspacing="0" cellpadding="0" class="aw-product-grid">
			<tbody><tr><td style="padding: 0;"><div class="aw-product-grid-container">

				<?php foreach ( $products as $product ): ?>

					<div class="aw-product-grid-item-3-col" style="<?php echo ( $n % 3 ? '' : 'margin-right: 0;' ) ?>">

						<a href="<?php echo $product->get_permalink() ?>"><?php echo AW_Mailer_API::get_product_image( $product ) ?></a>
						<h3><a href="<?php echo $product->get_permalink() ?>"><?php echo $product->get_title(); ?></a></h3>
						<p class="price"><strong><?php echo $product->get_price_html(); ?></strong></p>

					</div>

				<?php $n++; endforeach; ?>

			</div></td></tr></tbody>
		</table>

	<?php endif; ?>