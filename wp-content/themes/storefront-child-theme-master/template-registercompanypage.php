<?php
/**
 * The template for displaying the register your company page.
 *
 * Template name: Register Company Page
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<header class="ll-plain-header">
					
				<h1 class="page-title">Win-win for you & your company</h1>

			</header>

			<div class="ll-lead-intro sm say-hello">
				<p class="ll-lead-p">If your company’s eligible to be registered with us, there are lots of benefits to getting it signed up. Here are just some of them.</p>
				<p>(Even if you can’t get registered just yet you’re still welcome to buy from us – the value of each delivery just needs to be at least <?php echo get_option("minimumordervalue"); ?> or you can pay a £2.50 delivery charge.) </p>
			</div>


			<div class="ll-grid">

				<div class="ll-grid-panel"><img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/gridpanelimg1.jpg" alt="register" class="gridpanelreveal"></div>

				<div class="ll-grid-panel">

					<div class="ll-grid-panel-text gridpanelreveal">
					
					<p><i class="llicons-phone"></i>There’s no minimum order value, so each employee can order and pay for their own lunch online. </p>
					<p>And if you’re ordering food for a meeting you can just order what you need. </p>

					</div>

				</div>

				<div class="ll-grid-panel greybg">

					<div class="ll-grid-panel-text gridpanelreveal">
					
					<p><i class="llicons-users-plus"></i>Your company can choose to subsidise Lean Lunch meals for employees. </p>
					<p>A popular perk!</p>

					</div>

				</div>

				<div class="ll-grid-panel greenbg strapline"></div>

				
				<div class="ll-grid-panel greybg whitebg-swap repo-on-medium">

					<div class="ll-grid-panel-text gridpanelreveal">
					
					<p><i class="llicons-cargo-bike"></i>We’ll deliver to your office every day, freeing up people’s lunchtimes for fun rather than food shopping.</p>

					</div>

				</div>
				

				<div class="ll-grid-panel"><img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/gridpanelimg2.jpg" alt="register" class="gridpanelreveal"></div>


				<div class="ll-grid-panel greybg-swap">

					<div class="ll-grid-panel-text gridpanelreveal">
					
					<p><i class="llicons-chef"></i>You’ll be invited to tasting events to help us develop even more yummy dishes. </p>

					</div>	

				</div>

				<div class="ll-grid-panel"><img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/gridpanelimg4.jpg" alt="register" class="gridpanelreveal"></div>


				<div class="ll-grid-panel">

					<div class="ll-grid-panel-text gridpanelreveal">
					
					<p><i class="llicons-select"></i>Checkout will be faster – especially useful if you’re in charge of ordering food for meetings. </p>

					</div>

				</div>

				<div class="ll-grid-panel fw greybg">

					<div class="ll-grid-panel-text gridpanelreveal text-center">
					
					<p>With more people at your office eating Lean Lunch meals packed with feel-good nutrients you’re also more likely to have healthier, happier people around. And maybe even see productivity up, and sick days down. </p>
					<p class="thinmarg-bottom">Get in touch today to find out if and how you can get your company registered.</p>
					<a href="/contact-us" class="button">Contact us</a>
					</div>

				</div>

			</div>

		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();
