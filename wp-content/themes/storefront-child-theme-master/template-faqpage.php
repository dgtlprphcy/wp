<?php
/**
 * The template for displaying the FAQs page.
 *
 * Template name: FAQs Page
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<header class="ll-plain-header">
					
				<h1 class="page-title">FAQs</h1>

			</header>

			<div class="ll-faq-content">
				

			</div>

			<div class="ll-panel-cta">
				<div class="ll-panel-cta-img">
					<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/ordercta1.jpg" alt="order">
				</div>
				<div class="ll-panel-cta-img">
					<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/ordercta2.jpg" alt="order">
				</div>
				<div class="ll-panel-cta-img">
					<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/ordercta3.jpg" alt="order">
				</div>
				<div class="ll-panel-cta-text">
					<h4 class="ll-panel-cta-title">Delicious, nutritious meals delivered to your office</h4>
					<a href="/daily-menus" class="button">Order lunch</a>
				</div>
			</div>

		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();
