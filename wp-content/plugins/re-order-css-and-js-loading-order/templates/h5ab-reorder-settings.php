<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$scriptArray = get_option('h5abReOrder');
$styleArray = get_option('h5abReOrderCSS');
$jqueryHead = get_option('h5abReOrderjQuery');
$footer_script = get_option('h5abReOrderJSMove');

?>

<div class="h5ab-reorder-cont">

<h1>Re-Order Settings</h1>

<div class="h5ab-reorder-js-cont">
<h3>JavaScript Files</h3>
<ul class="h5ab-reorder-js-list">
<?php

//var_dump($scriptArray);

$jquery_array = array('jquery-core','jquery-migrate','jquery');

if (!empty($scriptArray)) 
{
    foreach($scriptArray as $key => $value) {
		 $hidejquery = false;
		 
		 if ($jqueryHead == 'true') {
                 foreach($jquery_array as $jquery_script) {
				   if($jquery_script == $value) $hidejquery = true;
			     }
         }
		 
		 $attr = esc_attr($value); 
		 //If jQuery lock is activated, don't show the scripts in the visual list
		 //they'll still get saved in the database so that they're enqueued
		 if($hidejquery) {
		     echo '<li style="display:none;" id="' . $attr .'" >' . $value . '</li>'; 
		   } else {
		       echo '<li id="' . $attr .'" >' . $value . '</li>';
		  }
    }
}
?>
</ul>
</div>

<div class="h5ab-reorder-css-cont">
<h3>CSS Stylesheets</h3>
<ul class="h5ab-reorder-css-list">
<?php
if (!empty($styleArray)) {
    foreach($styleArray as $key => $value) {
	    $attr = esc_attr($value);
        echo '<li id="' . $attr .'" >' . $value . '</li>';
     }
}
?>
</ul>
</div>

</div>

<h3>Script Settings</h3>

<input type="checkbox" class="h5ab-reorder-jquery-lock" id="h5ab-reorder-jquery-lock" <?php if ($jqueryHead == 'true') { echo 'checked'; } ?> />
<label for="h5ab-reorder-jquery-lock">jQuery First - Requires Script Reload</label>

<br/>

<input type="checkbox" class="h5ab-reorder-footer-script" id="h5ab-reorder-footer-script" <?php if ($footer_script == 'true') { echo 'checked'; } ?> />
<label for="h5ab-reorder-footer-script">Move Scripts to Footer</label>

<br/>

<button class="button button-primary show_field" id="h5ab-reorder-save">Save New Order</button>

<br/><br/>

<h3>Reset Scripts and Stylesheets</h3>
<p>Do this first after activation - May require up to 10 seconds</p>

<input type="radio" name="reset-method" class="reset-by-iframe" id="reset-by-iframe" checked />
<label for="reset-by-iframe">Iframe</label>
<input type="radio" name="reset-method" class="reset-by-window" id="reset-by-window" />
<label for="reset-by-window">Window</label>

<br/>

<a href="#" class="reset-scripts button button-primary" data-src="<?php echo site_url(); ?>">Reset Scripts and Stylesheets</a>

<br/><br/>

<div class="reset-data "></div>

<div class="h5ab-affiliate-advert">
                   
<p style="margin: 0; text-align: center;">Advertisements*</p>
<a href="https://www.rocketresponder.com/?ref=17327" target="_blank"><img src="<?php echo esc_url(plugins_url( '../images/rocket-responder-banner.jpg', __FILE__ )) ?>" border="0" style="max-width: 100%; height: auto;" /></a>
<br>
<br>
<a href="http://si123.seopressor.hop.clickbank.net" target="_blank"><img src="<?php echo esc_url(plugins_url( '../images/seopressor-banner.jpg', __FILE__ )) ?>" border="0" style="max-width: 100%; height: auto;" /></a>

</div>

<hr/>

<div style="width: 98%; padding: 0 5px;">
<p>*Affiliate Links - We (Plugin Authors) earn commission on sales generated through these links.</p>
</div>

<script>

jQuery(document).ready(function($){
		var updateListItems = function(listOrder) {
					 var obj = { '.h5ab-reorder-js-list' : listOrder.scripts , '.h5ab-reorder-css-list' : listOrder.styles};
					 $.each(obj, function(elem, items) {
					        $(elem).empty();
								$.each(items, function(index, name ) {
								  var listItem = '<li id="' + name + '" >' + name + '</li>';
								   $(elem).append(listItem);
						   });
					 });
				 };

    $('.reset-scripts').on('click', function(){
			var type,
				   data = { 'action': 'get_order_data' };

			if ($('.reset-by-iframe').is(':checked')) {
				  type = 'iframe';
				  $('.reset-data').html('<iframe src="<?php echo site_url(); ?>?h5ab-reset-scripts=1" style="width: 100%; height: 300px;"></iframe>');
			} else {
					type = 'window';
					var loadSite = window.open("<?php echo site_url(); ?>?h5ab-reset-scripts=1");
			 }

			    setTimeout(function() {
					 $.post(ajaxurl, data, function(response) {
							 var listOrder = $.parseJSON(response);
							 updateListItems(listOrder);
							 /*If(type == 'window') {
								  /*setTimeout(function() {
									loadSite.close();
									}, 5000
							 } */
					 });

                    location.reload();
				 }, 5000);

        return false;

    });

});

</script>
