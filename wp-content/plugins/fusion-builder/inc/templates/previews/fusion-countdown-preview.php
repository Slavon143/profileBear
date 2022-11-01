<?php
/**
 * Underscore.js template.
 *
 * @package fusion-builder
 */

$fusion_settings = awb_get_fusion_settings();

$show_weeks = strtolower( $fusion_settings->get( 'countdown_show_weeks' ) );
?>
<script type="text/template" id="fusion-builder-block-module-countdown-preview-template">

	<div class="fusion_countdown_timer">
		<h4 class="fusion_module_title"><span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>{{ fusionAllElements[element_type].name }}</h4>

		<#
		var countdownEnd = params.countdown_end,
			targetTime = new Date(),
			nowTime = new Date(),
			showWeeks = ( '' === params.show_weeks ) ? '<?php echo esc_attr( $show_weeks ); ?>' : params.show_weeks,
			secs = 0,
			mins = 0,
			hours = 0,
			days = 0,
			weeks = 0;

		if ( '' !== countdownEnd && 'undefined' !== typeof countdownEnd ) {
			var timer = countdownEnd.replace( ' ', '-' ).replace( new RegExp( ':', 'g' ), '-' ).split( '-' ),
				targetTime = new Date( timer[1] + '/' + timer[2] + '/' + timer[0] + ' ' + timer[3] + ':' + timer[4] + ':' + timer[5] ),
				differenceInSecs = Math.floor( ( targetTime.valueOf() - nowTime.valueOf()) / 1000 );

			secs = differenceInSecs % 60,
			mins = Math.floor( differenceInSecs / 60 ) % 60,
			hours = Math.floor( differenceInSecs / 60 / 60 ) % 24;

			if ( 'no' === showWeeks ) {
				days  = Math.floor( differenceInSecs / 60 / 60 / 24 );
				weeks = Math.floor( differenceInSecs / 60 / 60 / 24 / 7 );
			} else {
				days  = Math.floor( differenceInSecs / 60 / 60 / 24 ) % 7,
				weeks = Math.floor( differenceInSecs / 60 / 60 / 24 / 7 );
			}
		}

		if ( isNaN( weeks ) && isNaN( days ) && isNaN( hours ) && isNaN( mins ) && isNaN( secs ) ) { #>

			<span>Invalid date format.</span>

		<# } else {

			if ( 'no' !== showWeeks ) { #>
				<?php /* translators: Number. */ ?>
				<span><?php printf( esc_html__( '%s Weeks', 'fusion-builder' ), '{{ weeks }}' ); ?></span>
			<# } #>

			<?php /* translators: Number. */ ?>
			<span><?php printf( esc_html__( '%s Days', 'fusion-builder' ), '{{ days }}' ); ?></span>
			<?php /* translators: Number. */ ?>
			<span><?php printf( esc_html__( '%s Hrs', 'fusion-builder' ), '{{ hours }}' ); ?></span>
			<?php /* translators: Number. */ ?>
			<span><?php printf( esc_html__( '%s Min', 'fusion-builder' ), '{{ mins }}' ); ?></span>
			<?php /* translators: Number. */ ?>
			<span><?php printf( esc_html__( '%s Sec', 'fusion-builder' ), '{{ secs }}' ); ?></span>
		<# } #>

	</div>

</script>
