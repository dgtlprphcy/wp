<?php
/**
 * Bundled Item Short Description template
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/bundled-item-description.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 4.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $description === '' ){
	return;
}

?>
<div class="bundled_product_desc_wrapper">
	<div class="bundled_product_excerpt product_excerpt"><?php echo apply_filters( 'woocommerce_short_description', $description ); ?></div>
	<div class="bundled_product_description" id="bundledproductdescription_<?php echo $product_id?>-<?php echo $item_id?>">
		<div class="ingredients-nutritional-info hidden" id="modal_<?php echo $product_id?>-<?php echo $item_id?>">
			<h3 class="modal-box-title">Ingredients & nutritional info</h3>
			<div class="modal-box-content">
			<p class="modal-box-subtitle nomarg"><?php echo $title ?></p>
				<?php echo apply_filters( 'woocommerce_short_description', $full_description ); ?>
			</div>
			<a href="#modal_<?php echo $product_id?>-<?php echo $item_id?>" class="modal-close-icon close-modal"><i class="llicons-cross2"></i></a>
		</div>
	</div>

	<div class="bundled_product_extra_info">
		<a href="#modal_<?php echo $product_id?>-<?php echo $item_id?>" class="primary-color stronger open-modal wider"><i class="llicons-citrus"></i> Ingredients & nutritional info</a>
		<a href="#" class="woocommerce-product-gallery-external-trigger primary-color stronger"><i class="llicons-zoom-in"></i> Large / regular</a>
	</div>


</div>
