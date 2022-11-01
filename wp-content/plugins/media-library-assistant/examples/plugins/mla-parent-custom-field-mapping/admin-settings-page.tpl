<!-- template="page" -->
<a name="backtotop"></a>
&nbsp;
<div class="wrap">
<h1 class="wp-heading-inline">MLA Parent Custom Field Mapping [+version+] Settings</h1>
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
[+dismiss_button+]
</div>

<!-- template="page-level-options" -->
<tr valign="top">
  <td class="textright">
    <input name="mla_parent_custom_field_mapping_options[acf_support]" id="mla_parent_custom_field_mapping_acf_support" type="checkbox" [+acf_support_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>ACF Support</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to activate support for Advanced Custom Fields and ACF Pro.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="mla_parent_custom_field_mapping_options[wplr_support]" id="mla_parent_custom_field_mapping_wplr_support" type="checkbox" [+wplr_support_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Wp/LR Sync Support</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to activate support for WP/LR Sync.</div>
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
<p>In this tab you can add or remove support for the Advanced Custom Fields, ACF Pro and WP/LR Sync plugins. You can leave these options checked unless you find a specific problem they cause (unlikely).</p>
<p>You can find more information about using all of the features of this plugin in the Documentation tab on this page.</p>
<div class="mla-page-level-options-form">
	<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-parent-custom-field-mapping-tab">
		<table class="optiontable">
		<tbody>
			[+options_list+]
		</tbody>
		</table>
		<span class="submit mla-settings-submit">
		<input name="mla-parent-custom-field-mapping-options-save" class="button-primary" id="mla-parent-custom-field-mapping-options-save" type="submit" value="Save Changes" />
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
<h2>Plugin Documentation. In this tab, jump to:</h2>
<div class="mla-display-settings-page" id="mla-display-settings-documentation-tab" style="width:700px">
<ul class="mla-doc-toc-list">
<li><a href="#introduction"><strong>Introduction</strong></a></li>
<li><a href="#processing"><strong>How the Plugin Works</strong></a></li>
<li><a href="#composing_rules"><strong>Composing Mapping Rules</strong></a></li>
<li><a href="#applying_rules"><strong>Applying Mapping Rules</strong></a></li>
<li><a href="#rollover_actions"><strong>Mapping Rule Rollover Actions</strong></a></li>
<li><a href="#acf"><strong>Advanced Custom Fields (ACF) Features</strong></a></li>
<li><a href="#wplr_sync"><strong>WP/LR Sync Features</strong></a></li>
<li><a href="#plugin_options"><strong>Plugin Options</strong></a></li>
<li><a href="#debugging"><strong>Debugging and Troubleshooting</strong></a></li>
<li><a href="#examples"><strong>Examples</strong></a></li>
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
Many sites that "attach" Media Library items to a post or page also display information about those items in the post/page content. The MLA Parent Custom Field Mapping example plugin provides a convenient way to access such information by mapping item values to custom fields in the parent post/page. The example plugin was developed in response to this MLA support topic:
</p>
<ul class="mla-doc-toc-list">
<li><a href="https://wordpress.org/support/topic/https://wordpress.org/support/topic/iptc-exif-mapping-on-picture-upload-to-parent-posts-custom-fields/" title="View the topic" target="_blank">IPTC/EXIF Mapping on picture upload to parent post&rsquo;s custom fields</a></li>
</ul>
<p>
To use the plugin you must create Custom Field or IPTC/EXIF mapping rules that name the parent custom fields and specify where their values come from. Once the rules are defined they can run automatically when items are added to the Media Library or you can execute them manually.
</p>
<p>
More details on composing and applying rules are in the sections of this Documentation page.
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
The example plugin makes no changes or additions to the MLA core code; it hooks some of the actions and filters provided by MLA (and ACF and WP/LR Sync). The plugin works by detecting the presence of the <code>parent:</code> prefix in a mapping rule name at the beginning of any mapping rule(s) execution. When the prefix is present, the rule is applied to the custom fields of the item&rsquo;s parent post/page (if present) instead of the item itself. Mapping rules are executed when:
</p>
<ul class="mla-doc-toc-list">
<li>Items are uploaded to the Media Library from the MMMW Add Media.</li>
<li>MLA&rsquo;s "Map Custom Field Metadata" or "Map IPTC/EXIF metadata" link is clicked on the Media/Edit Media screen.</li>
<li>MLA&rsquo;s "Map Custom Field Metadata" or "Map IPTC/EXIF metadata" link is clicked in the Media/Assistant Bulk Edit area.</li>
<li>The "Execute" rollover action or "Execute" Bulk Action is applied in the Settings/Media Library Assistant Custom Fields or IPTC/EXIF tab.</li>
<li>The WP/LR Theme Assistant plugin creates a new post/page to host a gallery for a Lightroom collection.</li>
<li>The WP/LR Sync plugin adds Media Library items during syncronization.</li>
</ul>
<p>
Some of the actions above are performed on just one item for a WordPress "page load". For example, when new items are uploaded each item is processed in a separate WordPress page load, even if you drag & drop or select multiple files at one time. Other actions, such as the Media/Assistant Bulk Edit or Execute Bulk Action operate on multiple items in a single page load. 
</p>
<p>
The outline that follows is somewhat technical, but should give you an idea of the sequence of events and actions that allow the plugin to do its work. When the post/page is loaded this plugin is initialized, setting two MLA "hooks" that may be called to start the plugin&rsquo;s processing:
</p>
<ul class="mla-doc-toc-list">
<li><strong>mla_purge_custom_field_values</strong> - called when the "Purge Values" rollover action is clicked</li>
<li><strong>mla_mapping_settings</strong> - called when a mapping execution starts to inspect/modify rule definitions</li>
</ul>
<p>
When the "mla_purge_custom_field_values" filter is called for a "parent:" rule this plugin will delete the values for the named custom field in all posts and pages. Logic is included to delete all of the rows present for an ACF Repeater field.
</p>
<p>
When the "mla_mapping_settings" filter is called for the first time in a post/page load this plugin inspects all of the rules, looking for "parent:" rules. If any are found, this plugin 1) filters out any ACF Repeater and Subfield rules, 2) prepares a cache for WP/LR Sync processing and 3) adds filters to be called for each item&rsquo;s processsing:
</p>
<ul class="mla-doc-toc-list">
<li><strong>mla_mapping_old_custom_value</strong> - supplies the existing custom field value, if any, from the parent post/page rather than the item itself.</li>
<li><strong>mla_mapping_updates</strong> - called when the rules generate updates for the item. Filters out the "parent:" updates and applies them to the parent post/page rather than the item itself.</li>
<li><strong>acf/update_value - called when ACF tries to store new values, preventing ACF from overwriting values already stored by MLA&rsquo;s mapping rules</strong></li>
<li><strong>wplr_add_media_to_collection</strong> - called when WP/LR Sync attaches an item to a parent "collection" post. Applies updates from MLA&rsquo;s mapping rules when the parent post ID is finally known.</li>
</ul>
<p>
Once the above filters are in place they are called repeatedly as mapping rules are excuted for each item. You can find more details on the ACF and WP/LR Sync filters in the sections below.
&nbsp;
<a name="composing_rules"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Composing Mapping Rules</h3>
<p>
Parent mapping is activated by adding a <code>parent:</code> prefix to the Name field of a mapping rule. You can add the prefix when you create the rule or by clicking the "Change Name" and then "Enter new field" links below the Name on the Edit Rule screen (click the rule&rsquo;s "Edit" rollover action). For example, a rule named &ldquo;parent:Camera Used&rdquo; would create or update the &ldquo;Camera Used&rdquo; custom field in the parent post/page of an attached item. The rule can use any of the data sources available, including IPTC/EXIF/XMP metadata. You can add the prefix to rules on the Settings/Media Library Assistant "Custom Fields" and/or "IPTC/EXIF" tabs. 
</p>
<p>
When composing a "parent:" rule in the Custom Fields tab, always leave the "MLA Column", "Quick Edit" and "Bulk Edit" boxes <strong>unchecked</strong>. These three options apply to the Media Library item itself and they have no meaning when the custom field is stored in a parent post/page.
</p>
</p>
The "Existing Text" Keep/Replace choice is meaningful when multiple items are attached to a given post/page. If you select "Keep", the field value will be set when the first item is attached. If you select "Replace", the field value will be replaced each time an additional item is attached, so the current value will always be that of the last/most recent item attached to the post/page. The Existing Text setting is not used for Repeater Fields or Subfields, which are handled differently.
</p>
<p>
The "Option" choice ...
</p>
<p>
When you are using Advanced Custom Fields some additional information is required to make sure MLA&rsquo;s mapping updates are properly recorded for use by ACF. This additional information is coded in a special <strong><code>,acf()</code></strong> option field that is appended to the field name. You can find more information about this topic in the "Advanced Custom Fields (ACF) Features" section below.
<a name="applying_rules"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Applying Mapping Rules</h3>
<p>
As outlined in "How the Plugin Works", MLA mapping rules are executed when new items are added to the Media Library (assuming the "Enable ... mapping when adding new media" box is checked). If a parent post/page is known at that time the "parent:" rules will be applied automatically. For example, when you fill a Gutenberg "Image" or "Gallery" block by uploading new items the parent is assigned and the rules will be applied. The Classic Editor "Add Media..." popup window works in the same way.
</p>
<p>
If you are using the WP/LR Sync (and the optional Theme Assistant) plugin, adding new items to the Media Library with a sync action or creating a new post/page to host a collection gallery will apply the rules.
</p>
<p>
The current version of this plugin will <strong>not</strong> apply the rules when an item is attached to a post/page by manually setting the Post Parent value. If you need this feature, contact me at my web site.
</p>
<p>
There are a number of ways to manually apply the mapping rules to one, several or all of your items:
</p>
<ul class="mla-doc-toc-list">
<li>To map a single item, go to the Media/Assistant submenu and click the thumbnail of the item you want (or click the &ldquo;Edit&rdquo; rollover action) to get the Media/Edit Media screen. You can click the &ldquo;Map Custom Field metadata&rdquo; or "Map IPTC/EXIF metadata" links to run your rules on this item, then go to the parent post and scroll down and look at the custom field to inspect the results. Note: The Edit Post screen does not show custom fields by default; you may have to add them to your display.</li>
<li>To map two or more items, go to the Media/Assistant submenu and click the checkbox next to the items you want. Then, select &ldquo;Edit&rdquo; from the &ldquo;Bulk Actions&rdquo; dropdown above the checkboxes and click &ldquo;Apply&rdquo; to open the Bulk Edit area. Click the &ldquo;Map Custom Field metadata&rdquo; or "Map IPTC/EXIF metadata" button to run your rules on the selected items.</li>
<li>To map all of your items, stay on the Settings/Media Library Assistant tab and use the &ldquo;Execute&rdquo; rollover action as defined in the next section. This may take a while.</li>
<li>The Settings/Media Library Assistant tabs also have an &ldquo;Execute&rdquo; Bulk Action you can use to apply several rules to all of your items at once, and an &ldquo;Execute All Rules&rdquo; button to run all of the rules against all of your items.</li>
</ul>
<p>
There are a couple of techniques you can use to make manual mapping more convenient. First, on the Custom Fields and IPTC/EXIF tabs you can enter &ldquo;parent:&rdquo; in the search box and click &ldquo;Search Rules&rdquo; to filter the display, showing all and only the parent mapping rules. You can then check the rule(s) you want and use the &ldquo;Execute&rdquo; bulk action to apply them to all your items. Second, on the Media/Assistant admin submenu you can enter the ID number of a parent post/page in the Search Media Box to show all items attached to it. You can also click on the &ldquo;(Parent: xxxx)&rdquo; link in the ID/Parent column to filter the display. Once the display is filtered you can select all the attached items and then use the Bulk Edit area to apply your rules.
<a name="rollover_actions"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Mapping Rule Rollover Actions</h3>
<p>
When you hover over the rule name in the Name column of the rules table a list of rollover actions will appear. These can help you manage your parent mapping rules:
</p>
<ul class="mla-doc-toc-list">
<li><strong>Edit</strong> - activates the full-screen version of the rule editing feature. On this screen you can change the rule name to add or remove the &ldquo;parent:&rdquo; prefix, the custom field name and/or the &ldquo;,acf()&rdquo; option. It&rsquo;s a bit cumbersome but it works. It&rsquo;s helpful to copy the existing value before you click &ldquo;Change Name&rdquo; and then &ldquo;Enter new field&rdquo; because the current name is deleted and the text box is empty when you start.</li>
<li><strong>Quick Edit</strong> - activates the inline version of the rule editing feature. In this area you can change the rule settings but not the rule name.</li>
<li><strong>Execute</strong> - applies the rule to <strong>all</strong> of your items, using a series of AJAX requests to break the processing up into chunks and avoid script timeouts.<br />&nbsp;<br />While the process is running you can click the &ldquo;Pause&rdquo; button to suspend the process. Then, you can click the &ldquo;Cancel&rdquo; button to stop the process or the &ldquo;Resume&rdquo; button to carry on. To the right of the Resume button is a text box with the number of the next item to be processed. You can change this number to, for example, skip over items you know are already processed; this is handy if something goes wrong and you need to get around an error.<br />&nbsp;<br />For ACF Repeater Fields, the execute function applies to all of the Repeater's Subfields. Individual Subfield executes are not supported and are ignored.</li>
<li><strong>Purge Values</strong> - deletes all of the existing values for the custom field named in the rule. This can be handy if you are experimenting with a new rule, or if you change the definition of a rule and want to ensure that all of your items or posts/pages have consistent values.<br />&nbsp;<br />For ACF Repeater Fields, the purge values function eliminates all rows assigned to the field. Individual Subfield purges are not supported.</li> 
<li><strong>Delete Permanently</strong> - deletes the rule from the table. Note that existing custom field values are <strong>not</strong> deleted, so be sure to run Purge Values first if you want to clean things up.</li>
</ul>
<p>
&nbsp;
<a name="acf"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Advanced Custom Fields (ACF) Features</h3>
<p>
ACF provides a rich set of field data types and display formats. To do this it replaces the core WordPress user interface for custom fields, using custom logic to store, load and format field values. This MLA example plugin works well with the simple ACF field types, such as text and date fields. More complex ACF types such as checkboxes require additional logic to encode them for database storage; the example plugin does not contain equivalent logic and you will be disappointed in the results. You can contact me about the possibility of extending the example plugin for these needs.
</p>
<p>
When you are using Advanced Custom Fields some additional information is required to make sure MLA&rsquo;s mapping updates are properly recorded for use by ACF. This additional information is coded in a special <strong><code>,acf()</code></strong> option field that is appended to the field name. There are two ACF features that must be accomodated:
</p>
<ul class="mla-doc-toc-list">
<li>If and when ACF can be used to manually edit values supplied by MLA</li>
<li>For ACF Pro, how Repeater Fields and their Subfields are handled</li>
</ul>
<p>
Since ACF is not aware of MLA&rsquo;s mapping rule process it will often overwrite the values MLA stores with empty or obsolete values. MLA can prevent this problem with one of three options:
</p>
<ul class="mla-doc-toc-list">
<li><strong><code>,acf(read_only)</code></strong> - means ACF will never alter the value of the field. This is the most useful setting, and the one to use if you want only MLA to populate the field.</li>
<li><strong><code>,acf(empty)</code></strong> - means ACF will only alter the value of the field if its current value is empty. This means that any value you enter in the Edit Post/Page screen will be stored in the database if there is no current value in the database.</li>
<li><strong><code>,acf(default_value)</code></strong> - means ACF will only alter the value of the field if the current database value matches the Default Value set in the ACF Field definition. This would let you delete the field if its currrent value matched the ACF Default Value.</li>
</ul>
<p>
 The "read_only" option is usually the right choice. To specify one of these options, code something like <strong><code>parent:Camera Used,acf(read_only)</code></strong> as your field name.
</p>
<p>
ACF Pro Repeater Field values are stored as "rows" containing "subfields" in multiple WordPress custom fields tied togeter by a naming convention. Each subfield can be populated by a separate MLA mapping rule. To tie MLA&rsquo;s rules back to ACF Repeaters and Subfields two ",acf()" options are added to the rule names:
</p>
<ul class="mla-doc-toc-list">
<li><strong><code>,acf(repeater)</code></strong> - is appended to the name of the Repeater Field.</li>
<li><strong><code>,acf(subfield.<em>repeater-name</em>)"</code></strong> - is appended to each repeater subfield, where <strong><em>repeater-name</em></strong> is the name of the Repeater Field of which each Subfield is a part. You don&rsquo;t have to have a rule for all Sub Fields; any missing values are replaced by an empty string.</li>
</ul>
<p>
For example, you might code three rules for <strong><code>parent:Camera Settings,acf(repeater)</code></strong>, <strong><code>parent:Shutter Speed,acf(subfield.Camera Settings)</code></strong> and <strong><code>parent:Focal Length,acf(subfield.Camera Settings)</code></strong>.
</p>
<p>
Repeater fields are useful because a given post/page can have multiple attached items. For example, a post/page might have a gallery of images each of which contains camera and GPS location information. This information could be added to the post/page content or used to display a map containing pins for each image&rsquo;s location. The MLA example plugin will copy item values to Repeater fields and ensure that there is exactly one row for each <strong>unique combination</strong> of subfields. In the above example, there would be one row for each combination of Shutter Speed and Focal Length. If two or more images had the same combination, only one row would be added to the Repeater.
</p>
<p>
In the above example you may see a particular Shutter Speed value appear multiple times, paired with different Focal Length values. If you want exactly one occurance of each Shutter Speed and each Focal Length, make each one a subfield of its own separate Repeater field. 
</p>
<p>
There is no explicit way to match a Repeater row to the attached item(s) that created it, so if you add or remove attached items the Repeater field content might become incomplete or inaccurate. You may have to delete the field value and reapply the mapping rules to clean things up.
<a name="wplr_sync"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>WP/LR Sync Features</h3>
<p>
This example plugin supports WP/LR Sync and the optional Theme Assistant. WP/LR Theme Assistant is an extension for WP/LR Sync that allows you to create mappings between the WP/LR Sync API and the technical structure of your theme in order to automate content creation. Typically, it is used to create a page containing a gallery for each of your collections in Lightroom.
</p>
<p>
When WP/LR Theme Assistant adds images to a page containing a collection's gallery it attaches the items to the page. This example plugin will apply all the &ldquo;parent:&rdquo; mapping rules when that occurs.
<a name="plugin_options"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Plugin Options</h3>
<p>
The current plugin version has just two option settings; checkboxes for ACF and WP/LR Sync support. They are only used if one or both of the respective plugins is installed and active on the site.
</p>
<p>
You can leave the default (checked) setting in place unless you are having a specific problem that might be caused by this plugin interfering in some way with one of the other plugins.
<a name="debugging"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Debugging and Troubleshooting</h3>
<p>
When you are creating a new rule testing it out on one or a few posts/pages and carefully inspecting the results can be a valuable exercise. You may have to delete the field values, modify the rule and reapply it a few times to get the expected results.
</p>
<p>
If a problem persists you can activate the debug logging, run a test and inspect the log file for more information about what's going on. To activate  MLA&rsquo;s debug logging:
</p>
<ol>
<li>Navigate to the Settings/Media Library Assistant Debug tab.</li>
<li>Scroll down to the &ldquo;MLA Reporting&rdquo; text box and enter &ldquo;0x8013&rdquo;. This will turn on MLA debug logging for the example plugin,  AJAX operations (such as WP/LR Sync) and the IPTC/EXIF metadata mapping rules.</li>
<li>Click the Save Changes button to record your new setting.</li>
<li>Optionally, scroll to the bottom of the screen and click &ldquo;Reset&rdquo; to clear the error log. You may not want to do this depending on how you manage your error log.</li>
</ol>
<p>
Once that&rsquo;s done you can run a test. The debug log will be very detailed, so restricting the test as best you can will be very helpful. You can often update just one post and then collect the test results. One way to do that:
</p>
<ol>
<li>Manually delete the Repeater Field content for one of your posts.</li>
<li>Go to the Media/Assistant admin submenu table and find one of the attachments for that post.</li>
<li>Click on the &ldquo;(Parent:xxxx)&rdquo; link in the ID/Parent column to filter the display showing all the attachments for that one post.</li>
<li>Click the box next to the ID/Parent column title to select all the attachments.</li>
<li>Select &ldquo;Edit&rdquo; from the Bulk Actions dropdown and click &ldquo;Apply&rdquo;.</li>
<li>Click the &ldquo;Map IPTC/EXIF metadata&rdquo; button in the bottom-right corner of the Bulk Edit area.</li>
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
There should be a lot of messages written to the log, so limit the amount of activity during the logging period. You should see messages in the log like these:
</p>
<blockquote> 
[27-Jun-2020 23:09:30 UTC] 610 MLACore::mla_plugins_loaded_action() MLA 2.83 (20200621) mla_debug_level 0x13<br />
[27-Jun-2020 23:09:30 UTC] 37 MLA_Ajax::initialize( false ) $_REQUEST = array (<br />
  'action' => 'mla-inline-edit-scripts',<br />
  'mla_admin_nonce' => 'b09f9d91ed',<br />
  'bulk_action' => 'bulk_map',<br />
)<br />
</blockquote>
<p>
Of course, the details will be different. If you discover a defect in the plugin (or in MLA) you can <a href="http://wordpress.org/support/plugin/media-library-assistant" target="_blank">open a support topic</a> or <a href="http://davidlingren.com/#two" target="_blank">contact me at my web site</a> so it can be investigated further. I may ask for a copy of the log file from your tests.
<a name="examples"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Examples</h3>
<p>
To be added...
</p>
</div>