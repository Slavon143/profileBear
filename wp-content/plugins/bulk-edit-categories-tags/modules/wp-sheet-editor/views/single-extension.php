<div class="wpb_wrapper extension-item"><div class="vc_icon_element vc_icon_element-outer vc_icon_element-align-center"><div class="vc_icon_element-inner vc_icon_element-color-blue vc_icon_element-size-xl vc_icon_element-style- vc_icon_element-background-color-grey">
			<?php
			if (!empty($extension['image'])) {
				echo wp_kses_post($extension['image']);
			} else {
				?>
				<span class="vc_icon_element-icon fa <?php echo esc_attr($extension['icon']); ?>"></span>
			<?php } ?>
		</div></div>
	<div class="wpb_text_column wpb_content_element "><div class="wpb_wrapper"><h3><?php echo esc_html($extension['title']); ?></h3><?php echo wp_kses_post($extension['description']); ?>

		</div></div>
	<div class="addon-status"><?php
		if ($is_active) {
			echo '<p><i class="fa fa-check"></i>' . __('Active.', VGSE()->textname) . '</p>';
		}

		echo esc_html($extension['status']);
		?></div>
	<div class="addon-action">		
		<?php if (!empty($button_url) && !empty($button_label)) { ?>
			<a target="_blank" href="<?php echo esc_url($button_url); ?>" class="button button-primary button-primary"><?php echo esc_html($button_label); ?></a>
		<?php } ?>
	</div>

</div>