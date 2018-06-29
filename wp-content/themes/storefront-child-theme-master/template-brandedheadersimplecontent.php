<?php
/**
 * The template for displaying simple content page with branded header page.
 *
 * Template name: Simple content branded header page
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<header class="ll-plain-header">
					
				<h1 class="page-title"><?php echo get_the_title() ?></h1>

			</header>

			<div class="ll-page-content">
			<?php while ( have_posts() ) : the_post();

					the_content();

			endwhile; // End of the loop. ?>
			</div>

			<div class="ll-panel-cta">
				<div class="ll-panel-cta-img">
					<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/ordercta4.jpg" alt="order">
				</div>
				<div class="ll-panel-cta-img">
					<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/ordercta5.jpg" alt="order">
				</div>
				<div class="ll-panel-cta-img">
					<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/ordercta6.jpg" alt="order">
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
