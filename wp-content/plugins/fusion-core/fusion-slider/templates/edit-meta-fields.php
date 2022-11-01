<?php
/**
 * Edit meta-fields template.
 *
 * @package Fusion-Slider
 * @subpackage Templates
 * @since 1.0.0
 */

?>
<?php wp_nonce_field( 'fusion_core_meta_fields_nonce', 'fusion_core_meta_fields_nonce' ); ?>
<tr class="form-field fusion-double-fields">
	<th scope="row" valign="top"><label for="term_meta[slider_width]"><?php esc_html_e( 'Slider Size', 'fusion-core' ); ?></label></th>
	<td>
		<div class="fusion-double-fields">
			<div class="fusion-field avada-db-input-indicator">
				<span class="avada-db-indicator"><i class="fusiona-expand avada-db-rotate-45" aria-hidden="true"></i></span>
				<input type="text" name="term_meta[slider_width]" id="term_meta[slider_width]" value="<?php echo esc_attr( $term_meta['slider_width'] ) ? esc_attr( $term_meta['slider_width'] ) : ''; ?>">
			</div>
			<div class="fusion-field avada-db-input-indicator">
				<span class="avada-db-indicator"><i class="fusiona-expand avada-db-rotate-m45" aria-hidden="true"></i></span>
				<input type="text" name="term_meta[slider_height]" id="term_meta[slider_height]" value="<?php echo esc_attr( $term_meta['slider_height'] ) ? esc_attr( $term_meta['slider_height'] ) : ''; ?>">
			</div>
		</div>
		<p class="description"><?php esc_html_e( 'Enter a pixel value for width and height, ex: 1000px', 'fusion-core' ); ?></p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[slider_content_width]"><?php esc_html_e( 'Slider Content Max Width', 'fusion-core' ); ?></label></th>
	<td>
		<input type="text" name="term_meta[slider_content_width]" id="term_meta[slider_content_width]" value="<?php echo ( isset( $term_meta['slider_content_width'] ) && esc_attr( $term_meta['slider_content_width'] ) ) ? esc_attr( $term_meta['slider_content_width'] ) : ''; ?>">
		<p class="description"><?php esc_html_e( 'Controls the content width. In pixels, ex: 850px. Leave blank for site width.', 'fusion-core' ); ?></p>
	</td>
</tr>
<tr class="form-field form-field-checkbox">
	<th scope="row" valign="top"><label for="term_meta[full_screen]"><?php esc_html_e( 'Full Screen Slider', 'fusion-core' ); ?></label></th>
	<td>
		<div class="avada-db-checkbox-wrapper">
			<input type="hidden" name="term_meta[full_screen]" id="term_meta[full_screen]" value="0">
			<input type="checkbox" name="term_meta[full_screen]" id="term_meta[full_screen]" value="1" <?php echo esc_attr( $term_meta['full_screen'] ) ? 'checked="checked"' : ''; ?>>
			<p class="description"><?php esc_html_e( 'Check this option if you want full width and height of the screen. NOTE: This option is available on slider shortcodes as \'Full Height\'.', 'fusion-core' ); ?></p>
		</div>
	</td>
</tr>
<tr class="form-field form-field-checkbox">
	<th scope="row" valign="top"><label for="term_meta[parallax]"><?php esc_html_e( 'Parallax Scrolling Effect', 'fusion-core' ); ?></label></th>
	<td>
		<div class="avada-db-checkbox-wrapper">
			<input type="hidden" name="term_meta[parallax]" id="term_meta[parallax]" value="0">
			<input type="checkbox" name="term_meta[parallax]" id="term_meta[parallax]" value="1" <?php echo esc_attr( $term_meta['parallax'] ) ? 'checked="checked"' : ''; ?>>
			<p class="description"><?php esc_html_e( 'Check this box to enable a parallax scrolling effect. This only works when assigning the slider in page options and does not for the element. When enabled, the slider height you input will not be exact due to negative margin which is based on the overall header size. ex: 500px will show as 415px. Please adjust accordingly.', 'fusion-core' ); ?></p>
		</div>
	</td>
</tr>


<tr class="form-field form-field-checkbox">
	<th scope="row" valign="top"><label for="term_meta[slider_indicator]"><?php esc_html_e( 'Slider Indicator', 'fusion-core' ); ?></label></th>
	<td>
		<select name="term_meta[slider_indicator]" id="term_meta[slider_indicator]">
			<option value=""><?php esc_html_e( 'None', 'fusion-core' ); ?></option>
			<option value="scroll_down_indicator" <?php echo ( isset( $term_meta['slider_indicator'] ) && 'scroll_down_indicator' === esc_attr( $term_meta['slider_indicator'] ) ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Scroll Down Indicator', 'fusion-core' ); ?></option>
			<option value="pagination_circles" <?php echo ( isset( $term_meta['slider_indicator'] ) && 'pagination_circles' === esc_attr( $term_meta['slider_indicator'] ) ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Pagination Circles', 'fusion-core' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Choose do you want to display pagination circler or scroll down indicator.', 'fusion-core' ); ?></p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[slider_indicator_color]"><?php esc_html_e( 'Slider Indicator Color', 'fusion-core' ); ?></label></th>
	<td>
		<input type="text" name="term_meta[slider_indicator_color]" id="slider_indicator_color" value="<?php echo ( isset( $term_meta['slider_indicator_color'] ) && esc_attr( $term_meta['slider_indicator_color'] ) ) ? esc_attr( $term_meta['slider_indicator_color'] ) : ''; ?>">
		<?php /* translators: The default value. */ ?>
		<?php $default = sprintf( esc_html__( 'Default: %s', 'fusion-core' ), '#fff' ); ?>
		<?php /* translators: The default value text. */ ?>
		<p class="description"><?php printf( esc_html__( 'Select a color for the slider indicator icon. Hex color code, ex: #fff. %s', 'fusion-core' ), '<strong>' . esc_html( $default ) . '</strong>' ); ?></p>
	</td>
</tr>

<tr class="form-field form-field-checkbox">
	<th scope="row" valign="top"><label for="term_meta[nav_arrows]"><?php esc_html_e( 'Display Navigation Arrows', 'fusion-core' ); ?></label></th>
	<td>
		<div class="avada-db-checkbox-wrapper">
			<input type="hidden" name="term_meta[nav_arrows]" id="term_meta[nav_arrows]" value="0">
			<input type="checkbox" name="term_meta[nav_arrows]" id="term_meta[nav_arrows]" value="1" <?php echo esc_attr( $term_meta['nav_arrows'] ) ? 'checked="checked"' : ''; ?>>
			<p class="description"><?php esc_html_e( 'Check this box to display the navigation arrows.', 'fusion-core' ); ?></p>
		</div>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[nav_arrow_size]"><?php esc_html_e( 'Navigation Arrow Size', 'fusion-core' ); ?></label></th>
	<td>
		<input type="text" name="term_meta[nav_arrow_size]" id="term_meta[nav_arrow_size]" value="<?php echo ( isset( $term_meta['nav_arrow_size'] ) && esc_attr( $term_meta['nav_arrow_size'] ) ) ? esc_attr( $term_meta['nav_arrow_size'] ) : ''; ?>">
		<p class="description"><?php esc_html_e( 'Enter a pixel value for the arrow size, ex: 14px', 'fusion-core' ); ?></p>
	</td>
</tr>
<tr class="form-field fusion-double-fields">
	<th scope="row" valign="top"><label for="term_meta[nav_box_width]"><?php esc_html_e( 'Navigation Box Size', 'fusion-core' ); ?></label></th>
	<td>
		<div class="fusion-double-fields">
			<div class="fusion-field avada-db-input-indicator">
				<span class="avada-db-indicator"><i class="fusiona-expand avada-db-rotate-45" aria-hidden="true"></i></span>
				<input type="text" name="term_meta[nav_box_width]" id="term_meta[nav_box_width]" value="<?php echo esc_attr( $term_meta['nav_box_width'] ) ? esc_attr( $term_meta['nav_box_width'] ) : ''; ?>">
			</div>
			<div class="fusion-field avada-db-input-indicator">
				<span class="avada-db-indicator"><i class="fusiona-expand avada-db-rotate-m45" aria-hidden="true"></i></span>
				<input type="text" name="term_meta[nav_box_height]" id="term_meta[nav_box_height]" value="<?php echo esc_attr( $term_meta['nav_box_height'] ) ? esc_attr( $term_meta['nav_box_height'] ) : ''; ?>">
			</div>
		</div>

		<p class="description"><?php esc_html_e( 'Enter a pixel for height. Width accepts pixel and percentage values., ex: 40px', 'fusion-core' ); ?></p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[slideshow_speed]"><?php esc_html_e( 'Slideshow Speed', 'fusion-core' ); ?></label></th>
	<td>
		<input type="text" name="term_meta[slideshow_speed]" id="term_meta[slideshow_speed]" value="<?php echo esc_attr( $term_meta['slideshow_speed'] ) ? esc_attr( $term_meta['slideshow_speed'] ) : ''; ?>">
		<p class="description"><?php esc_html_e( 'Controls the speed of the slideshow. 1000 = 1 second.', 'fusion-core' ); ?></p>
	</td>
</tr>
<tr class="form-field form-field-checkbox">
	<th scope="row" valign="top"><label for="term_meta[loop]"><?php esc_html_e( 'Slide Loop', 'fusion-core' ); ?></label></th>
	<td>
		<div class="avada-db-checkbox-wrapper">
			<input type="hidden" name="term_meta[loop]" id="term_meta[loop]" value="0">
			<input type="checkbox" name="term_meta[loop]" id="term_meta[loop]" value="1" <?php echo esc_attr( $term_meta['loop'] ) ? 'checked="checked"' : ''; ?>>
			<p class="description"><?php esc_html_e( 'Check this box to have the slider loop infinitely.', 'fusion-core' ); ?></p>
		</div>
	</td>
</tr>
<tr class="form-field form-field-checkbox">
	<th scope="row" valign="top"><label for="term_meta[autoplay]"><?php esc_html_e( 'Autoplay', 'fusion-core' ); ?></label></th>
	<td>
		<div class="avada-db-checkbox-wrapper">
			<input type="hidden" name="term_meta[autoplay]" id="term_meta[autoplay]" value="0">
			<input type="checkbox" name="term_meta[autoplay]" id="term_meta[autoplay]" value="1" <?php echo esc_attr( $term_meta['autoplay'] ) ? 'checked="checked"' : ''; ?>>
			<p class="description"><?php esc_html_e( 'Check this box to autoplay the slides.', 'fusion-core' ); ?></p>
		</div>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[orderby]"><?php esc_html_e( 'Order By', 'fusion-core' ); ?></label></th>
	<td>
		<select name="term_meta[orderby]" id="term_meta[orderby]">
			<option value="date" <?php echo ( isset( $term_meta['orderby'] ) && 'date' === esc_attr( $term_meta['orderby'] ) ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Date', 'fusion-core' ); ?></option>
			<option value="ID" <?php echo ( isset( $term_meta['orderby'] ) && 'ID' === esc_attr( $term_meta['orderby'] ) ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'ID', 'fusion-core' ); ?></option>
			<option value="title" <?php echo ( isset( $term_meta['orderby'] ) && 'title' === esc_attr( $term_meta['orderby'] ) ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Title', 'fusion-core' ); ?></option>
			<option value="modified" <?php echo ( isset( $term_meta['orderby'] ) && 'modified' === esc_attr( $term_meta['orderby'] ) ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Modified', 'fusion-core' ); ?></option>
			<option value="rand" <?php echo ( isset( $term_meta['orderby'] ) && 'rand' === esc_attr( $term_meta['orderby'] ) ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Random', 'fusion-core' ); ?></option>
		</select>
		<p class="description"><?php esc_attr_e( 'Defines how the slides should be ordered.', 'fusion-core' ); ?></p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[order]"><?php esc_attr_e( 'Order', 'fusion-core' ); ?></label></th>
	<td>
		<select name="term_meta[order]" id="term_meta[order]">
			<option value="DESC" <?php echo ( isset( $term_meta['order'] ) && 'DESC' === esc_attr( $term_meta['order'] ) ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Descending', 'fusion-core' ); ?></option>
			<option value="ASC" <?php echo ( isset( $term_meta['order'] ) && 'ASC' === esc_attr( $term_meta['order'] ) ) ? 'selected="selected"' : ''; ?>><?php esc_html_e( 'Ascending', 'fusion-core' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'Defines the sorting order of the slides.', 'fusion-core' ); ?></p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[animation]"><?php esc_html_e( 'Animation', 'fusion-core' ); ?></label></th>
	<td>
		<select name="term_meta[animation]" id="term_meta[animation]">
		<option value="fade" <?php echo ( esc_attr( $term_meta['animation'] ) === 'fade' ) ? 'selected="selected"' : ''; ?>>Fade</option>
		<option value="slide" <?php echo ( esc_attr( $term_meta['animation'] ) === 'slide' ) ? 'selected="selected"' : ''; ?>>Slide</option>
		</select>
		<p class="description">
			<?php esc_html_e( 'The type of animation when slides rotate.', 'fusion-core' ); ?>
		</p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[animation_speed]"><?php esc_html_e( 'Animation Speed', 'fusion-core' ); ?></label></th>
	<td>
		<input type="text" name="term_meta[animation_speed]" id="term_meta[animation_speed]" value="<?php echo esc_attr( $term_meta['animation_speed'] ) ? esc_attr( $term_meta['animation_speed'] ) : ''; ?>">
		<p class="description"><?php esc_html_e( 'Controls the speed of the slide transition from slide to slide. 1000 = 1 second.', 'fusion-core' ); ?></p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[typo_sensitivity]"><?php esc_html_e( 'Responsive Typography Sensitivity', 'fusion-core' ); ?></label></th>
	<td>
		<input type="text" name="term_meta[typo_sensitivity]" id="term_meta[typo_sensitivity]" value="<?php echo esc_attr( $term_meta['typo_sensitivity'] ) ? esc_attr( $term_meta['typo_sensitivity'] ) : ''; ?>">
		<p class="description"><?php _e( 'Enter a value between <code>0</code> and <code>1</code>. ex: <code>0.1</code>.', 'fusion-core' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	</td>
</tr>
<tr class="form-field">
	<th scope="row" valign="top"><label for="term_meta[typo_factor]"><?php esc_html_e( 'Minimum Font Size Factor', 'fusion-core' ); ?></label></th>
	<td>
		<input type="text" name="term_meta[typo_factor]" id="term_meta[typo_factor]" value="<?php echo esc_attr( $term_meta['typo_factor'] ) ? esc_attr( $term_meta['typo_factor'] ) : ''; ?>">
		<p class="description"><?php esc_html_e( 'The minimum font-size of elements affected by responsive typography is body font-size multiplied by this factor.', 'fusion-core' ); ?></p>
	</td>
</tr>
