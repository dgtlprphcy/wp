<?php
/**
 * Bundled Product Image template
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/bundled-item-image.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 5.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// get product thumbnails
$_product = wc_get_product( $product_id );
$attachment_ids = $_product->get_gallery_image_ids();

if ( $attachment_ids && has_post_thumbnail($product_id) ) {
	$compareimageclass = "compare_image";
}else{
	$compareimageclass = "";
}

?><div class="<?php echo esc_attr( implode( ' ', $gallery_classes ) ); ?> woocommerce-product-gallery__wrapper"><?php

	if ( has_post_thumbnail( $product_id ) ) {

		$image_post_id = get_post_thumbnail_id( $product_id );
		$image_title   = esc_attr( get_the_title( $image_post_id ) );
		$image_data    = wp_get_attachment_image_src( $image_post_id, 'full' );
		$image_link    = $image_data[ 0 ];
		$image         = get_the_post_thumbnail( $product_id, apply_filters( 'bundled_product_large_thumbnail_size', 'shop_catalog' ), array(
			'title'                   => $image_title,
			'data-large_image'        => $image_link,
			'data-large_image_width'  => $image_data[ 1 ],
			'data-large_image_height' => $image_data[ 2 ],
		) );

		$html  = '<figure class="bundled_product_image woocommerce-product-gallery__image '.$compareimageclass.'">';
		$html .= $image;

		if ( $attachment_ids && has_post_thumbnail($product_id) ) {

			$full_size_image = wp_get_attachment_image_src( $attachment_ids[0], 'full' );
			$thumbnail       = wp_get_attachment_image_src( $attachment_ids[0], 'shop_thumbnail' );
			$image_title     = esc_attr( get_the_title( $attachment_ids[0] ) );

			$attributes = array(
				'title'                   => $image_title,
				'data-src'                => $full_size_image[0],
				'data-large_image'        => $full_size_image[0],
				'data-large_image_width'  => $full_size_image[1],
				'data-large_image_height' => $full_size_image[2],
			);

			$html .= wp_get_attachment_image( $attachment_ids[0], 'shop_single', false, $attributes );

		}


		$html .= '</figure>';

		$html .= sprintf( '<div class="hidden"><a href="%1$s" class="image zoom" title="%2$s" data-rel="%3$s">%4$s</a></div>', $image_link, $image_title, $image_rel, $image );


	} else {

		$html  = '<figure class="bundled_product_image woocommerce-product-gallery__image--placeholder">';
		$html .= sprintf( '<a href="%1$s" class="placeholder_image zoom" data-rel="%3$s"><img class="wp-post-image" src="%1$s" alt="%2$s"/></a>', wc_placeholder_img_src(), __( 'Bundled product placeholder image', 'woocommerce-composite-products' ), $image_rel );
		$html .= '</figure>';
	}

	echo apply_filters( 'woocommerce_bundled_product_image_html', $html, $product_id, $bundled_item );


	if ( $attachment_ids && has_post_thumbnail($product_id) ) {
		foreach ( $attachment_ids as $attachment_id ) {
			$full_size_image = wp_get_attachment_image_src( $attachment_id, 'full' );
			$thumbnail       = wp_get_attachment_image_src( $attachment_id, 'shop_thumbnail' );
			$image_title     = esc_attr( get_the_title( $attachment_id ) );

			$attributes = array(
				'title'                   => $image_title,
				'data-src'                => $full_size_image[0],
				'data-large_image'        => $full_size_image[0],
				'data-large_image_width'  => $full_size_image[1],
				'data-large_image_height' => $full_size_image[2],
			);

			$html  = '<div data-thumb="' . esc_url( $thumbnail[0] ) . '" class="woocommerce-product-gallery__image bundled_product_thumbs"><a href="' . esc_url( $full_size_image[0] ) . '" title="'.$image_title.'">';
			$html .= wp_get_attachment_image( $attachment_id, 'shop_single', false, $attributes );
	 		$html .= '</a></div>';

			echo apply_filters( 'woocommerce_single_product_image_thumbnail_html', $html, $attachment_id );
		}
	}



?></div>
