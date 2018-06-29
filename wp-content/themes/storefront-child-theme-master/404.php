<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">

		<main id="main" class="site-main" role="main">

			<div class="error-404 not-found">

				<div class="page-content text-center">

					<header class="page-header">
						<h1 class="page-title beta">Gosh, sorry - that’s not what you wanted is it?</h1>
					</header><!-- .page-header -->

					<p>If you head back to the <a href="/" class="fancy-link">home</a> page, hopefully you can find what you’re looking for.</p>
					
					<p>Or <a href="/contact-us" class="fancy-link">get in touch</a> and let us know how we can help.</p>
					

				</div><!-- .page-content -->
			</div><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer();
