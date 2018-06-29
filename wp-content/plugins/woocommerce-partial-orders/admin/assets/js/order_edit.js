
function wpo_init_tooltip(){
	jQuery( '.send-partial-orders-email' ).tipTip({
		'attribute': 'data-tip',
		'fadeIn': 50,
		'fadeOut': 50,
		'delay': 0,
		'defaultPosition': 'top'
	});
}

function woocommerce_partial_orders_create_dialog( item_id, order_id ){

	var container = jQuery("#partial_orders_update_item_container");
    
    //reset button just incase
    container.dialog({
        buttons:[
            {
                text: 'Close',
                click: function() {
                    jQuery( this ).dialog( "close" );
                }
            }
        ]
    });
	
	//get item details        
	var data = {
		action:     'wpo_set_item_shipped_dialog_content',
		item_id:    item_id,
		order_id:   order_id
	};
	
	jQuery.post( ajaxurl, data, function(response) {

		if(response == '0' || response == '-1'){ //ajax failed           
			container.html("Something went wrong when trying to access the order data.");         
		} else {
			container.html(response);
		}
	});
	
	
	container.dialog('open');
	
}

function woocommerce_partial_orders_set_item_unshipped( item_id, order_id ){ 
	
	if( confirm('Are you sure you want to delete the shipping history for this product?\r\nThis cannot be undone.') == true ){
		
		var container = jQuery("#partial_orders_update_item_container");
        container.html('<div style="text-align: center;"><img src="images/loading.gif" /></div>');
        
        var data = { 
            action: 'wpo_unset_item_shipped',
            item_id: item_id,
			order_id: order_id
        };
        
        jQuery.post( ajaxurl, data, function(response) {

            if(response == '0' || response == '-1'){ //ajax failed           
                container.html("Something went wrong when trying to set this items status to not shipped.");         
            } else {
                var data = jQuery.parseJSON(response);
				jQuery('tr.item[data-order_item_id=' + item_id + '] td.partial-orders').html(data.output);
				
                jQuery('#order_status option:selected').attr('selected', false); 
				jQuery('#order_status option[value="' + data.order_status + '"]').attr('selected', true);
				
				jQuery('#order_status_chosen .chosen-single span').html(jQuery('#order_status option:selected').text()); // Woo 2.2
				jQuery('#select2-chosen-1').html(jQuery('#order_status option:selected').text()); // Woo 2.3
				
				
				if(data.email_info == ''){
					jQuery('.send-partial-orders-email').hide();
				} else {
					jQuery('.send-partial-orders-email').attr('data-tip', data.email_info);
					wpo_init_tooltip();
					jQuery('.send-partial-orders-email').show();
				}
				
                container.dialog('close'); 
				
            }
        });
		
	}
}

function wpo_set_items(event){
    var action = '';
    if(event.data.action == 'set'){
        action = 'wpo_bulk_set_shipped';
    }
    else {
        action = 'wpo_bulk_unset_shipped';
    }
    
    var selected_rows = jQuery('#order_line_items').find('tr.selected');
    var item_ids = [];
    jQuery(selected_rows).each( function() {
        item_ids.push(jQuery(this).attr('data-order_item_id'));
    });
    
    if ( item_ids.length > 0 ) {
                      
        jQuery('table.woocommerce_order_items').block({ message: null, overlayCSS: { background: '#fff url(' + woocommerce_admin_meta_boxes.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });

        var data = {
            order_id:	woocommerce_admin_meta_boxes.post_id,
            order_item_ids:	item_ids,
            action: 	action,
            security: 	woocommerce_admin_meta_boxes.order_item_nonce
        };

        jQuery.ajax( {
            url: woocommerce_admin_meta_boxes.ajax_url,
            data: data,
            type: 'POST',
            success: function( response ) {
                var result = jQuery.parseJSON(response);
                //update
                jQuery(result.items).each( function(index, values) {
                    jQuery('tr[data-order_item_id="' + values.item_id + '"] .partial-orders').html(values.output);
                });

                if(typeof result.new_status != 'undefined'){						
                    jQuery('#order_status option:selected').attr('selected', false); 
                    jQuery('#order_status option[value="wc-' + result.new_status + '"]').attr('selected', true);
                    jQuery('#select2-chosen-1').html(jQuery('#order_status option:selected').text());
                }

                if(result.email_info == ''){
                    jQuery('.send-partial-orders-email').hide();
                } else {
                    jQuery('.send-partial-orders-email').attr('data-tip', result.email_info);
                    wpo_init_tooltip();
                    jQuery('.send-partial-orders-email').show();
                }

                jQuery('table.woocommerce_order_items').unblock();

            }
        } );

    }
    
}

jQuery( function($){
    
    //move shipped column to end
    /*$('.woocommerce_order_items tr').each(function() {
        $('th.partial-orders', this).insertAfter(jQuery('th:last', this));
        $('td.partial-orders', this).insertAfter(jQuery('td:last', this));

    });*/
	
	//add item row as attribute to td in table for easy processing in ajax update
    var count = 0;
    jQuery('#order_items_list td.partial-orders').each(function() { 
        jQuery(this).attr('item_row', count); //add row attribute     
        count++;
    });
    
    //create dialog 
    var default_content = '<div style="text-align: center;"><img src="images/loading.gif" /><br />Loading ... Please wait ...</div>';
    jQuery('body').append('<div id="partial_orders_update_item_container">'+default_content+'</div>'); //create dialog div 
    jQuery("#partial_orders_update_item_container").dialog({ //initialise dialog  
        width: 400,
        autoOpen: false,
        draggable: false,
        position: {my: "bottom", at: "center", of: window},
        title: 'Shipped status',
        buttons:[
            {
                text: 'Close',
                click: function() {
                    jQuery( this ).dialog( "close" );
                }
            }
        ],
        close: function( event, ui ) {
            jQuery( this ).html(default_content);
        }
    });
    
    //on bulk action submit - Pre woocommerce 2.6
    /*$('#woocommerce-order-items').on( 'click', '.do_bulk_action', function() { 
        
        var action = $(this).closest('.bulk_actions').find('select').val(); // Woo 2.1
		if( action === undefined ){
			action = $(this).closest('.bulk-actions').find('select').val();
		}
		var selected_rows = $('#woocommerce-order-items').find('.check-column input:checked');
		var item_ids = [];

        $(selected_rows).each( function() {

                var $item = $(this).closest('tr.item');

                item_ids.push( $item.attr( 'data-order_item_id' ) );

        } );

        if ( item_ids.length > 0 && ( action == 'bulk_set_shipped' || action == 'bulk_unset_shipped' ) ) {
                      
            $('table.woocommerce_order_items').block({ message: null, overlayCSS: { background: '#fff url(' + woocommerce_admin_meta_boxes.plugin_url + '/assets/images/ajax-loader.gif) no-repeat center', opacity: 0.6 } });
                  
            var data = {
                order_id:	woocommerce_admin_meta_boxes.post_id,
                order_item_ids:	item_ids,
                action: 	'wpo_' + action,
                security: 	woocommerce_admin_meta_boxes.order_item_nonce
            };
            
            $.ajax( {
                url: woocommerce_admin_meta_boxes.ajax_url,
                data: data,
                type: 'POST',
                success: function( response ) {
                    var result = $.parseJSON(response);
                    //update
                    $(result.items).each( function(index, values) {
                        $('tr[data-order_item_id="' + values.item_id + '"] .partial-orders').html(values.output);
                    });

                    if(typeof result.new_status != 'undefined'){						
						jQuery('#order_status option:selected').attr('selected', false); 
						jQuery('#order_status option[value="wc-' + result.new_status + '"]').attr('selected', true);
						jQuery('#order_status_chosen .chosen-single span').html(jQuery('#order_status option:selected').text());
                    }
                    
					if(result.email_info == ''){
						jQuery('.send-partial-orders-email').hide();
					} else {
						jQuery('.send-partial-orders-email').attr('data-tip', result.email_info);
						wpo_init_tooltip();
						jQuery('.send-partial-orders-email').show();
					}
					
                    $('table.woocommerce_order_items').unblock();
                    
                }
            } );
            
        }
        
    });*/

});
