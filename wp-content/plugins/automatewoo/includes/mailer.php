<?php

namespace AutomateWoo;

/**
 * @class Mailer
 */
class Mailer {

	/** @var string */
	public $email;

	/** @var string */
	public $template;

	/** @var string */
	public $heading;

	/** @var string */
	public $content;

	/** @var string */
	public $subject;

	/** @var string */
	public $from_name;

	/** @var string */
	public $from_email;

	/** @var array */
	public $attachments = [];

	/** @var string */
	public $email_type = 'html';

	/** @var string */
	public $extra_footer_text;

	/** @var string */
	public $tracking_pixel_url;

	/** @var callable - use to replace URLs in content e.g. for click tracking */
	public $replace_content_urls_callback;


	/**
	 * @param $subject
	 * @param $email
	 * @param $content
	 * @param string $template
	 */
	function __construct( $subject, $email, $content, $template = 'default' ) {

		$this->email = $email;
		$this->subject = $subject;
		$this->content = $content;
		$this->template = $template;
		$this->from_email = Emails::get_from_address( $this->template );
		$this->from_name = Emails::get_from_name( $this->template );

		// include css inliner
		if ( ! class_exists( 'AW_Emogrifier' ) && class_exists( 'DOMDocument' ) ) {
			include_once AW()->lib_path( '/emogrifier/emogrifier.php' );
		}

		// also include the WC packaged emogrifier incase other plugins are looking for this e.g. YITH email customizer
		if ( ! class_exists( 'Emogrifier' ) && class_exists( 'DOMDocument' ) ) {
			include_once( WC()->plugin_path() . '/includes/libraries/class-emogrifier.php' );
		}
	}


	/**
	 * @param $heading
	 */
	function set_heading( $heading ) {
		$this->heading = $heading;
	}


	/**
	 * @return string
	 */
	function get_from_email() {
		return $this->from_email;
	}


	/**
	 * @return string
	 */
	function get_from_name() {
		return $this->from_name;
	}


	/**
	 * @return true|\WP_Error
	 */
	function validate_recipient_email() {

		if ( ! $this->email ) {
			return new \WP_Error( 'email_blank', __( 'The email address is blank.', 'automatewoo' ) );
		}

		if ( ! is_email( $this->email ) ) {
			return new \WP_Error( 'email_invalid', sprintf(__( "'%s' is not a valid email address.", 'automatewoo' ), $this->email ) );
		}

		return true;
	}


	/**
	 * @return true|\WP_Error
	 */
	function send() {

		$validate_email = $this->validate_recipient_email();

		if ( is_wp_error( $validate_email ) ) {
			return $validate_email;
		}

		do_action( 'automatewoo/email/before_send', $this );

		add_filter( 'wp_mail_from', [ $this, 'get_from_email' ] );
		add_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ] );
		add_filter( 'wp_mail_content_type', [ $this, 'get_content_type' ] );
		add_action( 'wp_mail_failed', [ $this, 'log_wp_mail_errors' ] );

		$sent = wp_mail(
			$this->email,
			$this->subject,
			$this->get_html(),
			"Content-Type: " . $this->get_content_type() . "\r\n",
			$this->attachments
		);

		remove_filter( 'wp_mail_from', [ $this, 'get_from_email' ] );
		remove_filter( 'wp_mail_from_name', [ $this, 'get_from_name' ] );
		remove_filter( 'wp_mail_content_type', [ $this, 'get_content_type' ] );
		remove_action( 'wp_mail_failed', [ $this, 'log_wp_mail_errors' ] );

		if ( $sent === false ) {

			global $phpmailer;

			if ( $phpmailer && is_array( $phpmailer->ErrorInfo ) && ! empty( $phpmailer->ErrorInfo ) ) {

				$error = current( $phpmailer->ErrorInfo );
				return new \WP_Error( 4, sprintf( __( 'PHP Mailer - %s', 'automatewoo' ), is_object( $error ) ? $error->message : $error ) );
			}

			return new \WP_Error( 5, __( 'The wp_mail() function returned false.', 'automatewoo' ) );
		}

		return $sent;
	}


	/**
	 * @return string
	 */
	function get_html() {
		return apply_filters( 'woocommerce_mail_content', $this->style_inline( $this->get_raw_html() ) );
	}


	/**
	 * Returns html without CSS inline
	 *
	 * @return string
	 */
	function get_raw_html() {

		add_filter( 'woocommerce_email_footer_text', [ $this, 'add_extra_footer_text' ] );

		$this->prepare_content();

		// Buffer
		ob_start();

		$this->get_template_part( 'email-header.php', [
			'email_heading' => $this->heading
		] );

		echo $this->content;

		$this->get_template_part( 'email-footer.php' );

		$html = ob_get_clean();

		remove_filter( 'woocommerce_email_footer_text', [ $this, 'add_extra_footer_text' ] );

		return $html;
	}


	/**
	 * Prepare mailer content
	 */
	function prepare_content() {

		// Remove instances of links with a double 'http://'
		$this->content = str_replace( '"http://http://', '"http://', $this->content );
		$this->content = str_replace( '"https://https://', '"https://', $this->content );
		$this->content = str_replace( '"http://https://', '"https://', $this->content );
		$this->content = str_replace( '"https://http://', '"http://', $this->content );

		$this->content = $this->replace_urls_in_content( $this->content );
		$this->content = $this->add_tracking_pixel_to_content( $this->content );

		// pass through content filters to convert short codes etc
		// IMPORTANT do this after URLs are modified so entities are not encoded
		$this->content = apply_filters( 'automatewoo_email_content', $this->content );
	}


	/**
	 * Apply inline styles to dynamic content.
	 *
	 * @param string|null $content
	 * @return string
	 */
	function style_inline( $content ) {
		if ( ! class_exists( 'DOMDocument' ) ) return $content;

		ob_start();
		aw_get_template( 'email/styles.php' );
		$this->get_template_part( 'email-styles.php' );
		$css = apply_filters( 'woocommerce_email_styles', ob_get_clean() );
		$css = apply_filters( 'automatewoo/mailer/styles', $css, $this );

		try {
			$emogrifier = new \AW_Emogrifier( $content, $css );
			$emogrifier->disableStyleBlocksParsing();
			$content = $emogrifier->emogrify();
		}
		catch ( \Exception $e ) {
			$logger = new \WC_Logger();
			$logger->add( 'emogrifier', $e->getMessage() );
		}

		return $content;
	}


	/**
	 * @param $text
	 * @return string
	 */
	function add_extra_footer_text( $text ) {

		if ( ! $this->extra_footer_text )
			return $text;

		// add separator if there is footer text
		if ( trim( $text ) ) {
			$text .= apply_filters( 'automatewoo_email_footer_separator',  ' - ' );
		}

		$text .= $this->extra_footer_text;

		return $text;
	}


	/**
	 * @param $file
	 * @param array $args
	 */
	function get_template_part( $file, $args = [] ) {

		if ( $args && is_array( $args ) ) {
			extract( $args );
		}

		switch( $this->template ) {

			// default is the woocommerce template
			case 'default':
				$template_name = 'emails/' . $file;
				$template_path = '';
				break;

			case 'plain':
				// plain templates are not
				return aw_get_template('email/plain/' . $file, $args );
				break;

			default:
				$template_name = $file;
				$template_path = 'automatewoo/custom-email-templates/'. $this->template;
				break;
		}

		$located = wc_locate_template( $template_name, $template_path );

		// if using woo default, apply filters to support email customizer plugins
		if ( $this->template === 'default' ) {
			$located = apply_filters( 'wc_get_template', $located, $template_name, $args, $template_path, '' );

			do_action( 'woocommerce_before_template_part', $template_name, $template_path, $located, $args );

			include( $located );

			do_action( 'woocommerce_after_template_part', $template_name, $template_path, $located, $args );
		}
		else {
			include( $located );
		}
	}



	/**
	 * @param $content string
	 *
	 * @return string
	 */
	function replace_urls_in_content( $content ) {

		if ( ! $this->replace_content_urls_callback ) {
			return $content;
		}

		$replacer = new Replace_Helper( $content, $this->replace_content_urls_callback, 'href_urls' );
		return $replacer->process();
	}


	/**
	 * @param $content
	 * @return string
	 */
	function add_tracking_pixel_to_content( $content ) {

		if ( $this->tracking_pixel_url ) {
			$content = $content . '<img src="' . esc_url( $this->tracking_pixel_url ) . '" height="1" width="1" alt="" style="display:inline">';
		}

		return $content;
	}


	/**
	 * @return string
	 */
	function get_email_type() {
		return $this->email_type && class_exists( 'DOMDocument' ) ? $this->email_type : 'plain';
	}


	/**
	 * @return string
	 */
	function get_content_type() {
		switch ( $this->get_email_type() ) {
			case 'html' :
				return 'text/html';
			case 'multipart' :
				return 'multipart/alternative';
			default :
				return 'text/plain';
		}
	}


	/**
	 * @param $error \WP_Error
	 */
	function log_wp_mail_errors( $error ) {
		$log = new \WC_Logger();
		$log->add( 'automatewoo-wp-mail', $error->get_error_message() );
	}

}
