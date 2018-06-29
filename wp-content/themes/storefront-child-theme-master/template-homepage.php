<?php
/**
 * The template for displaying the homepage.
 *
 * This page template will display any functions hooked into the `homepage` action.
 * By default this includes a variety of product displays and the page content itself. To change the order or toggle these components
 * use the Homepage Control plugin.
 * https://wordpress.org/plugins/homepage-control/
 *
 * Template name: Homepage
 *
 * @package storefront
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<div class="ll-banner" data-parallax="scroll" data-bleed="10" data-image-src="<?php add_featured_image_as_page_banner()?>">
				<div class="ll-banner-gradient">
					<div class="ll-banner-text">
						<h1>Delicious, nutritious meals delivered to your office</h1>
						<p class="ll-lead">Want to eat healthier at work? <br>Looking for tasty, easy options for you or for meetings?</p>
						<p>Every weekday morning we deliver mouth-watering meals to offices in Leeds city centre. They’re freshly made and super nutritious.</p>
						<p class="ll-banner-cta"><a href="/daily-menus/" class="button button-lightgreen">Order lunch</a></p>
					</div>
				</div>
			</div>

			<div class="floating-cta hidden"><p><a href="/daily-menus/" class="button button-lightgreen">Order lunch</a></p></div>


			<div class="ll-howitworks">
				
				<h2 class="ll-howitworks-title alpha say-hello">Here’s how it works</h2>

				<div class="ll-container ll-howitworks-container">

					<div class="ll-howitworks-panel you-choose">
						<h3 class="ll-howitworks-panel-title">You choose</h3>
						<p>Pick the day(s) you want to order for and the meal(s) that take your fancy.</p>
					</div>

					<div class="ll-howitworks-panel we-make">
						<h3 class="ll-howitworks-panel-title">We make</h3>
						<p>We craft your meal(s) on the morning we deliver using whole ingredients and no nasties.</p>
					</div>

					<div class="ll-howitworks-panel we-deliver">
						<h3 class="ll-howitworks-panel-title">We deliver</h3>
						<p>Your meal will arrive before 12pm ready to eat (pop it in the fridge till you’re ready).</p>
					</div>

					<div class="ll-howitworks-panel you-devour">
						<h3 class="ll-howitworks-panel-title">You devour</h3>
						<p>It’s best to tuck in within 24 hours for maximum freshness and flavour.</p>
					</div>

				</div>

			</div>

			<div class="ll-dailymenulist">
				
				<h2 class="ll-dailymenulist-title alpha say-hello">From the daily menus</h2>

					<div class="ll-dailymenulist-wrapper">
					<div class="ll-dailymenulist-item open">
						
						<div class="ll-dailymenulist-item-header">
							<h3 class="ll-dailymenulist-item-title">Chicken shawarma & tabbouleh</h3>
							<p>Cardamom and sumac give the chicken its deep Middle Eastern flavours. Which get on great with the mixed tabbouleh salad featuring peppers, radish and fresh herbs. It also comes with pickled red cabbage, carrot slaw and pink onions and a really tasty tahini yoghurt dressing.</p>
							<p><a href="/daily-menus/" class="fancy-link">See full menu</a></p>
							<i class="llicons-plus"></i>
						</div>

						<div class="ll-dailymenulist-item-img lb1">
						<a href="/daily-menus/" class="button button-white">See full menu</a>
						</div>

					</div>

					<div class="ll-dailymenulist-item">
						
						<div class="ll-dailymenulist-item-header">
							<h3 class="ll-dailymenulist-item-title">Fantastic falafel with slaw, hummus & salsa</h3>
							<p>The roasted sweet potato with coriander and chickpea gives the falafel a great flavour, and the raw red pepper hummus sits brilliantly alongside it. The salsa’s a tangy mix of cherry tomatoes, red onion, balsamic vinegar and extra virgin olive oil.</p>
							<p><a href="/daily-menus/" class="fancy-link">See full menu</a></p>
							<i class="llicons-plus"></i>
						</div>

						<div class="ll-dailymenulist-item-img lb2">
						<a href="/daily-menus/" class="button button-white">See full menu</a>
						</div>

					</div>

					<div class="ll-dailymenulist-item">
											
						<div class="ll-dailymenulist-item-header">
							<h3 class="ll-dailymenulist-item-title">Super sesame salmon with a noodle salad</h3>
							<p>Your salmon fillet is baked with sesame seeds and served next to a rice noodle salad with raw cucumber, carrot and beetroot. To max the sesame flavour we dress the noodles in sesame oil and add a tahini dressing.</p>
							<p><a href="/daily-menus/" class="fancy-link">See full menu</a></p>
							<i class="llicons-plus"></i>
						</div>

						<div class="ll-dailymenulist-item-img lb3">
						<a href="/daily-menus/" class="button button-white">See full menu</a>
						</div>

					</div>

					<div class="ll-dailymenulist-item">
											
						<div class="ll-dailymenulist-item-header">
							<h3 class="ll-dailymenulist-item-title">Vietnamese chicken vermicelli with a zesty salad</h3>
							<p>Vietnamese-roasted chicken breast teams with brown rice vermicelli and soy sauce. To contrast the rich, salty flavour we add a salad of raw grapefruit, red pepper, carrot, cucumber, coriander and mint with toasted peanuts. And a chilli, lime and ginger dressing gives more citrus flavour and a little heat.</p>
							<p><a href="/daily-menus/" class="fancy-link">See full menu</a></p>
							<i class="llicons-plus"></i>
						</div>

						<div class="ll-dailymenulist-item-img lb4">
						<a href="/daily-menus/" class="button button-white">See full menu</a>
						</div>

					</div>

					</div>


			</div>

			<div class="ll-whatwereallabout">
				
				<h2 class="ll-whatwereallabout-title alpha say-hello">What we’re all about</h2>

				<div class="ll-whatwereallabout-tabs">
				 	
					<div class="ll-whatwereallabout-tabs-links ll-tabs-matchheight">
						<ul class="">
							<li><a href="#tab-1" class="active">Fuel for world-beaters</a></li>
							<li><a href="#tab-2">Deliciously nutritious</a></li>
							<li><a href="#tab-3">All good stuff</a></li>
							<li><a href="#tab-4">We love planet earth</a></li>
							<li><a href="#tab-5">More time for you</a></li>
							<li><a href="#tab-6">Spice up your life</a></li>
						</ul>
					 	
					</div>

					<div class="ll-whatwereallabout-tabs-content ll-tabs-matchheight">

						
						<div class="ll-whatwereallabout-tabs-content-panel active" id="tab-1">
						  
						  <div class="ll-whatwereallabout-text-overlay">
							<h3>Fuel for world-beaters</h3>
							<h4>Lunch is one of your three chances each day to give you and your health a boost.</h4>
							<p>We make it easy with feel-good food that helps keep your energy up and your body and mind on top form. However tedious your 4pm meeting is...</p>
						 </div>
						 	
						</div>
						
						<div class="ll-whatwereallabout-tabs-content-panel" id="tab-2">
						
							<div class="ll-whatwereallabout-text-overlay">
							  	<h3>Deliciously nutritious</h3>
							  	<h4>Our top-notch nutritionist and chefs make sure your meals are both really nourishing and super tasty.</h4>
							  	<p>With a balance of protein, healthy fats and complex carbs they’re designed to keep you satisfied well past biscuit o’clock.</p>
							</div>

						</div>

						<div class="ll-whatwereallabout-tabs-content-panel" id="tab-3">

							<div class="ll-whatwereallabout-text-overlay">
							 	<h3>All good stuff</h3>
							 	<h4>We make your meals fresh from all-natural ingredients, including raw veg to keep the nutrients to the max.</h4>
							 	<p>They’re low in salt with only natural sugars and no added preservatives - just loads of flavour.</p>
						 	</div>

						</div>

						<div class="ll-whatwereallabout-tabs-content-panel" id="tab-4">

							<div class="ll-whatwereallabout-text-overlay">
							  	<h3>We love planet earth</h3>
							  	<h4>Our packaging is recyclable and compostable. And by ordering in advance, you help us prevent food waste.</h4>
							  	<p>We also buy all our ingredients, including sustainably-sourced fish, from lovely local suppliers and deliver by bicycle trucks.</p>
							</div>

						</div>


						<div class="ll-whatwereallabout-tabs-content-panel" id="tab-5">

							 <div class="ll-whatwereallabout-text-overlay">
							 	<h3>More time for you</h3>
							 	<h4>We’re big fans of carving out time to get together over lunch or go for a wander (in the fresh air or the shops…) after eating.</h4>
							 	<p>So your meals will be delivered ready to eat. No queuing or prep – lunch is ready whenever you are.</p>
							</div>


						</div>

						<div class="ll-whatwereallabout-tabs-content-panel" id="tab-6">
							
							<div class="ll-whatwereallabout-text-overlay">

							  	<h3>Spice up your life</h3>
							  	<h4>Few things perk up a dull morning like having a new dish to look forward to at lunch.</h4>
							  	<p>Our menus change daily and there are always meat, fish and vegetarian masterpieces, giving plenty of opportunity to mix things up.</p>
							</div>

						</div>

					</div>
				 	

				</div>



			</div>

			<div class="ll-ourstory">

				<h2 class="ll-ourstory-title alpha say-hello">Our Story</h2>

				<div class="ll-ourstory-img ll-ourstory-mh">
				<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/ourstory.jpg" class="say-hello" alt="our story">
				</div>

				<div class="ll-ourstory-txt ll-ourstory-mh">
					
				
				<h2 class="ll-ourstory-txt-title alpha say-hello">Love food? Us too.</h2>
				<p>A couple of years ago I started trying to eat more healthily more often. But I worked in Leeds city centre and couldn’t find the sort of nutrient-packed food I wanted for lunch at the office. </p>
				<a href="/about-us/" class="button button-darkgreen">Read our story</a>

				</div>


			</div>

			<div class="ll-foodforthought">

				<div class="ll-container">


					<div class="ll-foodforthought-img">
						<img src="<?php echo get_stylesheet_directory_uri()?>/assets/img/foodforthought.png" class="say-hello" alt="Food for thought">
					</div>

					
					<div class="ll-foodforthought-txt">

						<h2 class="ll-foodforthought-title alpha say-hello">Food for thought</h2>
						<p>We don’t just deliver lunch. We can support your company’s wellness programme with nutrition seminars, workshops and one-to-ones.</p>
						<a href="/nutrition-services/" class="button button-darkgreen-outline">Nutrition sessions</a>

					</div>


				</div>

			</div>

		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();
