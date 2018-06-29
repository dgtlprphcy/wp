<?php

    /**
     * WC_Sagepay_Common_Functions class.
     *
     * Functions Common to all SagePay Gateways.
     */
    class WC_Sagepay_Common_Functions {

        private $version = '3.7.2';

        /**
         * __construct function.
         *
         * @access public
         * @return void
         */
        public function __construct() {

            // Add security check column to orders page in admin
            add_action( 'admin_init' , array( $this, 'sage_manage_edit_shop_order_columns' ), 10, 2 );

            // Add security check details
            add_action( 'manage_shop_order_posts_custom_column' , array( $this, 'security_check_admin_init') , 2 );

            // Enqueue Admin Scripts and CSS
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ), 99 );

        }

        /**
         * Returns the current WC version.
         */
        public static function wc_version() {
            return get_option( 'woocommerce_version' );
        }

        /**
         * Replace unwanted characters
         */
        public static function unwanted_array() {
            return array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o','ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
        }

        /**
         * Returns the link to sage
         */
        public static function link() {
            return 'http://www.sagepay.co.uk/support/online-shoppers/about-sage-pay';
        }

        /**
         * [sagepay_debug description]
         * @param  Array   $tolog   contents for log
         * @param  String  $id      payment gateway ID
         * @param  String  $message additional message for log
         * @param  boolean $start   is this the first log entry for this transaction
         */
        public static function sagepay_debug( $tolog = NULL, $id, $message = NULL, $start = FALSE ) {

            if( !isset( $logger ) ) {
                $logger      = new stdClass();
                $logger->log = new WC_Logger();
            }

            /**
             * If this is the start of the logging for this transaction add the header
             */
            if( $start ) {

                $logger->log->add( $id, __('', 'woocommerce_sagepayform') );
                $logger->log->add( $id, __('=============================================', 'woocommerce_sagepayform') );
                $logger->log->add( $id, __('', 'woocommerce_sagepayform') );
                $logger->log->add( $id, __('SagePay Log', 'woocommerce_sagepayform') );
                $logger->log->add( $id, __('' .date('d M Y, H:i:s'), 'woocommerce_sagepayform') );
                $logger->log->add( $id, __('', 'woocommerce_sagepayform') );

            }

            /**
             * Make sure we mask the card number
             */
            if( isset( $tolog["CardNumber"] ) && $tolog["CardNumber"] != '' ) {
                $tolog["CardNumber"] = substr( $tolog["CardNumber"], 0, 4 ) . str_repeat( "*", strlen($tolog["CardNumber"]) - 8 ) . substr( $tolog["CardNumber"], -4 );
            }

            $logger->log->add( $id, __('=============================================', 'woocommerce_sagepayform') );
            $logger->log->add( $id, $message );
            $logger->log->add( $id, print_r( $tolog, TRUE ) );
            $logger->log->add( $id, __('=============================================', 'woocommerce_sagepayform') );

        }

        /**
         * Returns the order formatted for sagepay basket.
         */
        public static function sagepay_state( $country, $state  ) {

            if ( $country == 'US' ) {
                return $state;
            } else {
                return '';
            }

        }

        /**
         * [get_basket description]
         * @param  [type] $option [Basket Option from settings]
         * @param  [type] $order  [WC Order ID]
         * @return [type]         [NULL or the basket in the required format]
         */
        public static function get_basket( $option, $order_id ) {

            if( !isset($option) ) {
                $option = 0;
            }

            switch ( $option ) {
                case 0:
                    return;
                    break;
                case 1:
                    return self::sagepay_basket( $order_id );
                    break;
                case 2:
                    return self::sagepay_basket_xml( $order_id );
                    break;
                default:
                   return self::sagepay_basket( $order_id );
            }

        }

        /**
         * Returns the order formatted for sagepay basket.
         */
        public static function sagepay_basket( $order_id ) {

            $order = new WC_Order( $order_id );

            // Cart Contents for SagePay 'Basket'
            $sagepay_basket = array();

            // Cart Contents
            $item_loop = 1;
            if ( sizeof( $order->get_items() ) > 0 ) {
                foreach ( $order->get_items() as $item ) {
                    if ( $item['qty'] ) {

                        $_product = $order->get_product_from_item( $item );

                        $item_loop++;

                        $item_name = $item['name'];

                        if ( $_product->get_sku() ) {
                            $item_name = '[' . $_product->get_sku() . ']' .  $item_name;
                        }
                            
                        if ( self::wc_version() < '2.4' ) {
                            // Deprecated in WC 2.4
                            $item_meta  = new WC_Order_Item_Meta( $item['item_meta'] );

                        } else {

                            $item_meta  = new WC_Order_Item_Meta( $item, $_product );

                        }

                        if ( $meta = $item_meta->display( true, true ) ) {
                            $item_name .= ' ( ' . $meta . ' )';
                        }

                        $sagepay_basket[] =
                            str_replace( ':', '', $item_name )                                                                  // Description
                            . ':' . $item['qty']                                                                                // Quantity
                            . ':' . $order->get_item_total( $item, false )                                                      // Ex Tax
                            . ':' . $order->get_item_tax( $item )                                                               // Tax Amount
                            . ':' . $order->get_item_total( $item, true )                                                       // Inc Tax
                            . ':' . $order->get_line_total( $item, true )                                                       // Total line cost
                        ;
                    }
                }
            }

            // Shipping Cost
            if ( $order->order_shipping ) {
                $sagepay_basket[] =
                    __( 'Shipping', 'woocommerce_sagepayform' )                                                                 // Description
                    . ':' . 1                                                                                                   // Quantity
                    . ':' . number_format( $order->order_shipping, 2, '.', '' )                                                 // Ex Tax
                    . ':' . number_format( $order->order_shipping_tax, 2, '.', '' )                                             // Tax Amount
                    . ':' . number_format( $order->order_shipping + $order->order_shipping_tax, 2, '.', '' )                    // Inc Tax
                    . ':' . number_format( $order->order_shipping + $order->order_shipping_tax, 2, '.', '' )                    // Total line cost
                ;
            } else {
                $sagepay_basket[] =
                    __( 'Shipping', 'woocommerce_sagepayform' )                                                                 // Description
                    . ':' . 1                                                                                                   // Quantity
                    . ':' . number_format( 0, 2, '.', '' )                                                                      // Ex Tax
                    . ':' . number_format( 0, 2, '.', '' )                                                                      // Tax Amount
                    . ':' . number_format( 0, 2, '.', '' )                                                                      // Inc Tax
                    . ':' . number_format( 0, 2, '.', '' )                                                                      // Total line cost
                ;               
            }
            
            // Coupon Cost
            if ( self::wc_version() < '2.3' ) {
                // get_order_discount() deprecated in WC 2.3
                $sagepay_basket[] =
                    __( 'Discount', 'woocommerce_sagepayform' )                                                                 // Description
                    . ':' . 1                                                                                                   // Quantity
                    . ':' . number_format( $order->get_order_discount(), 2, '.', '' )                                           // Ex Tax
                    . ':' . number_format( '0', 2, '.', '' )                                                                    // Tax Amount
                    . ':' . number_format( $order->get_order_discount(), 2, '.', '' )                                           // Inc Tax
                    . ':' . number_format( $order->get_order_discount(), 2, '.', '' )                                           // Total line cost
                ;
                $item_loop++;

            } else {

                if( 0 != $order->get_total_discount() ) {
                    $sagepay_basket[] =
                        __( 'Discount', 'woocommerce_sagepayform' )                                                                 // Description
                        . ':' . 1                                                                                                   // Quantity
                        . ':' . number_format( $order->get_total_discount(), 2, '.', '' )                                           // Ex Tax
                        . ':' . number_format( '0', 2, '.', '' )                                                                    // Tax Amount
                        . ':' . number_format( $order->get_total_discount(), 2, '.', '' )                                           // Inc Tax
                        . ':' . number_format( $order->get_total_discount(), 2, '.', '' )                                           // Total line cost
                    ;
                    $item_loop++;
                }

            }

            $sagepay_basket = $item_loop . ':' . implode( ':', $sagepay_basket );
            $sagepay_basket = str_replace( "\n", "", $sagepay_basket );
            $sagepay_basket = str_replace( "\r", "", $sagepay_basket );

            $sagepay_basket = strtr( $sagepay_basket, self::unwanted_array() );
            
            if( mb_strlen( $sagepay_basket ) > 7500 ) {
                $sagepay_basket = NULL;
            }

            return $sagepay_basket;

        }
        
        /**
         * Returns the order formatted for sagepay basket.
         */
        public static function sagepay_basket_xml( $order_id ) {

            // Check WC version - changes for WC 3.0.0
            $pre_wc_30 = version_compare( WC_VERSION, '3.0', '<' );

            $order       = new WC_Order( $order_id );

            $xml         = '';
            $basketxml   = '';
            $discountxml = '';
            $shippingxml = '';

            // Cart Contents
            $item_loop = 1;

            if ( sizeof( $order->get_items() ) > 0 ) {
                foreach ( $order->get_items() as $item ) {
                    if ( $item['qty'] ) {

                        $_product = $order->get_product_from_item( $item );

                        $item_loop++;

                        if ( self::wc_version() < '2.4' ) {
                            // Deprecated in WC 2.4
                            $item_meta  = new WC_Order_Item_Meta( $item['item_meta'] );
                        } else {
                            $item_meta  = new WC_Order_Item_Meta( $item, $_product );
                        }

                        if ( $meta = $item_meta->display( true, true ) ) {
                            $item['name'] = $item['name'] . ' ' . str_replace(array("\r", "\n"), '', $meta) . '';
                        }

                        $item['name'] = strtr( $item['name'], self::unwanted_array() );

                        $basketxml .= '<item>';
                        $basketxml .= '<description>' . mb_substr( $item['name'], 0, 100 ) . '</description>' . "\r\n";
                        if ( $_product->get_sku() && mb_strlen( $_product->get_sku() ) <= 12 ) {
                            $basketxml .= '<productSku>' . $_product->get_sku() . '</productSku>' . "\r\n";
                        }
                        $basketxml .= '<quantity>' . $item['qty'] . '</quantity>' . "\r\n";

                        $basketxml .= '<unitNetAmount>' . number_format( $order->get_item_total( $item, false ), 2, '.', '' ) . '</unitNetAmount>' . "\r\n"; 
                        $basketxml .= '<unitTaxAmount>' . number_format( $order->get_item_tax( $item ), 2, '.', '' ) . '</unitTaxAmount>' . "\r\n"; 
                        $basketxml .= '<unitGrossAmount>' . number_format( $order->get_item_total( $item, true ), 2, '.', '' ) . '</unitGrossAmount>' . "\r\n";
                        $basketxml .= '<totalGrossAmount>' . number_format( $order->get_line_total( $item, true ), 2, '.', '' ) . '</totalGrossAmount>' . "\r\n";
                        $basketxml .= '</item>' . "\r\n";
                    }
                }
            }

            // Coupon Cost
            if ( self::wc_version() < '2.3' ) {
                // get_order_discount() deprecated in WC 2.3
                if( 0 != $order->get_order_discount() ) {
                    $discountxml = '<discounts>' . "\r\n";
                    $discountxml .= '<discount>' . "\r\n";
                    $discountxml .= '<fixed>' . number_format( $order->get_order_discount(), 2, '.', '' ) . '</fixed>' . "\r\n";
                    $discountxml .= '<description>' . $order->get_used_coupons() . '</description>' . "\r\n";
                    $discountxml .= '</discount>' . "\r\n"; 
                    $discountxml .= '</discounts>' . "\r\n";
                }
            } else {
                if( 0 != $order->get_total_discount() ) {
                    $discountxml = '<discounts>' . "\r\n";
                    $discountxml .= '<discount>' . "\r\n";
                    $discountxml .= '<fixed>' . number_format( $order->get_total_discount(), 2, '.', '' ) . '</fixed>' . "\r\n";
                    $discountxml .= '<description>' . $order->get_used_coupons() . '</description>' . "\r\n";
                    $discountxml .= '</discount>' . "\r\n"; 
                    $discountxml .= '</discounts>' . "\r\n";
                }
            }

            // Shipping costs
            $total_shipping = $pre_wc_30 ? $order->get_total_shipping() : $order->get_shipping_total();
            $shipping_tax   = $pre_wc_30 ? $order->order_shipping_tax : $order->get_shipping_tax();
            if ( $total_shipping ) {
                $shippingxml .= '<deliveryNetAmount>' . number_format( $total_shipping - $shipping_tax, 2, '.', '' ) . '</deliveryNetAmount>' . "\r\n";
                $shippingxml .= '<deliveryTaxAmount>' . number_format( $shipping_tax, 2, '.', '' ) . '</deliveryTaxAmount>' . "\r\n"; 
                $shippingxml .= '<deliveryGrossAmount>' . number_format( $total_shipping, 2, '.', '' ) . '</deliveryGrossAmount> ' . "\r\n";
            } 

            // Bulid the XML
            $xml  = '<basket>' . "\r\n";

            $xml .= $basketxml;

            $xml .= $shippingxml;

            $xml .= $discountxml;

            $xml .= '</basket>';

            $xml = strtr( $xml, self::unwanted_array() );
            
            if( mb_strlen( $xml ) > 20000 ) {
                $xml = NULL;
            }

            return $xml;

        }

        /**
         * Add selected card icons to payment method label, defaults to Visa/MC/Amex/Discover
         */
        public static function get_icon( $cardtypes, $sagelink, $sagelogo, $id) {

            $icon = '';

            if ( ! empty( $cardtypes ) ) {

                if ( get_option('woocommerce_force_ssl_checkout')=='no' ) {

                    // display icons for the selected card types
                    foreach ( $cardtypes as $card_type ) {
                        $icon .= '<img src="' . esc_url( SAGEPLUGINURL . 'assets/images/card-' . strtolower( str_replace(' ','-',$card_type) ) . '.png' ) . '" alt="' . esc_attr( strtolower( $card_type ) ) . '" />';
                    }

                } else {

                    // display icons for the selected card types
                    foreach ( $cardtypes as $card_type ) {
                        $icon .= '<img src="' . esc_url( WC_HTTPS::force_https_url( SAGEPLUGINURL ) . 'assets/images/card-' . strtolower( str_replace(' ','-',$card_type) ) . '.png' ) . '" alt="' . esc_attr( strtolower( $card_type ) ) . '" />';
                    }

                }

            } else {

                if ( get_option('woocommerce_force_ssl_checkout')=='no' ) {
                    // use icon provided by filter
                    $icon = '<img src="' . esc_url( SAGEPLUGINURL . 'assets/images/cards.png' ) . '" alt="' . __( 'Credit card logos', 'woocommerce_sagepayform' ) . '" />';        
                } else {
                    // use icon provided by filter
                    $icon = '<img src="' . esc_url( WC_HTTPS::force_https_url( SAGEPLUGINURL . 'assets/images/cards.png' ) ) . '" alt="' . __( 'Credit card logos', 'woocommerce_sagepayform' ) . '" />';     
                }

            }
            
            /**
             * Add SagePay link
             */
            if ( $sagelink == '1' && $sagelogo != '1' ) {
                $what_is_sagepay = sprintf( '<a href="%1$s" class="about_sagepayform" style="float: right; line-height: 12px; font-size: 0.83em;" onclick="javascript:window.open(\'%1$s\',\'What is SagePay\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700\'); return false;" title="' . esc_attr__( 'What is SagePay?', 'woocommerce_sagepayform' ) . '">' . esc_attr__( 'What is SagePay?', 'woocommerce_sagepayform' ) . '</a>', esc_url( self::link() ) );
            } else {
                $what_is_sagepay = '';
            }

            /**
             * Add SagePay logo
             */
            if ( $sagelogo == '1' ) {

                if( $sagelink == '1' ) {

                    if ( get_option('woocommerce_force_ssl_checkout')=='no' ) {
                        // use icon provided by filter
                        $icon = $icon . sprintf( '<a href="%1$s" onclick="javascript:window.open(\'%1$s\',\'What is SagePay\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700\'); return false;" title="' . esc_attr__( 'What is SagePay?', 'woocommerce_sagepayform' ) . '">' . '<img src="' . esc_url( SAGEPLUGINURL . 'assets/images/sagepaylogo.png' ) . '" alt="Payments By SagePay" />' . '</a>', esc_url( self::link() ) );        
                    } else {
                        // use icon provided by filter
                        $icon = $icon . sprintf( '<a href="%1$s" onclick="javascript:window.open(\'%1$s\',\'What is SagePay\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700\'); return false;" title="' . esc_attr__( 'What is SagePay?', 'woocommerce_sagepayform' ) . '">' . '<img src="' . esc_url( WC_HTTPS::force_https_url( SAGEPLUGINURL . 'assets/images/sagepaylogo.png' ) ) . '" alt="Payments By SagePay" />' . '</a>', esc_url( self::link() ) );       
                    }

                } else {

                    if ( get_option('woocommerce_force_ssl_checkout')=='no' ) {
                        // use icon provided by filter
                        $icon = $icon . '<img src="' . esc_url( SAGEPLUGINURL . 'assets/images/sagepaylogo.png' ) . '" alt="Payments By SagePay" style="float: right;"/>';           
                    } else {
                        // use icon provided by filter
                        $icon = $icon . '<img src="' . esc_url( WC_HTTPS::force_https_url( SAGEPLUGINURL . 'assets/images/sagepaylogo.png' ) ) . '" alt="Payments By SagePay" style="float: right;"/>';      
                    }

                }

            }

            return apply_filters( 'woocommerce_gateway_icon', $icon . $what_is_sagepay, $id );
        }

        /**
         * Wrapper around @see WC_Order::get_order_currency() for versions of WooCommerce prior to 2.1.
         */
        public static function get_order_currency( $order ) {

            if ( method_exists( $order, 'get_order_currency' ) ) {
                $order_currency = version_compare( WC_VERSION, '2.7', '<' ) ? $order->get_order_currency() : $order->get_currency();
            } else {
                $order_currency = get_woocommerce_currency();
            }

            return $order_currency;
        }

        /**
         * Build the $vendortxcode for Sage.
         * Max length is 40 characters
         * MUST be unique for each transaction
         * Uses $order->order_key, $order->id, $order->get_order_number() and date('is', time() ) (current minutes and seconds)
         * 
         * Send $order->order_key to match order when returning from Sage
         * Send $order->id to retrive order when returning from Sage
         * Send $order->get_order_number() for easier tracking in MySagePay 
         *
         * @return $vendortxcode
         */
        public static function build_vendortxcode( $order, $id, $prefix = 'wc_' ) {

            // WooCommerce 3.0 compatibility 
            $order_id   = is_callable( array( $order, 'get_id' ) ) ? $order->get_id() : $order->id;
            $order_key  = is_callable( array( $order, 'get_order_key' ) ) ? $order->get_order_key() : $order->order_key;
            $order_key  = str_replace( 'wc_', $prefix, $order_key );

            $vendortxcode =  $order_key . '-' . str_replace( '#' , '' , $order_id ) . '-';

            // if $order_id and $order->get_order_number() don't match then add $order->get_order_number()
            if( str_replace( '#' , '' , $order_id ) != $order->get_order_number() ) {
                $vendortxcode .= str_replace( '#' , '' , $order->get_order_number() ) . '-';
            }

            // Add minutes and seconds to the end of the $vendortxcode to make it unique.
            $vendortxcode .= date('is', time() );

            // Clean up the $vendortxcode for SAGE Line 50
            $vendortxcode = str_replace( 'order_', '', $vendortxcode );

            // Let sites filter $vendortxcode
            $vendortxcode = apply_filters( 'woocommerce_' . $id . '_vendortxcode', $vendortxcode, $order );

            /**
             * Replace everything that's not allowed!
             * A-Z,a-z,0-9,_,- 
             * see http://www.sagepay.co.uk/file/25041/download-document/FORM_Integration_and_Protocol_Guidelines_270815.pdf
             */
            $vendortxcode = preg_replace( '/[^0-9a-zA-Z_\-]/', "", $vendortxcode );

            // Make sure it's not over 40 characters
            if ( strlen($vendortxcode) > 40 ) {
                $vendortxcode = substr( $vendortxcode, 0, 40 );
            }

            return $vendortxcode;

        }

        /**
         * Add Invoice Number column to orders page in admin
         */
        function sage_manage_edit_shop_order_columns( $columns ) {
            add_filter( 'manage_edit-shop_order_columns', 'security_check_column_admin_init' );
        }

        /**
         * Add sage security responses to order rows
         */
        function security_check_admin_init( $column ) {
            global $post, $woocommerce, $the_order;

            if ( $column === 'sage_security' ) {

                if( '' !== get_post_meta( $post->ID, '_AddressResult', TRUE ) ) {

                    $AddressResult = get_post_meta( $post->ID, '_AddressResult', TRUE );

                    switch ( $AddressResult ) {
                        case 'MATCHED':
                            $addressclass = 'sagepay-ok';
                            break;
                        case 'NOTMATCHED':
                            $addressclass = 'sagepay-fail';
                            break;
                        case 'NOTPROVIDED':
                        case 'NOTCHECKED':
                            $addressclass = 'sagepay-check';
                            break;
                        default :
                            $addressclass = 'sagepay-ok';
                            break;
                    }

                    printf( '<span class="%s tips" data-tip="%s">%s</span>', $addressclass,__( 'Address check ', 'woocommerce_sagepayform' ) . strtolower( $AddressResult ), 'A' );

                }

                if( '' !== get_post_meta( $post->ID, '_PostCodeResult', TRUE ) ) {

                    $PostCodeResult = get_post_meta( $post->ID, '_PostCodeResult', TRUE );
                    switch ( $PostCodeResult ) {
                        case 'MATCHED':
                            $postcodeclass = 'sagepay-ok';
                            break;
                        case 'NOTMATCHED':
                            $postcodeclass = 'sagepay-fail';
                            break;
                        case 'NOTPROVIDED':
                        case 'NOTCHECKED':
                            $postcodeclass = 'sagepay-check';
                            break;
                        default :
                            $postcodeclass = 'sagepay-ok';
                            break;
                    }

                    printf( '<span class="%s tips" data-tip="%s">%s</span>', $postcodeclass,__( 'Postcode check ', 'woocommerce_sagepayform' ) . strtolower( $PostCodeResult ), 'P' );

                }

                if( '' !== get_post_meta( $post->ID, '_CV2Result', TRUE ) ) {

                    $CV2Result = get_post_meta( $post->ID, '_CV2Result', TRUE );
                    switch ( $CV2Result ) {
                        case 'MATCHED':
                            $cv2class = 'sagepay-ok';
                            break;
                        case 'NOTMATCHED':
                            $cv2class = 'sagepay-fail';
                            break;
                        case 'NOTPROVIDED':
                        case 'NOTCHECKED':
                            $cv2class = 'sagepay-check';
                            break;
                        default :
                            $cv2class = 'sagepay-ok';
                            break;

                    }

                    printf( '<span class="%s tips" data-tip="%s">%s</span>', $cv2class,__( 'CV2 check ', 'woocommerce_sagepayform' ) . strtolower( $CV2Result ), 'C' );

                } 

                if( '' !== get_post_meta( $post->ID, '_3DSecureStatus', TRUE ) ) {

                    $SecureStatus = get_post_meta( $post->ID, '_3DSecureStatus', TRUE );
                    switch ( $SecureStatus ) {
                        case 'OK':
                            $secureclass = 'sagepay-ok';
                            break;
                        case 'NOTAUTHED':
                            $secureclass = 'sagepay-fail';
                            break;
                        case 'INCOMPLETE':
                        case 'NOTCHECKED':
                        case 'ERROR': 
                        case 'ATTEMPTONLY': 
                        case 'NOAUTH': 
                        case 'CANTAUTH': 
                        case 'MALFORMED': 
                        case 'INVALID':
                            $secureclass = 'sagepay-check';
                            break;
                        default :
                            $secureclass = 'sagepay-ok';
                            break;
                    }

                    printf( '<span class="%s tips" data-tip="%s">%s</span>', $secureclass,__( '3D secure check ', 'woocommerce_sagepayform' ) . strtolower( $SecureStatus ), '3' );

                }   

            }

        }

        /**
         * Load admin CSS
         * @param  [type] $hook [description]
         * @return void
         */
        function admin_scripts( $hook ) {

            wp_enqueue_style( 'sagepay-admin-wp', SAGEPLUGINURL.'assets/css/admin-css.css' , array(), $this->version );

        }

    }

    $WC_Sagepay_Common_Functions = new WC_Sagepay_Common_Functions();

    function security_check_column_admin_init( $columns ) {
        global $woocommerce;
                
        $columns["sage_security"] = __( 'Checks', 'woocommerce_sagepayform' );
                
        return $columns;

    }