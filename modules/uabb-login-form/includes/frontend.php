<?php
/**
 *  UABB Login Form Module front-end file
 *
 *  @package UABB Login Form Module
 */

if ( isset( $settings->input_field_width ) && ! empty( $settings->input_field_width ) ) {

	$input_field_width_class = 'uabb-lf-input-width_' . $settings->input_field_width;
}
if ( isset( $settings->wp_login_btn_col_width ) && ! empty( $settings->wp_login_btn_col_width ) ) {

	$button_width_class = 'uabb-lf-btn-width_' . $settings->wp_login_btn_col_width;
}

if ( ! is_user_logged_in() || FLBuilderModel::is_builder_active() ) {

	?>
	<div class="uabb-lf-form-wrap" data-nonce=<?php echo esc_attr( wp_create_nonce( 'uabb-lf-nonce' ) ); ?>>
		<?php
		if ( isset( $settings->social_buttons_position ) && 'top' === $settings->social_buttons_position ) {

			$module->render_social_form();
			$module->render_advanced_separator();
		}

		?>
		<?php if ( 'yes' === $settings->wp_login_select ) { ?>
		<div class="uabb-lf-custom-wp-login-form">
			<form class="uabb-lf-login-form">
			<?php if ( 'top' === $settings->error_msg_select ) { ?>
				<div class="uabb-lf-custom-error">
					<div class="uabb-lf-error-message-wrap">
						<label class="uabb-lf-error-message"></label>
					</div>
				</div>
				<?php
			}
			if ( 'yes' === $settings->enable_label ) {
				?>
				<div class="uabb-lf-input-group uabb-lf-label">
					<label for="uabb-lf-name"><?php echo wp_kses_post( $settings->username_label ); ?></label>
				</div>
				<?php } ?>
				<div class="uabb-lf-input-group uabb-lf-row uabb-lf-username-input">
					<input type="text" name="uabb-lf-name" aria-label="username" class="uabb-lf-username uabb-lf-form-input <?php echo esc_attr( $input_field_width_class ); ?>" value="" 
					<?php if ( 'yes' === $settings->enable_placeholder ) { ?>
						placeholder="<?php echo esc_attr( $settings->username_placeholder ); ?>" <?php } ?> required />
					<?php if ( 'enable' === $settings->fields_icon ) { ?>
						<span class="uabb-field-icon"><i class="fa fa-user"></i></span>
					<?php } ?>
				</div>
				<?php if ( 'yes' === $settings->enable_label ) { ?>
				<div class="uabb-lf-input-group uabb-lf-label">
					<label for="uabb-lf-password"> <?php echo wp_kses_post( $settings->password_label ); ?></label>
				</div>
				<?php } ?>
				<div class="uabb-lf-input-group uabb-lf-row uabb-lf-password-input">
					<input type="password" id="uabb-password-field" name="uabb-lf-password" aria-label="password" class="uabb-lf-password uabb-lf-form-input <?php echo esc_attr( $input_field_width_class ); ?> " value="" 
					<?php if ( 'yes' === $settings->enable_placeholder ) { ?>
						placeholder="<?php echo esc_attr( $settings->password_placeholder ); ?>" <?php } ?> required />
					<?php if ( 'enable' === $settings->fields_icon ) { ?>
						<span class="uabb-field-icon"><i class="fa fa-lock"></i></span>
					<?php } ?>
					<?php if ( 'enable' === $settings->eye_icon ) { ?>
							<span toggle="#uabb-password-field" aria-hidden="true" class="fa fa-fw fa-eye uabb-lf-icon toggle-password"></span>
						<?php } ?>
				</div>
				<?php if ( 'enable' === $settings->remember_me_select ) { ?>
					<div class="uabb-lf-input-group uabb-lf-row uabb-lf-checkbox">
						<div class="uabb-lf-outter">
							<label class="uabb-lf-checkbox-label" for="uabb-lf-checkbox-<?php echo esc_attr( $id ); ?>">
								<input type="checkbox" class="uabb-lf-remember-me-checkbox checkbox-inline" id="uabb-lf-checkbox-<?php echo esc_attr( $id ); ?>" name="uabb-lf-checkbox" value="1" />
								<span class="checkbox-label">
									<?php echo wp_kses_post( $settings->remember_me_text ); ?>
								</span>
							</label>
						</div>
					</div>
				<?php } ?>

				<div class="uabb-lf-input-group uabb-lf-row uabb-lf-submit-button-wrap">
					<div class="uabb-lf-submit-button-align">
					<button type="submit" class="uabb-lf-submit-button <?php echo esc_attr( $button_width_class ); ?>" name="uabb-lf-login-submit">
						<?php
						if ( isset( $settings->btn_icon ) && isset( $settings->btn_icon_position ) ) {

							echo ( '' !== $settings->btn_icon && 'before' === $settings->btn_icon_position ) ? '<i class="' . esc_attr( $settings->btn_icon ) . ' uabb-login-form-submit-button-icon "></i>' : ''; }
						?>
						<span class="uabb-login-form-button-text"><?php echo wp_kses_post( $settings->wp_login_btn_text ); ?></span>
						<?php
						if ( isset( $settings->btn_icon ) && isset( $settings->btn_icon_position ) ) {
							echo ( '' !== $settings->btn_icon && 'after' === $settings->btn_icon_position ) ? '<i class="' . esc_attr( $settings->btn_icon ) . ' uabb-login-form-submit-button-icon"></i>' : ''; }
						?>
					</button>
					</div>
				</div>
				<div class="uabb-lf-end-text-wrap">
					<?php if ( 'enable' === $settings->custom_link_select ) { ?>
						<div class="uabb-lf-input-group uabb-lf-row uabb-lf-custom-link">
							<a class="uabb-lf-lost-your-pass-label" href="
							<?php
								echo esc_url( $settings->custom_link_url )
							?>
								"
								> <?php echo esc_html( $settings->custom_link_text ); ?>
							</a>
						</div>
					<?php } ?>
					<?php if ( 'enable' === $settings->lost_your_password_select ) { ?>
						<div class="uabb-lf-input-group uabb-lf-row uabb-lf-lost-password">
							<a class="uabb-lf-lost-your-pass-label" href="
							<?php
							if ( 'default' === $settings->lost_your_password_custom_select ) {
								?>
								<?php echo esc_url( wp_lostpassword_url() ); ?>
								<?php } else { ?>
								<?php echo esc_url( $settings->lost_your_password_url ); ?>
								<?php } ?>"
								> <?php echo wp_kses_post( $settings->lost_your_password_text ); ?>
							</a>
						</div>
					<?php } ?>
				</div>
				<?php if ( 'bottom' === $settings->error_msg_select ) { ?>
				<div class="uabb-lf-custom-error">
					<div class="uabb-lf-error-message-wrap">
						<label class="uabb-lf-error-message"></label>
					</div>
				</div>
		<?php } ?>
			</form>
		</div>
		<?php } ?>		
		<?php
		if ( isset( $settings->social_buttons_position ) && 'bottom' === $settings->social_buttons_position ) {

			$module->render_advanced_separator();
			$module->render_social_form();
		}

		?>
	</div>
	<?php
} else {
	global $current_user;
	$uabb_lf_succesfully_login_text = __( 'You are Succesfully Logged in as ', 'uabb' );
	$uabb_lf_logout_text            = __( 'Logout', 'uabb' );
	?>
		<div class="uabb-lf-logout-text">
		<p> 
		<?php
		echo esc_html( apply_filters( 'uabb_lf_succesfully_login_text', $uabb_lf_succesfully_login_text ) );
		?>
					<b> 
					<?php
					echo esc_html( $current_user->display_name ) . '.'; 
					?>
			</b><a href="
			<?php
			if ( 'default' === $settings->wp_logout_redirect_select ) {

				echo esc_url( wp_logout_url() );
			} else {

				echo esc_url( wp_logout_url( $settings->logout_redirect_url ) );
			}

			?>
		"> <?php echo esc_html( apply_filters( 'uabb_lf_logout_text', $uabb_lf_logout_text ) ); ?></a> </p> 
		</div>

	<?php
}
?>
