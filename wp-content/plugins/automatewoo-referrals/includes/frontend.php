<?php

namespace AutomateWoo\Referrals;

use AutomateWoo\Compat;

/**
 * @class Frontend
 */
class Frontend {

	/** @var Advocate */
	private $current_advocate;


	/**
	 * Constructor
	 */
	function __construct() {

		if ( AW()->is_request( 'frontend' ) ) {
			add_shortcode( 'automatewoo_referrals_page', [ $this, 'get_share_page_html'] );
			add_shortcode( 'automatewoo_referrals_share_widget', [ $this, 'shortcode_share_widget'] );
			add_shortcode( 'automatewoo_advocate_referral_link', [ $this, 'shortcode_advocate_referral_link' ] );
			add_shortcode( 'automatewoo_advocate_referral_coupon', [ $this, 'shortcode_advocate_referral_coupon'] );
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
		add_action( 'wp_loaded', [ $this, 'maybe_handle_share_form' ] );
		add_action( 'woocommerce_email_footer', [ $this, 'maybe_display_widget_on_order_emails' ], 8 );

		add_filter( 'automatewoo/mailer/styles', [ $this, 'inject_email_styles' ] );
		add_filter( 'woocommerce_email_styles', [ $this, 'inject_email_styles' ] );
		add_filter( 'storefront_customizer_css', [ $this, 'storefront_css' ] );

		if ( AW_Referrals()->options()->enabled && AW_Referrals()->options()->widget_on_order_confirmed ) {

			switch ( (string) AW_Referrals()->options()->widget_on_order_confirmed ) {
				case 'top':
					add_action( 'woocommerce_before_template_part', [ $this, 'display_share_widget_before_thankyou' ], 10, 4 );
					break;

				case 'bottom':
					add_action( 'woocommerce_thankyou', [ $this, 'display_share_widget_after_thankyou' ], 20 );
					break;
			}
		}


		if ( AW()->is_request('frontend') ) {
			add_action( 'template_redirect', [ $this, 'maybe_prevent_caching' ] );
		}
	}


	/**
	 *
	 */
	function maybe_prevent_caching() {
		if ( $this->is_share_page() ) {
			$this->nocache();
		}
	}


	/**
	 * Register js and css
	 */
	function register_scripts() {

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		if ( AW_Referrals()->options()->type === 'link' ) {
			wp_register_script( 'jquery-cookie', WC()->plugin_url() . '/assets/js/jquery-cookie/jquery.cookie.js', [ 'jquery' ], '1.4.1' );
			$dependencies = [ 'jquery-cookie' ];
		}
		else {
			$dependencies = [ 'jquery' ];
		}

		wp_register_script( 'automatewoo-referrals', AW_Referrals()->url( "/assets/js/automatewoo-referrals$suffix.js" ), $dependencies, AW_Referrals()->version, true );
		wp_register_style( 'automatewoo-referrals', AW_Referrals()->url( '/assets/css/automatewoo-referrals.css' ), [], AW_Referrals()->version );

		wp_localize_script( 'automatewoo-referrals', 'automatewooReferralsLocalizeScript', [
			'is_link_based' => AW_Referrals()->options()->type === 'link',
			'link_param' => AW_Referrals()->options()->share_link_parameter,
			'cookie_expires' => apply_filters( 'automatewoo/referrals/link_cookie_expires', 365 )
		] );

		if ( AW_Referrals()->options()->type === 'link' || is_checkout() || is_account_page() || $this->is_share_page() ) {
			$this->enqueue_scripts();
		}
	}


	/**
	 * Enqueue js and css
	 */
	function enqueue_scripts() {
		wp_enqueue_script( 'automatewoo-referrals' );
		wp_enqueue_style( 'automatewoo-referrals' );
	}



	/**
	 * @return string
	 */
	function get_share_page_html() {

		if ( AW_Referrals()->options()->enabled ) {
			ob_start();

			$this->enqueue_scripts();

			AW_Referrals()->get_template( 'share-page.php', [
				'advocate' => $this->get_current_advocate(),
				'enable_facebook_share' => AW_Referrals()->options()->enable_facebook_share,
				'enable_twitter_share' => AW_Referrals()->options()->enable_twitter_share,
				'enable_email_share' => AW_Referrals()->options()->enable_email_share,
			]);

			return ob_get_clean();
		}
		else {
			return '<p><strong>' . __( 'Referrals are currently disabled.', 'automatewoo-referrals') . '</strong></p>';
		}
	}


	/**
	 * @param $template_name
	 * @param $template_path
	 * @param $located
	 * @param $args
	 */
	function display_share_widget_before_thankyou( $template_name, $template_path, $located, $args ) {
		if ( $template_name !== 'checkout/thankyou.php' )
			return;

		$order = isset( $args['order'] ) ? $args['order'] : false;

		if ( ! $order || $order->has_status( 'failed' ) )
			return;

		echo $this->get_share_widget( 'thankyou-top', Compat\Order::get_id( $order ) );
	}


	/**
	 * @param $order_id
	 */
	function display_share_widget_after_thankyou( $order_id ) {
		echo $this->get_share_widget( 'thankyou-bottom', $order_id );
	}


	/**
	 * @return string
	 */
	function shortcode_share_widget() {
		return $this->get_share_widget( 'shortcode' );
	}


	/**
	 * @param string $position
	 * @param $order_id
	 * @return string|false
	 */
	function get_share_widget( $position = '', $order_id = 0 ) {

		if ( ! AW_Referrals()->options()->enabled )
			return false;

		if ( ! apply_filters( 'automatewoo/referrals/show_share_widget', true, $position, $order_id, $this->get_current_advocate() ) )
			return false;

		$this->enqueue_scripts();

		ob_start();

		AW_Referrals()->get_template( 'share-widget.php', [
			'advocate' => $this->get_current_advocate(),
			'widget_heading' => AW_Referrals()->options()->widget_heading,
			'widget_text' => AW_Referrals()->options()->widget_text,
			'enable_facebook_share' => AW_Referrals()->options()->enable_facebook_share,
			'enable_twitter_share' => AW_Referrals()->options()->enable_twitter_share,
			'enable_email_share' => AW_Referrals()->options()->enable_email_share,
			'position' => $position
		]);

		return ob_get_clean();
	}


	/**
	 * @param \WC_Email $email
	 */
	function maybe_display_widget_on_order_emails( $email ) {

		if ( ! AW_Referrals()->options()->enabled || ! AW_Referrals()->options()->widget_on_order_emails ) {
			return;
		}

		if ( ! in_array( $email->id, [ 'customer_processing_order', 'customer_completed_order' ] ) ) {
			return;
		}

		/** @var \WC_Order $order */
		$order = $email->object;

		if ( $user_id = $order->get_user_id() ) {
			$advocate = new Advocate( $user_id );
		}
		else {
			$advocate = false;
		}

		$this->output_email_share_widget( $advocate );
	}


	/**
	 * @param Advocate|bool $advocate
	 */
	function output_email_share_widget( $advocate = false ) {

		if ( ! AW_Referrals()->options()->enabled )
			return;

		AW_Referrals()->get_template( 'share-widget-email.php', [
			'advocate' => $advocate,
			'widget_heading' => AW_Referrals()->options()->widget_heading,
			'widget_text' => AW_Referrals()->options()->widget_text,
			'enable_facebook_share' => AW_Referrals()->options()->enable_facebook_share,
			'enable_twitter_share' => AW_Referrals()->options()->enable_twitter_share,
			'enable_email_share' => AW_Referrals()->options()->enable_email_share,
		]);
	}


	function shortcode_advocate_referral_link() {
		if ( $advocate = $this->get_current_advocate() ) {
			return esc_url( $advocate->get_shareable_link() );
		}
	}


	function shortcode_advocate_referral_coupon() {
		if ( $advocate = $this->get_current_advocate() ) {
			return esc_attr( $advocate->get_shareable_coupon() );
		}
	}


	function maybe_handle_share_form() {

		if ( aw_request( 'action' ) != 'aw-referrals-email-share' ) {
			return;
		}

		$handler = new Invite_Form_Handler();
		$handler->handle();
		$handler->set_response_notices();
	}


	/**
	 * Ensure the current user is set up as an advocate
	 * @return false|Advocate
	 */
	function get_current_advocate() {

		if ( ! isset( $this->current_advocate ) ) {
			$this->current_advocate = $this->load_current_advocate();
		}

		return $this->current_advocate;
	}


	/**
	 * @return false|Advocate
	 */
	function load_current_advocate() {

		$advocate = new Advocate( get_current_user_id() );

		if ( ! $advocate->exists ) {
			return false;
		}

		if ( AW_Referrals()->options()->advocate_must_paying_customer ) {
			if ( ! $advocate->is_paying_customer() ) {
				return false;
			}
		}

		$advocate->store_ip();

		return $advocate;
	}


	/**
	 * Set nocache constants and headers.
	 */
	function nocache() {

		if ( ! defined( 'DONOTCACHEPAGE' ) ) {
			define( "DONOTCACHEPAGE", true );
		}
		if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
			define( "DONOTCACHEOBJECT", true );
		}
		if ( ! defined( 'DONOTCACHEDB' ) ) {
			define( "DONOTCACHEDB", true );
		}
		nocache_headers();
	}


	/**
	 * @param $styles
	 * @return string
	 */
	function inject_email_styles( $styles ) {
		ob_start();
		AW_Referrals()->get_template( 'email-styles.php' );
		$styles .= ob_get_clean();
		return $styles;
	}


	/**
	 * Add an account area icon if using storefront
	 */
	function storefront_css( $css ) {
		$css .= '
			.woocommerce-MyAccount-navigation ul li.woocommerce-MyAccount-navigation-link--referrals a:before {
				content: "\f0a1";
			}
		';

		return $css;
	}


	/**
	 * @return bool
	 */
	function is_share_page() {
		return AW_Referrals()->options()->referrals_page && is_page( AW_Referrals()->options()->referrals_page );
	}

}
