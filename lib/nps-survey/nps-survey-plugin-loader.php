<?php
/**
 * Plugin Loader.
 *
 * @package {{package}}
 * @since {{since}}
 */

namespace NPS_Survey;

/**
 * Plugin_Loader
 *
 * @since X.X.X
 */
class NPS_Survey_Plugin_Loader {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class Instance.
	 * @since X.X.X
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since X.X.X
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * @since X.X.X
	 */
	public function __construct() {

		spl_autoload_register( array( $this, 'autoload' ) );
		add_action( 'wp_loaded', array( $this, 'load_files' ) );
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class class name.
	 * @return void
	 */
	public function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$class_to_load = $class;

		$filename = strtolower(
			strval(
				preg_replace(
					array( '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ),
					array( '', '$1-$2', '-', DIRECTORY_SEPARATOR ),
					$class_to_load
				)
			)
		);

		$file = NPS_SURVEY_DIR . $filename . '.php';

		// if the file redable, include it.
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Load Files
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function load_files() {
		require_once NPS_SURVEY_DIR . 'classes/nps-survey-script.php';
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
NPS_Survey_Plugin_Loader::get_instance();
