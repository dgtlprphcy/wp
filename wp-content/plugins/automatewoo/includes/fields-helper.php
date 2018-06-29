<?php

namespace AutomateWoo;

/**
 * @class Fields_Helper
 * @since 2.9.9
 */
class Fields_Helper {

	/**
	 * @return array
	 */
	static function get_categories_list() {
		$list = [];

		$categories = get_terms( 'product_cat', [
			'orderby' => 'name',
			'hide_empty' => false
		]);

		foreach ( $categories as $category ) {
			$list[ $category->term_id ] = $category->name;
		}

		return $list;
	}


	/**
	 * @return array
	 */
	static function get_user_tags_list() {
		$list = [];

		$tags = get_terms([
			'taxonomy' => 'user_tag',
			'hide_empty' => false
		]);

		foreach ( $tags as $tag ) {
			$list[$tag->term_id] = $tag->name;
		}

		return $list;
	}

}
