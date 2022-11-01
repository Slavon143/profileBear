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

<!-- template="select-option" -->
		<option [+selected+] value="[+value+]">[+text+]</option>

<!-- template="page-level-options" -->
<tr valign="top">
  <td class="textright">
    <strong>Conversion Rules</strong>
  </td>
  <td>
    <textarea name="[+slug_prefix+]_options[conversion_rules]" id="[+slug_prefix+]_options_conversion_rules" rows="10" cols="80" >[+conversion_rules+]</textarea>
    <div class="mla-settings-help">&nbsp;&nbsp;Enter your conversion rules; one entry per line.<br />&nbsp;&nbsp;See Documentation tab for details.</div>
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

ul.mla_settings { 
	list-style-type: disc;
	list-style-position: outside;
}

ul.mla_settings li { 
	margin-left: 2em;
}
</style>
<h2>Plugin Options</h2>
<p>In this tab you can define the conversion rules you want to derive more useful representations of the WFU additional data fields.</p>
<p>You can find more information about using the features of this plugin in the Documentation tab on this screen.</p>
<div class="mla-page-level-options-form">
	<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-path-mapping-tab">
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

ul.mla_settings { 
	list-style-type: disc;
	list-style-position: outside;
}

ul.mla_settings li { 
	margin-left: 2em;
}
</style>
<h2>Plugin Documentation. In this tab, jump to:</h2>
<div class="mla-display-settings-page" id="mla-display-settings-documentation-tab" style="width:700px">
<ul class="mla-doc-toc-list">
<li><a href="#introduction"><strong>Introduction</strong></a></li>
<li><a href="#processing"><strong>How the Plugin Works</strong></a></li>
<li><a href="#general-options"><strong>General Plugin Options</strong></a></li>
<li><a href="#boolean-rule"><strong>The Boolean Rule</strong></a></li>
<li><a href="#list-rule"><strong>The Element and List Rules</strong></a></li>
<li><a href="#equal-rule"><strong>The Equal Rule</strong></a></li>
<!-- <li><a href="#examples"><strong>Examples</strong></a></li> -->
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
<!-- Refer to the Settings/Media Library Assistant Documentation tab like this: <a href="[+settingsURL+]?page=mla-settings-menu-documentation&mla_tab=documentation#mla_variable_parameters" target="_blank">Field-level substitution parameters</a>. -->
The <a href="https://wordpress.org/plugins/wp-file-upload/" title="WFU Repository page" target="_blank">WordPress File Upload plugin, by Nickolas Bossinas</a> (WFU), is a popular solution for uploading files from the WordPress front end and has an option to create Media Library items for the uploaded files. It supports additional form fields (like checkboxes, text fields, email fields, dropdown lists etc), which are copied into the item's Attachment Metadata "WFU User Data" array as an array of text values keyed by the form field labels.
</p>
<p>
This example plugin lets you access WFU form fields as MLA custom data sources in a more convenient way. For example, instead of coding <code>[+meta:WFU User Data.Description+]</code> you can code <code>[+wfu:Description+]</code>. This plugin also lets you define rules that derive additional data sources to transform WFU field values into a more useful form. Rule definitions and examples can be found in the sections below.
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
This plugin makes use of a hook MLA provides ('mla_expand_custom_prefix') to extend its <a href="[+settingsURL+]?page=mla-settings-menu-documentation&mla_tab=documentation#mla_variable_parameters" target="_blank">Field-level substitution parameters</a>. A new <code>wfu:</code> prefix is handled by the code in this plugin to return WFU additional form field values as well as derived field values that represent WFU values in a more useful way. The field name is either the WFU label or a data source name defined in one of this plugin's Conversion Rules. You can add any of MLA's option/format values after the field name to further modify the value, e.g., <code>[+wfu:Date Created,date('j F, Y')+]</code>.
</p>
<p>
&nbsp;
<a name="general-options"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>General Plugin Options</h3>
<p>
This version of the plugin contains one setting on the General tab, "Conversion Rules". It is a text area in which you enter your conversion rules, one rule per line. Each rule has three parts:
</p>
<ul class="mla_settings">
<li><strong>Data Source</strong> - The data source/substitution parameter name by which you access the rule value, ending with a comma. Names are case-sensitive and, like WordPress custom field names, can contain spaces. If your data source name is the same as a WFU additional field label, only the converted value will be available using the <code>wfu:</code> prefix.</li>
<li><strong>Rule Type</strong> - one of "boolean", "element", "list" or "equal". Each rule type is described in the sections following this one.</li>
<li><strong>Rule Argument(s)</strong> - rule-specific values, separaated by commas and enclosed in parentheses. Arguments for each rule type are documented in the sections below.</li>
</ul>
<p>
Here are some example rules:<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp;&nbsp;<em>Member Status,boolean(Active Member,active,inactive)<br />
&nbsp;&nbsp;&nbsp;&nbsp;Color Values,element(Red,Red,' ')<br />
&nbsp;&nbsp;&nbsp;&nbsp;Color Values,element(Green,Green,' ')<br />
&nbsp;&nbsp;&nbsp;&nbsp;Color Values,element(Blue,Blue,' ')<br />
&nbsp;&nbsp;&nbsp;&nbsp;Color Values,list<br />
&nbsp;&nbsp;&nbsp;&nbsp;drivetrain,equal(Transmission,"stick shift",manual,automatic)</em><br />
&nbsp;<br />
The next three sections below explain each of the rules in more detail.
<a name="boolean-rule"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The Boolean Rule</h3>
<p>
The boolean rule tests a WFU field and returns one of two values depending on whether the WFU field is "true" or "false". A field is considered "true" if it exists, is not empty and contains any value other than "false". A field is considered "false" if it does not exist, does exist but is empty or does exist and contains the value "false". The boolean rule has three arguments:
</p>
<ul class="mla_settings">
<li><strong>WFU Field</strong> - The label of the field as defined in WFU "Additional Fields". Names are case-sensitive and, like WordPress custom field names, can contain spaces.</li>
<li><strong>True Value</strong> - the value that will be returned if the field is considered "true".</li>
<li><strong>False Value</strong> - the value that will be returned if the field is considered "false". This argument is optional; an empty value will be returned if the argument is omitted.</li>
</ul>
<p>
The boolean rule type can be applied to any WFU field but is particularly useful for checkboxes. WFU "checkbox" fields contain one of two text values; "true" if the box is checked or "false" if not. The boolean rule type lets you convert this to a more useful result. For example, you can return a value if the box is checked or an "empty value" when the box is unchecked:<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp;&nbsp;<em>spicy,boolean(Add Peppers,Yes,' ')</em><br />
&nbsp;<br />
When you code <code>[+wfu:spicy+]</code> this plugin will test the WFU "Add Peppers" field and return "Yes" if the field is "true". Note how a single space is used to represent an empty value; this is the same as omitting the last argument.
<a name="list-rule"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The Element and List Rules</h3>
<p>
The element and list rules work together to combine multiple WFU fields into a single result. The result will be a list of all the non-empty elements. All of the element rules and the list rule must have the same data source name. Each element rule determines if/how one WFU field is added to the list. The optional list rule specifies the "glue" added between list elements. The element rule has three arguments:
</p>
<ul class="mla_settings">
<li><strong>WFU Field</strong> - The label of the field as defined in WFU "Additional Fields". Names are case-sensitive and, like WordPress custom field names, can contain spaces.</li>
<li><strong>True Value</strong> - the value that will be returned if the field is considered "true".</li>
<li><strong>False Value</strong> - the value that will be returned if the field is considered "false". This argument is optional; an empty value will be returned if the argument is omitted.</li>
</ul>
<p>
True and false values are determined as defined in the boolean rule type above.
</p>
<p>
The list rule is optional; you can create a list just by defining a number of element rules with a common data source name. The list rule has just one optional argument:
</p>
<ul class="mla_settings">
<li><strong>Glue</strong> - The character or characters that separate elements of the list. The default glue is a single comma character</li>
</ul>
<p>
The list rule type can be applied to any WFU field but is particularly useful for building a list of taxonomy term assignments. For example:<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp;&nbsp;<em>Condiments,element(Ketchup,Tomato Ketchup)<br />
&nbsp;&nbsp;&nbsp;&nbsp;Condiments,element(Mustard,Brown Mustard)<br />
&nbsp;&nbsp;&nbsp;&nbsp;Condiments,element(Relish,Pickle Relish)<br />
&nbsp;&nbsp;&nbsp;&nbsp;Condiments,list(';')</em><br />
&nbsp;<br />
When you code <code>[+wfu:Condiments+]</code> this plugin will test each of the elements and build a list of the "true" elements, e.g., "Tomato Ketchup;Brown Mustard;Pickle Relish" if all three elements are "true". theNote how the element rules return an empty "false" value by omitting the last argument. Note also that the taxonomy term <strong>name</strong> is returned, not the term slug.
<a name="equal-rule"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The Equal Rule</h3>
<p>
The equal rule tests a WFU field and returns one of two values depending on whether the WFU field is equal or not equal to a specified value. A field is considered equal if it exists, is not empty and contains the specified value. A field is considered not equal if it does not exist, does exist but is empty or does exist but contains a value different from the specified value. The equal rule has four arguments:
</p>
<ul class="mla_settings">
<li><strong>WFU Field</strong> - The label of the field as defined in WFU "Additional Fields". Names are case-sensitive and, like WordPress custom field names, can contain spaces. If you have multiple equal rules for the same data source name you can use different WFU fields in them, if that makes sense for your applicaion.</li>
<li><strong>Specified Value</strong> - the WFU field value that will matched against.</li>
<li><strong>Equal Value</strong> - the value that will be returned if the field matches the specified value.</li>
<li><strong>Not Equal Value</strong> - the value that will be returned if the field does not match the specified value. This argument is optional; an empty value will be returned if the argument is omitted.</li>
</ul>
<p>
The equal rule type can be applied to any WFU field. For example, you can return a value if a simple text box matches a specific value:<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp;&nbsp;<em>drivetrain,equal(Transmission,"stick shift",manual,automatic)</em>
</p>
<p>
You can also enter multiple equal rules for the same data source name to test for more than one value:<br />
&nbsp;<br />
&nbsp;&nbsp;&nbsp;&nbsp;<em>Majority Status,equal(Person Type,"Man",adult)<br />
&nbsp;&nbsp;&nbsp;&nbsp;Majority Status,equal(Person Type,"Woman",adult)<br />
&nbsp;&nbsp;&nbsp;&nbsp;Majority Status,equal(Person Type,"Teenager",minor)<br />
&nbsp;&nbsp;&nbsp;&nbsp;Majority Status,equal(Person Type,"Child",minor,unknown)</em>
</p>
<p>
When you code <code>[+wfu:Majority Status+]</code> this plugin will test the WFU "Person Type" field and return "adult" if the field value is "Man" or "Woman". It will return "minor" if the value is "Teenager" or "Child", and it will return "unknown" if the field contains anything else. Note in this example that the first non-empty "not matched" value (e.g., "unknown") will be returned when the WFU field does not match any of the specified values.
<!-- <a name="examples"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Examples</h3>
<p>
Provide some examples.
</p>
<p>
&nbsp; -->
<a name="debugging"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Debugging and Troubleshooting</h3>
<p>
If you are not getting the results you expect from the plugin carefully inspecting the results of parsing the rules and generating the data source values can be a valuable exercise.  For this shortcode plugin adding &ldquo;0x8000&rdquo; to the MLA Reporting value will generate useful information.
</p>
<p>
To activate MLA&rsquo;s debug logging:
</p>
<ol>
<li>Navigate to the Settings/Media Library Assistant Debug tab.</li>
<li>Scroll down to the &ldquo;MLA Reporting&rdquo; text box and enter &ldquo;0x8001&rdquo;. This will turn on MLA debug logging for messages specific to this example plugin.</li>
<li>Click the Save Changes button to record your new setting.</li>
<li>Optionally, scroll to the bottom of the screen and click &ldquo;Reset&rdquo; to clear the error log. You may not want to do this depending on how you manage your error log.</li>
</ol>
<p>
Once that&rsquo;s done you can run a test. The debug log can be very detailed, so restricting the test as best you can will be very helpful. When you&rsquo;ve finished testing, go back to the Debug screen and:
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
[28-Dec-2020 05:38:55 UTC] 610 MLACore::mla_plugins_loaded_action() MLA 2.96 mla_debug_level 0x8001<br />
[28-Dec-2020 05:38:55 UTC] 623 <strong>mla_debug REQUEST</strong> = array (
  'tax_input' => 
  array (
    'attachment_category' => 
    array (
      0 => 'def',
    ),
  ),
)
[28-Dec-2020 05:38:55 UTC] 635 <strong>mla_debug attributes</strong> = array (
  'post_mime_type' => 'image/j*',
  'numberposts' => '3',
  'columns' => '2',
  'size' => 'large',
  'mla_debug' => 'log',
  'mla_page_parameter' => 'mla_paginate_current',
)

</blockquote>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
</p>
</div>