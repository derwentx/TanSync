<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class TanSync_Settings {

	/**
	 * The single instance of TanSync_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( ) {
		$this->parent = TanSync::instance();
		$this->base = 'tsync_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );

		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );
	}

	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();

	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'TanSync', TANSYNC_DOMAIN ) , __( 'TanSync', TANSYNC_DOMAIN ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}

	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', TANSYNC_DOMAIN ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	public function validate_json( $json_str ) {
		$obj = json_decode($json_str);
		if( $obj != null ) {
			return $json_str;
		} else {
			return "// INVALID JSON! //\n".$json_str;
		}
	}

	public function refresh_roles( $check ){
		if(TANSYNC_DEBUG) error_log("checking refresh_roles: ".serialize($check));
		if($check){
			$groups_roles = $this->parent->groups_roles;
			add_action('shutdown', array(&$groups_roles, 'role_refresh'));
			// $groups_roles->role_refresh();
			return false;
		}
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		// $settings['User Fields'] = array(
		// 	'title'				=> __( 'Additional User Fields', TANSYNC_DOMAIN ),
		// 	'description'		=> __( 'Describes the fields added to the user profile screens', TANSYNC_DOMAIN ),
		// 	'fields'			=> array(
			// 	array(
			// 		'id' 			=> 'text_field',
			// 		'label'			=> __( 'Some Text' , TANSYNC_DOMAIN ),
			// 		'description'	=> __( 'This is a standard text field.', TANSYNC_DOMAIN ),
			// 		'type'			=> 'text',
			// 		'default'		=> '',
			// 		'placeholder'	=> __( 'Placeholder text', TANSYNC_DOMAIN )
			// 	),
			// 	array(
			// 		'id' 			=> 'password_field',
			// 		'label'			=> __( 'A Password' , TANSYNC_DOMAIN ),
			// 		'description'	=> __( 'This is a standard password field.', TANSYNC_DOMAIN ),
			// 		'type'			=> 'password',
			// 		'default'		=> '',
			// 		'placeholder'	=> __( 'Placeholder text', TANSYNC_DOMAIN )
			// 	),
			// 	array(
			// 		'id' 			=> 'secret_text_field',
			// 		'label'			=> __( 'Some Secret Text' , TANSYNC_DOMAIN ),
			// 		'description'	=> __( 'This is a secret text field - any data saved here will not be displayed after the page has reloaded, but it will be saved.', TANSYNC_DOMAIN ),
			// 		'type'			=> 'text_secret',
			// 		'default'		=> '',
			// 		'placeholder'	=> __( 'Placeholder text', TANSYNC_DOMAIN )
			// 	),
			// 	array(
			// 		'id' 			=> 'text_block',
			// 		'label'			=> __( 'A Text Block' , TANSYNC_DOMAIN ),
			// 		'description'	=> __( 'This is a standard text area.', TANSYNC_DOMAIN ),
			// 		'type'			=> 'textarea',
			// 		'default'		=> '',
			// 		'placeholder'	=> __( 'Placeholder text for this textarea', TANSYNC_DOMAIN )
			// 	),
			// 	array(
			// 		'id' 			=> 'single_checkbox',
			// 		'label'			=> __( 'An Option', TANSYNC_DOMAIN ),
			// 		'description'	=> __( 'A standard checkbox - if you save this option as checked then it will store the option as \'on\', otherwise it will be an empty string.', TANSYNC_DOMAIN ),
			// 		'type'			=> 'checkbox',
			// 		'default'		=> ''
			// 	),
			// 	array(
			// 		'id' 			=> 'select_box',
			// 		'label'			=> __( 'A Select Box', TANSYNC_DOMAIN ),
			// 		'description'	=> __( 'A standard select box.', TANSYNC_DOMAIN ),
			// 		'type'			=> 'select',
			// 		'options'		=> array( 'drupal' => 'Drupal', 'joomla' => 'Joomla', 'wordpress' => 'WordPress' ),
			// 		'default'		=> 'wordpress'
			// 	),
			// 	array(
			// 		'id' 			=> 'radio_buttons',
			// 		'label'			=> __( 'Some Options', TANSYNC_DOMAIN ),
			// 		'description'	=> __( 'A standard set of radio buttons.', TANSYNC_DOMAIN ),
			// 		'type'			=> 'radio',
			// 		'options'		=> array( 'superman' => 'Superman', 'batman' => 'Batman', 'ironman' => 'Iron Man' ),
			// 		'default'		=> 'batman'
			// 	),
			// 	array(
			// 		'id' 			=> 'multiple_checkboxes',
			// 		'label'			=> __( 'Some Items', TANSYNC_DOMAIN ),
			// 		'description'	=> __( 'You can select multiple items and they will be stored as an array.', TANSYNC_DOMAIN ),
			// 		'type'			=> 'checkbox_multi',
			// 		'options'		=> array( 'square' => 'Square', 'circle' => 'Circle', 'rectangle' => 'Rectangle', 'triangle' => 'Triangle' ),
			// 		'default'		=> array( 'circle', 'triangle' )
			// 	),
			// array(
			// 	'id' 			=> 'number_field',
			// 	'label'			=> __( 'A Number' , TANSYNC_DOMAIN ),
			// 	'description'	=> __( 'This is a standard number field - if this field contains anything other than numbers then the form will not be submitted.', TANSYNC_DOMAIN ),
			// 	'type'			=> 'number',
			// 	'default'		=> '',
			// 	'placeholder'	=> __( '42', TANSYNC_DOMAIN )
			// ),
			// array(
			// 	'id' 			=> 'colour_picker',
			// 	'label'			=> __( 'Pick a colour', TANSYNC_DOMAIN ),
			// 	'description'	=> __( 'This uses WordPress\' built-in colour picker - the option is stored as the colour\'s hex code.', TANSYNC_DOMAIN ),
			// 	'type'			=> 'color',
			// 	'default'		=> '#21759B'
			// ),
			// array(
			// 	'id' 			=> 'an_image',
			// 	'label'			=> __( 'An Image' , TANSYNC_DOMAIN ),
			// 	'description'	=> __( 'This will upload an image to your media library and store the attachment ID in the option field. Once you have uploaded an imge the thumbnail will display above these buttons.', TANSYNC_DOMAIN ),
			// 	'type'			=> 'image',
			// 	'default'		=> '',
			// 	'placeholder'	=> ''
			// ),
			// array(
			// 	'id' 			=> 'multi_select_box',
			// 	'label'			=> __( 'A Multi-Select Box', TANSYNC_DOMAIN ),
			// 	'description'	=> __( 'A standard multi-select box - the saved data is stored as an array.', TANSYNC_DOMAIN ),
			// 	'type'			=> 'select_multi',
			// 	'options'		=> array( 'linux' => 'Linux', 'mac' => 'Mac', 'windows' => 'Windows' ),
			// 	'default'		=> array( 'linux' )
			// )
		// );

		$settings['Sync'] = array(
			'title'				=> __( 'Sync Settings', TANSYNC_DOMAIN ),
			'description'		=> __( 'Extra User Fields that are synced with a remote target', TANSYNC_DOMAIN ),
			'fields'			=> array(
				array(
					'id' 			=> 'sync_field_settings',
					'label'			=> __( 'Synchrnoised Field Settings' , TANSYNC_DOMAIN ),
					'description'	=> __( 'Enter user fields that are read from external source and whether this field is modified by WordPress', TANSYNC_DOMAIN ),
					'type'			=> 'textarea',
					'default'		=> '',
					'placeholder'	=> __( '{"<field_id_1>":<sync_params_1>, "<field_id_2>":<sync_params_2>, ...}', TANSYNC_DOMAIN ),
					'callback'		=> array(&$this, 'validate_json')
				),
				array(
					'id'			=> 'sync_email_enable',
					'label'			=> 'Enable Synchronization Email',
					'description'	=> __( 'Enables the service that emails staff regularly about user account changes', TANSYNC_DOMAIN),
					'type'			=> 'checkbox',
					'default'		=> 'on'
				),
				array(
					'id'			=> 'sync_email_interval',
					'label'			=> 'Synchronization Email Interval',
					'description'	=> __( 'Enter the Interval (in seconds) that the plugin checks for updated users', TANSYNC_DOMAIN),
					'type'			=> 'number',
					'default'		=> 300,
					'placeholder'	=> 300
				),
				array(
					'id'			=> 'sync_email_to',
					'label'			=> __( 'Synchronize Email Address', TANSYNC_DOMAIN),
					'description'	=> __( 'Enter the email to which the synchronization messages are sent', TANSYNC_DOMAIN),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> 'user@example.com'
				)
			)
		);

		$settings['Targeted Content'] = array(
			'title'				=> __( 'Targeted Content', TANSYNC_DOMAIN),
			'description'		=> __( 'Settings to determine how targeted content is displayed to the user'),
			'fields'			=> array(
				array(
					'id'			=> 'targeted_content_conditions',
					'label'			=> __('Targeted Content Conditions', TANSYNC_DOMAIN),
					'description'	=> __('Enter a list of page slugs and the conditions required to display those pages to the user', TANSYNC_DOMAIN),
					'type'			=> 'textarea',
					'placeholder'	=> __( '[{"slug":"page_slug_1", "Conditions":<page_conditions_1>}, {"slug":"page_slug_2", "condtions":<page_conditions_2>}]'),
					'callback'		=> array(&$this, 'validate_json')
				),
			)
		);

		$settings['Groups and Roles'] = array(
			'title'				=> __( 'Groups and Roles', TANSYNC_DOMAIN),
			'description'		=> __( 'Settings to determine how groups and roles are modified by synchronization'),
			'fields'			=> array(
				array(
					'id'			=> 'role_field',
					'label'			=> __('Role Field', TANSYNC_DOMAIN),
					'description'	=> __('Enter the field which the role of a user is based on', TANSYNC_DOMAIN),
					'type'			=> 'text',
					'placeholder'	=> 'act_role',
					'default' 		=> 'act_role',
				),
				array(
					'id'			=> 'default_role',
					'label'			=> __('Default Role', TANSYNC_DOMAIN),
					'description'	=> __('Enter the role which a user defaults to', TANSYNC_DOMAIN),
					'type'			=> 'text',
					'placeholder'	=> 'RN',
					'default'		=> 'RN'
				),
				array(
					'id'			=> 'group_role_mapping',
					'label'			=> __('Group Role Mapping', TANSYNC_DOMAIN),
					'description'	=> __('Enter the mapping which is used to determine the users role', TANSYNC_DOMAIN),
					'type'			=> 'textarea',
					'callback'		=> array(&$this, 'validate_json'),
					'placeholder'	=>
'{
	"ADMIN":{
		"roles":["administrator"],
		"groups":["Registered", "Wholesale", "Distributor", "Admin"]
		"memberships":["wholesale", "distributor"]
	},
	"RN":{
		"roles":["customer","rn"],
		"groups":["Registered", "Local"]
	},
	"XRN":{
		"roles":["customer","xrn"],
		"groups":["Registered", "Export"]
	},
	"RP":{
		"roles":["customer", "rp"],
		"groups":["Registered", "Local", "Preferred"]
	},
	"XRN":{
		"roles":["customer", "xrp"],
		"groups":["Registered", "Export", "Preferred"]
	},
	"WN":{
		"roles":["customer", "wn"],
		"groups":["Registered", "Wholesale", "Local"],
		"memberships":["wholesale"]
	},
	"XWN":{
		"roles":["customer", "xwn"],
		"groups":["Registered", "Wholesale", "Export"],
		"memberships":["wholesale"]
	},
	"WP":{
		"roles":["customer", "wp"],
		"groups":["Registered", "Wholesale", "Local", "Preferred"],
		"memberships":["wholesale"]
	},
	"XWP":{
		"roles":["customer", "xwp"],
		"groups":["Registered", "Wholesale", "Export", "Preferred"],
		"memberships":["wholesale"]
	},
	"DN":{
		"roles":["customer", "dn"],
		"groups":["Registered","Distributor", "Local"],
		"memberships":["distributor"]
	},
	"XDN":{
		"roles":["customer", "xdn"],
		"groups":["Registered","Distributor", "Export"],
		"memberships":["distributor"]
	},
	"DP":{
		"roles":["customer", "dp"],
		"groups":["Registered","Distributor", "Local", "Preferred"],
		"memberships":["distributor"]
	},
	"XDP":{
		"roles":["customer", "xdp"],
		"groups":["Registered","Distributor", "Export", "Preferred"],
		"memberships":["distributor"]
	}
}',
				),
				array(
					'id'			=> 'enable_role_refresh',
					'label'			=> __('Role Refresh', TANSYNC_DOMAIN),
					'description'	=> __('Tick this box to allow Tansync to refresh all users\' roles and groups when this form is submitted'),
					'type'			=> 'checkbox',
					'default'		=> '',
					'callback'		=> array(&$this, 'refresh_roles'),
				)
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'TanSync Settings' , TANSYNC_DOMAIN ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , TANSYNC_DOMAIN ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}

	/**
	 * gets the specified option
	 */
	public function get_option( $option_slug, $default = null ) {
		// if(WP_DEBUG) error_log("getting option $option_slug");
		if ( is_string($this->base) and is_string($option_slug) ) {
			return get_option( $this->base . $option_slug, $default );
		} else {
			return null;
		}
	}

	/**
	 * sets the specified option
	 */
	public function set_option( $option_slug, $value ) {
		// if(WP_DEBUG) error_log("getting option $option_slug");
		if ( is_string($this->base) and is_string($option_slug) ) {
			return update_option( $this->base . $option_slug, $value );
		} else {
			return null;
		}
	}

	public function get_sync_settings(){
		$sync_settings_json = $this->get_option("sync_field_settings", "");
		$sync_settings = json_decode($sync_settings_json);
		if($sync_settings){
			return get_object_vars($sync_settings);
		} else {
			return array();
		}
	}

	/**
	 * Main TanSync_Settings Instance
	 *
	 * Ensures only one instance of TanSync_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see TanSync()
	 * @return Main TanSync_Settings instance
	 */
	public static function instance ( ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
