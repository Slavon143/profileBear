<?php
/**
 * An underscore.js template.
 *
 * @package fusion-builder
 */

?>
<script type="text/template" id="fusion-layout-options">
	<div class="fusion-layout-overlay"></div>
	<div class="layoutbox fusion-layout-options">
		<!-- heading -->
		<div class="layoutbox-heading">
			<div class="layoutbox-title">
				<?php esc_html_e( 'Layout Conditions', 'fusion-builder' ); ?>
			</div>
			<div class="layoutbox-controls layoutbox-controls-right">
				<span style="display:none;" class="loader"><?php esc_html_e( 'Saving conditions', 'fusion-builder' ); ?></span>
				<a class="control close fusiona-layout-close" href="#" aria-label="<?php esc_attr_e( 'Close Modal', 'fusion-builder' ); ?>"></a>
			</div>
		</div>
		<!-- content -->
		<div class="layoutbox-content">
			<div class="layout-options-section">
				<?php $first = true; ?>
				<?php foreach ( Fusion_Template_Builder()->get_layout_conditions() as $type ) : ?>
					<a class="layout-option-type<?php echo $first ? ' current' : ''; ?>" href="#<?php echo esc_attr( preg_replace( '/\s+/', '_', $type['label'] ) ); ?>">
						<?php echo esc_html( $type['label'] ); ?>
						<?php $first = false; ?>
					</a>
				<?php endforeach; ?>
			</div>
			<div class="layout-options-section">
				<?php
				$count = 0;
				$tabs  = [
					[
						'id'    => 'include',
						'label' => esc_html__( 'Include', 'fusion-builder' ),
					],
					[
						'id'    => 'exclude',
						'label' => esc_html__( 'Exclude', 'fusion-builder' ),
					],
				];
				?>
				<?php foreach ( Fusion_Template_Builder()->get_layout_conditions() as $parent_id => $type ) : ?>
					<div id="<?php echo esc_attr( preg_replace( '/\s+/', '_', $type['label'] ) ); ?>" class="layout-option-tab" <?php echo 0 !== $count ? 'style="display:none"' : ''; ?> >
						<?php foreach ( $type['conditions'] as $key => $condition ) : ?>
							<div class="layout-option">
								<?php if ( isset( $condition['multiple'] ) ) : ?>
									<?php $options = Fusion_Template_Builder()->get_layout_child_conditions( $condition['id'] ); ?>
									<div data-page="1" data-condition="<?php echo esc_attr( $condition['id'] ); ?>" class="layout-option-parent<?php echo 10 > count( $options ) ? ' no-search' : ''; ?>">
										<a href="#" class="load-child" aria-label="<?php esc_attr_e( 'Open Layout Options', 'fusion-builder' ); ?>">
											<?php echo esc_html( $condition['label'] ); ?>
											<i aria-hidden="true" class="fusiona-chevron-small-down" aria-hidden="true"></i>
										</a>
										<div class="child-options-wrap">
											<div class="layoutbox-loader"><span class="fusion-builder-loader"></span></div>
											<?php if ( 10 <= count( $options ) ) : ?>
												<div class="layoutbox-search">
													<div class="layoutbox-search-input">
														<i class="fusiona-search" aria-hidden="true"></i>
														<input placeholder="Search... " type="search" />
													</div>
													<ul class="layoutbox-search-results">
													</ul>
												</div>
											<?php endif; ?>
											<ul class="child-options">
												<?php
												foreach ( $options as $option ) {
													$option['checked'] = false;
													echo '<# print( templateForChildOption( ' . wp_json_encode( $option ) . ' ) ) #>';
												}
												?>
											</ul>
											<?php if ( 10 <= count( $options ) ) : ?>
												<button data-empty="<?php esc_html_e( 'NO MORE ITEMS', 'fusion-builder' ); ?>" class="load-more" type="button">
													<i class="fusiona-loop-alt2" aria-hidden="true"></i>
													<span>
														<?php esc_html_e( 'LOAD MORE', 'fusion-builder' ); ?>
													</span>
												</button>
											<?php elseif ( 0 === count( $options ) ) : ?>
												<button disabled class="load-more disabled" type="button">
													<i class="fusiona-loop-alt2" aria-hidden="true"></i>
													<span>
														<?php esc_html_e( 'NO ITEMS', 'fusion-builder' ); ?>
													</span>
												</button>
											<?php endif; ?>
										</div>
									</div>
									<ul class="child-options-preview"></ul>
								<?php else : ?>
									<input id="<?php echo esc_attr( $condition['id'] ); ?>-include" data-type="<?php echo esc_attr( $condition['type'] ); ?>" data-label="<?php echo esc_attr( $condition['label'] ); ?>" type="checkbox" name="<?php echo esc_attr( $condition['id'] ); ?>" value="include">
									<label aria-hidden="true" for="<?php echo esc_attr( $condition['id'] ); ?>-include" class="option-include">
										<i class="fusiona-checkmark" aria-hidden="true"></i>
									</label>
									<input id="<?php echo esc_attr( $condition['id'] ); ?>-exclude" data-type="<?php echo esc_attr( $condition['type'] ); ?>" data-label="<?php echo esc_attr( $condition['label'] ); ?>" type="checkbox" name="<?php echo esc_attr( $condition['id'] ); ?>" value="exclude">
									<label aria-hidden="true" for="<?php echo esc_attr( $condition['id'] ); ?>-exclude" class="option-exclude">
										<i class="fusiona-cross" aria-hidden="true"></i>
									</label>
									<span id="<?php echo esc_attr( $condition['id'] ); ?>" class="layout-option-label">
										<?php echo esc_html( $condition['label'] ); ?>
									</span>
								<?php endif ?>
							</div>
						<?php endforeach; ?>
					</div>
					<?php $count++; ?>
				<?php endforeach; ?>
			</div>
			<div class="layout-options-section">
				<h3><?php esc_attr_e( 'Manage Conditions', 'fusion-builder' ); ?></h3>
				<div class="layout-manage-conditions include">
					<div><?php esc_attr_e( 'Include', 'fusion-builder' ); ?></div>
					<div class="layout-conditions"></div>
				</div>
				<div class="layout-manage-conditions exclude">
					<div><?php esc_attr_e( 'Exclude', 'fusion-builder' ); ?></div>
					<div class="layout-conditions"></div>
				</div>
				<div class="layout-manage-conditions empty-conditions" style="display:none;">
					<div><i class="fusiona-exclamation-sign" aria-hidden="true"></i><?php esc_attr_e( 'No condition selected', 'fusion-builder' ); ?></div>
				</div>
			</div>
		</div>
	</div>
</script>
