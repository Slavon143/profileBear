<!-- template="page" -->
<a name="backtotop"></a>
&nbsp;
<div class="wrap">
<h1 class="wp-heading-inline">[+plugin_title+] [+version+] Settings</h1>
[+messages+]
[+tablist+]
[+tab_content+]
</div><!-- wrap -->

<!-- template="tablist" -->
<h2 class="nav-tab-wrapper">
[+tablist+]
</h2>
<!-- template="tablist-item" -->
<a data-tab-id="[+data-tab-id+]" class="nav-tab [+nav-tab-active+]" href="?page=[+settings-page+]&amp;mla_tab=[+data-tab-id+]">[+title+]</a>

<!-- template="messages" -->
<div class="[+mla_messages_class+]">
<p>
[+messages+]
</p>
</div>

<!-- template="page-level-options" -->
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[acf_checkbox_enabled]" id="[+slug_prefix+]_options_acf_checkbox_enabled" type="checkbox" [+acf_checkbox_enabled_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable Checkbox Field</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to activate support for one ACF Checkbox field.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Checkbox Field</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[acf_checkbox_fields]" id="[+slug_prefix+]_options_acf_checkbox_fields" type="text" size="30" maxlength="30" value="[+acf_checkbox_fields+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one (and only one) checkbox field name (not label).</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Checkbox Titles</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[acf_checkbox_titles]" id="[+slug_prefix+]_options_acf_checkbox_titles" type="text" size="30" maxlength="30" value="[+acf_checkbox_titles+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one  (and only one) checkbox field title.</div>
  </td>
</tr>

<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[acf_select_enabled]" id="[+slug_prefix+]_options_acf_select_enabled" type="checkbox" [+acf_select_enabled_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable Select Fields</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to activate support for one or more ACF Select fields.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Select Fields</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[acf_select_fields]" id="[+slug_prefix+]_options_acf_select_fields" type="text" size="80" maxlength="80" value="[+acf_select_fields+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one or more select field names (not labels) separated by commas.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Select Titles</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[acf_select_titles]" id="[+slug_prefix+]_options_acf_select_titles" type="text" size="80" maxlength="80" value="[+acf_select_titles+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one or more optional select field titles separated by commas. An empty value will be replaced by the corresponding ACF Label.</div>
  </td>
</tr>

<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[acf_repeater_enabled]" id="[+slug_prefix+]_options_acf_repeater_enabled" type="checkbox" [+acf_repeater_enabled_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable Repeater Field</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to activate support for "where used" analysis of one ACF Repeater field.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Repeater Field</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[acf_repeater_fields]" id="[+slug_prefix+]_options_acf_repeater_fields" type="text" size="30" maxlength="30" value="[+acf_repeater_fields+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one (and only one) repeater field name (not label).</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Repeater Subfield</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[acf_repeater_subfields]" id="[+slug_prefix+]_options_acf_repeater_subfields" type="text" size="30" maxlength="30" value="[+acf_repeater_subfields+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one (and only one) repeater field subfield name (not label).</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Repeater Title</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[acf_repeater_titles]" id="[+slug_prefix+]_options_acf_repeater_titles" type="text" size="30" maxlength="30" value="[+acf_repeater_titles+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one (and only one) repeater field title.</div>
  </td>
</tr>

<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[acf_image_enabled]" id="[+slug_prefix+]_options_acf_image_enabled" type="checkbox" [+acf_image_enabled_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable Image Fields</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to activate support for "where used" analysis of one or more ACF Image fields.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Image Fields</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[acf_image_fields]" id="[+slug_prefix+]_options_acf_image_fields" type="text" size="80" maxlength="80" value="[+acf_image_fields+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one or more image field names (not labels) separated by commas.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Image Titles</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[acf_image_titles]" id="[+slug_prefix+]_options_acf_image_titles" type="text" size="80" maxlength="80" value="[+acf_image_titles+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one or more optional image field titles separated by commas. An empty value will be replaced by the corresponding ACF Label.</div>
  </td>
</tr>


<!-- template="general-tab" --> 
<style type='text/css'>
.mla-settings-help {
	font-size: 8pt;
	padding-bottom: 5px
}

.mla-page-level-options-form {
	margin-left: 0px;
	margin-top: 10px;
	padding-bottom: 10px;
	border-bottom:thin solid #888888;
}

span.submit.mla-settings-submit,
p.submit.mla-settings-submit {
	padding-bottom: 0px
}
</style>
<h2>Plugin Options</h2>
<div  style="max-width:700px">
<p>In this tab you can enable any of four types of enhanced support for the popular Advanced Custom Fields (ACF) plugin.</p>
<ol>
<li>An ACF "checkbox" custom field is added to the Media/Assistant submenu table, Quick Edit and Bulk Edit areas.</li>
<li>ACF "select" custom fields are made available in the Media/Assistant submenu table as columns that display the ACF label (Vs the value) ssigned to each choice. You can edit the fields by changing the label; the plugin will convert this to the corresponding value and update the field appropriately.</li>
<li>An ACF "repeater" custom field is analyzed to display "where used" information in the Media/Assistant submenu table.</li>
<li>ACF "image" custom fields are analyzed to display "where used" information in the Media/Assistant submenu table. Some "image" custom field(s) values are made available as custom data substitution parameters, using the prefix "acf:"</li>
</ol>
<p>You can find more information about using all of the features of this plugin in the Documentation tab on this page.</p>
</div>
<div class="mla-page-level-options-form">
	<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="[+slug_prefix+]_options_general_tab">
		<table class="optiontable">
		<tbody>
			[+options_list+]
		</tbody>
		</table>
		<span class="submit mla-settings-submit">
		<input name="[+slug_prefix+]_options_save" class="button-primary" id="[+slug_prefix+]_options_save" type="submit" value="Save Changes" />
		<input name="[+slug_prefix+]_options_reset" class="button-primary alignright" id="[+slug_prefix+]_options_reset" type="submit" value="Delete Settings, Restore Defaults" />
		</span>
		[+_wpnonce+]
	</form>
</div>

<!-- template="documentation-tab" -->
<style type='text/css'>
.mla-doc-toc-list {
	list-style-position:inside;
	list-style:disc;
	line-height: 15px;
	padding-left: 20px
}

.mla-doc-hook-label {
	text-align: right;
	padding: 0 1em 2em 0;
	vertical-align: top;
	font-weight:bold
}

.mla-doc-hook-definition {
	vertical-align: top;
}

.mla-doc-table-label {
	text-align: right;
	padding-right: 10px;
	vertical-align: top;
	font-weight:bold
}

.mla-doc-table-sublabel {
	padding-right: 10px;
	vertical-align: top
}

.mla-doc-table-reverse {
	text-align: right;
	padding-right: 10px;
	vertical-align:top
}

.mla-doc-table-definition {
	vertical-align: top;
}

.mla-doc-bold-link {
	font-size:14px;
	font-weight:bold
}
</style>
<h2>Plugin Documentation</h2>
<p>In this tab, jump to:</p>
<div class="mla-display-settings-page" id="[+slug_prefix+]_options_documentation_tab" style="width:700px">
<ul class="mla-doc-toc-list">
<li><a href="#introduction"><strong>Introduction</strong></a></li>
<li><a href="#processing"><strong>How the Plugin Works</strong></a></li>
<li><a href="#checkbox"><strong>Checkbox Field</strong></a></li>
<li><a href="#select"><strong>Select Fields</strong></a></li>
<li><a href="#repeater"><strong>Repeater Field "Where Used" Analysis</strong></a></li>
<li><a href="#image"><strong>Image Fields "Where Used" Analysis</strong></a></li>
<li><a href="#debugging"><strong>Debugging and Troubleshooting</strong></a></li>
</ul>
<p>
&nbsp;
<a name="introduction"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Introduction</h3>
<p>
The <a href="https://wordpress.org/plugins/advanced-custom-fields/" title="ACF, by Delicious Brains" target="_blank">Advanced Custom Fields</a> plugin allows you to quickly and easily add fields of many types to WP edit screens. The MLA Advanced Custom Fields Example plugin makes some types of ACF fields available in the Media/Assistant submenu table and the quick and bulk edit areas. It allows you to do "where-used" anaysis to see where Media Library items are used in ACF Image and Repeater fields. The example plugin was originally developed (as the "MLA ACF Checkbox Example" plugin) in response to this MLA support topic:
</p>
<ul class="mla-doc-toc-list">
<li><a href="https://wordpress.org/support/topic/bulk-edit-a-custom-field-value/#post-4428935" title="View the topic" target="_blank">Bulk edit a custom field value</a></li>
</ul>
<p>
The Select Field support was added in response to this MLA support topic:
</p>
<ul class="mla-doc-toc-list">
<li><a href="https://wordpress.org/support/topic/bulk-edit-acf-custom-field/" title="View the topic" target="_blank">Bulk edit ACF custom field</a></li>
</ul>
<p>
The Repeater Field support was added in response to this MLA support topic:
</p>
<ul class="mla-doc-toc-list">
<li><a href="https://wordpress.org/support/topic/advanced-custom-fields-repeater/" title="View the topic" target="_blank">Advanced Custom Fields repeater</a></li>
</ul>
<p>
The Image Field support was added in response to this MLA support topic:
</p>
<ul class="mla-doc-toc-list">
<li><a href="https://wordpress.org/support/topic/finding-where-used-in-custom-field/" title="View the topic" target="_blank">finding "where used" in custom field</a></li>
</ul>
<p>
To use the plugin you must configure the options on the General tab for the field type(s) you want to add to MLA. Each type has an "Enable" checkbox so you can turn it on or off easily, without deleting any of the other values you enter. For each of the types you must enter the Field Name(s) of the ACF fields you want to use. These names are all lowercase and may have underscores. You can also enter "Titles" to identify the fields in the MLA User Interface.
</p>
<p>
More details on the features available for each field type are in the sections of this Documentation page.
</p>
<p>
You might also be interested in the "MLA Parent Custom Field Mapping" example plugin, which allows IPTC/EXIF and Custom Field mapping rules to update custom fields in the item’s parent post/page rather than the items’s own fields. Support for Advanced Custom Fields and WP/LR Sync is included. That plugin was developed in response to this MLA support topic:
</p>
<ul class="mla-doc-toc-list">
<li><a href="https://wordpress.org/support/topic/iptc-exif-mapping-on-picture-upload-to-parent-posts-custom-fields/" title="View the topic" target="_blank">IPTC/EXIF Mapping on picture upload to parent post’s custom fields</a></li>
</ul>
<p>
&nbsp;
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
The example plugin makes no changes or additions to the MLA core code; it uses (or "hooks" - verb) some of the many actions and filters MLA provides. When the post/page is loaded this plugin is initialized, setting thirteen MLA "hooks" (noun) that may be called to perform the plugin&rsquo;s processing. There are three groups of action/filter functions that implement the features of this example plugin. Within each group the "Enable" option settings are examined to see if the corresponding ACF field type is to be processed. The outline that follows is somewhat technical, but should give you an idea of the sequence of events and actions that allow the plugin to do its work.
</p>
<p>
To support adding columns to the Media/Assistant admin submenu table five hooks are set:
</p>
<ul class="mla-doc-toc-list">
<li><strong>mla_list_table_get_columns</strong> - called at the start of composing the Media/Assistant submenu page. Column definitions for the fields defined on the General tab are added to the list.</li>
<li><strong>mla_list_table_get_hidden_columns</strong> - called when the submenu table navigation elements are composed. Definitions for this plugin&rsquo;s columns are added..</li>
<li><strong>mla_list_table_get_sortable_columns</strong> - called when the submenu table navigation elements are composed. Definitions for this plugin&rsquo;s columns are added.</li>
<li><strong>mla_list_table_column_default</strong> - Called when the MLA_List_Table can't find a value for a given column. This example plugin will look for a match with the fields defined on the General tab and supply the field&rsquo;s value if matched.</li>
<li><strong>mla_list_table_query_final_terms</strong> - called just before the database is queried to find items for the submenu table display. If a sort specification for a select column is present it is added to the query arguments.</li>
</ul>
<p>
All five of the above hooks are defined and called ("applied") in <code>/media-library-assistant/includes/class-mla-list-table.php</code>.
</p>
<p>
To support editing the ACF field values in the Media/Assistant Bulk and Quick Edit areas six hooks are set:
</p>
<ul class="mla-doc-toc-list">
<li><strong>mla_list_table_build_inline_data</strong> - called for each item as it is added to the table display. Current values for this plugin&rsquo;s fields are added.</li>
<li><strong>mla_list_table_inline_fields</strong> - called as the Quick and Bulk Edit areas are set up. This plugin&rsquo;s fields are added to the list of fields eligible for Quick and Bulk editing.</li>
<li><strong>mla_list_table_inline_action</strong> - called just before the Quick Edit updates are processed. This plugin&rsquo;s checkbox and select field values are converted from the values used in the User Interface to the values stored in the database.</li>
<li><strong>mla_list_table_bulk_action_initial_request</strong> - called at the start of the AJAX request that will apply Bulk Edit updates. This plugin&rsquo;s checkbox and select field values are saved for later processing and removed from the request to prevent MLA's default processing.</li>
<li><strong>mla_list_table_bulk_action</strong> - called just before the Bulk Edit updates are processed. This plugin&rsquo;s checkbox and select field values are converted from the values used in the User Interface to the values stored in the database.</li>
<li><strong>mla_list_table_inline_values</strong> - called as the Quick and Bulk Edit areas are set up. This plugin&rsquo;s fields are added to the HTML markup for the areas.</li>
</ul>
<p>
The build inline data hook is defined and called ("applied") in <code>/media-library-assistant/includes/class-mla-list-table.php</code>. The other five hooks are defined and called ("applied") in <code>/media-library-assistant/includes/class-mla-main.php</code>.
</p>
<p>
To support adding columns to the Media/Assistant admin submenu table one hook is set:
</p>
<ul class="mla-doc-toc-list">
<li><strong>mla_expand_custom_prefix</strong> - called when a substitution parameter's prefix value is not recognized by MLA. In this plugin the <code>acf:</code> custom prefix lowers the risk that the plugin&rsquo;s data-source name(s) will conflict with other plugins or future MLA versions.</li>
</ul>
<p>
The above hook is defined and called ("applied") in <code>/media-library-assistant/includes/class-mla-data.php</code>. It is the only plugin feature that must be available in the "front end" of the site, where it can be used with the MLA shortcodes to display galleries.
<a name="checkbox"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Checkbox Field</h3>
<p>
An ACF "Checkbox" field has one or more HTML checkbox elements, each with a value and a label. The value is stored in the database and the label is shown to the user. Each element can have just a label, in which case the label and value are identical, or a separate value and label. Each element is shown to the user as an HTML checkbox, and the value is stored in the database only if the box is checked.
</p>
<p>
This example plugin supports one ACF Checkbox field. The title displayed in the Media/Assistant admin submenu is set in the General tab. The "MLA value" for the field shows only the elements whose box is checked; the value is shown as a simple string if just one box is checked or as a comma-separated list if more than one box is checked. This plugin manages the values associated with the field, but you must also create an MLA Custom Field mapping rule to add the field to the UI. To create the ruls:
</p>
<ol>
<li>Navigate to the Settings/Media Library Assistant "Custom Fields" tab.</li></li>
<li>Scroll down to the "Add New Custom Field Rule" section on the left-hand side of the page.</li>
<li>Select the ACF field name in the "Name" dropdown. If you don't see it in the list, click "Enter new field" and enter the name of the field.</li>
<li>Leave the default Data Source dropdown, "– None (select a value) –". You don't want this rule to overwrite the ACF values.</li>
<li>Leave the Meta/Template text box empty.</li>
<li>Check the "MLA Column" box if you want to display the field as a column in the Media/Assistant submenu table.</li>
<li>Check the "Quick Edit" box to enable changing the field value(s) in the Media/Assistant Quick Edit area.</li>
<li>Check the "Bulk Edit" box to enable changing the field value(s) in the Media/Assistant Bulk Edit area.</li>
<li>Select "Keep" for "Existing Text".</li>
<li>Select "Native" for "Format".</li>
<li>Select "Text" for "Option".</li>
<li>Check the "Delete NULL Values" box to avoid storing empty values.</li>
<li>Leave the default "Status", "Active".</li>
<li>Click "Add Rule" to create the new rule.</li>
</ol>
<p>
Once the rule is in place this plugin will "capture" the custom field and apply its logic:
</p>
<ul class="mla-doc-toc-list">
<li>If the "MLA Column" box is checked the corresponding Media/Assistant table column will have the Title entered in the General tab and will display field values as a comma-separated list.</li>
<li>If the "Quick Edit" box is checked the field will appear in the Quick Edit Area. You can enter one or more comma-separated field values in the text box and they will be converted to an array for storage in the database.</li>
<li>If the "Bulk Edit" box is checked the field will appear in the Bulk Edit Area. You can enter one or more comma-separated field values in the text box and they will be converted to an array for storage in the database. You can also enter the special value "empty" to delete/uncheck all of the boxes in the field.</li>
</ul>
<p>
You can always define the custom field rule without naming it as the Checkbox field in this plugin. If the field has exactly one checkbox element you can enter the field value directly. However, if the field has more than one checkbox element the values will be shown as an array and the Quick and Bulk edit functions will not work..
</p>
<p>
<strong>NOTE:</strong> Although commas are allowed in the ACF field value, they are not supported in this plugin because the comma is used here to separate one value from another. If that's a problem, open a support topic and I will consider enhancing this plugin.
<a name="select"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Select Fields</h3>
<p>
An ACF "Select" field creates an HTML dropdown control with one or more choices, each with a value and a label. The value is stored in the database and the label is shown to the user. Each element can have just a label, in which case the label and value are identical, or a separate value and label. The choices shown to the user as an HTML dropdown control, and the selected value is stored in the database.
</p>
<p>
This example plugin supports one or more Select fields. The title displayed in the Media/Assistant admin submenu is set in the General tab. The "MLA value" for the field shows the label. To change the choice recorded in the database you can enter the label of the choice you want in the Quick or Bulk Edit areas. This plugin translates the label entered to the corresponding value and stores that in the database. You can sort the submenu table by clicking the column header. The sort order will be the field value, not the label, so the results might appear to be out of order.
</p>
<p>
If your ACF field definition allows "Select multiple values", the label(s) will be presented as a comma separated list. You can enter multiple comma separated labels in the Quick and Bulk Area text boxes. You can also enter the special value "empty" in the Bulk Edit text box to delete all choices.
</p>
<p>
You do not have to define an MLA Custom Field mapping rule for your select field(s). This plugin will automatically add the fields specified on the General tab to the Media/Assistant submenu table and the Quick and Bulk Edit areas. If you do define an MLA rule for the field it will be shown in addition to the field defined by this plugin. The MLA field will show and use the ACF values, not the labels, for its content.
</p>
<p>
&nbsp;
<a name="repeater"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Repeater Field "Where Used" Analysis</h3>
<p>
An ACF "Repeater" field provides a solution for repeating content. This field type acts as a parent to a set of sub fields which can be repeated again and again. Any kind of field can be used within a Repeater, and there are no limits to the number of repeats. If one of a Repeater's sub fields is an ACF "Image" field, this plugin can show you where each Media Library item is used in that sub field. 
</p>
<p>
This example plugin analyzes one Image sub field in one Repeater field; the results are shown in a Media/Assistant submenu table column. For each item used in the Repeater/Image sub field the column will show the title, type (post, page, etc.) and ID of the using entity. You can click on the title to go to the Edit page for the entity.
&nbsp;
<a name="image"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Image Fields "Where Used" Analysis</h3>
<p>
An ACF "Image" field allows an image to be uploaded and selected by using the native WordPress media modal. The ID of the Media Library item containing the image is stored in the database.
</p>
<p>
This example plugin analyzes one or Image field(s); the results for each field are shown in a Media/Assistant submenu table column. For each item used in each Image field the column will show the title, type (post, page, etc.) and ID of the using entity. You can click on the title to go to the Edit page for the entity.
<a name="debugging"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Debugging and Troubleshooting</h3>
<p>
If you are not getting the results you expect from the plugin carefully inspecting the results of its operations can be a valuable exercise. You can activate some MLA debug logging to add information to the site error log that details what the plugin is doing.
</p>
<p>
The most common cause of unexpected results is mispelling the ACF field name or trying to use the field label instead of the name. For the Quick and Bulk edit areas, make sure you enter the exact checkbox value or select option label without leading or trailing spaces. These values are case sensitive; "Yes" is different from "yes".</p>
</p>
<p>
If a problem persists you can activate additional MLA debug logging, run a test and inspect the log file for more information about what's going on. To activate MLA&rsquo;s debug logging:
</p>
<ol>
<li>Navigate to the Settings/Media Library Assistant Debug tab.</li>
<li>Scroll down to the &ldquo;MLA Reporting&rdquo; text box and enter &ldquo;0x8003&rdquo;. This will turn on MLA debug logging for the example plugin and AJAX operations (such as Quick and Bulk updates).</li>
<li>Click the Save Changes button to record your new setting.</li>
<li>Optionally, scroll to the bottom of the screen and click &ldquo;Reset&rdquo; to clear the error log. You may not want to do this depending on how you manage your error log.</li>
</ol>
<p>
Once that&rsquo;s done you can run a test. The debug log can be very detailed, so restricting the test as best you can will be very helpful. If you are working with the Media/Assistant admin submenu table try reducing the "Entries per page" to a small number, e.g., 1. When you&rsquo;ve finished testing, go back to the Debug screen and:
</p>
<ol>
<li>Enter &ldquo;0&rdquo; in the MLA Reporting text box to turn debug logging off.</li>
<li>Click the Save Changes button to record your new setting.</li>
<li>Scroll to the bottom and click &ldquo;Download&rdquo; to get the log content in a text file.</li>
<li>Optionally, scroll to the bottom of the screen and click &ldquo;Reset&rdquo; to clear the error log.</li>
</ol>
<p>
There may be a lot of messages written to the log, so limit the amount of activity during the logging period. You should see messages in the log like these:
</p>
<blockquote> 
[07-Oct-2021 23:51:09 UTC] 187 MLAACFExample::initialize plugin_settings = array (<br />
  'acf_checkbox_enabled' => true,<br />
  ...<br />
)<br />
[07-Oct-2021 23:51:14 UTC] 640 MLACore::mla_plugins_loaded_action() MLA 2.97 (20210930) mla_debug_level 0x8003
</blockquote>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
</p>
</div>