<?php

/**
 * Plugin Name: Re-Order - CSS and JS Loading Order
 * Plugin URI: https://www.html5andbeyond.com/
 * Description: Change the loading order of CSS and JS files used by themes and plugins
 * Version: 0.3
 * Author: HTML5andBeyond
 * Author URI: https://www.html5andbeyond.com/
 * License: GPLv2 or Higher
 */

if ( ! defined( 'ABSPATH' ) ) exit;

	define( 'H5AB_REORDER_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
	define('H5AB_REORDER_PLUGIN_URL', plugin_dir_url( __FILE__ ));

    $default_enqueued_array = array( 'WP_Scripts' => array(), 'WP_Styles' => array() );
    global $default_enqueued_array;

	if(!class_exists('H5AB_REORDER')) {

			class H5AB_REORDER {

				public function __construct() {

                    add_action( 'admin_menu', array($this, 'add_menu') );

                    add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts'), 1 );
                    add_action( 'wp_enqueue_scripts', array($this, 'admin_scripts_front'), 1 );

                    add_action( 'wp_ajax_ajax_order', array($this, 'update_reorder_settings') );
					
					add_action( 'wp_ajax_get_order_data', array($this, 'get_order_data') );

                    add_action( 'wp_enqueue_scripts', array($this, 'list_and_dequeue'), 100 );
                    add_action( 'wp_enqueue_scripts', array($this, 'load_re_ordered_css_js'), 100 );

				}

                public function add_menu() {

					add_menu_page('Re-Order', 'Re-Order','administrator', 'h5ab-re-order-settings',
					array($this, 'plugin_settings_page'), H5AB_REORDER_PLUGIN_URL . 'images/icon.png');

				}

                public function plugin_settings_page() {

					if(!current_user_can('administrator')) {
						  wp_die('You do not have sufficient permissions to access this page.');
					}

                    include_once(sprintf("%s/templates/h5ab-reorder-settings.php", H5AB_REORDER_PLUGIN_DIR));

				}

                public function admin_scripts() {

                    wp_enqueue_style( 'reorder-admin-styles', H5AB_REORDER_PLUGIN_URL . 'css/h5ab-reorder-admin.css' );

                    wp_enqueue_script( 'jquery-ui-core' );
                    wp_enqueue_script( 'jquery-ui-sortable' );

                    wp_register_script( 'reorder-ajax', H5AB_REORDER_PLUGIN_URL . 'js/reorder-admin.js', array('jquery', 'jquery-ui-core') );
                    wp_enqueue_script( 'reorder-ajax' );
					wp_localize_script( 'reorder-ajax', 'ajax_object', array('ajax_url' => admin_url( 'admin-ajax.php' )) );

                }

                public function admin_scripts_front() {

                    wp_register_script( 'move-script-tags', H5AB_REORDER_PLUGIN_URL . 'js/move-scripts.js', array('jquery', 'jquery-ui-core') );
                    wp_enqueue_script( 'move-script-tags' );

                }

                public function update_reorder_settings() {

                    $new_js_order = array();
                    $new_css_order = array();
					$ajaxResponse = array('jQueryScripts' => array());
					
					$jquery_array = array('jquery-core','jquery-migrate','jquery');
					$jquery_lock = sanitize_text_field($_POST['jquery_lock']);
                    $footer_script = sanitize_text_field($_POST['js_foot']);

                    if (isset($_POST['js_order'])) 
					{	 
					    $js_order = $_POST['js_order'];
						$js_store_order = $js_order;
						
					    //If jQuery lock has been activated, ensure the scripts are first in the array
					   //before saving in the database
					   if (!empty($jquery_lock) && $jquery_lock == 'true') {
                             foreach($jquery_array as $jquery_script) {
                                 $key = array_search($jquery_script, $js_order);
								 if($key !== false) unset($js_order[$key]);
                            }
						     
							 $ajaxResponse['jQueryScripts'] = $jquery_array;
							 $js_store_order = array_unique(array_merge($jquery_array, $js_order));
					   }
					   
                        foreach($js_store_order as $key => $value) {
                            array_push($new_js_order, stripslashes(sanitize_text_field($value)));
                       }

                 }
               
                 if (isset($_POST['css_order']))
				 {
                        $css_order = $_POST['css_order'];

                        foreach($css_order as $key => $value) {
                            array_push($new_css_order, stripslashes(sanitize_text_field($value)));
                        }

                  }

					$updateTasks = array('h5abReOrder' => $new_js_order, 'h5abReOrderCSS' => $new_css_order, 'h5abReOrderjQuery' => $jquery_lock, 'h5abReOrderJSMove' => $footer_script);
					
					 //If any of these are true, return success ,if all are false, return failure
					 foreach($updateTasks as $key =>$value) {
					     $result = update_option($key, $value);
						  if($result) $success = true;
					 }

					 $response = ($success)? 1: 0;

				     $class = ($response) ? 'h5ab-feedback updated' : 'h5ab-feedback error';
					 $message = ($response) ? 'Settings updated successfully' : 'Settings could not be updated';
					 $feedback = "<div class='{$class}'><p>". $message. "</p></div>";
					 //Send the feedback and the new JS script order to the JavaScript
					 $ajaxResponse['feedback'] = $feedback;
					 $ajaxResponse['newJSOrder'] = $js_order;
                     echo json_encode($ajaxResponse, true);
					 
                     die();

                }

                public function ajax_update() {

                    delete_option( 'h5abReOrder' );
                    delete_option( 'h5abReOrderCSS' );

                }
				
				public function get_order_data() {
				       $scripts_order = get_option('h5abReOrder');
					   $styles_order = get_option('h5abReOrderCSS');
					   $list_order_data = array("scripts" => $scripts_order, "styles" => $styles_order);
					   echo (json_encode($list_order_data));
					   die();
				}

                public function list_and_dequeue() {

                    global $wp_scripts, $wp_styles, $default_enqueued_array;

                    /***************************************************
                    JavaScript
                    ***************************************************/
					if(isset($_GET['h5ab-reset-scripts']) && $_GET['h5ab-reset-scripts'] == 1){
					        $this->ajax_update();
					}
					
                    if ( ! empty( $wp_scripts ) ) {
                        $scripts = wp_clone( $wp_scripts );
                        $scripts->done = array();
                        $scripts->to_do = array();
                        $scripts->groups = array();
                        $queue = array_unique( array_merge( array_keys( $default_enqueued_array['WP_Scripts'] ), $scripts->queue ) );
                        $scripts->all_deps( $queue );
                        $get_scripts = $scripts->to_do;
                    }

                    $get_scripts_bu = $get_scripts;
                    $scriptArray = get_option('h5abReOrder');
                    $jquery_lock = get_option('h5abReOrderjQuery');
					
					$jquery_array = array('jquery-core','jquery-migrate','jquery');

                    if ($jquery_lock == 'true') {

                        foreach($jquery_array as $key => $value) {

                            if (($key = array_search($value, $get_scripts_bu)) !== false) {
                                unset($get_scripts_bu[$key]);
                            }

                        }

                    }
                    if (!empty($scriptArray)) {
					  
                        $db_scripts = $scriptArray;
                        $merge_script = array_unique(array_merge($db_scripts, $get_scripts_bu));

                        if ($jquery_lock == 'true') {
                   
                            $jquery_merge = array_unique(array_merge($jquery_array, $merge_script));
                            update_option('h5abReOrder', $jquery_merge);
                        } else {
                            update_option('h5abReOrder', $merge_script);
                        }

                    }

                    //var_dump($merge_scripts);
                    //var_dump($get_scripts_bu);

                    if (empty($scriptArray) || sizeof($scriptArray) == 1) {
                        update_option( 'h5abReOrder', $get_scripts_bu );
                    }

                    foreach ($get_scripts as $key => $value) {
                        wp_dequeue_script( $value );
                    }

                    /***************************************************
                    CSS
                    ***************************************************/

                    if ( ! empty( $wp_styles ) ) {
                        $styles = wp_clone( $wp_styles );
                        $styles->done = array();
                        $styles->to_do = array();
                        $styles->groups = array();
                        $queue = array_unique( array_merge( array_keys( $default_enqueued_array['WP_Styles'] ), $styles->queue ) );
                        $styles->all_deps( $queue );
                        $get_styles = $styles->to_do;
                    }

                    $get_styles_bu = $get_styles;
                    $styleArray = get_option('h5abReOrderCSS');

                    if (!empty($styleArray)) {
                        $db_styles = $styleArray;
                        $merge_styles = array_unique(array_merge($db_styles, $get_styles_bu));
                        update_option('h5abReOrderCSS', $merge_styles);
                    }

                    if (empty($styleArray) || sizeof($styleArray) == 1) {
                        update_option( 'h5abReOrderCSS', $get_styles_bu );
                    }

                    foreach ($get_styles as $key => $value) {
                        wp_dequeue_style( $value );
                    }

                }

                public function load_re_ordered_css_js() {

                    $jquery_footer = get_option('h5abReOrderJSMove');

                    if ($jquery_footer == 'true') {

                        remove_action('wp_head', 'wp_print_scripts');
                        remove_action('wp_head', 'wp_print_head_scripts', 9);
                        remove_action('wp_head', 'wp_enqueue_scripts', 1);
                        add_action('wp_footer', 'wp_print_scripts', 5);
                        add_action('wp_footer', 'wp_enqueue_scripts', 5);
                        add_action('wp_footer', 'wp_print_head_scripts', 5);

                    }

                    $scriptArray = get_option('h5abReOrder');

                    foreach ($scriptArray as $key => $value) {
                        wp_enqueue_script( esc_attr($value) );
                    }

                    $styleArray = get_option('h5abReOrderCSS');

                    foreach ($styleArray as $key => $value) {
                        wp_enqueue_style( esc_attr($value) );
                    }

                }
                
                public static function deactivate() {
                    delete_option( 'h5abReOrder' );
                    delete_option( 'h5abReOrderCSS' );
                    delete_option( 'h5abReOrderjQuery' );
                    delete_option( 'h5abReOrderJSMove' );
                }


        }

	}

	if(class_exists('H5AB_REORDER')) {
        register_deactivation_hook( __FILE__, array('H5AB_REORDER' , 'deactivate'));
		$H5AB_REORDER = new H5AB_REORDER();
	}

?>
