<?php

/**
 * Class LP_Addon
 */
class LP_Addon {
	/**
	 * Current version of addon.
	 *
	 * @var string
	 */
	public $version = null;

	/**
	 * Required version for current version of addon.
	 *
	 * @var string
	 */
	public $require_version = null;

	/**
	 * Path to addon.
	 *
	 * @var string
	 */
	public $plugin_file = null;

	/**
	 * Addon textdomain name.
	 *
	 * @var string
	 */
	public $text_domain = '';

	/**
	 * @var null
	 */
	protected $_valid = null;

	/**
	 * Singleton instance of the addon.
	 *
	 * @var array
	 */
	public static $instances = array();

	/**
	 * @var array
	 */
	protected static $_admin_notices = array();

	/**
	 * @var string
	 */
	protected $_template_path = '';

	protected static $on_activate_plugins = array();

	/**
	 * LP_Addon constructor.
	 */
	public function __construct() {
		if ( ! $this->_check_version() ) {
			return;
		}

		$this->_define_constants();
		$this->_includes();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'check_addon_update_version_4x' ) );
	}

	public static function admin_errors() {
		if ( ! self::$_admin_notices ) {
			return;
		}

		foreach ( self::$_admin_notices as $notice ) {
			?>
			<div class="error"><p><?php echo $notice; ?></p></div>
			<?php
		}
	}

	public function _plugin_links( $links ) {
		if ( method_exists( $this, 'plugin_links' ) ) {
			$plugin_links = call_user_func( array( $this, 'plugin_links' ) );

			if ( is_callable( array( $this, 'plugin_links' ) ) && $plugin_links ) {
				$links = array_merge( $links, $plugin_links );
			}
		}

		return $links;
	}

	/**
	 * Init
	 */
	public function init() {
		if ( ! $this->_check_version() ) {
			return;
		}

		$this->load_text_domain();

		add_filter(
			"plugin_action_links_{$this->get_plugin_slug()}",
			array(
				$this,
				'_plugin_links',
			)
		);

		$this->_init_hooks();
		$this->_enqueue_assets();
	}

	/**
	 * Define add-on constants.
	 */
	protected function _define_constants() {

	}

	/**
	 * Includes add-on files.
	 */
	protected function _includes() {

	}

	/**
	 * Init add-on hooks.
	 */
	protected function _init_hooks() {

	}

	/**
	 * Enqueue scripts.
	 */
	protected function _enqueue_assets() {

	}

	/**
	 * Get plugin slug from plugin file.
	 *
	 * @return bool|string
	 */
	public function get_plugin_slug() {
		if ( empty( $this->plugin_file ) ) {
			return false;
		}

		$dir      = dirname( $this->plugin_file );
		$basename = basename( $dir );

		return $basename . '/' . basename( $this->plugin_file );
	}

	/**
	 * Check required version of LP.
	 *
	 * @return bool|null
	 */
	protected function _check_version() {
		if ( null === $this->_valid ) {
			$this->_valid = true;

			if ( $this->require_version ) {
				if ( version_compare( $this->require_version, LEARNPRESS_VERSION, '>' ) ) {
					add_action( 'admin_notices', array( $this, '_admin_notices' ) );

					$this->_valid = false;
				}
			}
		}

		return $this->_valid;
	}

	/**
	 * Admin notices
	 */
	public function _admin_notices() {
		?>
		<div class="error">
			<p>
				<?php
				printf(
					__(
						'<strong>%1$s</strong> add-on version %2$s requires <strong>LearnPress</strong> version %3$s or higher',
						'learnpress'
					),
					esc_html( $this->get_name() ),
					esc_html( $this->version ),
					esc_html( $this->require_version )
				);
				?>
			</p>
		</div>
		<?php
	}

	/**
	 * Add notice and deactivate learnpress add-on if version < 4.0.0
	 *
	 * @return void
	 */
	public function check_addon_update_version_4x() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		if ( ! empty( $all_plugins ) ) {
			foreach ( $all_plugins as $file => $plugin ) {
				if ( preg_match( '/^learnpress-/', $file ) ) {
					if ( version_compare( $plugin['Version'], '4.0.0', '<' ) ) {
						add_action(
							'admin_notices',
							function() use ( $plugin ) {
								?>
								<div class="error">
									<p>
										<?php
										printf(
											__(
												'<strong>%1$s</strong> add-on version %2$s is not compatible with LearnPress latest version. Please update %3$s to version 4.x.',
												'learnpress'
											),
											$plugin['Name'],
											$plugin['Version'],
											$plugin['Name']
										);
										?>
									</p>
								</div>
								<?php
							}
						);

						if ( is_plugin_active( $file ) ) {
							deactivate_plugins( $file );
						}
					}
				}
			}
		}
	}

	/**
	 * @return mixed
	 */
	public function get_name() {
		return str_replace( '_', ' ', str_replace( 'LP_Addon_', '', get_class( $this ) ) );
	}

	/**
	 * Load text domain
	 */
	public function load_text_domain() {
		$plugin_path   = dirname( $this->plugin_file );
		$plugin_folder = basename( $plugin_path );
		$text_domain   = empty( $this->text_domain ) ? $plugin_folder : $this->text_domain;
		$locale        = apply_filters( 'plugin_locale', get_locale(), $plugin_folder );
		$domain_files  = array();

		if ( is_admin() ) {
			$domain_files[] = WP_LANG_DIR . "/{$plugin_folder}/{$plugin_folder}-admin-{$locale}.mo";
			$domain_files[] = WP_LANG_DIR . "/plugins/{$plugin_folder}-admin-{$locale}.mo";
		}

		$domain_files[] = WP_CONTENT_DIR . "/plugins/{$plugin_folder}/languages/{$plugin_folder}-{$locale}.mo";
		$domain_files[] = WP_LANG_DIR . "/{$plugin_folder}/{$plugin_folder}-{$locale}.mo";

		foreach ( $domain_files as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			load_textdomain( $text_domain, $file );
		}

		if ( $text_domain ) {
			load_plugin_textdomain( $text_domain, false, plugin_basename( $plugin_path ) . '/languages' );
		}
	}

	/**
	 * Load Addon
	 *
	 * @param        $instance
	 * @param        $path
	 * @param string   $plugin_file
	 */
	public static function load( $instance, $path, $plugin_file = '' ) {
		$plugin_folder = '';

		if ( $plugin_file ) {
			$plugin_folder = dirname( $plugin_file );
		}

		if ( $plugin_folder ) {
			$path = "{$plugin_folder}/$path";
		}

		if ( ! file_exists( $path ) ) {
			self::$_admin_notices['add-on-file-no-exists'] = sprintf(
				__( '%s plugin file does not exist.', 'learnpress' ),
				$path
			);

			return;
		}

		include_once $path;
		$addon_instance = null;

		if ( class_exists( $instance ) ) {
			$addon_instance = null;
			if ( is_callable( array( $instance, 'instance' ) ) ) {
				$addon_instance = call_user_func( array( $instance, 'instance' ) );
			} else {
				$addon_instance = new $instance();
			}
		}

		if ( ! $addon_instance ) {
			self::$_admin_notices['add-on-class-no-exists'] = sprintf(
				__( '%s plugin class does not exist.', 'learnpress' ),
				$instance
			);

			return;
		}

		$addon_instance->plugin_file = $plugin_file;

		self::$instances[ $instance ] = $addon_instance;
	}

	public function get_plugin_url( $sub = '/' ) {
		return plugins_url( $sub, $this->plugin_file );
	}

	/**
	 * Get template path.
	 *
	 * @return string
	 */
	public function get_template_path() {
		if ( empty( $this->_template_path ) ) {
			$this->_template_path = learn_press_template_path() . '/addons/' . preg_replace(
				'!^learnpress-!',
				'',
				dirname( $this->get_plugin_slug() )
			);
		}

		return $this->_template_path;
	}

	/**
	 * Get content template of addon in theme or inside itself.
	 *
	 * @param string $template_name
	 * @param array  $args
	 */
	public function get_template( $template_name, $args = array() ) {
		learn_press_get_template(
			$template_name,
			$args,
			$this->get_template_path(),
			dirname( $this->plugin_file ) . '/templates/'
		);
	}

	/**
	 * Locate template of addon in theme or inside itself.
	 *
	 * @param string $template_name
	 *
	 * @return string
	 */
	public function locate_template( $template_name ) {
		return learn_press_locate_template(
			$template_name,
			$this->get_template_path(),
			dirname( $this->plugin_file ) . '/templates/'
		);
	}

	/**
	 * Output content of admin view file.
	 *
	 * @param string $view
	 * @param array  $args
	 *
	 * @since x.x.x
	 */
	public function admin_view( $view, $args = array() ) {
		$args['plugin_file'] = $this->plugin_file;
		learn_press_admin_view( $view, $args );
	}

	/**
	 * Get content of admin view file.
	 *
	 * @param string $view
	 * @param array  $args
	 *
	 * @return string
	 * @since x.x.x
	 */
	public function admin_view_content( $view, $args = array() ) {
		ob_start();
		$this->admin_view( $view, $args );

		return ob_get_clean();
	}

	/**
	 * @return mixed
	 */
	public static function instance() {
		$name = self::_get_called_class();
		if ( false === $name ) {
			return false;
		}

		if ( empty( self::$instances[ $name ] ) ) {
			self::$instances[ $name ] = new $name();
		}

		return self::$instances[ $name ];
	}

	/**
	 * @return bool|string
	 */
	protected static function _get_called_class() {
		if ( function_exists( 'get_called_class' ) ) {
			return get_called_class();
		}

		$backtrace = debug_backtrace();

		if ( empty( $backtrace[2] ) ) {
			return false;
		}

		if ( empty( $backtrace[2]['args'][0] ) ) {
			return false;
		}

		return $backtrace[2]['args'][0];
	}
}

add_action( 'admin_notices', array( 'LP_Addon', 'admin_errors' ) );
