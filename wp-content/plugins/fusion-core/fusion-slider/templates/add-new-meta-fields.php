<?php
/**
 * New meta fields template.
 *
 * @package Fusion-Slider
 * @subpackage Templates
 * @since 1.0.0
 */

?>
<?php wp_nonce_field( 'fusion_core_meta_fields_nonce', 'fusion_core_meta_fields_nonce' ); ?>
<div class="form-field">
	<label for="term_meta[slider_width]"><?php esc_html_e( 'Slider Size', 'fusion-core' ); ?></label>
	<div class="fusion-double-fields">
		<div class="fusion-field avada-db-input-indicator">
			<span class="avada-db-indicator"><i class="fusiona-expand avada-db-rotate-45" aria-hidden="true"></i></span>
			<input type="text" name="term_meta[slider_width]" id="term_meta[slider_width]" value="100%">
		</div>
		<div class="fusion-field avada-db-input-indicator">
			<span class="avada-db-indicator"><i class="fusiona-expand avada-db-rotate-m45" aria-hidden="true"></i></span>
			<input type="text" name="term_meta[slider_height]" id="term_meta[slider_height]" value="400px">
		</div>
	</div>
	<p class="description"><?php esc_html_e( 'Enter a pixel value for width and height, ex: 1000px', 'fusion-core' ); ?></p>
</div>
<div class="form-field">
	<label for="term_meta[slider_content_width]"><?php esc_html_e( 'Slider Content Max Width', 'fusion-core' ); ?></label>
	<input type="text" name="term_meta[slider_content_width]" id="term_meta[slider_content_width]" value="">
	<p class="description"><?php esc_html_e( 'Controls the content width. In pixels, ex: 850px. Leave blank for site width.', 'fusion-core' ); ?></p>
</div>
<div class="form-field form-field-checkbox">
	<label for="term_meta[full_screen]"><?php esc_html_e( 'Full Screen Slider', 'fusion-core' ); ?></label>
	<div class="avada-db-checkbox-wrapper">
		<input type="hidden" name="term_meta[full_screen]" id="term_meta[full_screen]" value="0">
		<input type="checkbox" name="term_meta[full_screen]" id="term_meta[full_screen]" value="1">
		<p class="description"><?php esc_html_e( 'Check this option if you want full width and height of the screen.', 'fusion-core' ); ?></p>
	</div>
</div>
<div class="form-field form-field-checkbox">
	<label for="term_meta[parallax]"><?php esc_html_e( 'Parallax Scrolling Effect', 'fusion-core' ); ?></label>
	<div class="avada-db-checkbox-wrapper">
		<input type="hidden" name="term_meta[parallax]" id="term_meta[parallax]" value="0">
		<input type="checkbox" name="term_meta[parallax]" id="term_meta[parallax]" value="1">
		<p class="description"><?php esc_html_e( 'Check this box to enable a parallax scrolling effect. This only works when assigning the slider in page options and does not for the element. When enabled, the slider height you input will not be exact due to negative margin which is based on the overall header size. ex: 500px will show as 415px. Please adjust accordingly.', 'fusion-core' ); ?></p>
	</div>
</div>
<div class="form-field form-field-checkbox">
	<label for="term_meta[slider_indicator]"><?php esc_html_e( 'Slider Indicator', 'fusion-core' ); ?></label>
	<select name="term_meta[slider_indicator]" id="term_meta[slider_indicator]">
		<option value=""><?php esc_html_e( 'None', 'fusion-core' ); ?></option>
		<option value="scroll_down_indicator"><?php esc_html_e( 'Scroll Down Indicator', 'fusion-core' ); ?></option>
		<option value="pagination_circles"><?php esc_html_e( 'Pagination Circles', 'fusion-core' ); ?></option>
	</select>
	<p class="description"><?php esc_html_e( 'Choose do you want to display pagination circler or scroll down indicator.', 'fusion-core' ); ?></p>
</div>
<div class="form-field">
	<label for="term_meta[slider_indicator_color]"><?php esc_html_e( 'Slider Indicator Color', 'fusion-core' ); ?></label>
	<input type="text" name="term_meta[slider_indicator_color]" id="slider_indicator_color" value="">
	<?php /* translators: The default value. */ ?>
	<?php $default = sprintf( esc_html__( 'Default: %s', 'fusion-core' ), '#fff' ); ?>
	<?php /* translators: The default value text. */ ?>
	<p class="description"><?php printf( esc_html__( 'Select a color for the slider indicator icon. Hex color code, ex: #fff. %s', 'fusion-core' ), '<strong>' . esc_attr( $default ) . '</strong>' ); ?></p>
</div>
<div class="form-field form-field-checkbox">
	<label for="term_meta[nav_arrows]"><?php esc_html_e( 'Display Navigation Arrows', 'fusion-core' ); ?></label>
	<div class="avada-db-checkbox-wrapper">
		<input type="hidden" name="term_meta[nav_arrows]" id="term_meta[nav_arrows]" value="0">
		<input type="checkbox" name="term_meta[nav_arrows]" id="term_meta[nav_arrows]" value="1" checked="checked">
		<p class="description"><?php esc_html_e( 'Check this box to display the navigation arrows.', 'fusion-core' ); ?></p>
	</div>
</div>
<div class="form-field">
	<label for="term_meta[nav_arrow_size]"><?php esc_html_e( 'Navigation Arrow Size', 'fusion-core' ); ?></label>
	<input type="text" name="term_meta[nav_arrow_size]" id="term_meta[nav_arrow_size]" value="25px">
	<p class="description"><?php esc_html_e( 'Enter a pixel value for the arrow size, ex: 14px', 'fusion-core' ); ?></p>
</div>
<div class="form-field">
	<label for="term_meta[nav_box_width]"><?php esc_html_e( 'Navigation Box Size', 'fusion-core' ); ?></label>
	<div class="fusion-double-fields">
		<div class="fusion-field avada-db-input-indicator">
			<span class="avada-db-indicator"><i class="fusiona-expand avada-db-rotate-45" aria-hidden="true"></i></span>
			<input type="text" name="term_meta[nav_box_width]" id="term_meta[nav_box_width]" value="63px">
		</div>
		<div class="fusion-field avada-db-input-indicator">
			<span class="avada-db-indicator"><i class="fusiona-expand avada-db-rotate-m45" aria-hidden="true"></i></span>
			<input type="text" name="term_meta[nav_box_height]" id="term_meta[nav_box_height]" value="63px">
		</div>
	</div>
	<p class="description"><?php esc_html_e( 'Enter a pixel value for width and height, ex: 40px', 'fusion-core' ); ?></p>
</div>
<div class="form-field">
	<label for="term_meta[slideshow_speed]"><?php esc_html_e( 'Slideshow Speed', 'fusion-core' ); ?></label>
	<input type="text" name="term_meta[slideshow_speed]" id="term_meta[slideshow_speed]" value="7000">
	<p class="description"><?php esc_html_e( 'Controls the speed of the slideshow. 1000 = 1 second.', 'fusion-core' ); ?></p>
</div>
<div class="form-field form-field-checkbox">
	<label for="term_meta[loop]"><?php esc_html_e( 'Slide Loop', 'fusion-core' ); ?></label>
	<div class="avada-db-checkbox-wrapper">
		<input type="hidden" name="term_meta[loop]" id="term_meta[loop]" value="0">
		<input type="checkbox" name="term_meta[loop]" id="term_meta[loop]" value="1">
		<p class="description"><?php esc_html_e( 'Check this box to have the slider loop infinitely.', 'fusion-core' ); ?></p>
	</div>
</div>
<div class="form-field form-field-checkbox">
	<label for="term_meta[autoplay]"><?php esc_html_e( 'Autoplay', 'fusion-core' ); ?></label>
	<div class="avada-db-checkbox-wrapper">
		<input type="hidden" name="term_meta[autoplay]" id="term_meta[autoplay]" value="0">
		<input type="checkbox" name="term_meta[autoplay]" id="term_meta[autoplay]" value="1" checked="checked">
		<p class="description"><?php esc_html_e( 'Check this box to autoplay the slides.', 'fusion-core' ); ?></p>
	</div>
</div>
<div class="form-field">
	<label for="term_meta[orderby]"><?php esc_html_e( 'Order By', 'fusion-core' ); ?></label>
	<select name="term_meta[orderby]" id="term_meta[orderby]">
		<option value="date"><?php esc_html_e( 'Date', 'fusion-core' ); ?></option>
		<option value="ID"><?php esc_html_e( 'ID', 'fusion-core' ); ?></option>
		<option value="title"><?php esc_html_e( 'Title', 'fusion-core' ); ?></option>
		<option value="modified"><?php esc_html_e( 'Modified', 'fusion-core' ); ?></option>
		<option value="rand"><?php esc_html_e( 'Random', 'fusion-core' ); ?></option>
	</select>
	<p class="description"><?php esc_html_e( 'Defines how the slides should be ordered.', 'fusion-core' ); ?></p>
</div>
<div class="form-field">
	<label for="term_meta[order]"><?php esc_html_e( 'Order', 'fusion-core' ); ?></label>
	<select name="term_meta[order]" id="term_meta[order]">
		<option value="DESC"><?php esc_html_e( 'Descending', 'fusion-core' ); ?></option>
		<option value="ASC"><?php esc_html_e( 'Ascending', 'fusion-core' ); ?></option>
	</select>
	<p class="description"><?php esc_html_e( 'Defines the sorting order of the slides.', 'fusion-core' ); ?></p>
</div>
<div class="form-field">
	<label for="term_meta[animation]"><?php esc_html_e( 'Animation', 'fusion-core' ); ?></label>
	<select name="term_meta[animation]" id="term_meta[animation]">
		<option value="fade"><?php esc_html_e( 'Fade', 'fusion-core' ); ?></option>
		<option value="slide"><?php esc_html_e( 'Slide', 'fusion-core' ); ?></option>
	</select>
	<p class="description">
		<?php esc_html_e( 'The type of animation when slides rotate.', 'fusion-core' ); ?>		
	</p>
</div>
<div class="form-field">
	<label for="term_meta[animation_speed]"><?php esc_html_e( 'Animation Speed', 'fusion-core' ); ?></label>
	<input type="text" name="term_meta[animation_speed]" id="term_meta[animation_speed]" value="600">
	<p class="description"><?php esc_html_e( 'Controls the speed of the slide transition from slide to slide. 1000 = 1 second.', 'fusion-core' ); ?></p>
</div>
<div class="form-field">
	<label for="term_meta[typo_sensitivity]"><?php esc_html_e( 'Responsive Typography Sensitivity', 'fusion-core' ); ?></label>
	<input type="text" name="term_meta[typo_sensitivity]" id="term_meta[typo_sensitivity]" value="1">
	<p class="description"><?php _e( 'Enter a value between <code>0</code> and <code>1</code>. ex: <code>0.1</code>.', 'fusion-core' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
</div>
<div class="form-field">
	<label for="term_meta[typo_factor]"><?php esc_html_e( 'Minimum Font Size Factor', 'fusion-core' ); ?></label>
	<input type="text" name="term_meta[typo_factor]" id="term_meta[typo_factor]" value="1.5">
	<p class="description"><?php esc_html_e( 'The minimum font-size of elements affected by responsive typography is body font-size multiplied by this factor.', 'fusion-core' ); ?></p>
</div>
