jQuery(document).ready(function($){

      if ($( '.h5ab-reorder-css-list li' ).length > 2) {
	  $( '.h5ab-reorder-js-list, .h5ab-reorder-css-list' ).sortable().disableSelection();
      }

	  $('#h5ab-reorder-save').on('click', function(){
	        var     data,
			           jsOrder,
					   cssOrder,
					   jQueryHead,
					   jsFooter;
			        
				jsOrder = $('.h5ab-reorder-js-list').sortable( "toArray" );
				cssOrder = $('.h5ab-reorder-css-list').sortable( "toArray");

		       jQueryHead = ($('.h5ab-reorder-jquery-lock').is(':checked')) ? true: false;
               jsFooter = ($('.h5ab-reorder-footer-script').is(':checked')) ? true: false;
	
			  var data = {
				'action': 'ajax_order',
				'css_order': cssOrder,
				'js_order' : jsOrder,
				'jquery_lock': jQueryHead,
				'js_foot': jsFooter
               };

				$.post(ajax_object.ajax_url, data, function(response) {
				     var html = '',
   					        response = $.parseJSON(response),
					        newJSOrder = response.newJSOrder,
							jQueryScripts = response.jQueryScripts;
		
					$('.h5ab-reorder-js-list').empty();
					
				    $.each(jQueryScripts , function(index, name) {
						  html += '<li style="display:none;" id="' + name + '" >' + name + '</li>';
			         });
					 
					$.each(newJSOrder, function(index, name) {
						 html += '<li id="' + name + '" >' + name + '</li>';
			         });
					 
	                 $('.h5ab-reorder-js-list').append(html);
					 $('.h5ab-reorder-cont').prepend(response.feedback);
					 $('.h5ab-feedback').fadeOut(5000, function() { });

				});
	  });

});
