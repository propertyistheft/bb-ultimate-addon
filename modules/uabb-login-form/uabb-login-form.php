<?php
/**
 *  UABB Login Form Module file
 *
 *  @package UABB Login Form Module
 */

/**
 * Function that initializes UABB Login Form Module
 *
 * @class UABBLoginForm
 */
class UABBLoginForm extends FLBuilderModule {

	/**
	 * Constructor function for the module. You must pass the
	 * name, description, dir and url in an array to the parent class.
	 *
	 * @method __construct
	 */
	public function __construct() {

		parent::__construct(
			array(
				'name'            => __( 'Login Form', 'uabb' ),
				'description'     => __( 'Login Module', 'uabb' ),
				'category'        => BB_Ultimate_Addon_Helper::module_cat( BB_Ultimate_Addon_Helper::$content_modules ),
				'group'           => UABB_CAT,
				'dir'             => BB_ULTIMATE_ADDON_DIR . 'modules/uabb-login-form/',
				'url'             => BB_ULTIMATE_ADDON_URL . 'modules/uabb-login-form/',
				'editor_export'   => true, // Defaults to true and can be omitted.
				'enabled'         => true, // Defaults to true and can be omitted.
				'partial_refresh' => true,
				'icon'            => 'login.svg',
			)
		);

		add_action( 'wp_ajax_nopriv_uabb-lf-form-submit', array( $this, 'uabb_lf_form_submit_ajax_function' ) );
		add_action( 'wp_ajax_nopriv_uabb-lf-google-submit', array( $this, 'uabb_lf_google_submit_ajax_function' ) );
		add_action( 'wp_ajax_nopriv_uabb-lf-facebook-submit', array( $this, 'uabb_lf_facebook_submit_ajax_function' ) );
		$this->add_css( 'font-awesome-5' );
	}

	/**
	 * Function that enqueue's the scripts
	 *
	 * @since 1.24.0
	 * @method enqueue_scripts
	 */
	public function enqueue_scripts() {

			$uabb_social_google_client_id = '';

			$uabb_setting_options = UABB_Init::$uabb_options['fl_builder_uabb'];

		if ( is_array( $uabb_setting_options ) ) {

			$uabb_social_google_client_id = ( array_key_exists( 'uabb-social-google-client-id', $uabb_setting_options ) ) ? $uabb_setting_options['uabb-social-google-client-id'] : '';
		}

		$settings = $this->settings;

		if ( isset( $settings->google_login_select ) && 'yes' === $settings->google_login_select && ! empty( $uabb_social_google_client_id ) ) {

			$this->add_js( 'uabb_lf_google_login', 'https://accounts.google.com/gsi/client' );
		}
	}
	/**
	 * Function to get the icon for the Login Form Module.
	 *
	 * @since 1.24.0
	 * @method get_icons
	 * @param string $icon gets the icon for the module.
	 */
	public function get_icon( $icon = '' ) {

		if ( '' !== $icon && file_exists( BB_ULTIMATE_ADDON_DIR . 'modules/uabb-login-form/icon/' . $icon ) ) {
			return fl_builder_filesystem()->file_get_contents( BB_ULTIMATE_ADDON_DIR . 'modules/uabb-login-form/icon/' . $icon );
		}
		return '';
	}

	/**
	 * Get access token info.
	 *
	 * @since 1.20.1
	 * @access public
	 * @param string $id_token ID token.
	 * @param string $uabb_google_client_id settings page client ID.
	 * @return array
	 */
	public function verify_user_data( $id_token, $uabb_google_client_id ) {

		require_once BB_ULTIMATE_ADDON_DIR . 'modules/uabb-login-form/includes/vendor/autoload.php';

		// Get $id_token via HTTPS POST.
		$client = new \Google_Client( array( 'client_id' => $uabb_google_client_id ) );  //PHPCS:ignore:PHPCompatibility.PHP.ShortArray.Found

		$verified_data = $client->verifyIdToken( $id_token );

		if ( $verified_data ) {
			return $verified_data;
		} else {
			wp_send_json_error(
				array(
					'error' => __( 'Unauthorized access', 'uabb' ),
				)
			);
		}

	}

	/**
	 * Google button ajax function.
	 *
	 * @since 1.24.0
	 * @method uabb_lf_google_submit_ajax_function
	 */
	public function uabb_lf_google_submit_ajax_function() {

		check_ajax_referer( 'uabb-lf-nonce', 'nonce' );

		$data      = array();
		$response  = array();
		$user_data = array();
		$result    = '';

		if ( isset( $_POST['data'] ) ) {

			$id_token             = filter_input( INPUT_POST, 'id_token', FILTER_SANITIZE_STRING );
			$uabb_setting_options = UABB_Init::$uabb_options['fl_builder_uabb'];

			if ( is_array( $uabb_setting_options ) ) {

				$uabb_social_google_client_id = ( array_key_exists( 'uabb-social-google-client-id', $uabb_setting_options ) ) ? $uabb_setting_options['uabb-social-google-client-id'] : '';
			}
			$verified_data = $this->verify_user_data( $id_token, $uabb_social_google_client_id );

			$data       = array_map( 'sanitize_text_field', $_POST['data'] );
			$name       = isset( $verified_data['name'] ) ? $verified_data['name'] : '';
			$email      = isset( $verified_data['email'] ) ? $verified_data['email'] : '';
			$send_email = $data['send_email'];

			// Check if email is verified with Google.
			if ( empty( $verified_data ) || ( $verified_data['aud'] !== $uabb_social_google_client_id ) || ( isset( $verified_data['email'] ) && $verified_data['email'] !== $email ) ) {
				wp_send_json_error(
					array(
						'error' => __( 'Unauthorized access', 'uabb' ),
					)
				);
			}

			$user_data = get_user_by( 'email', $email );

			$response['username'] = $name;

			if ( ! empty( $user_data ) && false !== $user_data ) {

				$user_ID    = $user_data->ID;
				$user_email = $user_data->user_email;
				wp_set_auth_cookie( $user_ID );
				wp_set_current_user( $user_ID, $name );
				do_action( 'wp_login', $user_data->user_login, $user_data );
				$response['success'] = true;

			} else {

				$password = wp_generate_password( 12, true, false );

				if ( username_exists( $name ) ) {
					// Generate something unique to append to the username in case of a conflict with another user.
					$suffix = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
					$name  .= $suffix;

					$user_array = array(
						'user_login' => strtolower( preg_replace( '/\s+/', '', $name ) ),
						'user_pass'  => $password,
						'user_email' => $email,
						'first_name' => $verified_data['name'],
					);
					$result     = wp_insert_user( $user_array );
				} else {
					$result = wp_create_user( $name, $password, $email );
				}

				if ( 'no' !== $send_email ) {
					$this->send_user_email( $result, $send_email );
				}

				$user_data = get_user_by( 'email', $email );

				if ( $user_data ) {

					$user_ID    = $user_data->ID;
					$user_email = $user_data->user_email;

					$user_meta = array(
						'login_source' => 'google',
					);

					update_user_meta( $user_ID, 'uabb_login_form', $user_meta );

					if ( wp_check_password( $password, $user_data->user_pass, $user_data->ID ) ) {

						wp_set_auth_cookie( $user_ID );
						wp_set_current_user( $user_ID, $name );
						do_action( 'wp_login', $user_data->user_login, $user_data );
						$response['success'] = true;
					}
				}
			}

				wp_send_json( $response );

		} else {
			die;
		}
	}
	/**
	 * Facebook button ajax function.
	 *
	 * @since 1.24.0
	 * @method uabb_lf_facebook_submit_ajax_function
	 */
	public function uabb_lf_facebook_submit_ajax_function() {

		check_ajax_referer( 'uabb-lf-nonce', 'nonce' );

		$uabb_social_facebook_app_id     = '';
		$uabb_social_facebook_app_secret = '';

		$uabb_setting_options = UABB_Init::$uabb_options['fl_builder_uabb'];

		if ( is_array( $uabb_setting_options ) ) {

			$uabb_social_facebook_app_id     = ( array_key_exists( 'uabb-social-facebook-app-id', $uabb_setting_options ) ) ? $uabb_setting_options['uabb-social-facebook-app-id'] : '';
			$uabb_social_facebook_app_secret = ( array_key_exists( 'uabb-social-facebook-app-secret', $uabb_setting_options ) ) ? $uabb_setting_options['uabb-social-facebook-app-secret'] : '';
		}

		$username   = isset( $_POST['name'] ) ? sanitize_user( $_POST['name'] ) : '';
		$first_name = isset( $_POST['first_name'] ) ? sanitize_user( $_POST['first_name'] ) : '';
		$last_name  = isset( $_POST['last_name'] ) ? sanitize_user( $_POST['last_name'] ) : '';
		$email      = ! empty( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';

		$user_id_js = filter_input( INPUT_POST, 'userID', FILTER_SANITIZE_STRING );

		$password_length = apply_filters( 'uabb_lf_password_length', 12 );

		$access_token = filter_input( INPUT_POST, 'security_string', FILTER_SANITIZE_STRING );

		$rest_data = $this->get_user_profile_info_facebook( $access_token, $uabb_social_facebook_app_id, $uabb_social_facebook_app_secret );

		$fb_data = $rest_data['data'];

		if ( empty( $user_id_js ) || empty( $rest_data ) || empty( $uabb_social_facebook_app_id ) || ( $user_id_js !== $fb_data['user_id'] ) || ( $fb_data['app_id'] !== $uabb_social_facebook_app_id ) || ( false === $fb_data['is_valid'] ) || ( ! empty( $email ) && ( $email !== $fb_data['email'] ) ) ) {

			wp_send_json_error(
				array(
					'error' => __( 'Unauthorized access', 'uabb' ),
				)
			);
		}
		if ( empty( $email ) && empty( $fb_data['email'] ) ) {

			$fb_data['email'] = $fb_data['user_id'] . '@facebook.com';
		}

		$username = explode( '@', $fb_data['email'], 2 );
		$username = $username[0];

		$user_data = get_user_by( 'email', $fb_data['email'] );

		if ( ! empty( $user_data ) && false !== $user_data ) {

			$user_ID    = $user_data->ID;
			$user_email = $user_data->user_email;
			$username   = $user_data->user_login;
			wp_set_auth_cookie( $user_ID );
			wp_set_current_user( $user_ID, $username );
			do_action( 'wp_login', $user_data->user_login, $user_data );
			wp_send_json_success();
			exit;
		} else {
			if ( username_exists( $username ) ) {
					// Generate something unique to append to the username in case of a conflict with another user.
					$suffix    = '-' . zeroise( wp_rand( 0, 9999 ), 4 );
					$username .= $suffix;
			}
			$password       = wp_generate_password( $password_length, true, false );
			$facebook_array = array(
				'user_login' => $username,
				'user_pass'  => $password,
				'user_email' => $fb_data['email'],
				'first_name' => isset( $first_name ) ? $first_name : $username,
				'last_name'  => isset( $last_name ) ? $last_name : '',
			);

			wp_insert_user( $facebook_array );
			$user_data = get_user_by( 'email', $fb_data['email'] );

			if ( $user_data ) {

				$user_ID    = $user_data->ID;
				$user_email = $user_data->user_email;

				$user_meta_array = array(

					'email'      => $js_email,
					'login_from' => 'facebook',
				);

				update_user_meta( $user_ID, 'uabb_login_form', $user_meta_array );

				if ( wp_check_password( $password, $user_data->user_pass, $user_data->ID ) ) {
					wp_set_auth_cookie( $user_ID );
					wp_set_current_user( $user_ID, $username );
					do_action( 'wp_login', $user_data->user_login, $user_data );
					wp_send_json_success();
					exit;
				}
			}
		}

	}

	/**
	 * Login Form Submit button ajax function.
	 *
	 * @since 1.24.0
	 * @method uabb_lf_form_submit_ajax_function
	 */
	public function uabb_lf_form_submit_ajax_function() {

		check_ajax_referer( 'uabb-lf-nonce', 'nonce' );

		$username   = ! empty( $_POST['username'] ) ? sanitize_user( $_POST['username'] ) : '';
		$password   = ! empty( $_POST['password'] ) ? sanitize_text_field( $_POST['password'] ) : '';
		$rememberme = ! empty( $_POST['rememberme'] ) ? sanitize_text_field( $_POST['rememberme'] ) : '';
		$user_data  = get_user_by( 'login', $username );
		if ( ! $user_data ) {
			$user_data = get_user_by( 'email', $username );
		}
		if ( $user_data ) {
			$user_ID    = $user_data->ID;
			$user_email = $user_data->user_email;

			if ( wp_check_password( $password, $user_data->user_pass, $user_data->ID ) ) {
				if ( '1' === $rememberme ) {

					wp_set_auth_cookie( $user_ID, true );
				} else {

					wp_set_auth_cookie( $user_ID );
				}
				wp_set_current_user( $user_ID, $username );
				do_action( 'wp_login', $user_data->user_login, $user_data );
				wp_send_json_success();

			} else {

				wp_send_json_error( 'Incorrect Password' );
			}
		} else {

			wp_send_json_error( 'Incorrect Username' );
		}

	}
	/**
	 * Function that authenticates Google user.
	 *
	 * @since 1.24.1
	 * @method get_user_profile_info_google
	 * @param string $id_token ID Token.
	 * @param string $uabb_social_google_client_id CLient ID.
	 * @return array $user_data User Data.
	 */
	public function get_user_profile_info_google( $id_token, $uabb_social_google_client_id ) {

		require_once BB_ULTIMATE_ADDON_DIR . 'modules/uabb-login-form/includes/vendor/autoload.php';

		// Get $id_token via HTTPS POST.
		$client = new Google_Client( array( 'client_id' => $uabb_social_google_client_id ) );  //PHPCS:ignore:PHPCompatibility.PHP.ShortArray.Found

		$user_data = $client->verifyIdToken( $id_token );

		if ( $user_data ) {

			return $user_data;
		} else {

			wp_send_json_error(
				array(
					'error' => __( 'Unauthorized access', 'uabb' ),
				)
			);
		}

	}
	/**
	 * Function that authenticates Facebook user.
	 *
	 * @since 1.24.1
	 * @method get_user_profile_info_facebook
	 * @param string $input_token Access Token.
	 * @param string $uabb_social_facebook_app_id App ID.
	 * @param string $uabb_social_facebook_app_secret App Secret.
	 */
	public function get_user_profile_info_facebook( $input_token, $uabb_social_facebook_app_id, $uabb_social_facebook_app_secret ) {
		$access_token = '';

		$url = add_query_arg(
			array(
				'client_id'     => $uabb_social_facebook_app_id,
				'client_secret' => $uabb_social_facebook_app_secret,
				'grant_type'    => 'client_credentials',
			),
			'https://graph.facebook.com/oauth/access_token'
		);

		$response = wp_remote_get( $url );

		if ( ! is_wp_error( $response ) ) {

			$rest_data    = wp_remote_retrieve_body( $response );
			$rest_data    = json_decode( $rest_data );
			$access_token = $rest_data->access_token;

		}

		$finalurl = add_query_arg(
			array(
				'input_token'  => $input_token,
				'access_token' => $access_token,
			),
			'https://graph.facebook.com/debug_token'
		);

		$finalresponse = wp_remote_get( $finalurl );

		if ( ! is_wp_error( $finalresponse ) && ! is_wp_error( $response ) ) {

			$rest_datafinal                  = wp_remote_retrieve_body( $finalresponse );
			$rest_datafinal                  = json_decode( $rest_datafinal, true );
			$user_data_url                   = 'https://graph.facebook.com/' . $rest_datafinal['data']['user_id'];
			$final_user_data_url             = add_query_arg(
				array(
					'fields'       => 'email',
					'access_token' => $input_token,
				),
				$user_data_url
			);
			$final_user_data_response        = wp_remote_get( $final_user_data_url );
			$final_user_data_response        = wp_remote_retrieve_body( $final_user_data_response );
			$final_user_data_response        = json_decode( $final_user_data_response );
			$rest_datafinal['data']['email'] = $final_user_data_response->email;

			return $rest_datafinal;
		}

		wp_send_json_error(
			array(
				'error' => __( 'Unauthorized access', 'uabb' ),
			)
		);
	}
	/**
	 * Function that renders the Advanced separator
	 *
	 * @since 1.24.0
	 * @method render_advanced_separator
	 */
	public function render_advanced_separator() {
		if ( 'enable' === $this->settings->separator_select ) {
			$separator_settings = array(
				'separator'                => 'line_text',
				'text_inline'              => $this->settings->separator_text,
				'text_tag_selection'       => $this->settings->separator_text_tag_selection,
				'text_font_typo'           => $this->settings->separator_typo,
				'text_color'               => $this->settings->separator_text_color,
				'icon_photo_position'      => $this->settings->text_position,
				'icon_spacing'             => $this->settings->text_spacing,
				'responsive_compatibility' => $this->settings->responsive_compatibility,
				'color'                    => $this->settings->separator_color,
				'height'                   => $this->settings->separator_height,
				'width'                    => $this->settings->separator_width,
				'alignment'                => $this->settings->separator_alignment,
				'style'                    => $this->settings->separator_style,
			);

			echo '<div class="uabb-lf-advanced-separator">';
			FLBuilder::render_module_html( 'advanced-separator', $separator_settings );
			echo '</div>';
		}
	}

	/**
	 * Function that renders the Social Form.
	 *
	 * @since 1.24.0
	 * @method render_social_form
	 */
	public function render_social_form() {

		if ( 'yes' === $this->settings->google_login_select || 'yes' === $this->settings->facebook_login_select ) {

			$uabb_setting_options = UABB_Init::$uabb_options['fl_builder_uabb'];
		}

			$uabb_social_facebook_app_id       = '';
			$uabb_social_google_client_id      = '';
			$uabb_social_facebook_redirect_url = '';
			$login_url                         = '#';
			$social_buttons_layout_desktop     = '';
			$social_buttons_layout_medium      = '';
			$social_buttons_layout_responsive  = '';
			$input_field_width_class           = '';
			$google_button_string              = 'Log In with Google';
			$facebook_button_string            = 'Log In with Facebook';
			$google_button_class               = '';
			$facebook_button_class             = '';
		if ( isset( $uabb_setting_options ) && is_array( $uabb_setting_options ) ) {

			$uabb_social_facebook_app_id       = ( array_key_exists( 'uabb-social-facebook-app-id', $uabb_setting_options ) ) ? $uabb_setting_options['uabb-social-facebook-app-id'] : '';
			$uabb_social_facebook_redirect_url = ( array_key_exists( 'uabb-social-facebook-redirect-url', $uabb_setting_options ) ) ? $uabb_setting_options['uabb-social-facebook-redirect-url'] : '';
			$uabb_social_google_client_id      = ( array_key_exists( 'uabb-social-google-client-id', $uabb_setting_options ) ) ? $uabb_setting_options['uabb-social-google-client-id'] : '';
			$uabb_social_facebook_app_secret   = ( array_key_exists( 'uabb-social-facebook-app-secret', $uabb_setting_options ) ) ? $uabb_setting_options['uabb-social-facebook-app-secret'] : '';
			$login_url                         = '';

		}
			/* Google Button String and Class. */
		if ( isset( $this->settings->social_button_theme ) && ! empty( $this->settings->social_button_theme ) ) {

			if ( 'dark' === $this->settings->social_button_theme ) {

				$google_button_class    = ( 'uabb-lf-google-dark' );
				$google_button_string   = __( 'Log In with Google', 'uabb' );
				$facebook_button_class  = 'uabb-lf-facebook-dark';
				$facebook_button_string = __( 'Log In with Facebook', 'uabb' );

			}
			if ( 'light' === $this->settings->social_button_theme ) {

				$google_button_class    = 'uabb-lf-google-light';
				$google_button_string   = __( 'Google', 'uabb' );
				$facebook_button_class  = 'uabb-lf-facebook-light';
				$facebook_button_string = __( 'Facebook', 'uabb' );

			}
		}

			/* Social Buttons Layout Desktop. */
		if ( isset( $this->settings->social_buttons_layout ) && ! empty( $this->settings->social_buttons_layout ) ) {

			if ( 'inline' === $this->settings->social_buttons_layout ) {

				$social_buttons_layout_desktop = 'uabb-social-layout-inline-desktop';
			}
			if ( 'stack' === $this->settings->social_buttons_layout ) {

				$social_buttons_layout_desktop = 'uabb-social-layout-stack-desktop';
			}
		}

		?>
		<?php if ( 'yes' === $this->settings->google_login_select || 'yes' === $this->settings->facebook_login_select ) { ?>
			<div class="uabb-lf-social-login-wrap <?php echo esc_attr( $social_buttons_layout_desktop ); ?>">
				<?php if ( 'yes' === $this->settings->google_login_select && isset( $uabb_social_google_client_id ) && ! empty( $uabb_social_google_client_id ) ) { ?>

					<div class="uabb-lf-input-group uabb-lf-row uabb-lf-google-login uabb-lf-google-button-wrap">
						<?php
						if ( 'light' === $this->settings->social_button_theme ) {
							$theme = 'outline';
							$width = '135';
							$text  = 'signin';

						} else {
							$theme = 'filled_blue';
							$width = '212';
							$text  = 'signin_with';

						}
						?>
							<div id="g_id_onload"
								data-client_id="<?php echo esc_attr( $uabb_social_google_client_id ); ?>"
								data-context="signin"
								data-ux_mode="popup"
								data-callback="handleCredentialResponse"
								data-auto_prompt="false">
							</div>

							<div class="g_id_signin"
								data-type="standard"
								data-theme="<?php echo esc_attr( $theme ); ?>"
								data-text=="s<?php echo esc_attr( $text ); ?>"
								data-size="large"
								data-logo_alignment="left"
								data-width="<?php echo esc_attr( $width ); ?>">
							</div>
						</div>
				<?php } elseif ( 'yes' === $this->settings->google_login_select && empty( $uabb_social_google_client_id ) && FLBuilderModel::is_builder_active() ) { ?>

							<label class="uabb-social-error-message">
								<?php esc_attr_e( 'Please configure the Google Client ID from', 'uabb' ); ?>
								<b>
									<?php
										esc_attr_e( 'Dashboard > Settings > UABB > Social Login Settings', 'uabb' );
									?>
								</b>
							</label>
				<?php } ?>

				<?php if ( 'yes' === $this->settings->facebook_login_select && isset( $uabb_social_facebook_app_id ) && ! empty( $uabb_social_facebook_app_id ) && isset( $uabb_social_facebook_app_secret ) && ! empty( $uabb_social_facebook_app_secret ) ) { ?>

						<div class="uabb-lf-input-group uabb-lf-row uabb-lf-facebook-login uabb-lf-facebook-button-wrap">
							<div class="uabb-lf-input-group uabb-lf-row">
								<div class="uabb-facebook-content-wrapper uabb-fbLink <?php echo esc_attr( $facebook_button_class ); ?>" id="uabb-fbLink" data-appid="<?php echo esc_attr( $uabb_social_facebook_app_id ); ?>">
									<div class="uabb-facebook-button-icon">
										<div class="uabb-facebook-button-iconImage">
										<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 216 216" width="20px" height="20px" class="uabb-facebook-button-svg" color="#FFFFFF"><path fill="#FFFFFF" d="M204.1 0H11.9C5.3 0 0 5.3 0 11.9v192.2c0 6.6 5.3 11.9 11.9
										11.9h103.5v-83.6H87.2V99.8h28.1v-24c0-27.9 17-43.1 41.9-43.1
										11.9 0 22.2.9 25.2 1.3v29.2h-17.3c-13.5 0-16.2 6.4-16.2
										15.9v20.8h32.3l-4.2 32.6h-28V216h55c6.6 0 11.9-5.3
										11.9-11.9V11.9C216 5.3 210.7 0 204.1 0z"></path></svg>
										</div>
									</div>
									<span class="uabb-facebook-text"><?php echo esc_html( apply_filters( 'uabb_login_form_facebook_button_text', $facebook_button_string ) ); ?></span>
								</div>
							</div>
						</div>
				<?php } elseif ( 'yes' === $this->settings->facebook_login_select && ( empty( $uabb_social_facebook_app_id ) || empty( $uabb_social_facebook_app_secret ) ) && FLBuilderModel::is_builder_active() ) { ?>

						<label class="uabb-social-error-message">
							<?php
								esc_attr_e( 'Please configure the Facebook App ID and Facebook App Secret from', 'uabb' );
							?>
								<b>
									<?php
										esc_attr_e( 'Dashboard > Settings > UABB > Social Login Settings', 'uabb' );
									?>
								</b>
						</label>
				<?php } ?>
			</div>
	<?php	} ?>

		<?php
	}
}

/*
 * Condition to verify Beaver Builder version.
 * And accordingly render the required form settings file.
 *
 */

if ( UABB_Compatibility::$version_bb_check ) {

	require_once BB_ULTIMATE_ADDON_DIR . 'modules/uabb-login-form/uabb-login-bb-2-2-compatibility.php';
} else {

	require_once BB_ULTIMATE_ADDON_DIR . 'modules/uabb-login-form/uabb-login-bb-less-than-2-2-compatibility.php';
}

