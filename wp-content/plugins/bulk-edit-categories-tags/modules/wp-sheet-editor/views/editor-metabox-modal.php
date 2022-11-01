
<style>
	.vgca-iframe-wrapper iframe {
		width: 100%;
		min-width: 100%;
		max-width: 100%;
		height: 100%;
		position: absolute;
		border: 0px;
		left: 0;
	}
	.vgca-iframe-wrapper {
		width: 100%;
		min-width: 100%;
		max-width: 100%;
		height: 100%;
		position: relative;	
		min-height: 80px;
		display: none;
	}
	.lds-ring {
		display: inline-block;
		position: relative;
		width: 64px;
		height: 64px;
	}
	.lds-ring div {
		box-sizing: border-box;
		display: block;
		position: absolute;
		width: 51px;
		height: 51px;
		margin: 6px;
		border: 6px solid #fff;
		border-radius: 50%;
		animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
		border-color: #fff transparent transparent transparent;
	}
	.lds-ring div:nth-child(1) {
		animation-delay: -0.45s;
	}
	.lds-ring div:nth-child(2) {
		animation-delay: -0.3s;
	}
	.lds-ring div:nth-child(3) {
		animation-delay: -0.15s;
	}
	@keyframes lds-ring {
		0% {
			transform: rotate(0deg);
		}
		100% {
			transform: rotate(360deg);
		}
	}
	.vgca-loading-indicator {
		display: block;
		background: #00000030;
		position: absolute;
		z-index: 9;
		left: 49%;
		border-radius: 50%;
	}

</style>
<div  class="vgca-iframe-wrapper">
	<!--Loading indicator-->
	<div class="lds-ring vgca-loading-indicator"><div></div><div></div><div></div><div></div></div>
	<div class="iframe-template" id="vgca-iframe-<?php echo esc_attr($editor->provider->key); ?>" data-src="<?php
			if ($editor->provider->key === 'user') {
				$base_url = 'user-edit.php?wpse_source=1&wpse_metabox_iframe=1&wpse_post_type=' . $current_post_type . '&user_id=';
			} elseif ($editor->provider->key === 'term') {
				$base_url = 'term.php?wpse_source=1&wpse_metabox_iframe=1&wpse_post_type=' . $current_post_type . '&taxonomy=' . $current_post_type . '&tag_ID=';
			} else {
				$base_url = 'post.php?wpse_source=1&wpse_metabox_iframe=1&wpse_post_type=' . $current_post_type . '&action=edit&post=';
			}
			echo esc_url(admin_url($base_url));
			?>"></div>
</div>