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
<p>There are no General option settings in this version of the plugin.</p>
<p>You can find more information about using all of the features of this plugin in the Documentation tab on this page.</p>
<div class="mla-page-level-options-form" style="display:none">
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
<li><a href="#use_filters"><strong>use_filters, for [mla_term_list]</strong></a></li>
<li><a href="#add_filters_to"><strong>add_filters_to, for [mla_gallery]</strong></a></li>
<li><a href="#default_empty_gallery"><strong>default_empty_gallery, for [mla_gallery]</strong></a></li>
<li><a href="#muie_terms_search"><strong>The [muie_terms_search] shortcode</strong></a></li>
<li><a href="#muie_keyword_search"><strong>The [muie_keyword_search] shortcode</strong></a></li>
<li><a href="#muie_orderby"><strong>The [muie_orderby] and [muie_order] shortcodes</strong></a></li>
<li><a href="#muie_per_page"><strong>The [muie_per_page] shortcode</strong></a></li>
<li><a href="#muie_assigned_items_count"><strong>The [muie_assigned_items_count] shortcode</strong></a></li>
<li><a href="#muie_text_box"><strong>The [muie_text_box] shortcode</strong></a></li>
<li><a href="#muie_archive_list"><strong>The [muie_archive_list] shortcode</strong></a></li>
<li style="list-style-type:none"><ul class="mla-doc-toc-list">
<li><a href="#archive_type">Archive Type</a></li>
<li><a href="#archive_source">Archive Source</a></li>
<li><a href="#archive_list_output_formats">Archive List Output Formats</a></li>
<li><a href="#archive_list_display_content">Archive List Display Content</a></li>
<li><a href="#archive_list_link">Archive List Item Link Values (Array, Flat and List)</a></li>
<li><a href="#archive_list_other">Archive List Other Parameters</a></li>
<li><a href="#archive_list_data_selection">Archive List Data Selection Parameters</a></li>
<li><a href="#archive_list_substitution">Archive List Substitution Parameters</a></li>
</ul></li>
<li><a href="#filters_examples"><strong>use_filters/add_filters_to Example</strong></a></li>
<li><a href="#sticky_examples"><strong>Sticky Shortcodes Example</strong></a></li>
<li><a href="#muie_archive_list_examples"><strong>Archive List Shortcode Examples</strong></a></li>
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
This example plugin provides shortcodes to improve user experience for [mla_term_list], [mla_tag_cloud] and [mla_gallery] shortcodes. It also provides a number of additional shortcodes to implement "sticky" versions of search, sort and per-page controls for filtering an [mla_gallery] display as well as the [muie_archive_list] shortcode for date-based filtering.
</p>
<p>
You can find detailed information and examples of these features in the sections below. Briefly:
</p>
<ul class="mla-doc-toc-list">
<li>If you add "use_filters=true" to an [mla_term_list] shortcode this plugin will retain the  selected terms when the page is refreshed and pass them back into the shortcode.</li>
<li>If you add "add_filters_to=any" to an [mla_gallery] shortcode this plugin will retain settings for terms search, keyword search, taxonomy queries and posts_per_page when the page is refreshed or pagination moves to a new page.</li>
<li>If you add "add_filters_to=<taxonomy_slug>" to an [mla_gallery] shortcode this plugin will do the actions in 2. and will also match the taxonomy_slug to a simple taxonomy query (if present) and add that query to the taxonomy queries. If the simple query is "muie-no-terms", it will be ignored.</li>
<li>If you add "default_empty_gallery=true" an [mla_gallery] shortcode the initial gallery display will show no items, until a selection is made from the other controls.</li>
<li>Shortcodes are provided to generate text box controls and retain their settings when the page is refreshed or pagination moves to a new page:</li>
<li style="list-style-type:none"><ul class="mla-doc-toc-list">
<li>[muie_terms_search] generates a terms search text box</li>
<li>[muie_keyword_search] generates a keyword search text box</li>
<li>[muie_orderby] generates an order by dropdown control</li>
<li>[muie_order] generates ascending/descending radio buttons</li>
<li>[muie_per_page] generates an items per page text box</li>
<li>[muie_assigned_items_count] returns the number of items assigned to any term(s) in the selected taxonomy</li>
<li>[muie_text_box] generates a text box with a "sticky" value that survives page refresh and pagination</li>
</ul></li>
<li>The [muie_archive_list] shortcode lets you construct lists and controls for filtering a gallery on the values of date variables in the items' post row or a custom field.</li>
</ul>
<p>
With a bit of work you can add a tag cloud that works with these filters. Here's an example you can adapt for your application:
</p>
<code>
&nbsp;&nbsp;&nbsp;&nbsp;&lt;style type='text/css'>
<br />&nbsp;&nbsp;&nbsp;&nbsp;#mla-tag-cloud .mla_current_item {
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;color:#FF0000;
<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;font-weight:bold}
<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;/style>
<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;span id=mla-tag-cloud>
<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;strong>Tag Cloud&lt;/strong>
<br />&nbsp;&nbsp;&nbsp;&nbsp;[mla_tag_cloud]
<br />&nbsp;&nbsp;&nbsp;&nbsp;taxonomy=attachment_tag
<br />&nbsp;&nbsp;&nbsp;&nbsp;current_item="{+request:current_item+}"
<br />&nbsp;&nbsp;&nbsp;&nbsp;mla_link_href="{+currentlink_url+}&tax_input{{+query:taxonomy+}}{}={+slug+}&muie_per_page={+template:({+request:muie_per_page+}|5)+}" link_class="{+current_item_class+}"
<br />&nbsp;&nbsp;&nbsp;&nbsp;[/mla_tag_cloud]
<br />&nbsp;&nbsp;&nbsp;&nbsp;&lt;/span>
</code>
<p>
This example plugin uses three of the many filters available in the [mla_gallery] and [muie_archive_list] shortcodes and illustrates some of the techniques you can use to customize the gallery display and archive list controls.
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
The example plugin makes no changes or additions to the MLA core code; it hooks some of the actions and filters MLA provides. The plugin works by detecting the presence of its parameters (use_filters, add_filters_to, archive_parameter_name) in the [mla_term_list] or [mla_gallery] parameter list. The added shortcodes this plugin provides use the standard WordPress <code>add_shortcode()</code> function. They add controls to the post/page, typically in an HTML form, that supply parameters to [mla_gallery].
</p>
<p>
The "sticky" shortcodes work in two ways. First, when the page is refreshed they look for a query argument or form data value corredponding to their name and if they find it they use the incoming value to initialize their new value. Second, they participate in the generation of <code>[mla_gallery]</code> pagination links, adding their current values to a <code>muie_filters</code> array, JSON encoding the array and adding it as a query argument to each pagination URL. When a pagination URL is returned from the browser the <code>muie_filters</code> array is decoded and its elements added to the <code>$_REQUEST</code> array.
<a name="use_filters"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>use_filters, for [mla_term_list]</h3>
<p>
If you add "use_filters=true" to an <code>[mla_term_list]</code> shortcode this plugin will retain the selected terms when the page is refreshed and pass them back into the shortcode.
</p>
<p>
For gallery pagination links, the term list parameters (e.g., "tax_input") are recovered from the "muie_filters" query attribute and restored to the <code>$_REQUEST</code> array so the <code>[mla_gallery]</code> shortcodes can access them.
</p>
<p>
If the <code>$_REQUEST['tax_input']</code> element is present the selected terms are added to the <code>[mla_term_list]</code> shortcode parameters so the list output reflects them. They can also be accessed in an <code>[mla_gallery]</code> shortcode with the `request:` substitution parameter prefix.
</p>
<p>
If you use the "mla_control_name" to replace the default <code>tax_input[[+taxonomy+]][]</code> name attribute, term selections will still be copied to the <code>$_REQUEST['tax_input']</code> element and the <code>muie_filters['tax_input']</code> query attribute. You can disable this behavoir by coding "use_filters=local".
</p>
<p>
If you are not getting the results you expect carefully inspecting the results of parsing the specification and generating the query can be a valuable exercise. You can add the <code>muie_debug=true</code> or <code>muie_debug=log</code> parameters to the <code>[mla_gallery]</code> shortcode, run a test and inspect the log file or the screen messages for more information about what's going on.
<a name="add_filters_to"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>add_filters_to, for [mla_gallery]</h3>
<p>
If you add "add_filters_to=any" to an [mla_gallery] shortcode this plugin will retain settings for all of the <code>[muie_...]</code> shortcodes, simple taxonomy queries and tax_input when the page is refreshed or pagination moves to a new page. All simple taxonomy queries and tax_input are combined into a single <code>tax_query</code> parameter for this purpose. You can add <code>tax_relation</code> (default AND), <code>tax_operator</code> (default IN) and <code>tax_include_children</code> (default true) parameters to tailor the query to your needs. You can specify taxonomy-specific settings by appending '_operator" or "_children" to the taxonomy slug, e.g., <code>attachment_tag_operator=AND</code> or <code>attachment_tag_children=false</code>.
</p>
<p>
If you add "add_filters_to={taxonomy_slug}" to an [mla_gallery] shortcode this plugin will do the actions described above and will also match the taxonomy_slug to a simple taxonomy query (if present) and look for a special "muie-no-terms" value. If the simple taxonomy value is "muie-no-terms", the taxonomy will be ignored, i.e., not added to the <code>tax_query</code> parameter.
</p>
<p>
&nbsp;
<a name="default_empty_gallery"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>default_empty_gallery, for [mla_gallery]</h3>
<p>
If you add "default_empty_gallery=true" to an [mla_gallery] shortcode the initial gallery display will show no items, until a selection is made from the other controls.
</p>
<p>
If you also add an "mla_control_name" parameter to the shortcode with a comma-separated list of one or more control names, the presence of any non-empty control name elements in the request will cause the "empty gallery" test to fail and will display the gallery.
<a name="muie_terms_search"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The [muie_terms_search] shortcode</h3>
<p>
The <code>[muie_terms_search]</code> shortcode generates a text box for passing the "mla_terms_phrases" parameter to an <code>[mla_gallery]</code> shortcode. You can add parameters to this shortcode to pass the other terms search parameters as well:
</p>
<ul class="mla-doc-toc-list">
<li>muie_terms_parameter</li>
<li>muie_attributes</li>
<li>mla_terms_taxonomies</li>
<li>mla_phrase_delimiter</li>
<li>mla_phrase_connector</li>
<li>mla_term_delimiter</li>
<li>mla_term_connector</li>
</ul>
<p>
The shortcode is meant to be placed in an HTML form that lets the user enter criteria to filter a gallery display. If you require multiple shortcodes on the same page you can use the <code>muie_terms_parameter</code> parameter to give them unique names. Be sure to add a <code>muie_terms_parameter</code> parameter with the same name to every <code>[mla_gallery]</code> shortcode that uses the results of this shortcode.
</p>
<p>
You can use the <code>muie_attributes</code> parameter to replace the default <code>type=text</code> and/or <code>size=20</code> attributes or to add attributes such as <code>placeholder="Enter some terms"</code>. separate multiple attributes with spaces and quote any value containing spaces.
<a name="muie_keyword_search"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The [muie_keyword_search] shortcode</h3>
<p>
The <code>[muie_keyword_search]</code> shortcode generates a text box for passing the "s" parameter to an <code>[mla_gallery]</code> shortcode. You can add parameters to this shortcode to pass the other keyword search parameters as well:
</p>
<ul class="mla-doc-toc-list">
<li>muie_keyword_parameter</li>
<li>muie_attributes</li>
<li>mla_search_fields</li>
<li>mla_search_connector</li>
<li>sentence</li>
<li>exact</li>
</ul>
<p>
The shortcode is meant to be placed in an HTML form that lets the user enter criteria to filter a gallery display. If you require multiple shortcodes on the same page you can use the <code>muie_keyword_parameter</code> parameter to give them unique names. Be sure to add a <code>muie_keyword_parameter</code> parameter with the same name to every <code>[mla_gallery]</code> shortcode that uses the results of this shortcode.
</p>
<p>
The <code>mla_search_fields</code>, <code>mla_search_connector</code>, <code>sentence</code> and <code>exact</code> parameters are passed to <code>[mla_gallery]</code> as well.
</p>
<p>
You can use the <code>muie_attributes</code> parameter to replace the default <code>type=text</code> and/or <code>size=20</code> attributes or to add attributes such as <code>placeholder="Enter some keywords"</code>. separate multiple attributes with spaces and quote any value containing spaces.
<a name="muie_orderby"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The [muie_orderby] and [muie_order] shortcodes</h3>
<p>
The <code>[muie_orderby]</code> shortcode generates a dropdown control for passing the "orderby" parameter to an <code>[mla_gallery]</code>, <code>[mla_tag_cloud]</code>, or <code>[mla_term_list]</code> shortcode. You can add parameters to this shortcode to change the default value:
</p>
<ul class="mla-doc-toc-list">
<li>shortcode - mla_gallery, mla_tag_cloud, or mla_term_list</li>
<li>sort_fields</li>
<li>meta_value_num</li>
<li>meta_value</li>
</ul>
<p>
The <code>shortcode</code> parameter simply validates the list of sort fields allowed for each shortcode. For <code>[mla_gallery]</code>, the <code>[muie_orderby]</code> value is automatically translated to the <code>orderby</code> parameter. For <code>[mla_tag_cloud]</code>, or <code>[mla_term_list]</code> you must add something like <code>orderby="{+template:{+request:muie_orderby+}|name+}+}"</code> to the shortcode parameters.
</p>
<p>
The <code>[muie_order]</code> shortcode generates a radio button control for passing the "order" parameter to an <code>[mla_gallery]</code>, <code>[mla_tag_cloud]</code>, or <code>[mla_term_list]</code> shortcode. You can add parameters to this shortcode to change the default value:
</p>
<ul class="mla-doc-toc-list">
<li>default_order - ASC or DESC</li>
<li>asc_label</li>
<li>desc_label</li>
</ul>
<p>
For <code>[mla_gallery]</code>, the <code>[muie_order]</code> value is automatically translated to the <code>order</code> parameter. For <code>[mla_tag_cloud]</code>, or <code>[mla_term_list]</code> you must add something like <code>order="{+template:{+request:muie_order+}|ASC+}+}"</code> to the shortcode parameters.
</p>
<p>
The shortcodes are meant to be placed in an HTML form that lets the user enter criteria to filter a gallery display.
<a name="muie_per_page"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The [muie_per_page] shortcode</h3>
<p>
The <code>[muie_per_page]</code> shortcode generates a text box for passing the "posts_per_page" parameter to an <code>[mla_gallery]</code> shortcode. You can add parameters to this shortcode to change the default value:
</p>
<ul class="mla-doc-toc-list">
<li>posts_per_page</li>
<li>numberposts</li>
</ul>
<p>
The shortcode is meant to be placed in an HTML form that lets the user enter criteria to filter a gallery display.
<a name="muie_assigned_items_count"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The [muie_assigned_items_count] shortcode</h3>
<p>
The <code>[muie_assigned_items_count]</code> shortcode returns the number of items assigned to any term(s) in the selected taxonomy. You can add parameters to this shortcode to change the default value:
</p>
<ul class="mla-doc-toc-list">
<li>taxonomy - required; taxonomy slug.</li>
<li>post_type</li>
<li>post_status</li>
<li>post_mime_type</li>
</ul>
<p>
The shortcode is meant to be used anywhere you want to display the calculated number of items.
<a name="muie_text_box"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The [muie_text_box] shortcode</h3>
<p>
The <code>[muie_text_box]</code> shortcode generates a general purpose text box for passing a value to an <code>[mla_gallery]</code> shortcode. The value is "sticky"; it will survive page refreshes and pagination. You can add parameters to this shortcode to change the default values:
</p>
<ul class="mla-doc-toc-list">
<li>name</li>
<li>id</li>
<li>type</li>
<li>value</li>
</ul>
<p>
The shortcode is meant to be placed in an HTML form that lets the user enter criteria to filter a gallery display.
<a name="muie_archive_list"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>The [muie_archive_list] shortcode</h3>
<p>
The [muie_archive_list] shortcode function displays date-oriented values in a variety of formats; flat text, link lists, dropdown controls and "pagination"-like links. The archive list works with year, month, week and day values. MLA Archive List enhancements for lists and controls include:
</p>
<ul class="mla-doc-toc-list">
<li>Several display formats, including "flat", "list" and "dropdown".</li>
<li>Access to a wide range of content using the term-specific and Field-level Substitution parameters. A powerful Content Template facility lets you assemble content from multiple sources and vary the results depending on which data elements contain non-empty values for a given value.</li>
<li>Display Style and Display Content parameters for easy customization of the list display and the destination/value behind each value.
</li>
</ul>
<p>
The <code>[muie_archive_list]</code> shortcode has many parameters and some of them have a complex syntax; it can be a challenge to build a correct shortcode. The WordPress Shortcode API has a number of limitations that make techniques such as entering HTML or splitting shortcode parameters across multiple lines difficult. Read and follow the rules and guidelines in the MLA "Entering Long/Complex Shortcodes" Documentation section to get the results you want. 
</p>
<p>
The next Documentation sections are a complete reference for the shortcode. Don't be put off by the volume of material; you won't need most of the parameters for typical applications. You may want to start by reviewing the <a href="#muie_archive_list_examples">Archive List shortcode Examples</a> to see how easy using the shortcode can be.
</p>
<p>
Many of the <code>[muie_archive_list]</code> concepts and shortcode parameters are modeled after the <code>[mla_gallery]</code> and <code>[mla_tag_cloud]</code> shortcodes, so the learning curve is shorter. Differences and parameters unique to the shortcode are given in the sections below.
</p>
<a name="archive_type"></a>
</p>
<h4>Archive Type</h4>
<p>
List values have a prefix denoting the level of detail they contain followed by a numeric value. The <strong>"archive_type"</strong> parameter determines the level of detail for the list values:
</p>
<table>
<tr>
<td class="mla-doc-table-label">daily</td>
<td>four-digit year, two-digit month and two-digit day, e.g. <strong>"D20210615"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">weekly</td>
<td>four-digit year and two-digit week, e.g. <strong>"W202123"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">monthly</td>
<td>four-digit year and two-digit month, e.g. <strong>"M202106"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">yearly</td>
<td>four-digit year, e.g. <strong>"Y2021"</strong>. <strong>"yearly" is the default archive type value.</strong></td>
</tr>
</table>
<p>
These values will automatically be translated to the appropriate parameter values for the `[mla_gallery]` shortcode.
<a name="archive_source"></a>
</p>
<h4>Archive Source</h4>
<p>
The data source for an archive must be a text database field that contains a valid date format. The <strong>"archive_source"</strong> parameter determines the source for the list values:
</p>
<table>
<tr>
<td class="mla-doc-table-label">post_date</td>
<td>the post_date value in the wp_posts table. <strong>"post_date" is the default archive source value.</strong></td>
</tr>
<tr>
<td class="mla-doc-table-label">post_date_gmt</td>
<td>the post_date_gmt value in the wp_posts table.</td>
</tr>
<tr>
<td class="mla-doc-table-label">post_modified</td>
<td>the post_modified value in the wp_posts table.</td>
</tr>
<tr>
<td class="mla-doc-table-label">post_modified_gmt</td>
<td>the post_modified_gmt value in the wp_posts table.</td>
</tr>
<tr>
<td class="mla-doc-table-label">custom</td>
<td style="padding-bottom: 2em;">a custom field value. The custom field must contain a text value with a date format recognized by the database SQL date query functions.</td>
</tr>
<tr>
<td class="mla-doc-table-label">archive_key</td>
<td>for the "custom" archive source, this separate parameter gives the name of the custom field to be used for the query. For example, you can code something like <code>archive_source=custom archive_key="Date Taken"</code> where &ldquo;Date Taken&rdquo; is the name of a custom field you have created.</td>
</tr>
</table>
<p>
The four values from the wp_posts table are in a valid date format. For custom fields, a variety of common formats are acceptable. For example, you can use a mapping rule to source the custom field from:
</p>
<table>
<tr>
<td class="mla-doc-table-label">exif:DateTimeOriginal</td>
<td>YYYY:MM:DD HH:MM:SS</td>
</tr>
<tr>
<td class="mla-doc-table-label">iptc:DateCreated</td>
<td>YYYYMMDD</td>
</tr>
<tr>
<td class="mla-doc-table-label">xmp:CreateDate</td>
<td>YYYY-MM-DD HH:MM:SS  two digit month and day, 24-hour clock</td>
</tr>
</table>
<p>
You can use the timestamp and date field-level option/format values to convert many other formats to one of the valid formats illustrated above.
<a name="archive_list_output_formats"></a>
</p>
<h4>Archive List Output Formats</h4>
<p>
The default archive list output is a dropdown control with options for each archive value. The output markup and display format are determined by the <strong>"archive_output"</strong> parameter:
</p>
<table>
<tr>
<td class="mla-doc-table-label">dropdown</td>
<td>Returns an HTML "select" control with a sequence of HTML "option" tags. <strong>"dropdown" is the default output format value.</strong></td>
</tr>
<tr>
<td class="mla-doc-table-label">list</td>
<td>Returns hyperlinks enclosed by one of the HTML list tags; unordered (<strong>&lt;ul&gt;&lt;/ul&gt;, the default tag value</strong>) or ordered (&lt;ol&gt;&lt;/ol&gt;).</td>
</tr>
<tr>
<td class="mla-doc-table-label">flat</td>
<td>Returns a sequence of hyperlink tags without further HTML markup. The "separator" parameter content (default, one newline character) is inserted between each hyperlink.</td>
</tr>
<tr>
<td class="mla-doc-table-label">array</td>
<td>Returns a PHP array of list hyperlinks. This output format is not available through the shortcode; it is allowed when the <code>MLAShortcodes::muie_archive_list()</code> function is called directly from your theme or plugin PHP code.</td>
</tr>
<tr>
<tr>
<td class="mla-doc-table-label">next_archive</td>
<td>returns a link to the next archive value. The optional ",wrap" or ",always_wrap" qualifier determines what happens at the last value. If you omit the qualifier, an empty string is returned for the "next_archive" from the last value. If you code the ",wrap" or ",always_wrap" qualifier, the "next_archive" from the last value will be to the first value.</td>
</tr>
<tr>
<td class="mla-doc-table-label">current_archive</td>
<td>returns a link to the current archive value. This gives you an easy way to provide a visual indication of where you are within the list. The "span" and "none" link formats are often used with this archive_output type.</td>
</tr>
<tr>
<td class="mla-doc-table-label">previous_archive</td>
<td>returns a link to the previous archive value. The optional ",wrap" or ",always_wrap" qualifier determines what happens at the first value. If you omit the qualifier, an empty string is returned for the "previous_archive" from the first value. If you code the ",wrap" or ",always_wrap" qualifier, "previous_archive" from the first value will be to the last value.</td>
</tr>
<td class="mla-doc-table-label">paginate_archive</td>
<td>returns a link to values at the start and end of the list and to pages around the current value ( e.g.: « Previous 1995 ... 2000 2001 2002 2003 2004 ... 2021 Next » ). The optional ",show_all" qualifier will show all of the values instead of a short list around the current value. The optional ",prev_next" qualifier will include the "« Previous" and "Next »" portions of the link list.</td>
</tr>
</table>
<p>
The "next_archive", "current_archive" and "previous_archive" return an empty string if there is no current value, i.e., if no <code>muie_current_archive</code> value is set in the shortcode parameters or in the URL query attributes. If you code the "option_none_label" parameter its value will be displayed when there is no current value. If you add the ",always_wrap" qualifier the "next_archive" and "previous_archive" will <strong>always</strong> return the last/first value in the archive list if there is one.
<h4>Specific parameters for the <code>paginate_archive</code> output type</h4>
<table>
<tr>
<td class="mla-doc-table-label">end_size</td>
<td>How many numbers (default 1) appear on either the start and the end list edges</td>
</tr>
<tr>
<td class="mla-doc-table-label">mid_size</td>
<td>How many numbers (default 1) appear to either side of current item, but not including current item</td>
</tr>
<tr>
<td class="mla-doc-table-label">prev_text</td>
<td>the "previous page" text (default "&laquo; Previous") , which appears when the ",prev_next" qualifier is added to the output_type</td>
</tr>
<tr>
<td class="mla-doc-table-label">next_text</td>
<td>the "next page" text (default "Next &raquo;") , which appears when the ",prev_next" qualifier is added to the output_type</td>
</tr>
</table>
<p>If you code the "<strong>,show_all</strong>" qualifier, the above parameters have no effect. The "<strong>,show_all</strong>" qualifer is basically the same as "flat" output with short labels.
<a name="archive_list_display_content"></a>
</p>
<h4>Archive List Display Content</h4>
<p>
For the "dropdown" archive_output type, the list comprises a select tag (&lt;select&gt;&lt;/select&gt;) enclosing a list of option (&lt;option&gt;&lt;/option&gt;) tags. For the "list" archive_output type, the list comprises an unordered list tag (&lt;ul&gt;&lt;/ul&gt;) enclosing a list of item (&lt;li&gt;&lt;/li&gt;) tags. The following parameters customize the overall type and content of the control/list:
</p>
<table>
<tr>
<td class="mla-doc-table-label">muie_current_archive</td>
<td>Identifies the current/selected value in the list. It will be ignored if it does not match an item in the list.</td>
</tr>
<tr>
<td class="mla-doc-table-label">archive_parameter_name</td>
<td>The name of the parameter containing the current item value; <strong>default "muie_current_archive"</strong>. You can change the name if you need multiple controls/lists on one post/page.</td>
</tr>
<tr>
<td class="mla-doc-table-label">archive_order</td>
<td>Selects the order in which values are displayed. Use <strong>"DESC" (the default value)</strong> to display the newest value first, or "ASC" to display the oldest value first.</td>
</tr>
<tr>
<td class="mla-doc-table-label">archive_limit</td>
<td>Sets the maximum number of values to be displayed. Use <strong>"0" (zero, the default value)</strong> to display all of the values. Use a positive number to limit the values.</td>
</tr>
<tr>
<td class="mla-doc-table-label">archive_label</td>
<td>Selects the length of the text displayed for each value. Use "short" for short labels, e.g., "Sep 2021". Use "long" for more complete labels, e.g., "September 2021". <strong>The default value for the "paginate_archive" archive_output is "short". All other output types default to "long".</strong>  </td>
</tr>
<tr>
<td class="mla-doc-table-label">show_count</td>
<td>Selects the option to display the number of items assigned to each archive value.Use <strong>"true" (the default value)</strong> to display the count. Use "false" to omit the count.</td>
</tr>
<tr>
<td class="mla-doc-table-label">hide_if_empty</td>
<td>If <strong>false (default)</strong>, display a control with "option_none_label" text &amp;  "option_none_value" value. If true, display  "option_none_label" as plain text or nothing.</td>
</tr>
</table>
<p>
The following parameters customize overall control/list content and markup for the "dropdown" and "list" archive_output types:
</p>
<table>
<tr>
<td class="mla-doc-table-label">listtag</td>
<td>The HTML tag that encloses the list of archive values. For the <strong>"dropdown" archive_output type, the default is "select"</strong>. For the <strong>"list" archive_output type, the default is "ul"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">listtag_name</td>
<td>For the "dropdown" archive_output type, the "name" attribute of HTML tag that begins the list of archive values. <strong>The default is "muie_current_archive"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">listtag_id</td>
<td>The "id" attribute of the HTML tag that begins the list of archive values; <strong>default "muie_archive_list-{$instance}"</strong>, where "{$instance}" is a number starting at "1" making the value unique.</td>
</tr>
<tr>
<td class="mla-doc-table-label">listtag_class</td>
<td>The "class" attribute of the HTML tag that begins the list of archive values; <strong>default "muie-archive-list"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">listtag_attributes</td>
<td>Additional attribute(s) the HTML tag that begins the list of archive values; <strong>default empty, no value</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">itemtag</td>
<td>The HTML tag that encloses each value in the archive list. For the <strong>"dropdown" archive_output type, the default is "option"</strong>. For the <strong>"list" archive_output type, the default is "li"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">current_archive_class</td>
<td>The class attribute for the current item in the archive list as determined by the "muie_current_archive" parameter (if specified); <strong>default "muie-current-archive"</strong>.</td>
</tr>
</table>
<p>
The following parameters customize the content of two special control/list item values:
</p>
<table>
<tr>
<td class="mla-doc-table-label">option_none_label</td>
<td>Text to display when there are no archive values in the list. If the "hide_if_empty=true" parameter is present this is displayed as plain text; <strong>default is empty, no value</strong>. If not, the option_none_label and option_none_value are used to generate an "empty" control or list; <strong>default "No archives"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">option_none_value</td>
<td>Control value to use when there are no archive values in the list; <strong>default "no-archives"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">option_all_label</td>
<td>Text to display for showing an "all values" option. Default will not show an option to select "all values". When this option is selected all items, regardless of their archive value, are included in the `[mla_gallery]` results.</td>
</tr>
<tr>
<td class="mla-doc-table-label">option_all_value</td>
<td>Control value for "all values" option. <strong>Default empty, no value</strong>. When this option is selected all items, regardless of their archive value, are included in the `[mla_gallery]` results. To display an empty gallery enter an invalid date value in the appropriate format.</td>
</tr>
</table>
<p>
The following parameters customize content and markup for each control/list item:
</p>
<table>
<tr>
<td class="mla-doc-table-label">itemtag_id</td>
<td>The id attribute for each item in the list; <strong>default empty, no value</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">itemtag_class</td>
<td>The class attribute(s) for each item in the list; <strong>default "muie-archive-list-item"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">itemtag_attributes</td>
<td>Additional attribute for each item in the list; <strong>default empty, no value</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">itemtag_value</td>
<td>A custom format or content template for the item value; <strong>default determined by "archive_type"</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">itemtag_label</td>
<td>A custom format or content template for the item name/label; <strong>default determined by "archive_type"</strong>.</td>
</tr>
</table>
<p>
For the "list", "flat" and "array" archive_output types, each item in the list comprises an archive value and a hyperlink surrounding the value. The following parameters customize link content and markup:
</p>
<table>
<tr>
<td class="mla-doc-table-label">separator</td>
<td>The text/space between items. <strong>Default "\n"</strong> (newline whitespace)</td>
</tr>
<tr>
<td class="mla-doc-table-label">link</td>
<td>Chooses the destination of the item hyperlink; details in the next section below.</td>
</tr>
<tr>
<td class="mla-doc-table-label">link_id</td>
<td>The id attribute for each link in the list; <strong>default empty, no value</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">link_class</td>
<td>The class attribute for each link in the list; <strong>default empty, no value</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">rollover_text</td>
<td>The text for the "title" attribute (Rollover Text); <strong>default empty, no value</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">link_attributes</td>
<td>Additional attribute(s) for each link in the list; <strong>default empty, no value</strong>.</td>
</tr>
<tr>
<td class="mla-doc-table-label">link_text</td>
<td>The text surrounded by the hyperlink tags (&lt;a ... &gt;&lt;/a&gt;). The default text is an appropriate display format for each value.</td>
</tr>
<tr>
<td class="mla-doc-table-label">link_href</td>
<td>The href attribute of the hyperlink tags; <strong>default empty, no value</strong>. When present, this value replaces the <code>link=</code> values (current, view, span or none)</td>
</tr>
</table>
<p>
The item and link parameters are an easy way to customize the content and markup for each control/list item. They support the Archive List Substitution Parameters and Content Template substitution parameters. For example, if you code "<code>rollover_text='{+current_label+} ({+items+})'</code>, the rollover text will contain the item value's label followed by the number of items having that value in parentheses. Simply add "{+" before the substitution parameter name and add "+}" after the name. Note that the enclosing delimiters are different than those used in the templates, since the WordPress shortcode parser reserves square brackets ("[" and "]") for its own use.
</p>
<p>
The "link_href" parameter is a great way to change the destination your list item links to and/or add arguments to the link for later processing. For example, to make a gallery item link back to the current page/post you can code: <code>link_href='{+page_url+}'</code>. You can also add arguments to the link, e.g., <code>mla_link_href='{+page_url+}?firstarg=value1&amp;amp;myarg=myvalue'</code>. Note the use of the HTML entity name "&amp;amp;" to put an ampersand in the value; the WordPress "visual" post editor will replace "&amp;", "&lt;" and ">" with "&amp;amp;", "&amp;lt;" and "&amp;gt;" whether you like it not. The <strong>only</strong> markup parameters modified by this parameter are "link_url" and "thelink". The markup parameters "currentlink" and "viewlink" are not modified.
</p>
<p>
The "link_attributes" parameter accepts any value and adds it to the "&lt;a&gt;" or "&lt;span&gt;" tags for the item. For example, you can add an HTML "target" attribute to the hyperlink. If you code <code>link_attributes='target="_blank"'</code> the item will open in a new window or tab. You can also use "_self", "_parent", "_top" or the "<em>framename</em>" of a named frame. Note the use of single quotes around the parameter value and the double quotes within the parameter. <strong>IMPORTANT:</strong> since the shortcode parser reserves square brackets ("[" and "]") for its own use, <strong>you must substitute curly braces for square brackets</strong> if your attributes require brackets. If you must code a curly brace in your attribute value, preface it with <strong>two backslash characters</strong>, e.g., "\\{" or "\\}". If you code an attribute already present in the tag, your value will override the existing value.
<a name="archive_list_link"></a>
</p>
<h4>Archive List Item Link Values (Array, Flat and List)</h4>
<p>
The Link parameter specifies the target and type of link from the list item back to the current post/page, the item's archive page, edit page or other destination. You can also specify a non-hyperlink treatment for each item. These values only apply to the hyperlinks generated for the "array", "flat" and "list" output formats.
</p>
<table>
<tr>
<td class="mla-doc-table-label">current</td>
<td>Link back to the current post/page with a query argument, <code>muie_current_archive</code>, set to the archive_type value of the selected item. <strong>"current" is the default item link value.</strong></td>
</tr>
<tr>
<td class="mla-doc-table-label">view</td>
<td>Link to the term's "archive page". Support for archive pages, or "date archives", is theme-dependent. There is an introduction to tag archives in the WordPress Codex at the bottom of the <a href="http://codex.wordpress.org/Function_Reference/wp_tag_cloud#Creating_a_Tag_Archive" title="Codex Tag Archive Discussion" target="_blank"><code>wp_tag_cloud</code> Function Reference</a>.</td>
</tr>
<tr>
<td class="mla-doc-table-label" style="font-style:italic">(link_href)</td>
<td>Link to a custom destination, typically another post/page. If the "link_href" parameter is present the value of the "link" parameter is ignored. See the example later in this section for more details.</td>
</tr>
<tr>
<td class="mla-doc-table-label">span</td>
<td>Substitutes a <code>&lt;span&gt;&lt;/span&gt;</code> tag for the hyperlink tag. You can use the  "link_id", "link_class" and "link_attributes" parameters to add attributes to the <code>&lt;span&gt;</code> tag. You can use the "link_text" parameter to customize the text within the span.</td>
</tr>
<tr>
<td class="mla-doc-table-label">none</td>
<td>Eliminates the hyperlink tag surrounding the item text. You can use the "mla_link_text" parameter to customize the contents.</td>
</tr>
</table>
<p>
Using the "link_href" parameter to completely replace the link destination URL is a common and useful choice. With this parameter you can use the archive list to select a value and then go to another post/page that uses that selection as part of an <code>[mla_gallery]</code> shortcode. The examples section illustrates this technique. 
<a name="archive_list_other"></a>
</p>
<h4>Archive List Other Parameters</h4>
<table>
<tr>
<td class="mla-doc-table-label">muie_debug</td>
<td>controls debug log output; <strong>default empty; no value</strong>, "false", "log" or "true". See below for details.</td>
</tr>
<tr>
<td class="mla-doc-table-label">mla_debug</td>
<td>adds debug log output for "get attachments" database query; <strong>default "false"</strong> or "true". See below for details.</td>
</tr>
<tr>
<td class="mla-doc-table-label">echo</td>
<td>This does not apply to the shortcode; it is allowed when the <code>MLAShortcodes::muie_archive_list()</code> function is called directly from your theme or plugin PHP code. If <code>echo=false</code> content generated by the function is returned to the caller; <strong>false is the default value</strong>. If <code>echo=true</code> content is echoed to the browser and nothing is returned.</td>
</tr>
</table>
<p>
The "muie_debug" parameter controls the display of information about parameter processing and control/list output generation. If you leave this parameter out you can use the "MLA Reporting" value in the Settings/Media Library Assistant Debug Tab to activate logging by adding "0x8000" to the value, e.g., enter "0x8001" to log this category only.
</p>
<p>If you code <code>muie_debug=true</code> you will see a lot of information added to the post or page containing the list. Of course, this parameter should <strong><em>ONLY</em></strong> be used in a development/debugging environment; it's quite ugly.
</p>
<p>
If you code <code>muie_debug=log</code> all of the information will be written to the error log. You can use the MLA Debug Tab to view and download the information in the error log.
</p>
<p>
When <code>muie_debug=log</code> or <code>muie_debug=true</code> you can activate some database logging by adding the <code>mla_debug=true</code> paremeter. In this shortcode, <code>mla_debug</code> only accepts "true" or "false" and it goes to the destination set by the <code>muie_debug</code> parameter.
<a name="archive_list_data_selection"></a>
</p>
<h4>Archive List Data Selection Parameters</h4>
<p>
The Archive List shortcode displays only those date values to which one or more Media Library items are assigned. The Data Selection parameters provide a way to filter the list, e.g., specifying a taxonomy term or a particular MIME type.
</p>
<p>
The Archive List shortcode uses the same database query functions employed by the <code>[mla_gallery]</code> shortcode. All of the <code>[mla_gallery]</code> data selection parameters are available, including:
</p>
<ul class="mla-doc-toc-list">
<li>Include, Exclude</li>
<li>Post ID, "ids", Post Parent</li>
<li>Author, Author Name</li>
<li>Category Parameters</li>
<li>Tag Parameters</li>
<li>Simple Taxonomy Parameters</li>
<li>Compound Taxonomy Parameters, "tax_input"</li>
<li>Taxonomy Queries, the "tax_query"</li>
<li>Taxonomy term keyword(s) search</li>
<li>Post MIME Type</li>
<li>Post Type, Post Status</li>
<li>Simple Date Parameters</li>
<li>Date and Time Queries, the "date query"</li>
<li>Simple Custom Field Parameters</li>
<li>Custom Field Queries, the "meta_query"</li>
<li>Keyword(s) Search</li>
</ul>
<p>
If you do not code any data selection parameters, <code>post_parent=all</code> will be added so the default data selection parameters are:
</p>
<ul class="mla-doc-toc-list">
<li><code>post_parent=all</code></li>
<li><code>post_type=attachment</code></li>
<li><code>post_status=inherit</code></li>
<li><code>post_mime_type=image</code></li>
</ul>
<p>
The default parameters will select all of the image items in your Media Library, extract the date value you have chosen from each item and use the values to build the archive list. For example, if you simply code <code>[muie_archive_list]</code> with no parameters you will get a dropdown control with one option for each year in which one or more items was added to the Media Library.
<a name="archive_list_substitution"></a>
</p>
<h4>Archive List Substitution Parameters</h4>
<p>
Substitution parameters are a powerful way to add general and attachment-specific values to the list display. For example, if you code "<code>mla_link_href="{+page_url+}?muie_current_archive={+current_value+}&amp;current_name={+current_label,url+}"</code>, the hyperlinks behind each list term will contain the page URL, the current archive value and the value's label encoded in url format. There are many parameter names like `page_url` and `current_label` divided in several categories:
</p>
<table>
<tr>
<td class="mla-doc-table-label">Shortcode-specific</td>
<td>All of the shortcode arguments defined above are available as substitution parameters, as well as additional values from sources like query arguments and shortcode parameters. The "request:" and "query:" prefixes can be used in the list. A Content Template lets you compose a value from multiple substitution parameters and test for empty values, choose among two or more alternatives or suppress output entirely.</td>
</tr>
<tr>
<td class="mla-doc-table-label">List-specific</td>
<td>values that are known at the beginning of shortcode processing and remain the same for the entire shortcode, such as the ID and URL of the post/page in which the shortcode appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">Item-specific</td>
<td  style="vertical-align: top">values that change for each term/item in the list, such as Name and Description</td>
</tr>
</table>
<p>
The following paragraphs go into more detail about each category and the parameter names within them.
</p>
<p>
To use a substitution parameter in your shortcode, simply add "{+" before the substitution parameter name and add "+}" after the name. Note that the enclosing delimiters are different than those used in Style and Markup templates, since the WordPress shortcode parser reserves square brackets ("[" and "]") for its own use. Also, because square brackets are reserved, <strong>you must substitute curly braces for square brackets</strong> if your parameter values require them. For example, if your shortcode parameter is <code>mla_link_attributes='rel="shadowbox{sbalbum-{+instance+}};player=img"'</code>, the actual attribute added to the link will be <code>rel="shadowbox[sbalbum-1];player=img"</code>. If you must code a curly brace in a parameter value, preface it with <strong>two backslash characters</strong>, e.g., "\\{" or "\\}".
</p>
<p>
<strong>Shortcode-level substitution parameters</strong> are available throughout the gneration of the control/list:</p>
<table>
<tr>
<td class="mla-doc-table-label">shortcode arguments</td>
<td>All of the shortcode arguments defined above are available. If an argument has been set by a shortcode parameter its value will reflect that. If the shortcode parameter contains substitution parameters or a content template the argument will reflect the results of processing those elements. All arguments that do not appear as shortcode parameters will be set to their default values.</td>
</tr>
<tr>
<td class="mla-doc-table-label">request: prefix</td>
<td>The parameters defined in the <code>$_REQUEST</code> array; the "query strings" sent from the browser. The PHP $_REQUEST variable is a superglobal Array that contains the contents of both $_GET, $_POST and $_COOKIE arrays.</td>
</tr>
<tr>
<td class="mla-doc-table-label">query: prefix</td>
<td>The parameters defined in the <code>[muie_archive_list]</code> shortcode. For example, if your shortcode is <code>[muie_archive_list taxonomy=attachment_tag div-class=some_class]</code> you can access the parameters as <code>[+query:taxonomy+]</code> and <code>[+query:div-class+]</code> respectively. If the shortcode parameter contains substitution parameters or a content template the query: value will NOT reflect the results of processing those elements. You can define your own parameters, e.g., "div-class"; they will be accessible as shortcode-level data but will otherwise be ignored.</td>
</tr>
<tr>
<td class="mla-doc-table-label">template: prefix</td>
<td>A Content Template, which lets you compose a value from multiple substitution parameters and test for empty values, choosing among two or more alternatives or suppressing output entirely. See the "Content Templates" section of the Settings/Media Library Assistant Documentation tab for details. Note that the formatting option is not supported for content templates.</td>
</tr>
</table>
<p>
<strong>List-specific substitution parameters</strong> are known at the beginning of shortcode processing and they do not change during processing. They can be used, for example, in any of the data selection parameters to change the items selected for the list based on information about the post/page on which the list appears. The list-specific substitution parameters are:
</p>
<table>
<tr>
<td class="mla-doc-table-label">instance</td>
<td>starts at "1"', incremented for each additional shortcode in the post/page</td>
</tr>
<tr>
<td class="mla-doc-table-label">selector</td>
<td>"muie_archive_list-{$instance}", e.g., muie_archive_list-1</td>
</tr>
<tr>
<td class="mla-doc-table-label">site_url</td>
<td>absolute URL to the site directory, without trailing slash</td>
</tr>
<tr>
<td class="mla-doc-table-label">base_url</td>
<td>absolute URL to the upload directory, without trailing slash</td>
</tr>
<tr>
<td class="mla-doc-table-label">base_dir</td>
<td>absolute (full) path to the upload directory, without trailing slash</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_ID,<br />id</td>
<td style="vertical-align: top">the <code>ID</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_author</td>
<td>the <code>post_author</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_date</td>
<td>the <code>post_date</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_content</td>
<td>the <code>post_content</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_title</td>
<td>the <code>post_title</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_excerpt</td>
<td>the <code>post_excerpt</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_status</td>
<td>the <code>post_status</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_name</td>
<td>the <code>post_name</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_modified</td>
<td>the <code>post_modified</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_guid</td>
<td>the <code>post_guid</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_type</td>
<td>the <code>post_type</code> value of the post/page in which the list appears</td>
</tr>
<tr>
<td class="mla-doc-table-label">page_url</td>
<td>absolute URL to the page or post on which the list appears, if any, with trailing slash</td>
</tr>
</table>
<p>
<strong>Item-specific substitution parameters</strong> are, well, specific to each item in the archive list. The list-specific substitution parameters are:
</p>
<table>
<tr>
<td class="mla-doc-table-label">current_value</td>
<td>the value of the item, e.g., "post_date,M(202011)" or "custom:CreateDate,M(202011)". When passed to <code>[mla_gallery[</code> this will be translated to the appropriate date query parameter(s).</td>
</tr>
<tr>
<td class="mla-doc-table-label">current_label_short</td>
<td>the short version of the item's label, e.g., "Nov 2020".</td>
</tr>
<tr>
<td class="mla-doc-table-label">current_label_long</td>
<td>the long version of the item's label, e.g., "November 2020".</td>
</tr>
<tr>
<td class="mla-doc-table-label">current_label</td>
<td>the selected version of the item's label, based on the archive_label parameter.</td>
</tr>
<tr>
<td class="mla-doc-table-label">items</td>
<td>the number of items that will be selected by this value.</td>
</tr>
<tr>
<td class="mla-doc-table-label">year</td>
<td>the four-digit year portion of the value, if present, else empty.</td>
</tr>
<tr>
<td class="mla-doc-table-label">month</td>
<td>the two-digit month portion of the value, if present, else empty.</td>
</tr>
<tr>
<td class="mla-doc-table-label">week</td>
<td>the two-digit week portion of the value, if present, else empty.</td>
</tr>
<tr>
<td class="mla-doc-table-label">day</td>
<td>the two-digit day portion of the value, if present, else empty.</td>
</tr>
<tr>
<td class="mla-doc-table-label">m</td>
<td>the six-digit year and month portion of the value, if present, else empty.</td>
</tr>
<tr>
<td class="mla-doc-table-label">yyyymmdd</td>
<td>for weekly archives, a random date within the week, formatted as "yyyy-mm-dd". Used to determine the start and end dates of the week.</td>
</tr>
<tr>
<td class="mla-doc-table-label">month_long</td>
<td>the long version of the value's month, e.g., "November".</td>
</tr>
<tr>
<td class="mla-doc-table-label">month_short</td>
<td>the short version of the value's month, e.g., "Nov".</td>
</tr>
<tr>
<td class="mla-doc-table-label">week_start_raw</td>
<td>the timestamp version of the week's first day, e.g., "1605484800".</td>
</tr>
<tr>
<td class="mla-doc-table-label">week_start_short</td>
<td>the short version of the week's first day, e.g., "2020-11-16".</td>
</tr>
<tr>
<td class="mla-doc-table-label">week_start</td>
<td>the long version of the week's first day, e.g., "November 16, 2020".</td>
</tr>
<tr>
<td class="mla-doc-table-label">week_end_raw</td>
<td>the timestamp version of the week's last day, e.g., "1606089599"</td>
</tr>
<tr>
<td class="mla-doc-table-label">week_end_short</td>
<td>the short version of the week's last day, e.g., "2020-11-22".</td>
</tr>
<tr>
<td class="mla-doc-table-label">week_end</td>
<td>the long version of the week's last day, e.g., "November 22, 2020".</td>
</tr>
<tr>
<td class="mla-doc-table-label">item_id</td>
<td>the id attribute of the item, taken from the itemtag_id, if present, or defaulted to the listtag_id, a dash, then the current_value.</td>
</tr>
<tr>
<td class="mla-doc-table-label">item_class</td>
<td>the class attribute of the item, taken from the itemtag_class, if present, or defaulted to "muie-archive-list-item". If this is the current archive item, the "current_archive_class" value is added.</td>
</tr>
<tr>
<td class="mla-doc-table-label">item_attributes</td>
<td>all of the item's attributes; the "item_id", "item_class" and any additional attributes from the "itemtag_attributes" parameter.</td>
</tr>
<tr>
<td class="mla-doc-table-label">item_selected</td>
<td>set to "selected=selected" for the current item, else empty.</td>
</tr>
<tr>
<td class="mla-doc-table-label">item_label</td>
<td>the text label of the item, taken from the itemtag_label, if present, or defaulted to the current_label.</td>
</tr>
<tr>
<td class="mla-doc-table-label">item_link_id</td>
<td>the id attribute of the item's link, taken from the link_id, if present, or defaulted to empty.</td>
</tr>
<tr>
<td class="mla-doc-table-label">item_link_class</td>
<td>the class attribute of the item's link, taken from the link_class, if present, or defaulted to empty.</td>
</tr>
<tr>
<td class="mla-doc-table-label">item_link_rollover</td>
<td>the title attribute of the item's link, taken from the rollover_text, if present, or defaulted to empty.</td>
</tr>
<tr>
<td class="mla-doc-table-label">item_link_attributes</td>
<td>all of the link's attributes; the "item_link_id", "item_link_class" and any additional attributes from the "link_attributes" parameter.</td>
</tr>
<tr>
<td class="mla-doc-table-label">item_link_text</td>
<td>the text value of the link, taken from the link_text, if present, or defaulted to the current_label.</td>
</tr>
<tr>
<td class="mla-doc-table-label">thelink</td>
<td>the item's full link value, selected by the link parameter (current, view, span, none).</td>
</tr>
<tr>
<td class="mla-doc-table-label">currentlink</td>
<td>the link back to the current page with the value of the current item.</td>
</tr>
<tr>
<td class="mla-doc-table-label">viewlink</td>
<td>the link to the item's archive page.</td>
</tr>
<tr>
<td class="mla-doc-table-label">link_url</td>
<td>the URL portion of thelink, if any. Empty for the span and none settings.</td>
</tr>
<tr>
<td class="mla-doc-table-label">currentlink_url</td>
<td>the URL portion of currentlink.</td>
</tr>
<tr>
<td class="mla-doc-table-label">viewlink_url</td>
<td>the URL portion of viewlink.</td>
</tr>
</table>
<p>
&nbsp;
<a name="filters_examples"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>use_filters/add_filters_to Example</h3>
<p>
Here is a simple example with a term selection search form and a paginated gallery display:
</p>
<code>
&lt;form id="mla-search-form" action="." method="post"&gt;
<br />&lt;h3&gt;Att. Categories&lt;/h3&gt;
<br />[mla_term_list mla_output=dropdown taxonomy=attachment_category minimum=1 post_mime_type=image mla_option_value="{+slug+}" <strong>use_filters=true</strong>]
<br />&lt;input id="search-form-submit" name="search_form_submit" type="submit" value="Filter"&gt;
<br />&lt;/form&gt;
<br />&nbsp;
<br />&lt;h3&gt;Gallery&lt;/h3&gt;
<br />[mla_gallery attachment_category="{+template:({+request:tax_input.attachment_category+}|muie-no-terms)+}" <strong>add_filters_to=attachment_category</strong> posts_per_page=4 mla_output="paginate_links,prev_next"]
<br />&nbsp;
<br />[mla_gallery attachment_category="{+template:({+request:tax_input.attachment_category+}|muie-no-terms)+}" <strong>add_filters_to=attachment_category</strong> posts_per_page=4 mla_nolink_text="Select a term to display the gallery."]
</code>
<p>
The <code>[mla_term_list]</code> shortcode generates a dropdown control containing all terms in the Att. Categories taxonomy that have at least one Media Library "image" item assigned to them. The first <code>[mla_gallery]</code> shortcode generates pagination controls when there are more than four items assigned to the selected term. The second <code>[mla_gallery]</code> shortcode generates the gallery display, up to four items at a time.
</p>
<p>
The <code>use_filters=true</code> parameter in the <code>[mla_term_list]</code> shortcode activates the logic to preserve term selections across page refreshes and pagination operations. The <code>add_filters_to=attachment_category</code> parameter in both <code>[mla_gallery]</code> shortcodes does two things. First, it activates the logic to add term selections to the pagination links. Second, it looks for the special <code>attachment_category=muie-no-terms</code> value and drops the taxonomy query from the shortcode when it is found. This causes the initial gallery display, i.e., before a term is selected, to include all images in the Media Library.
</p>
<p>
The <code>attachment_category="{+template:({+request:tax_input.attachment_category+}|muie-no-terms)+}"</code> parameter matches the term selection value in the search form when the "Filter" button is clicked. Before the "Filter" button is clicked there is no <code>tax_input</code> array value in the request parameters so the template selects the <code>muie-no-terms</code> value instead.
</p>
<p>
If you change <code>add_filters_to=attachment_category</code> to <code>add_filters_to=any</code>, the <code>muie-no-terms</code> match logic is disabled. The <code>muie-no-terms</code> value is treated like a term slug and, since it does not match any term in the taxonomy the initial gallery display will be empty.
</p>
<p>
As always, the data selection parameters (including <code>add_filters_to</code>) in both of the <code>[mla_gallery]</code> shortcodes <strong>must be identical</strong>. This is necessary for the pagination controls to select the same items as the gallery display.
<a name="sticky_examples"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Sticky Shortcodes Example</h3>
<p>
Here is a simple example with a search form containing some sticky shortcodes and a paginated gallery display:
</p>
<code>
&lt;form id="mla-search-form" action="." method="post"&gt;<br />
Att. Tags: [muie_terms_search]<br />
mla_terms_taxonomies=attachment_tag<br />
mla_term_delimiter=' '<br />
mla_phrase_connector='OR'<br />
[/muie_terms_search]<br />
<br />
Keyword(s): [muie_keyword_search]<br />
mla_search_fields='title,excerpt,content'<br />
mla_search_connector='OR'<br />
[/muie_keyword_search]<br />
<br />
Items per page: [muie_per_page numberposts=4]<br />
<br />
&lt;input id="search-form-submit" name="search_form_submit" type="submit" value="Filter"&gt;<br />
&lt;/form&gt;<br />
&lt;h3&gt;Gallery&lt;/h3&gt;<br />
[mla_gallery add_filters_to=any default_empty_gallery=true post_parent=all posts_per_page=4 mla_output="paginate_links,prev_next"]<br />
<br />
[mla_gallery add_filters_to=any default_empty_gallery=true post_parent=all posts_per_page=4 mla_caption="{+title+} : {+description+}" mla_nolink_text="Enter tag(s) and/or keyword(s) to display the gallery."]
</code>
<p>
The first two shortcodes in the example use the alternative "enclosing shortcode" syntax so parameters can be entered on multiple lines for readability.
</p>
<p>
The <code>[muie_terms_search]</code> shortcode generates a text box that accepts one or more phrases to be matched to part or all of a term name in the Att. Tags taxonomy. Phrases are separated by spaces and if two or more phrases are entered any one of them will yield a match. The value entered in the text box is passed to <code>[mla_gallery]</code> as <code>mla_terms_phrases</code>. The <code>mla_terms_taxonomies</code>, <code>mla_term_delimiter</code> and <code>mla_phrase_connector</code> parameters are passed to <code>[mla_gallery]</code> as well.
</p>
<p>
The <code>[muie_keyword_search]</code> shortcode generates a text box that accepts one or more keywords to be matched to part or all of the Title, Caption (excerpt) or Description (content) fields. If two or more keywords are entered any one of them will yield a match. The value entered in the text box is passed to <code>[mla_gallery]</code> as <code>s</code>. The <code>mla_search_fields</code>, <code>mla_search_connector</code>, <code>sentence</code> and <code>exact</code> parameters are passed to <code>[mla_gallery]</code> as well.
</p>
<p>
The <code>[muie_per_page]</code> shortcode generates a text box that accepts the number of posts per page to be displayed in the gallery. The value entered in the text box is passed to <code>[mla_gallery]</code> as <code>muie_per_page</code> and it will be converted to <code>posts_per_page</code>. The initial gallery display will not be limited unless you also add an explicit <code>posts_per_page</code> parameter to <code>[mla_gallery]</code>; as the above example shows, its value should match the default value entered in <code>[muie_per_page]</code>.
</p>
<p>
The two <code>[mla_gallery]</code> shortcodes include <code>add_filters_to=any</code> to activate the sticky shortcode processing. The <code>default_empty_gallery=true</code> parameter ensures that no items are displayed until a term or keyword search is performed. If you omit this parameter, the <code>post_parent=all</code> parameter displays all Media Library image items; without it only the items attached to the post/page are displayed by default. The <code>posts_per_page=4</code> parameter limits the initial gallery display to four items.
</p>
<p>
As always, the data selection parameters (including <code>add_filters_to</code>) in both of the <code>[mla_gallery]</code> shortcodes <strong>must be identical</strong>. This is necessary for the pagination controls to select the same items as the gallery display.
<a name="muie_archive_list_examples"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Archive List Shortcode Examples</h3>
<p>
The MUIE Archive List shortcode is very flexible and has many parameters and features. However, you can accomplish many application goals with simple shortcodes and just a few parameters. For example, here is a shortcode that will generate a dropdown control with an option to select Media Library items uploaded in a given year:
<br />&nbsp;<br />
<code>[muie_archive_list]</code>
<br />&nbsp;<br />
Pretty simple. Adding a couple of parameters gives you an archive with year and month values and a default option for the initial display:
<br />&nbsp;<br />
<code>[muie_archive_list archive_type=monthly option_all_label="Select a Year and Month"]</code>
<br />&nbsp;<br />
The default source date for archive values is the "Uploaded on" date (post_date); you can easily change this to a custom field. For example, if you have created a "CreateDate" custom field by mapping the <code>exif:DateTimeOriginal</code>, <code>iptc:DateCreated</code> and/or <code>xmp:CreateDate</code> values you can build the archive from it:
<br />&nbsp;<br />
<code>[muie_archive_list archive_type=monthly archive_source=custom archive_key=CreateDate option_all_label="Select a Year and Month"]</code>
<br />&nbsp;<br />
The default output is a dropdown control you can add to an HTML "search form". You can change this to an unordered list where each list item is a hyperlink to the corresponding value:
<br />&nbsp;<br />
<code>[muie_archive_list archive_type=monthly archive_output=list archive_limit=10 show_count=true]</code>
<br />&nbsp;<br />
The above example also limits the display to the ten most recent values and adds a count of the number of items selected by each value to the display.
</p>
<h4>Archive List and MLA Gallery combinations</h4>
<p>
The MUIE Archive List shortcode is designed to work with the <code>[mla_gallery]</code> shortcode to display galleries filtered by a date value. To link the archive list to the gallery simply add <code>archive_parameter_name=muie_current_archive</code> to your <code>[mla_gallery]</code> shortcode. Here is a simple example combining an archive search form with a gallery display:
<br />&nbsp;<br />
<code>
&lt;h3&gt;Archive Search Form&lt;/h3&gt;<br />
&lt;form id="archive-search-form" action="." method="post"&gt;<br />
[muie_archive_list archive_type=monthly option_all_label="Select Uploaded on date"]<br />
&lt;input id="search-form-submit" name="search_form_submit" type="submit" value="Filter"&gt;<br />
&lt;/form&gt;<br />
&lt;h3&gt;Gallery&lt;/h3&gt;<br />
[mla_gallery post_parent=all <strong>archive_parameter_name=muie_current_archive</strong> ]
</code>
<br />&nbsp;<br />
The above example displays all of the image items in the Media Library when the page is first loaded or when the "Select Uploaded on date" option is selected. If you want to start with an empty gallery, use a date that doesn't match any of your items, e.g., add these parameters to the shortcodes:
<br />&nbsp;<br />
<code>
&lt;h3&gt;Archive Search Form&lt;/h3&gt;<br />
&lt;form id="archive-search-form" action="." method="post"&gt;<br />
[muie_archive_list archive_type=monthly option_all_label="Select Uploaded on date" <strong>option_all_value=post_date,M(190001)</strong>]<br />
&lt;input id="search-form-submit" name="search_form_submit" type="submit" value="Filter"&gt;<br />
&lt;/form&gt;<br />
&lt;h3&gt;Gallery&lt;/h3&gt;<br />
[mla_gallery post_parent=all archive_parameter_name=muie_current_archive <strong>muie_current_archive="{+template:{+request:muie_current_archive+}|post_date,M\\(190001\\)+}"</strong> ]
</code>
<br />&nbsp;<br />
Note the double backslash characters in the template; they prevent the template processor from interpreting the parentheses as a "Conditional" element. Two are required because of the way WordPress processes shortcode parameters.
</p>
<h4>Filtering the MLA Gallery directly</h4>
<p>You can use the <code>[mla_gallery]</code> parameters by themselves to filter a gallery display by date values. This is particularly useful when you want to filter based on a date held in a custom field, which the core WordPress date parameters and date query do not support. For example, if you have a custom field named "Publication Date" and want to display what was published in 2020 you can code something like:
<br />&nbsp;<br />
<code>[mla_gallery post_parent=all archive_parameter_name=muie_current_archive muie_current_archive="custom:Publication Date,Y(2020)"]
</code>
<br />&nbsp;<br />
Note the use of <code>post_parent=all</code>  to overide the default display of items attached to the current post/page. You can use the <code>current_timestamp</code>, <code>current_datetime</code> and <code>current_getdate</code> field-level data sources to, for example, return items published in the current month. Code something like:
<br />&nbsp;<br />
<code>[mla_gallery post_parent=all archive_parameter_name=muie_current_archive muie_current_archive="custom:Publication Date,M({+current_datetime,date( 'Ym' )+})"]
</code>
<br />&nbsp;<br />
By adjusting the format code you can specify any of the four archive types: daily 'Ymd', weekly 'YW', monthly 'Ym', yearly 'Y'.
</p>
<h4>Filtered Archive List and MLA Gallery combinations</h4>
<p>
As described in the "Archive List Data Selection Parameters" section above you can filter the archive list to show the dates present in a subset of your Media Library items. For example, here is a taxonomy term-specific variation on the archive search form:
<br />&nbsp;<br />
<code>
&lt;h3&gt;Archive Search Form for Att. Category ABC&lt;/h3&gt;<br />
&lt;form id="archive-search-form" action="." method="post"&gt;<br />
[muie_archive_list <strong>attachment_category=abc</strong> archive_type=monthly option_all_label="Select Uploaded on date"]<br />
&lt;input id="search-form-submit" name="search_form_submit" type="submit" value="Filter"&gt;<br />
&lt;/form&gt;<br />
&lt;h3&gt;Gallery for Att. Category ABC&lt;/h3&gt;<br />
[mla_gallery <strong>attachment_category=abc</strong> archive_parameter_name=muie_current_archive]
</code>
<br />&nbsp;<br />
This example will initially show all items assigned to <code>attachment_category=abc</code> and the dropdown control will show only those dates that have one or more items assigned to the term. Selecting an archive value will combine the term filter and the date filter for the gallery display.
</p>
<h4>Combining an Archive List with other criteria</h4>
<p>
Finally, here is an example that shows how you can combine <code>[muie_archive_list]</code> with the other MUIE elements to create a powerful multi-criteria search application. In this example the <code>tax_input={+template:({+request:tax_input,array+})+}</code> parameter added to the <code>[muie_archive_list]</code> shortcode links the archive list to whatever taxonomy term is selected in the <code>[mla_term_list]</code> shortcode:
<br />&nbsp;<br />
<code>
&lt;form id="mla-text-form" action="." method="post" class="row"&gt;<br />
&lt;strong&gt;Att. Categories&lt;/strong&gt;<br />
[mla_term_list taxonomy=attachment_category post_mime_type=image number=15 mla_output=dropdown mla_option_value="{+slug+}" use_filters=true show_count=true pad_counts=false option_all_text="Select Att. Category"]<br />
<br />
&lt;strong&gt;Archive Search&lt;/strong&gt;<br />
[muie_archive_list tax_input={+template:({+request:tax_input,array+})+} archive_type=weekly archive_source=custom archive_key=CreateDate xarchive_output=list xlink=span option_all_label="Select Archive" archive_limit=10 muie_debug=log mla_debug=true]<br />
<br />
&lt;strong&gt;Keyword Search&lt;/strong&gt;<br />
[muie_keyword_search mla_search_connector='OR']<br />
<br />
Items per page: [muie_per_page numberposts=3]<br />
<br />
&lt;input id="text-form-submit" name="text_form_submit" type="submit" value="Filter"&gt;<br />
&lt;/form&gt;<br />
<br />
&lt;h3&gt;Gallery&lt;/h3&gt;<br />
[mla_gallery post_parent=all order<br />by=date posts_per_page=20 add_filters_to=any archive_parameter_name=muie_current_archive mla_output="paginate_links,prev_next"]<br />
<br />
[mla_gallery post_parent=all orderby=date posts_per_page=20 add_filters_to=any archive_parameter_name=muie_current_archive mla_nolink_text="Enter a term above to see a gallery of items that match."]
</code>
<br />&nbsp;<br />
The above example can be adapted to a variety of application scenarios.
<a name="debugging"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Debugging and Troubleshooting</h3>
<p>
If you are not getting the results you expect from "use_filters" and "add_filters_to" carefully inspecting the results of parsing the specification and generating the query can be a valuable exercise. You can add the <code>muie_debug=true</code> or <code>muie_debug=log</code> parameters to the <code>[mla_gallery]</code> or <code>[mla_term_list]</code> shortcode, run a test and inspect the log file or the screen messages for more information about what's going on.
</p>
<p>
You can also add the <code>mla_debug=true</code> or <code>mla_debug=log</code> parameters to the <code>[mla_gallery]</code> shortcode for even more debug information. If you do this, make sure that both <code>muie_debug</code> and <code>mla_debug</code> are set to the same value, i.e., both "true" or both "log".
</p>
<p>
Most of the shortcodes added by this example plugin are very straightforward and they do not generate debug log entries. The <code>[muie_archive_list]</code> shortcode can be much more complex. For this shortcode adding &ldquo;0x8000&rdquo; to the MLA Reporting value will generate useful information.
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