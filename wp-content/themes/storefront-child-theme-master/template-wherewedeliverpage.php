<?php
/**
 * The template for displaying the where we deliver page.
 *
 * Template name: Where we deliver Page
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<header class="ll-plain-header">
					
				<h1 class="page-title">Where we deliver</h1>

			</header>

			<div class="ll-lead-intro sm say-hello">
				<p class="ll-lead-p">At the moment, we only deliver to Leeds city centre.</p>
				<p>This map shows if you’re in our delivery area.</p>
			</div>


			<div class="ll-map">
				<iframe src="https://www.google.com/maps/d/u/0/embed?mid=185DvmvW3id3kUwIlULd4P5PedY0" frameborder="0" style="pointer-events:none;"></iframe>

			</div>

		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();
