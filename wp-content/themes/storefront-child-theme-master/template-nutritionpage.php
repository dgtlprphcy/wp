<?php
/**
 * The template for displaying the nutrition page.
 *
 * Template name: Nutrition Page
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<div class="ll-banner ll-page-banner" data-parallax="scroll" data-bleed="10" data-image-src="<?php add_featured_image_as_page_banner()?>">
				<div class="ll-banner-gradient">

				<div class="ll-banner-text">
				<h1>Brilliant nutrition sessions <br>at your company</h1>
				</div>
				</div>
			</div>

			<div class="ll-lead-intro say-hello">
				<p class="ll-lead-p">When we talk to companies with wellness programmes, nutrition is always one of the key pillars. Which makes sense – who’s going to feel great and perform their best when they’re chomping through toast, sandwiches, pasta and chocolates in a typical day?</p>
				<p>But just making a few changes here and there can make a huge difference. Whether it’s a bit more fruit and veg and healthy fats or fewer starchy foods and sugary treats.</p>

			</div>

			<div class="ll-text-panel">
				
				<div class="ll-text-panel-row">
					
					<div class="ll-text-panel-col practicaladvice">

						<h2 class="ll-text-panel-title say-hello">Practical advice (with no boring bits)</h2>

					</div>

					<div class="ll-text-panel-col white">

							<div class="say-hello">
							<p class="ll-lead-p">Our nutrition seminars, workshops and one-to-ones are run by our nutritionist Liz Cooper. They can be completely bespoke for your company, and the kind of things Liz covers are how to eat better to improve your:</p>
 
 							<ul>
 								<li>Energy and mood</li>
								<li>Gut health</li>
								<li>Immunity</li>
 							</ul>

							<p>Liz makes sure they’re fun, interesting and far from preachy. They’re designed to get people thinking and give them practical tips. So they don’t just learn why to make changes for their physical and mental health but how.  </p>
 
							</div>

					</div>

				</div>

			</div>	


			<div class="ll-inpage-banner" data-parallax="scroll" data-bleed="10" data-image-src="<?php echo get_stylesheet_directory_uri()?>/assets/img/nutrition2.jpg">
			</div>

			<div class="ll-text-panel">


				<div class="ll-text-panel-row">

					<div class="ll-text-panel-col fromanexpert">

						<h2 class="ll-text-panel-title say-hello">From an expert, not Google </h2>


					</div>
					

					<div class="ll-text-panel-col white">

							<div class="say-hello">
							<p class="ll-lead-p">They’ll come away with professional, expert advice and factual details – rather than the mind-boggling array of information and opinions an internet search comes up with. </p>
				
							<p>They’ll even get a Lean Lunch goodie bag too. </p>

							<p class="thinmarg-bottom"><b>Interested? Why not get in touch today to find out more?</b></p>
				
							<a href="/contact-us" class="button">Contact us</a>


							</div>

					</div>

	

				</div>


			</div>

		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();
