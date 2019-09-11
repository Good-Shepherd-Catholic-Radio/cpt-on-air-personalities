<?php
/**
 * Plugin Name: CPT On-Air Personalities
 * Plugin URI: https://github.com/Good-Shepherd-Catholic-Radio/cpt-on-air-personalities
 * Description: Creates the "On-Air Personalities" Custom Post Type
 * Version: 1.0.0
 * Text Domain: gscr-cpt-on-air-personalities
 * Author: Eric Defore
 * Author URI: http://realbigmarketing.com/
 * Contributors: d4mation
 * GitHub Plugin URI: Good-Shepherd-Catholic-Radio/cpt-on-air-personalities
 * GitHub Branch: events-rewrite
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'GSCR_CPT_On_Air_Personalities' ) ) {

	/**
	 * Main GSCR_CPT_On_Air_Personalities class
	 *
	 * @since	  1.0.0
	 */
	class GSCR_CPT_On_Air_Personalities {
		
		/**
		 * @var			GSCR_CPT_On_Air_Personalities $plugin_data Holds Plugin Header Info
		 * @since		1.0.0
		 */
		public $plugin_data;
		
		/**
		 * @var			GSCR_CPT_On_Air_Personalities $admin_errors Stores all our Admin Errors to fire at once
		 * @since		1.0.0
		 */
		private $admin_errors;
		
		/**
		 * @var			GSCR_CPT_On_Air_Personalities $cpt Holds the CPT
		 * @since		1.0.0
		 */
		public $cpt;

		/**
		 * Get active instance
		 *
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  object self::$instance The one true GSCR_CPT_On_Air_Personalities
		 */
		public static function instance() {
			
			static $instance = null;
			
			if ( null === $instance ) {
				$instance = new static();
			}
			
			return $instance;

		}
		
		protected function __construct() {
			
			$this->setup_constants();
			$this->load_textdomain();
			
			if ( version_compare( get_bloginfo( 'version' ), '4.4' ) < 0 ) {
				
				$this->admin_errors[] = sprintf( _x( '%s requires v%s of %s or higher to be installed!', 'Outdated Dependency Error', 'gscr-cpt-on-air-personalities' ), '<strong>' . $this->plugin_data['Name'] . '</strong>', '4.4', '<a href="' . admin_url( 'update-core.php' ) . '"><strong>WordPress</strong></a>' );
				
				if ( ! has_action( 'admin_notices', array( $this, 'admin_errors' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_errors' ) );
				}
				
				return false;
				
			}
			
			if ( ! class_exists( 'RBM_CPTS' ) ||
				! class_exists( 'RBM_FieldHelpers' ) ) {
				
				$this->admin_errors[] = sprintf( _x( 'To use the %s Plugin, both %s and %s must be active as either a Plugin or a Must Use Plugin!', 'Missing Dependency Error', 'gscr-cpt-on-air-personalities' ), '<strong>' . $this->plugin_data['Name'] . '</strong>', '<a href="//github.com/realbig/rbm-field-helpers/" target="_blank">' . __( 'RBM Field Helpers', 'gscr-cpt-on-air-personalities' ) . '</a>', '<a href="//github.com/realbig/rbm-cpts/" target="_blank">' . __( 'RBM Custom Post Types', 'gscr-cpt-on-air-personalities' ) . '</a>' );
				
				if ( ! has_action( 'admin_notices', array( $this, 'admin_errors' ) ) ) {
					add_action( 'admin_notices', array( $this, 'admin_errors' ) );
				}
				
				return false;
				
			}
			
			$this->require_necessities();
			
			// Register our CSS/JS for the whole plugin
			add_action( 'init', array( $this, 'register_scripts' ) );
			
		}

		/**
		 * Setup plugin constants
		 *
		 * @access	  private
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function setup_constants() {
			
			// WP Loads things so weird. I really want this function.
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			
			// Only call this once, accessible always
			$this->plugin_data = get_plugin_data( __FILE__ );

			if ( ! defined( 'GSCR_CPT_On_Air_Personalities_VER' ) ) {
				// Plugin version
				define( 'GSCR_CPT_On_Air_Personalities_VER', $this->plugin_data['Version'] );
			}

			if ( ! defined( 'GSCR_CPT_On_Air_Personalities_DIR' ) ) {
				// Plugin path
				define( 'GSCR_CPT_On_Air_Personalities_DIR', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'GSCR_CPT_On_Air_Personalities_URL' ) ) {
				// Plugin URL
				define( 'GSCR_CPT_On_Air_Personalities_URL', plugin_dir_url( __FILE__ ) );
			}
			
			if ( ! defined( 'GSCR_CPT_On_Air_Personalities_FILE' ) ) {
				// Plugin File
				define( 'GSCR_CPT_On_Air_Personalities_FILE', __FILE__ );
			}

		}

		/**
		 * Internationalization
		 *
		 * @access	  private 
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function load_textdomain() {

			// Set filter for language directory
			$lang_dir = GSCR_CPT_On_Air_Personalities_DIR . '/languages/';
			$lang_dir = apply_filters( 'gscr_cpt_on_air_personalities_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'gscr-cpt-on-air-personalities' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'gscr-cpt-on-air-personalities', $locale );

			// Setup paths to current locale file
			$mofile_local   = $lang_dir . $mofile;
			$mofile_global  = WP_LANG_DIR . '/gscr-cpt-on-air-personalities/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/gscr-cpt-on-air-personalities/ folder
				// This way translations can be overridden via the Theme/Child Theme
				load_textdomain( 'gscr-cpt-on-air-personalities', $mofile_global );
			}
			else if ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/gscr-cpt-on-air-personalities/languages/ folder
				load_textdomain( 'gscr-cpt-on-air-personalities', $mofile_local );
			}
			else {
				// Load the default language files
				load_plugin_textdomain( 'gscr-cpt-on-air-personalities', false, $lang_dir );
			}

		}
		
		/**
		 * Include different aspects of the Plugin
		 * 
		 * @access	  private
		 * @since	  1.0.0
		 * @return	  void
		 */
		private function require_necessities() {
			
			require_once GSCR_CPT_On_Air_Personalities_DIR . 'core/cpt/class-gscr-cpt-on-air-personalities.php';
			$this->cpt = new CPT_GSCR_On_Air_Personalities();
			
		}
		
		/**
		 * Show admin errors.
		 * 
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  HTML
		 */
		public function admin_errors() {
			?>
			<div class="error">
				<?php foreach ( $this->admin_errors as $notice ) : ?>
					<p>
						<?php echo $notice; ?>
					</p>
				<?php endforeach; ?>
			</div>
			<?php
		}
		
		/**
		 * Register our CSS/JS to use later
		 * 
		 * @access	  public
		 * @since	  1.0.0
		 * @return	  void
		 */
		public function register_scripts() {
			
			wp_register_style(
				'gscr-cpt-on-air-personalities',
				GSCR_CPT_On_Air_Personalities_URL . 'assets/css/style.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : GSCR_CPT_On_Air_Personalities_VER
			);
			
			wp_register_script(
				'gscr-cpt-on-air-personalities',
				GSCR_CPT_On_Air_Personalities_URL . 'assets/js/script.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : GSCR_CPT_On_Air_Personalities_VER,
				true
			);
			
			wp_localize_script( 
				'gscr-cpt-on-air-personalities',
				'gscrCPTOnAirPersonalities',
				apply_filters( 'gscr_cpt_on_air_personalities_localize_script', array() )
			);
			
			wp_register_style(
				'gscr-cpt-on-air-personalities-admin',
				GSCR_CPT_On_Air_Personalities_URL . 'assets/css/admin.css',
				null,
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : GSCR_CPT_On_Air_Personalities_VER
			);
			
			wp_register_script(
				'gscr-cpt-on-air-personalities-admin',
				GSCR_CPT_On_Air_Personalities_URL . 'assets/js/admin.js',
				array( 'jquery' ),
				defined( 'WP_DEBUG' ) && WP_DEBUG ? time() : GSCR_CPT_On_Air_Personalities_VER,
				true
			);
			
			wp_localize_script( 
				'gscr-cpt-on-air-personalities-admin',
				'gscrCPTOnAirPersonalities',
				apply_filters( 'gscr_cpt_on_air_personalities_localize_admin_script', array() )
			);
			
		}
		
	}
	
} // End Class Exists Check

/**
 * The main function responsible for returning the one true GSCR_CPT_On_Air_Personalities
 * instance to functions everywhere
 *
 * @since	  1.0.0
 * @return	  \GSCR_CPT_On_Air_Personalities The one true GSCR_CPT_On_Air_Personalities
 */
add_action( 'plugins_loaded', 'gscr_cpt_on_air_personalities_load', 999 );
function gscr_cpt_on_air_personalities_load() {

	require_once __DIR__ . '/core/gscr-cpt-on-air-personalities-functions.php';
	GSCRCPTONAIRPERSONALITIES();

}
