<?php
/**
 *  UABB FAQ Module front-end file
 *
 *  @package UABB FAQ
 */

$module->render_schema( true );
?>
<div class="uabb-faq-module uabb-faq-layout-<?php echo esc_attr( $settings->layout_style ); ?> uabb__faq-layout-<?php echo esc_attr( $settings->faq_layout ); ?>" >
	<?php if ( 'accordion' === esc_attr( $settings->faq_layout ) ) { ?>
		<div class="<?php echo ( FLBuilderModel::is_builder_active() ) ? 'uabb-faq-edit ' : ''; ?>uabb-module-content uabb-faq-module uabb-faq__layout-accordion
								<?php
								if ( 'yes' === esc_attr( $settings->faq_collapse ) ) {
									echo 'uabb-faq-collapse';}
								?>
		">
			<?php
			$item_count = count( $settings->faq_items );
			for ( $i = 0; $i < $item_count;
			$i++ ) :
				if ( empty( $settings->faq_items[ $i ] ) ) {
					continue;}
				?>
			<div role="tablist" class="uabb-faq-item"
				<?php
				if ( ! empty( $settings->id ) ) {
					echo ' id="' . sanitize_html_class( $settings->id ) . '-' . esc_attr( $i ) . '"';}
				?>
				data-index="<?php echo esc_attr( $i ); ?>">
				<div role="tab" class="uabb-faq-questions-button uabb-faq-questions-button<?php echo esc_attr( $id ); ?> uabb-faq-questions uabb-faq-questions<?php echo esc_attr( $id ); ?> uabb-faq-<?php echo esc_attr( $settings->icon_position ); ?>-text" aria-selected="false" tabindex="0" aria-expanded="true" aria-controls="expandable" data-index="<?php echo esc_attr( $i ); ?>">
					<?php echo wp_kses_post( $module->render_icon( 'before' ) ); ?>
					<<?php echo esc_attr( $settings->tag_selection ); ?> class="uabb-faq-question-label" tabindex="0" ><?php echo wp_kses_post( $settings->faq_items[ $i ]->faq_question ); ?></<?php echo esc_attr( $settings->tag_selection ); ?>>
					<?php echo wp_kses_post( $module->render_icon( 'after' ) ); ?>
				</div>
				<div role="tabpanel" class="uabb-faq-content uabb-faq-content<?php echo esc_attr( $id ); ?> fl-clearfix" aria-expanded="true" >
					<?php echo wp_kses_post( $module->get_faq_content( $settings->faq_items[ $i ] ) ); ?>
				</div>
			</div>
			<?php endfor; ?>
		</div>
	<?php } else { ?>
		<div class="uabb-faq-module uabb-module-content uabb-faq__column-<?php echo esc_attr( $settings->columns ); ?> uabb-faq__column-medium-<?php echo esc_attr( $settings->columns_medium ); ?> uabb-faq__column-responsive-<?php echo esc_attr( $settings->columns_responsive ); ?> ">
			<div class="uabb-faq-wrap uabb-faq__layout-grid uabb-faq-equal-<?php echo esc_attr( $settings->faq_equal_height ); ?>" >
				<?php
				$item_count = count( $settings->faq_items );
				for ( $i = 0; $i < $item_count;
				$i++ ) :
					if ( empty( $settings->faq_items[ $i ] ) ) {
						continue;}
					?>
			<div role="tablist" class="uabb-faq-item"
					<?php
					if ( ! empty( $settings->id ) ) {
						echo ' id="' . sanitize_html_class( $settings->id ) . '-' . esc_attr( $i ) . '"';}
					?>
					>
			<div class="uabb-faq-item-wrap">
				<div role="tab" class="uabb-faq-questions-button uabb-faq-questions uabb-faq-questions<?php echo esc_attr( $id ); ?> uabb-faq-<?php echo esc_attr( $settings->icon_position ); ?>-text">
					<<?php echo esc_attr( $settings->tag_selection ); ?> class="uabb-faq-question-label"><?php echo wp_kses_post( $settings->faq_items[ $i ]->faq_question ); ?></<?php echo esc_attr( $settings->tag_selection ); ?>>
				</div>
				<div role="tabpanel" class="uabb-faq-content uabb-faq-content<?php echo esc_attr( $id ); ?> fl-clearfix">
					<?php echo wp_kses_post( $module->get_faq_content( $settings->faq_items[ $i ] ) ); ?>
				</div>
			</div>
			</div>
				<?php endfor; ?>
			</div>
		</div>
	<?php } ?>
</div>
