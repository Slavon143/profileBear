<!-- template="page" -->
<a name="backtotop"></a>
&nbsp;
<div class="wrap">
<h1 class="wp-heading-inline">MLA USP Novo-Map Example [+version+] Settings</h1>
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
    <input name="mla_usp_novo_map_options[process_usp_posts]" id="mla_usp_novo_map_process_usp_posts" type="checkbox" [+process_usp_posts_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Process User Submitted Posts</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to process the Content Templates, Term Assignments and Featured Images for User Submitted Posts.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="mla_usp_novo_map_options[assign_usp_categories]" id="mla_usp_novo_map_assign_usp_categories" type="checkbox" [+assign_usp_categories_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Assign USP Post Categories</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to assign categories to the USP Post.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>USP Post Category Slug(s)</strong>
  </td>
  <td>
    <textarea rows="[+usp_categories_rows+]" cols="[+usp_categories_cols+]" name="mla_usp_novo_map_options[usp_category_slugs]" id="mla_usp_novo_map_usp_category_slugs">[+usp_category_slugs+]</textarea>
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one or more Category <strong>slug</strong> values, separated by commas.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="mla_usp_novo_map_options[assign_usp_tags]" id="mla_usp_novo_map_assign_usp_tags" type="checkbox" [+assign_usp_tags_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Assign USP Post Tags</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to assign tags to the USP Post.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>USP Post Tag Slug(s)</strong>
  </td>
  <td>
    <textarea rows="[+usp_tags_rows+]" cols="[+usp_tags_cols+]" name="mla_usp_novo_map_options[usp_tag_slugs]" id="mla_usp_novo_map_usp_tag_slugs">[+usp_tag_slugs+]</textarea>
    <div class="mla-settings-help">&nbsp;&nbsp;Enter one or more Post Tag <strong>slug</strong> values, separated by commas.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="mla_usp_novo_map_options[create_usp_title]" id="mla_usp_novo_map_create_usp_title" type="checkbox" [+create_usp_title_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Populate the USP Post Title</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to replace the Title value of the USP Post.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>USP Post Title Template</strong>
  </td>
  <td>
    <textarea rows="[+usp_title_rows+]" cols="[+usp_title_cols+]" name="mla_usp_novo_map_options[usp_title_template]" id="mla_usp_novo_map_title_template">[+usp_title_template+]</textarea>
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the Content Template for the Post Title (without the enclosing [+template: ... +] delimiters.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="mla_usp_novo_map_options[create_usp_excerpt]" id="mla_usp_novo_map_create_usp_excerpt" type="checkbox" [+create_usp_excerpt_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Populate the USP Post Excerpt</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to replace the Excerpt value of the USP Post.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>USP Post Excerpt Template</strong>
  </td>
  <td>
    <textarea rows="[+usp_excerpt_rows+]" cols="[+usp_excerpt_cols+]" name="mla_usp_novo_map_options[usp_excerpt_template]" id="mla_usp_novo_map_usp_excerpt_template">[+usp_excerpt_template+]</textarea>
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the Content Template for the Post Excerpt (without the enclosing [+template: ... +] delimiters.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="mla_usp_novo_map_options[create_usp_content]" id="mla_usp_novo_map_create_usp_content" type="checkbox" [+create_usp_content_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Populate the USP Post Content</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to replace the Content value of the USP Post.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>USP Post Content Template</strong>
  </td>
  <td>
    <textarea rows="[+usp_content_rows+]" cols="[+usp_content_cols+]" name="mla_usp_novo_map_options[usp_content_template]" id="mla_usp_novo_map_usp_content_template">[+usp_content_template+]</textarea>
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the Content Template for the Post Content (without the enclosing [+template: ... +] delimiters.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="mla_usp_novo_map_options[create_novo_map_marker]" id="mla_usp_novo_map_create_novo_map_marker" type="checkbox" [+create_novo_map_marker_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Create Novo Map Marker</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to automatically create the Novo Map marker.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Novo Map Infobox Template</strong>
  </td>
  <td>
    <textarea rows="[+novo_map_infobox_rows+]" cols="[+novo_map_infobox_cols+]" name="mla_usp_novo_map_options[novo_map_infobox_template]" id="mla_usp_novo_map_novo_map_infobox_template">[+novo_map_infobox_template+]</textarea>
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the Content Template for the Novo Map Infobox (without the enclosing [+template: ... +] delimiters.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Select a marker image</strong>
  </td>
  <td>
    <textarea rows="[+usp_marker_rows+]" cols="[+usp_marker_cols+]" name="mla_usp_novo_map_options[usp_marker_index]" id="mla_usp_novo_map_marker_index">[+usp_marker_index+]</textarea>
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the number (1 - 9) of the default marker image.</div>
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
<p>In this tab you can activate the processing of User Submitted Posts and decide which elements of the new posts will be populated from information in the Featured Image uploaded to the post.</p>
<p>You can find more information about using the features of this plugin in the Documentation tab on this screen.</p>
<div class="mla-page-level-options-form">
	<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-path-mapping-tab">
		<table class="optiontable">
		<tbody>
			[+options_list+]
		</tbody>
		</table>
		<span class="submit mla-settings-submit">
		<input name="mla-path-mapping-options-save" class="button-primary" id="mla-path-mapping-options-save" type="submit" value="Save Changes" />
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
<li><a href="#process-usp"><strong>Process User Submitted Posts</strong></a></li>
<li><a href="#assign-categories"><strong>Assign USP Post Categories</strong></a></li>
<li><a href="#assign-tags"><strong>Assign USP Post Tags</strong></a></li>
<li><a href="#populate-title"><strong>Populate the USP Post Title</strong></a></li>
<li><a href="#populate-excerpt"><strong>Populate the USP Post Excerpt</strong></a></li>
<li><a href="#populate-content"><strong>Populate the USP Post Content</strong></a></li>
<li><a href="#generate-marker"><strong>Create Novo Map Marker</strong></a></li>
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
This plugin enhances any application that combines two WordPress plugins to produce annotated maps:
</p>
<ul class="mla-doc-toc-list">
<li><a href="https://wordpress.org/plugins/novo-map/" title="Plugin Repository page" target="_blank">Novo-Map : your WP posts on custom google maps</a></li>
<li><a href="https://wordpress.org/plugins/user-submitted-posts/" title="Plugin Repository page" target="_blank">User Submitted Posts (USP)</a></li>
</ul>
<p>
The plugin includes features to automatically add content to the USP posts and to create a Novo-Map marker with the location of an uploaded image attached as the post's Featured Image. To use the plugin:
</p>
<ul class="mla-doc-toc-list">
<li>Create a WordPress page that includes the <code>[user-submitted-posts]</code> shortcode,</li>
<li>Set the "Post Status" field to "Pending" so posts must be reviewed before publication,</li>
<li>Display the "Post Images" form field and check the "Featured Image" box in the USP "Image Uploads" section,</li>
<li>Configure the options for this plugin to add content, assign terms and create a Novo-Map marker when the post is reviewed for publication.</li>
</ul>
<p>
The sections below give you more details about each of the plugin options in the General tab.
<a name="process-usp"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Process User Submitted Posts</h3>
<p>
This is the "master on/off switch" for the plugin. When the box is unchecked the plugin does not perform any actions. When the box is checked the plugin will use hooks (actions and filters) provided by the User Submitted Posts plugin to populate the new post with information extracted from the Featured Image uploaded to the post. You can also generate a Novo Map "Marker" entry based on location information contained in the Featured Image.
<a name="assign-categories"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Assign USP Post Categories</h3>
<p>
You can assign terms in the WordPress Categories taxonomy be entering one or more <strong>slug</strong> values in the text box, separated by commas. Slug values can be found in the Posts/Categories admin submenu table. Any term values entered here will be appended to terms already assigned to the Post.
<a name="assign-tags"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Assign USP Post Tags</h3>
<p>
You can assign terms in the WordPress Tags taxonomy be entering one or more <strong>slug</strong> values in the text box, separated by commas. Slug values can be found in the Posts/Tags admin submenu table. Any term values entered here will be appended to terms already assigned to the Post.
<a name="populate-title"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Populate the USP Post Title</h3>
<p>
You can update the Title be entering a Content Template in the text box (without the <code>template:</code> prefix). You can access the existing Post values with the <code>usp:</code> prefix, e.g., <code>[+usp:post_title+]</code> to access the current Title value. See the "Content" section below for additional information.
<a name="populate-excerpt"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Populate the USP Post Excerpt</h3>
<p>
You can update the Excerpt by entering a Content Template in the text box (without the <code>template:</code> prefix). You can access the existing Post values with the <code>usp:</code> prefix, e.g., <code>[+usp:post_excerpt+]</code> to access the current Excerpt value. See the "Content" section below for additional information.
</p>
<a name="populate-content"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Populate the USP Post Content</h3>
<p>
<p>
You can update the Content by entering a Content Template in the text box (without the <code>template:</code> prefix). You can access the existing Post values with the <code>usp:</code> prefix, e.g., <code>[+usp:post_content+]</code> to access the current Content value. The <code>usp:</code> prefix gives you access to all of the Post's current values:
</p>
<ul class="mla-doc-toc-list">
<li>ID</li>
<li>post_author</li>
<li>post_date</li>
<li>post_date_gmt</li>
<li>post_content</li>
<li>post_title</li>
<li>post_excerpt</li>
<li>post_status</li>
<li>comment_status</li>
<li>ping_status</li>
<li>post_name</li>
<li>post_modified</li>
<li>post_modified_gmt</li>
<li>post_content_filtered</li>
<li>post_parent</li>
<li>guid</li>
<li>menu_order</li>
<li>post_mime_type</li>
<li>comment_count</li>
</ul>
<p>
You can access any of the "Field-level data sources" for the Post's Featured Image as well. A complete list can be found in the Settings/Media Library Assistant Documenttation tab.
</p>
<p>
You can access the terms assigned to the Post with the <code>usp_terms:</code> prefix, e.g., <code>[+usp_terms:post_tag+]</code> or <code>[+usp_terms:category+]</code> to access the current Tags or Categories.
<a name="generate-marker"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Create Novo Map Marker</h3>
<p>
Check this box to generate a Novo Map "Marker" entry based on location information contained in the Featured Image:
</p>
<ul class="mla-doc-toc-list">
<li>The "Title of the Marker" will be filled from the Title of the User Submitted Post.</li>
<li>The Latitude will be filled from the <code>[+exif:GPS.LatitudeSDD+]</code> value in the Featured Image, if present.</li>
<li>The Longitude will be filled from the <code>[+exif:GPS.LongitudeSDD+]</code> value in the Featured Image, if present.</li>
<li>The Text description of the Infobox will be filled from the Novo Map Infobox Template, applied to the Featured Image.</li>
</ul>
<p>
The marker will be generated and added to the Novo Map dtabase. You can update or delete the marker by editing the new post and scrolling down to the Novo Map infobox.
</p>
<p>
In the "Select a marker image" text box you can choose from the nine default marker images provided by Novo Map. The markers are numbered 1 to 9, from left to right when viewed in the "Select" popup window in the Novo Map metabox on the Edit Media screen. For example, the red bulls-eye is number 1, the green "guitar pick" is number two, and the "push pin" is number 8. Enter the number of the marker you want in the text box.
</p>
</div>