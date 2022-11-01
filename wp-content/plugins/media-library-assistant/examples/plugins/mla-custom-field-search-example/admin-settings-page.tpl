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
    <input name="[+slug_prefix+]_options[media_assistant_support]" id="[+slug_prefix+]_options_media_assistant_support" type="checkbox" [+media_assistant_support_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable Media/Assistant searches</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to activate support for the Search Media box on the Media/Assistant admin submenu.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[mmmw_support]" id="[+slug_prefix+]_options_mmmw_support" type="checkbox" [+mmmw_support_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable Media Manager searches</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to activate support for the Media Manager Modal (popup) Window.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Prefix</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[prefix]" id="[+slug_prefix+]_options_prefix" type="text" size="20" maxlength="20"value="[+prefix+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the prefix value that signifies a custom field search. Be sure to include something like a colon at the end.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Default Field(s)</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[default_fields]" id="[+slug_prefix+]_options_default_fields" type="text" size="40" maxlength="40"value="[+default_fields+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the (comma-separated) custom field name(s) to be searched by default.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>All Fields Name</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[all_fields]" id="[+slug_prefix+]_options_all_fields" type="text" size="20" maxlength="20"value="[+all_fields+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the name that signifies a search of all custom fields.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="[+slug_prefix+]_options[all_fields_support]" id="[+slug_prefix+]_options_all_fields_support" type="checkbox" [+all_fields_support_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Enable All Fields name substitution</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to use the above name for an "All Fields" search.</div>
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
<p>In this tab you can add or remove custom field search support for the Media/Assistant admin submenu and/or the Media Manager Modal (popup) Window (MMMW). The MMMW is used by the Media/Library grid view, the classic "Add Media..." function and the Gutenberg Image and Gallery blocks. You can leave these options checked unless you find a specific problem they cause (unlikely).</p>
<p>You can also specify one or more custom fields to search by default, i.e., without entering the field name(s) each time you perform a search. Finally, you can replace the default values used for the prefix that signals a custom field search and the special "field name" that specifies a search of all existing custom fields.</p>
<p>You can find more information about using all of the features of this plugin in the Documentation tab on this page.</p>
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
<li><a href="#composing_queries"><strong>Composing a Custom Field Query</strong></a></li>
<li><a href="#multiple_values"><strong>Searching for Multiple Values</strong></a></li>
<li><a href="#wildcards"><strong>Searching Partial Values; Wildcards</strong></a></li>
<li><a href="#multiple_fields"><strong>Searching in Multiple Fields or All Fields</strong></a></li>
<li><a href="#non_null_searches"><strong>Searching for the Presence of Any Value</strong></a></li>
<li><a href="#null_searches"><strong>Searching for the Absence of Any Value</strong></a></li>
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
The MLA "Search Media" text box lets you search for keywords on the Media/Assistant admin submenu and in the Media Manager Modal (popup) Window. This example plugin lets you search custom field content from those same text boxes. The example plugin was originally developed in response to this MLA support topic:
</p>
<ul class="mla-doc-toc-list">
<li><a href="https://wordpress.org/support/topic/searching-on-custom-fields/" title="View the topic" target="_blank">Searching on custom fields</a></li>
</ul>
<p>
The current version, with enhanced search features and these Settings tabs was inspried by this MLA support topic:
</p>
<ul class="mla-doc-toc-list">
<li><a href="https://wordpress.org/support/topic/search-media-by-custom-field/" title="View the topic" target="_blank">Search Media by Custom Field</a></li>
</ul>
<p>
To use the plugin you must configure the options on the General tab, including the field name(s) to be searched by default. Once the settings are in place you simply use the custom field prefix to specify that the search be handled by this example plugin.
</p>
<p>
More details on composing and running searches are in the sections of this Documentation page.
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
The example plugin makes no changes or additions to the MLA core code; it hooks some of the actions and filters MLA provides. The plugin works by detecting the presence of the <code>custom:</code> prefix (or the prefix set on the General tab) in the Search Media text. When the prefix is present, the plugin replaces the standard keyword(s) search with a custom field query. The example plugin uses MLA's existing "Table View" feature as described in the "Library Views/Post MIME Type Processing" section of the Settings/Media Library Assistant Documentation tab to compose and execute the custom field query.</p>
<p>
The outline that follows is somewhat technical, but should give you an idea of the sequence of events and actions that allow the plugin to do its work. When the post/page is loaded this plugin is initialized, setting up to five MLA "hooks" that may be called to start the plugin&rsquo;s processing. If the General tab "Enable Media/Assistant searches" box is checked three hooks are set:
</p>
<ul class="mla-doc-toc-list">
<li><strong>mla_list_table_new_instance</strong> - called at the start of composing the Media/Assistant submenu page. If the custom search prefix is found in the Search Media text, the search specification is saved for processing in the next step.</li>
<li><strong>mla_list_table_query_final_terms</strong> - called just before the database is queried to find items for the submenu table display. If the search specification is present it is translated to a custom field query and added to the query arguments.</li>
<li><strong>mla_list_table_submenu_arguments</strong> - called when the submenu table navigation elements are composed. If the search specification is present it is added to the pagination links in the table header and footer areas.</li>
</ul>
<p>
The General tab "Enable Media Manager searches" box adds the plugin's features to the Media/Library Grid view and the Media Manager Modal (popup) Window (MMMW). If the box is checked two hooks are set:
</p>
<ul class="mla-doc-toc-list">
<li><strong>mla_media_modal_query_initial_terms</strong> - called at the start of the AJAX request that will fill the MMMW's item grid. If the custom search prefix is found in the "query_attachments" Search Media text, the search specification is saved for processing in the next step.</li>
<li><strong>mla_media_modal_query_final_terms</strong> - called just before the database is queried to find items for the MMMW item grid. If the search specification is present it is translated to a custom field query and added to the query arguments.</li>
</ul>
<p>
Once the custom field query is added to the database query arguments the example plugin's job is done and processing proceeds normally.
<a name="composing_queries"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Composing a Custom Field Query</h3>
<p>
A custom field query has four parts:
</p>
<ol>
<li>A prefix, "custom:" by default, or whatever you set on the General tab.</li>
<li>A comma-separated list of one or more custom field names. If you omit this the "Default Field(s): set on the General tab will be substituted.</li>
<li>An equals sign ("="), to divide the field names from the values</li>
<li>A comma-separated list of one or more values</li>
</ol>
<p>
So, for example, if you have a custom field named "Kingdom" with values of "Animal", "Mineral" and "Vegetable" you can compose a search like:
</p>
<ul class="mla-doc-toc-list">
<li><code>custom:Kingdom=Animal</code></li>
</ul>
<p>
If you have set "Kingdom" as the Default Field you can compose a search like:
</p>
<ul class="mla-doc-toc-list">
<li><code>custom:=Animal</code></li>
</ul>
<p>
It is important to note that custom field values are somewhat different from the keywords and phrases used in the standard Search Media searches. There is no "and/or" option or matching on one of the words you enter. If you enter "this example", the search will not match "this" or "example".
&nbsp;
<a name="multiple_values"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Searching for Multiple Values</h3>
<p>
If you want to search for more than one value, simply separate each value you want by a comma, such as:
</p>
<ul class="mla-doc-toc-list">
<li><code>custom:Kingdom=Animal,Mineral</code></li>
</ul>
<p>
&nbsp;
<a name="wildcards"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Searching Partial Values; Wildcards</h3>
<p>Wildcard specifications are also supported; for example:
</p>
<ul class="mla-doc-toc-list">
<li><code>custom:Kingdom=*al</code> to match anything ending in "al", e.g., "Animal" and "Mineral"</li>
<li><code>custom:Kingdom=*get*</code> to match "Vegetable".</li>
<li><code>custom:Headline=*this*,*example*</code> will match values containing "this" or "example".</li>
<li>As explained below, a value of <code>custom:Kingdom=*</code> will match any non-NULL value for a custom field.</li>
</ul>
<p>
&nbsp;
<a name="multiple_fields"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Searching in Multiple Fields or All Fields</h3>
<p>
If you want to search for the same value(s) in more than one custom field, simply separate each field name you want to search in by a comma, such as:
</p>
<ul class="mla-doc-toc-list">
<li><code>custom:Artist,Patron=smith</code></li>
<li><code>custom:Artist,Patron=smith,jones</code></li>
</ul>
<p>
If you want to search for the same value(s) in all of your custom field, check the "Enable All Fields name substitution" box and enter your "All Fields Name" (default "*"), such as:
</p>
<ul class="mla-doc-toc-list">
<li><code>custom:*=smith</code></li>
<li><code>custom:*=smith,jones</code></li>
</ul>
<p>
&nbsp;
<a name="non_null_searches"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Searching for the Presence of Any Value</h3>
<p>
To return all items that have a non-NULL value in the field, enter the custom field name and then "=*". You can also enter the prefix "custom:" followed by just the custom field name(s). For example, <code>custom:My Featured Items</code>. For example:
</p>
<ul class="mla-doc-toc-list">
<li><code>custom:Kingdom=*</code></li>
<li><code>custom:Artist,Patron</code></li>
</ul>
<p>
&nbsp;
<a name="null_searches"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Searching for the Absence of Any Value</h3>
<p>
To return all items that have a NULL value in the field, enter the prefix "custom:" followed by the custom field name(s) and then "=". You can also enter a single custom field name (exactly one name) and then ",null".</p>
<ul class="mla-doc-toc-list">
<li><code>custom:Artist,Patron=</code></li>
<li><code>custom:Kingdom,null</code></li>
</ul>
<p>
<a name="debugging"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Debugging and Troubleshooting</h3>
<p>
If you are not getting the results you expect from a custom field search carefully inspecting the results of parsing the specification and generating the query can be a valuable exercise. These queries follow different rules than the standard keyword search rules, and you may have to adjust the query a few times to get the expected results.
</p>
<p>
For a quick look at the plugin's operation on a given search you can add a debugging prefix to the search text. There are two debugging prefixes:
</p>
<ul class="mla-doc-toc-list">
<li><strong><code>'}|{'</code></strong> - Write debug information to the console, e.g., <code>}|{custom:Kingdom=*</code>. This option writes log entries as PHP Warnings, which might be displayed in the browser window or written to the error log depending on how your site is configured. It's quick and easy but the results are ugly. Also, it will not work for the Media/Library Grid view or the MMMW; information will go to the log for these cases.</li>
<li><strong><code>'{|}'</code></strong> - Write debug information to the error log, e.g., <code>{|}custom:Artist,Patron</code>. This option avoids cluttering the display with ugly messages but requires you to find a view the error log file to see the results. The MLA Debug tab may be an easy way to find and view the log.</li>
</ul>
<p>
</p>
<p>
If a problem persists you can activate additional MLA debug logging, run a test and inspect the log file for more information about what's going on. To activate MLA&rsquo;s debug logging:
</p>
<ol>
<li>Navigate to the Settings/Media Library Assistant Debug tab.</li>
<li>Scroll down to the &ldquo;MLA Reporting&rdquo; text box and enter &ldquo;0x3&rdquo;. This will turn on MLA debug logging for the example plugin and AJAX operations (such as "query_attachments").</li>
<li>Click the Save Changes button to record your new setting.</li>
<li>Optionally, scroll to the bottom of the screen and click &ldquo;Reset&rdquo; to clear the error log. You may not want to do this depending on how you manage your error log.</li>
</ol>
<p>
Once that&rsquo;s done you can run a test. The debug log can be very detailed, so restricting the test as best you can will be very helpful. One way to do that:
</p>
<ol>
<li>Go to the Media/Assistant admin submenu table.</li>
<li>Enter your custom field search specification in the Search Media text box. You do not need to add either of the above debugging prefixes for this test.</li>
<li>Click the Search Media text box to filter the display.</li>
</ol>
<p>
When you&rsquo;ve finished testing, go back to the Debug screen and:
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
[28-Dec-2020 05:38:55 UTC] 610 MLACore::mla_plugins_loaded_action() MLA 2.94 mla_debug_level 0x3<br />
[28-Dec-2020 05:38:55 UTC] 37 MLA_Ajax::initialize( true ) $_REQUEST = array (
  'action' => 'query-attachments',
  'post_id' => '7283',
  'query' => <br />
</blockquote>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
</p>
</div>