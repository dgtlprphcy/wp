<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package storefront
 */

?>

		</div><!-- .col-full -->
	</div><!-- #content -->

	<?php do_action( 'storefront_before_footer' ); ?>

	<footer id="colophon" class="site-footer" role="contentinfo">
		<div class="xcol-full">

			<div class="site-info">


				<div class="site-info-item">
					<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/logo-withstrapline.svg" class="footer-logo">
				</div>
				<div class="site-info-item">
					<div class="footer-links">
					<ul>
						<li>Tel: <a href="tel:0113 403 2162">0113 4032162</a></li>
						<li><a href="/contact-us">Contact us</a></li>
						<li><a href="/terms-conditions">Terms</a></li>
						<li><a href="/privacy">Privacy</a></li>
					</ul>
					</div>
				</div>
				<div class="site-info-item">
					<p class="footer-social"><a href="https://www.facebook.com/Lean-Lunch-203793363371818/" target="_blank"><i class="llicons-fb"></i></a>  
					<a href="https://twitter.com/leanlunchuk" target="_blank"><i class="llicons-twitter"></i></a>  
					<a href="https://www.instagram.com/leanlunchuk/?hl=en" target="_blank"><i class="llicons-instagram"></i></a>
					<a href="https://www.linkedin.com/company-beta/18060890/" target="_blank"><i class="llicons-linkedin"></i></a>
					</p>
				</div>

				<p class="copyright"><?php echo esc_html( apply_filters( 'storefront_copyright_text', $content = '&copy; ' . date( 'Y' ). ' ' . get_bloginfo( 'name' )  ) ); ?></p>


			</div>

		</div><!-- .col-full -->
	</footer><!-- #colophon -->

	<?php do_action( 'storefront_after_footer' ); ?>

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
