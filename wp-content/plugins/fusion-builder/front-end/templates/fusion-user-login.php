<?php
/**
 * Underscore.js template
 *
 * @package fusion-builder
 * @since 2.0
 */

?>
<script type="text/html" id="tmpl-fusion_login-shortcode">
<#
var placeholder;

var html = '<div ' + _.fusionGetAttributes( loginShortCodeAttr ) + '>' + styles;

if ( loggedIn ) {

	html += '<h3 class="fusion-login-heading">' + values.heading + '</h3>';
	html += '<div class="fusion-login-caption">' + values.caption + '</div>';
	html += '<' + values.main_container + ' ' + _.fusionGetAttributes( loginShortcodeFormAttr ) + '>';

	placeholder  = 'yes' === values.show_placeholders  ? 'placeholder="' + extras.useroremail_text + '"' : '';
	html += '<div class="fusion-login-fields">';

	html += '<div class="fusion-login-input-wrapper">';
	html += '<label class="' + values.label_class + '" for="user_login">' + extras.useroremail_text + '</label>';
	html += '<input type="text" name="log" ' + placeholder + ' value="" size="20" class="fusion-login-username input-text" id="user_login" />';
	html += '</div>';

	placeholder  = 'yes' === values.show_placeholders  ? 'placeholder="' + extras.password_text + '"' : '';
	html += '<div class="fusion-login-input-wrapper">';
	html += '<label class="' + values.label_class + '" for="user_pass">' + extras.password_text + '</label>';
	html += '<input type="password" name="pwd" ' + placeholder + ' value="" size="20" class="fusion-login-password input-text" id="user_pass" />';
	html += '</div>';

	html += '</div>';

	html += '<div class="fusion-login-additional-content">';

	html += '<div class="fusion-login-submit-wrapper">';
	html += '<button ' + _.fusionGetAttributes( loginShortcodeButton ) + '>' + extras.login_text + '</button>';

	// Set the query string for successful password reset.
	if ( ! values.redirection_link ) {
		values.redirection_link = '#';
	}

	html += '</div>';

	html += '<div class="fusion-login-links">';

	if ( 'yes' === values.show_remember_me ) {
		html += '<label class="fusion-login-remember-me"><input name="rememberme" type="checkbox" id="rememberme" value="forever" />' + extras.rememberme_text + '</label>';
	}
	if ( '' !== values.lost_password_link ) {
		html += '<a class="fusion-login-lost-passowrd" target="_self" href="' + values.lost_password_link + '">' + extras.lost_text + '</a>';
	}
	if ( '' !== values.register_link ) {
		html += '<a class="fusion-login-register" target="_self" href="' + values.register_link + '">' + extras.register_text + '</a>';
	}
	html += '</div>';

	html += '</div>';

	html += '</' + values.main_container + '>';
} else {

	html += '<div class="fusion-login-caption">' + extras.welcome_text + '</div>';
	html += '<div class="fusion-login-avatar">' + extras.user_avatar + '</div>';
	html += '<ul class="fusion-login-loggedin-links">';
	html += '<li><a href="#">' + extras.dashboard_text + '</a></li>';
	html += '<li><a href="#">' + extras.profile_text + '</a></li>';
	html += '<li><a href="#">' + extras.logout_text + '</a></li>';
	html += '</ul>';

}

html += '</div>';
#>
{{{ html }}}
</script>
<script type="text/html" id="tmpl-fusion_lost_password-shortcode">
<#
var html = '';

if ( loggedIn ) {

	html = '<div ' + _.fusionGetAttributes( loginShortCodeAttr ) + '>' + styles;
	html += '<h3 class="fusion-login-heading">' + values.heading + '</h3>';
	html += '<div class="fusion-login-caption">' + values.caption + '</div>';
	html += '<' + values.main_container + ' ' + _.fusionGetAttributes( loginShortcodeFormAttr ) + '>';

	html += '<p class="fusion-login-input-wrapper">' + extras.lostfull_text + '</p>';

	placeholder  = 'yes' === values.show_placeholders  ? 'placeholder="' + extras.useroremail_text + '"' : '';
	html += '<div class="fusion-login-input-wrapper">';
	html += '<label class="' + values.label_class + '" for="user_login">' + extras.useroremail_text + '</label>';
	html += '<input type="text" name="user_login" ' + placeholder + ' value="" size="20" class="fusion-login-username input-text" id="user_login" />';
	html += '</div>';

	html += '<div class="fusion-login-submit-wrapper">';
	html += '<button ' + _.fusionGetAttributes( loginShortcodeButton ) + '>' + extras.reset_text + '</button>';

	html += '</div>';

	html += '</' + values.main_container + '>';
	html += '</div>';

} else {
	html += '[fusion_alert type="general" border_size="1px" box_shadow="yes"] You are already signed in [/fusion_alert]';
}
#>
{{{ html }}}
</script>
<script type="text/html" id="tmpl-fusion_register-shortcode">
<#
var html = register_note = '';
if ( loggedIn ) {
	html = '<div ' + _.fusionGetAttributes( loginShortCodeAttr ) + '>' + styles;
	html += '<h3 class="fusion-login-heading">' + values.heading + '</h3>';
	html += '<div class="fusion-login-caption">' + values.caption + '</div>';
	html += '<' + values.main_container + ' ' + _.fusionGetAttributes( loginShortcodeFormAttr ) + '>';

	placeholder  = 'yes' === values.show_placeholders  ? 'placeholder="' + extras.useroremail_text + '"' : '';
	html += '<div class="fusion-login-fields">';

	html += '<div class="fusion-login-input-wrapper">';
	html += '<label class="' + values.label_class + '" for="user_login">' + extras.username_text + '</label>';
	html += '<input type="text" name="user_login" ' + placeholder + ' value="" size="20" class="fusion-login-username input-text" id="user_login" />';
	html += '</div>';

	placeholder  = 'yes' === values.show_placeholders  ? 'placeholder="' + extras.email_text + '"' : '';
	html += '<div class="fusion-login-input-wrapper">';
	html += '<label class="' + values.label_class + '" for="user_pass">' + extras.email_text + '</label>';
	html += '<input type="text" name="user_email" ' + placeholder + ' value="" size="20" class="fusion-login-email input-text" id="user_email" />';
	html += '</div>';

	html += '</div>';

	if ( '' !== values.register_note ) {
		html += '<p class="fusion-login-input-wrapper">' + values.register_note + '</p>';
	}

	html += '<div class="fusion-login-submit-wrapper">';
	html += '<button ' + _.fusionGetAttributes( loginShortcodeButton ) + '>' + extras.register_text + '</button>';
	html += '</div>';

	html += '</' + values.main_container + '>';
	html += '</div>';

} else {
	html += FusionPageBuilderApp.renderContent( '[fusion_alert type="general" border_size="1px" box_shadow="yes"] You are already signed up [/fusion_alert]' );
}
#>
{{{ html }}}
</script>
