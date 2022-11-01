
<form action="http://newsletters.apps.vegacorp.me/lists/bh871wnhpee1a/subscribe" method="post" accept-charset="utf-8" target="_blank" class="">
	<?php echo sprintf(__('<p>As a thank you for using our plugin, we want to give you access to a <b>Premium Extension for free</b>.</p>
				<p>The <b>Auto fill cells</b> extension allows you to copy information between posts.</p>
				<p><img src="%s" /></p><p>Copy categories , excerpts , dates , authors , etc.</p>
				<p>Please enter your email below and we will send you the download link for free.</p><p>We will also send you tips on how to use the spreadsheet editor to improve your SEO, increase visitors and subscribers, and <b>edit your posts extra fast</b>.</p>', VGSE()->textname), VGSE()->plugin_url . 'assets/imgs/drag-down-autofill-demo.gif'); ?>
    <div class="form-group">
        <label><?php _e('Email', $this->textname); ?></label>
		<?php
		$user = get_userdata(get_current_user_id());
		?>
        <input type="email" class="form-control vg-email" name="EMAIL" placeholder="<?php _e('My email is....', $this->textname); ?>" value="<?php echo esc_attr($user->user_email); ?>" required style="
			   min-width: 220px;
			   display: block;
               margin-bottom: 10px;
			   ">
        <button type="submit" class="button-primary"><?php _e('Download extension for free', $this->textname); ?></button> 
        - <a class="button vg-close-optin-notice"><b><?php _e('Dont show this again', $this->textname); ?></b></a>
	</div>

    <br class="clearfix"> </br>

</form>