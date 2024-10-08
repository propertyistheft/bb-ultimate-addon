<?php
/**
 * Render the frontend content.
 *
 * @package UABB Info Box Module
 */

$nofollow = '';
$target   = '';
if ( isset( $settings->link_nofollow ) ) {
	$nofollow = $settings->link_nofollow;
}
if ( isset( $settings->link_target ) ) {
	$target = $settings->link_target;
}
$stacked_class = '';
if ( 'none' !== $settings->image_type ) {
	if ( 'right' === $settings->img_icon_position ) {
		if ( 'stack' === $settings->mobile_view ) {
			if ( 'reversed' === $settings->stacking_order ) {
				$stacked_class = 'uabb-reverse-order';
			}
		}
	}
}
?>
<?php
if ( 'module' === $settings->cta_type && ! empty( $settings->link ) ) {
	$link_rel = BB_Ultimate_Addon_Helper::get_link_rel( $target, $nofollow, 0 );
	echo '<a href="' . esc_url( $settings->link ) . '" target="' . esc_attr( $target ) . '" ' . ( ! is_null( $link_rel ) ? wp_kses_post( $link_rel ) : '' ) . ' class="uabb-infobox-module-link" aria-label="' . esc_attr__( 'Go to', 'uabb' ) . ' ' . esc_attr( $settings->link ) . '">';
}
?>
<div class="uabb-module-content <?php echo wp_kses_post( $module->get_classname() ); ?> <?php echo esc_attr( $stacked_class ); ?>">
	<div class="uabb-infobox-left-right-wrap">
		<?php
		$module->render_image( 'left' ); // Image left.
		// phpcs:disable Squiz.PHP.EmbeddedPhp.ContentAfterEnd
		?><div class="uabb-infobox-content">
			<?php
			// phpcs:enable
			$module->render_image( 'above-title' ); // Image above title.
			$module->render_title(); // Title.
			$module->render_image( 'below-title' ); // Image below title.
			$module->render_separator(); // Separator.

			if ( '' !== $settings->text || 'link' === $settings->cta_type || 'button' === $settings->cta_type ) {
				?>
			<div class="uabb-infobox-text-wrap">
				<?php
				
				$module->render_text(); // Text.
				$module->render_link(); // Link CTA.
				$module->render_button(); // Button CTA.
				?>
			</div>
				<?php
			}
			?>
        </div><?php // @codingStandardsIgnoreLine.
		// phpcs:disable Squiz.PHP.EmbeddedPhp.ContentAfterEnd
		// Image right.
		$module->render_image( 'right' );
		// phpcs:enable
		?>
	</div>
</div>
<?php
if ( 'module' === $settings->cta_type && ! empty( $settings->link ) ) {
	echo '</a>';
}
?>
