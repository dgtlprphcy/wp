<?php
	/**
	 * Admin Notices for SagePay Form
	 */
	class WC_Gateway_Sagepay_Form_Update_Notice {
		
		public function __construct() {
			global $current_user;
			$current_user 	= wp_get_current_user();
			$user_id 		= $current_user->ID;
			
            /**
             * Add admin notice if mcrypt_encrypt not found
             * Only shown on SagePay settings page.
             */
            if( isset( $_GET['page'] ) && isset( $_GET['section'] ) ) {
            	if( current_user_can( 'manage_options' ) && 'wc-settings' == $_GET['page'] && 'checkout' == $_GET['tab'] && 'sagepayform' == $_GET['section'] ) {
            		add_action('admin_notices', array($this, 'admin_notice') );
				}
			}

		}
	
		/**
		 * Display a notice
		 */
		function admin_notice() {

			$is_error = FALSE;
			
			// Check for mcrypt_encrypt
			if( !function_exists('mcrypt_encrypt') ) {

				$is_error = TRUE;

				$error .= '<h3 class="alignleft" style="line-height:150%">';
				$error .= sprintf(__('IMPORTANT! SagePay requires the PHP Mcrypt library (<a href="%1$s" target="_blank">%1$s</a>) and your hosting does not appear to have support for this. Please contact your host.', 'woocommerce_sagepayform'), 'http://php.net/manual/en/book.mcrypt.php');	
				$error .= '</h3>';

			}

			// Check for potential URL length issues
			if ( function_exists( 'ini_get' ) && extension_loaded( 'suhosin' ) ) {
				
				if( ini_get('suhosin.get.max_value_length') < 2000 || ini_get('suhosin.get.max_vars') < 2000 ) {

					$is_error = TRUE;

					$error .= __( '<h3 class="alignleft" style="line-height:150%">Warning</h3>', 'woocommerce_sagepayform' );
					$error .= __( '<p><strong>Your server configuration may need to be adjusted for SagePay Form to work correctly. Please place a test order to make sure your customers will be returned to your site correctly</strong></p>', 'woocommerce_sagepayform' );
					$error .= __( '<p>If you experience an issue after paying - you will probably see a white screen with a notice to check your WooCommerce SagePay Form settings - please ask your host to increase the following values</p>', 'woocommerce_sagepayform' );

					$error .= sprintf(__( '<p>suhosin.get.max_value_length = %s IDEAL VALUE : 2000</br />', 'woocommerce_sagepayform' ), size_format( wc_let_to_num( ini_get('suhosin.get.max_value_length') ) ) );

					$error .= sprintf(__( 'suhosin.get.max_vars = %s IDEAL VALUE : 2000</p>', 'woocommerce_sagepayform' ), size_format( wc_let_to_num( ini_get('suhosin.get.max_vars') ) ) );

					$error .= __( '<p>If you have successfully placed a test order and were returned to your "Thank you for ordering" page then you can ignore this warning</p>', 'woocommerce_sagepayform' );

				}

			}

			if( TRUE === $is_error ) {

				$output .= '<div class="notice notice-error">';
				$output .= $error;
				$output .= '<br class="clear">';
				$output .= '</div>';

				echo $output;

			}			
		
		}

	} // End class
	
	$WC_Gateway_Sagepay_Form_Update_Notice = new WC_Gateway_Sagepay_Form_Update_Notice;