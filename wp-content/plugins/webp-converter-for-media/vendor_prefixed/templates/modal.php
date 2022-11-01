<?php

namespace WebpConverterVendor;

/**
 * @var string                                           $plugin_slug   .
 * @var MattPlugins\DeactivationModal\Model\FormTemplate $form_template .
 * @var MattPlugins\DeactivationModal\Model\FormOptions  $form_options  .
 * @var MattPlugins\DeactivationModal\Model\FormValues   $form_values   .
 *
 * @package Gbiorczyk\DeactivationModal
 */
?>

<div class="mattDeactivationModal"
	data-matt-deactivation-modal="<?php 
echo esc_attr($plugin_slug);
?>"
	hidden>
	<div class="mattDeactivationModal__wrapper">
		<form action="<?php 
echo esc_url($form_template->get_api_url());
?>"
			method="POST"
			class="mattDeactivationModal__form"
			data-matt-deactivation-modal-form>
			<button type="button"
				class="mattDeactivationModal__close dashicons dashicons-no"
				data-matt-deactivation-modal-button-close></button>

			<?php 
if ($form_template->get_logo_url() === null) {
    ?>
				<div class="mattDeactivationModal__headline">
					<?php 
    echo wp_kses_post($form_template->get_form_title());
    ?>
				</div>
			<?php 
} else {
    ?>
				<div class="mattDeactivationModal__headline" style="background-image: url(<?php 
    echo esc_attr($form_template->get_logo_url());
    ?>);">
					<?php 
    echo wp_kses_post($form_template->get_form_title());
    ?>
				</div>
			<?php 
}
?>
			<div class="mattDeactivationModal__desc">
				<?php 
echo wp_kses_post($form_template->get_form_desc());
?>
			</div>

			<ul class="mattDeactivationModal__options">
				<?php 
foreach ($form_options->get_options() as $option) {
    $option_id = \sprintf('option-%1$s-%2$s', $plugin_slug, $option->get_key());
    ?>
					<li class="mattDeactivationModal__option">
						<input type="radio"
							name="<?php 
    echo esc_attr($form_template->get_field_name_reason());
    ?>"
							value="<?php 
    echo esc_attr($option->get_key());
    ?>"
							id="<?php 
    echo esc_attr($option_id);
    ?>"
							class="mattDeactivationModal__optionInput">
						<label
							for="<?php 
    echo esc_attr($option_id);
    ?>"
							class="mattDeactivationModal__optionLabel">
							<?php 
    echo esc_html($option->get_label());
    ?>
						</label>
						<div class="mattDeactivationModal__optionExtra">
							<?php 
    $message = $option->get_message() !== null ? \call_user_func($option->get_message()) : null;
    if ($message !== null) {
        ?>
								<div class="mattDeactivationModal__optionMessage">
									<?php 
        echo wp_kses_post($message);
        ?>
								</div>
							<?php 
    }
    ?>
							<?php 
    if ($option->get_question() !== null) {
        ?>
								<div class="mattDeactivationModal__optionBox">
									<label
										for="<?php 
        echo esc_attr($option_id);
        ?>-message"
										class="mattDeactivationModal__optionBoxLabel">
										<?php 
        echo esc_html($option->get_question());
        ?>
									</label>
									<textarea class="mattDeactivationModal__optionBoxTextarea"
										name="<?php 
        echo esc_attr(\sprintf($form_template->get_field_name_comment(), $option->get_key()));
        ?>"
										id="<?php 
        echo esc_attr($option_id);
        ?>-message"
										placeholder=". . ."
										rows="2"></textarea>
								</div>
							<?php 
    }
    ?>
						</div>
					</li>
				<?php 
}
?>
			</ul>

			<ul class="mattDeactivationModal__buttons">
				<li class="mattDeactivationModal__button">
					<button type="submit"
						class="mattDeactivationModal__buttonInner mattDeactivationModal__buttonInner--blue"
						data-matt-deactivation-modal-button-submit>
						<?php 
echo esc_html($form_template->get_button_submit_label());
?>
					</button>
				</li>
				<li class="mattDeactivationModal__button">
					<button type="button"
						class="mattDeactivationModal__buttonInner mattDeactivationModal__buttonInner--gray"
						data-matt-deactivation-modal-button-skip>
						<?php 
echo esc_html($form_template->get_button_skip_label());
?>
					</button>
				</li>
			</ul>

			<?php 
foreach ($form_values->get_values() as $form_value) {
    ?>
				<input type="hidden"
					name="<?php 
    echo esc_attr($form_value->get_key());
    ?>"
					value="<?php 
    echo esc_attr(\call_user_func($form_value->get_value_callback()));
    ?>">
			<?php 
}
?>
		</form>
	</div>
</div>
<?php 
