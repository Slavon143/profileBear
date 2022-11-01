<!-- template="page" -->
<a name="backtotop"></a>
&nbsp;
<div class="wrap">
<h1 class="wp-heading-inline">MLA Random Galleries Example [+version+] Settings</h1>
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
    <strong>Random Taxonomy</strong>
  </td>
  <td>
    <input name="mla_random_galleries_options[random_taxonomy]" id="mla_random_galleries_random_taxonomy" type="text" size="40" value="[+random_taxonomy+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the slug of the taxonomy to select terms from. Must be present.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Random Term List</strong>
  </td>
  <td>
    <input name="mla_random_galleries_options[random_term_list]" id="mla_random_galleries_random_term_list" type="text" size="80" value="[+random_term_list+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the complete comma-delimited list of terms from which to select items for display.<br />&nbsp;&nbsp;Leave the box empty to make all terms eligible for selection/display.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="mla_random_galleries_options[verify_post_attributes]" id="mla_random_galleries_verify_post_attributes" type="checkbox" [+verify_post_attributes_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Verify Post Attributes</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to enforce <code>post_type=attachment</code>, <code>post_status=inherit</code> and <code>post_mime_type=image</code> restrictions.<br />&nbsp;&nbsp;These require an additional database query, and can impact performance.<br />&nbsp;&nbsp;You can overide any of these parameters with shortcode parameters.</div>
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
<p>In this tab you can specify the default taxonomy from which to select terms and the list of all terms eligible for processing.</p>
<p>You can find more information about using the features of this plugin in the Documentation tab on this page.</p>
<div class="mla-page-level-options-form">
	<form action="[+form_url+]" method="post" class="mla-display-settings-page" id="mla-display-settings-random-galleries-tab">
		<table class="optiontable">
		<tbody>
			[+options_list+]
		</tbody>
		</table>
		<span class="submit mla-settings-submit">
		<input name="mla-random-galleries-options-save" class="button-primary" id="mla-random-galleries-options-save" type="submit" value="Save Changes" />
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
<li><a href="#parameters"><strong>Shortcode Parameters</strong></a></li>
<li><a href="#random_taxonomy"><strong>Specifying a Random Taxonomy</strong></a></li>
<li><a href="#random_term_list"><strong>Specifying a Random Term List</strong></a></li>
<li><a href="#verify_post_attributes"><strong>Database Query - Verify Post Attributes</strong></a></li>
<li><a href="#random_term"><strong>Specifying the Random Term(s)</strong></a></li>
<li><a href="#posts_per_term"><strong>Limiting Items per Term</strong></a></li>
<li><a href="#shuffle_gallery"><strong>Ordering the Items - Shuffle Gallery</strong></a></li>
<li><a href="#posts_per_page"><strong>Limiting Total Gallery Items</strong></a></li>
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
For many sites that divide Media Library items into categories or group items with tags, some sort of overview or portfolio page displays a random sampling of items assigned to one or more category/tag terms. The MLA Random Galleries Example plugin provides a convenient way to organize these displays and a much more efficient database query technique to assemble the items for display. The example plugin was developed in response to these MLA support topics:
</p>
<ul class="mla-doc-toc-list">
<li><a href="https://wordpress.org/support/topic/multiple-calls-to-a-smaller-amount/" title="View the topic" target="_blank">multiple calls to a smaller amount</a></li>
<li><a href="https://wordpress.org/support/topic/gallery-incl-control-break/" title="View the topic" target="_blank">Gallery incl control break</a></li>
</ul>
<p>
To use the plugin you must specify one taxonomy from which to select terms and a list of terms to draw from. This can be done by setting plugin options or adding parameters to an <code>[mla_gallery]</code> shortcode. Then, add a <code>random_term</code> parameter to an <code>[mla_gallery]</code> shortcode to choose one or more terms to filter the items for a gallery display. Often you will have several such shortcodes on a given post/page to show a sampling of items from different parts of the site.
</p>
<p>
More details on each option setting and shortcode parameter are in the sections of this Documentation page.
<a name="processing"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>How the Plugin Works</h3>
<p>
The plugin works by detecting the presence of its parameters in one or more <code>[mla_gallery]</code> shortcodes on a post/page. When the parameter(s) are present, two tasks are performed:</p>
<ul class="mla-doc-toc-list">
<li><strong>Generate the random galleries</strong> - This is usually done once per page load, for performance reasons. It happens when the first shortcode containing the parameters is processed. If you add taxnonmy or term list parameters to a subsequent shortcode, the random galleries are regenerated with the new parameter values.</li>
<li><strong>Populate the random gallery</strong> - This happens once for each shortcode that contains the plugin parameters. It uses the results of the first task to select items for display.</li>
</ul>
<p>
The first task executes just one or two database queries to fetch all of the items assigned to the term(s) in the random term list. First, if the random term list is empty, an optional query is executed to fetch all of the terms in the taxonomy to populate a default list. The primary query in this task: 1) converts the slug values to term_id values using the wp_terms table, 2) converts the term_id values to taxonomy-specific term_taxonomy_id values using the wp_term_taxonomy table and then 3) uses the term_taxonomy_id values to fetch the term assignments ( item ID values) from the wp_term_relationship table. This is much more efficient than the normal WordPress queries, which require a separate query for each term and accesses the wp_posts table as well.
</p>
<p>
If the Verify Post Attributes option is active the primary query is a bit different. In the third step of the query the wp_term_relationship table is joined to the wp_posts table so the post_type, post_status and post_mime_type restrictions are enforced. This step is not necessary if the random taxonomy is used only to assign terms to Media Library image items. That&rsquo;s normally the case when the Att. Categories or Att. Tags taxonomies are used. 
</p>
<p>
The final step of the first task builds an in-memory table of all the items assigned to each term in the random term list. For each term the items are stored in random order, and the order will change on each page load or each time the table is regenerated.
</p>
<p>
The second task is performed for each shortcode on the post/page that contains the plugin&rsquo;s parameters. The centerpiece of this task is the <code>random_term</code> parameter that names the term(s) used to select items for the gallery display. For each term in the parameter, the in-memory table is accessed to find the items assigned to that term. Once all of the terms named in the random term parameter have been processed the order of the consolidated list of items is again randomized, unless <code>shuffle_gallery</code> is false. The selected items are passed back to the <code>[mla_gallery]</code> shortcode as an <code>ids=</code> list to generate the gallery display.
</p>
<p>
The <code>posts_per_term</code> or <code>numberposts</code> parameter can be used to limit the total number of items displayed, but these are processed in the <code>[mla_gallery]</code> shortcode, not in this plugin.
<a name="parameters"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Shortcode Parameters</h3>
<p>
You can use shortcode parameters to control all aspects of random gallery processing. For convenience, you can also set default values for the first three parameters in the list in the General tab on this Settings page. Each of the parameters in the list below will be covered in more detail in the Documentation sections following this summary.
</p>
<ul class="mla-doc-toc-list">
<li><code>random_taxonomy</code> - Gives the slug for the taxonomy in which the &ldquo;random terms&rdquo; are defined. If not supplied, a plugin option gives a default value.</li>
<li><code>random_term_list</code> - A comma-separated list of <strong>all</strong> the term names or slugs that will be used to select items for display in all of the shortcodes on the post/page. If not supplied, a plugin option gives a default value. If the parameter is present but empty, all terms in the random taxonomy are processed.</li>
<li><code>verify_post_attributes</code> - A boolean value (default false) that, when true, adds <code>post_type</code>, <code>post_status</code> and <code>post_mime_type</code> tests to the selection of items for display. This extra step can cause slower performance, but is useful if the taxonomy assignments include posts, pages and non-image items.</li>
<li><code>random_term</code> - A comma-separated list of the term names or slugs that will be used to select items for display by the current shortcode. If the parameter is present but empty, all terms in the random term list are displayed. If the parameter is missing the gallery will be empty.</li>
<li><code>shuffle_gallery</code> - A boolean value (default true) that, when false, disables the final re-ordering of selected items in all terms for the gallery display. The items are grouped by term value and displayed in the term order of the random terms list.</li>
</ul>
<p>
The first three parameters are processed when the first shortcode containing random gallery parameters is encountered. The results are stored for use in subsequent random gallery shortcodes on the post/page. If a subsequent shortcode contains a different value for the taxonomy or term list, the parameters are processed again for the new combination of values. See the Examples section for, well, an example.
<p>
Any additional shortcode parameters are simply passed along to the <code>[mla_gallery]</code> logic that composes the gallery display. When <code>verify_post_attributes=true</code>, three additional parameters are used by this plugin to ensure that all of the items selected will be displayed in the gallery:
</p>
<ul class="mla-doc-toc-list">
<li><code>post_type</code> - Names the post type(s) that filter the gallery display. The default value, "attachment" is compatible with <code>[mla_gallery]</code>.</li>
<li><code>post_status</code> - Names the post status value(s) that filter the gallery display. The default value, "inherit" is compatible with <code>[mla_gallery]</code>.</li>
<li><code>post_mime_type</code> - Names the MIME type(s) that filter the gallery display. The default value, "image" is compatible with <code>[mla_gallery]</code>. You can specify "all" to remove this filter criterion.</li>
</ul>
<p>
&nbsp;
<a name="random_taxonomy"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Specifying a Random Taxonomy</h3>
<p>
You must specify exactly one taxonomy from which to select terms for your random galleries, either as a plugin option setting or as a parameter of the first shortcode in which random gallery parameters are coded.  This allows the plugin to perform one set of efficient database queries for all of the terms in the random term list. Subsequent shortcodes on the post/page can display items from other terms in the list without additional database activity. The value must be a taxonomy slug, e.g., <code>attachment_category</code>, not the name, e.g., &ldquo;Att. Categories&rdquo;. The default value is <code>attachment_category</code>.
</p>
<p>
You can change the random taxonomy by coding the parameter in one of the subsequent shortcodes on the post/page. This causes a fresh set of database queries to be executed, giving a new collection of terms and item assignments. If you change the taxonomy you will also want to change the random term list, unless you are using an empty list to use all of the terms in the taxonomy.
</p>
<p>
It is best to use a taxonomy that assigns terms only to Media Library, or &ldquo;attachment&rdquo; items. If you use a taxonomy such as the WordPress <code>category</code> or <code>post_tag</code> taxonomy and assign terms to posts and pages as well as attachments you can activate <code>verify_post_attributes</code>. This adds a check to filter out posts, pages and non-image MIME types but can significantly slow down the queries required to generate the random term assignments. If you do not add the check your gallery display will probably contain fewer items than you intended.
</p>
<p>
It is also better if the terms in your random term list are assigned only to image items, not to other items such as PDF documents. If you want to include non-image items in your display be sure to add an appropriate <code>post_mime_type</code> parameter to your shortcode. Otherwise your gallery display will probably contain fewer items than you intended.
</p>
<p>
Finally, if an item is assigned to more than one term in your random term list there is a chance it will displace some other item, resulting in fewer items than you expect. This is less likely if all of the terms in your list contain more items (per term) than you want to display.
<a name="random_term_list"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Specifying a Random Term List</h3>
<p>
The random term list is the master list of all terms that are used for random gallery display on a given post/page. You can set a defulat list as a plugin option and overide it by coding a shortcode parameter. The list is processed when the first shortcode containing random gallery parameters is processed to find all items assigned to each of the terms in the list. You can have subsequent shortcodes on the post/page using different terms from the list to organize the galleries, but they all must use one or more terms from the master list.
</p>
<p>
Typically you will set the term list once, in the General tab on this Settings page. If you want different lists on different posts/pages, specify the most common list as an option setting and then use a shortcode parameter for the less common list(s). 
</p>
<p>
You can change the random term list by coding the parameter in one of the subsequent shortcodes on the post/page. This causes a fresh set of database queries to be executed, giving a new collection of terms and item assignments.
</p>
<p>
If your random taxonomy contains a modest number of terms you can leave the random term list empty. This will cause the plugin to generate a list of all taxonomy terms and use that to find item assignments. Processing all taxonomy terms is convenient but can take a lot of time and memory if your random taxonomy has dozens or hundreds of terms.
<a name="verify_post_attributes"></a>
&nbsp;
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3><strong>Database Query - Verify Post Attributes</strong></h3>
<p>
One of the main advantages this plugin provides is a performace gain when multiple random terms and/or random galleries appear on a given post/page. The plugin queries the database once each page load to process all of the terms in the random term list. The default queries do not access the <code>wp_posts</code> table at all, which avoids a lot of time and memory consumption. 
</p>
<p>
These shortcuts work best when two assumptions are true: 1) only attachment items are assigned to terms in the random taxonomy, and 2) the items assigned to terms in the random term list all have one of the image MIME types. If either assumption is false, some of the items selected by this plugin will be filtered out of the final display. You can relax the &ldquo;image MIME type&rdquo; assumption by adding <code>post_mime_type=all</code> to your shortcode(s). 
</p>
<p>
If you want to exclude posts, pages and other post types from this plugin's processing you can activate the &ldquo;Verify Post Attributes&rdquo; action by checking the option setting box or by adding <code>verify_post_attributes=true</code> to the shortcode that processes the random term list. This action will add the <code>wp_posts</code> table to the database query that selects the items.
<a name="random_term"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Specifying the Random Term(s)</h3>
<p>
For each random gallery shortcode on the post/page you must specify one or more terms from which to select the items for display. There is no default for this parameter and if it is missing your random gallery will be empty. Each term name or slug value you add to this parameter must also appear in the random (master) term list.
</p>
<p>
If this parameter is present, but empty, e.g., <code>random_term=''</code>, all of the terms in the random term list will be used to select items for display. 
<a name="posts_per_term"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Limiting Items per Term</h3>
<p>
You can set an upper bound for the number of items selected from each of the terms in the <code>random_term</code> parameter. For example, to select just one item for each term, code <code>posts_per_term=1</code>.
</p>
<p>
Within each term the order of assigned items is randomized. If you display only some of the assigned items the selection drawn from a given term will change each time the post/page is loaded.
<a name="shuffle_gallery"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3><strong>Ordering the Items - Shuffle Gallery</strong></h3>
<p>
Within each random gallery the order of items assigned to a given term is always randomized. In addition, the final order of displayed items across all term values is randomized unless you add a <code>shuffle_gallery=false</code> parameter to the shortcode.
</p>
<p>
When <code>shuffle_gallery=false</code>, The final order of items assigned to different terms is determined in one of three ways:.
</p>
<ol class="mla-doc-toc-list">
<li>If the <code>random_term</code> parameter includes two or more term names, items are displayed in the order of the terms in the list.</li>
<li>If the <code>random_term</code> parameter is empty, items are displayed in the order of the terms specified in the current random term list.</li>
<li>If the <code>random_term_list</code> is empty, items are displayed in the order of the term_taxonomy_id values for the random taxonomy.</li>
</ol>
<p>
Of course, you can add <code>orderby</code> and/or <code>orderby</code> parameters to the shortcode. These will overide the order supplied by this plugin with the sorting supplied by the <code>[mla_gallery]</code> shortcode itself.
<a name="posts_per_page"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3><strong>Limiting Total Gallery Items</strong></h3>
<p>
You can set an upper bound for the total number of items displayed in the random gallery. For example, to select six items in the gallery, code <code>posts_per_page=6</code> or <code>numberposts=6</code>. Either parameter is passed along with the list of items generated by this plugin and applied when the gallery display is generated by <code>[mla_gallery]</code>.
</p>
<p>
Within each random gallery the final order of displayed items is randomized (unless <code>shuffle_gallery</code> is false). If you display only some of the items the display will change each time the post/page is loaded.
<a name="examples"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Examples</h3>
<p>
Once the plugin is installed and activated you can visit the General tab on this Settings page to specify the taxonomy and term(s) you want to use as defaults for the random gallery generation. For the examples in this section the following default values are assumed:
</p>
<ul class="mla-doc-toc-list">
<li>
<strong>Random Taxonomy</strong> - attachment_category
</li>
<li>
<strong>Random Term List</strong> - &ldquo;Category One,second-category,Final Category&rdquo;
</li>
<li>
<strong>Verify Post Attributes</strong> - unchecked (false)
</li>
</ul>
<p>
It's a little unusual to mix term names (Category One, Final Category) with term slugs (second-category) but the plugin will accept either format. Our first example uses the default settings to display three random galleries on one page:
</p>
<blockquote><code>
&lt;h3&gt;Category One Sample&lt;/h3&gt;<br />
[mla_gallery random_term=category-one posts_per_page=3]<br />
&lt;h3&gt;Second Category Sample&lt;/h3&gt;<br />
[mla_gallery random_term="Second Category" posts_per_page=3]<br />
&lt;h3&gt;Final Category Sample&lt;/h3&gt;<br />
[mla_gallery random_term=final-category posts_per_page=3]
</code></blockquote>
<p>
When the first shortcode is encountered the default random taxonomy and random term list are used to find all items assigned to the specified terms. Then, the first shortcode displays three random items assigned to Category One. The next shortcode displays three items from the Second Category and the final shortcode displays three items from the Final Category. Again, the choice to use term names or slugs is up to you, and a mix is shown here.
</p>
<p>
Our second example shows how to overide the default random term list and how to limit the number of items selected from each term:</p>
<blockquote><code>
&lt;h3&gt;Category One Sample&lt;/h3&gt;<br />
[mla_gallery random_term_list=category-one,second-category,third-category random_term=category-one posts_per_page=3]<br />
&lt;h3&gt;Second and Third Category Samples&lt;/h3&gt;<br />
[mla_gallery random_term=second-category,third-category posts_per_term=1]<br />
</code></blockquote>
<p>
When the first shortcode is encountered the default random taxonomy and <code>random_term_list</code> parameter are used to find all items assigned to the specified terms. Then, the first shortcode displays three random items assigned to Category One. The second shortcode displays one item from the Second Category and one item from the Third Category.
</p>
<p>
Our third example shows how to easily display a sample item from each of the terms in a taxonomy. Here, the <code>random_term_list</code> parameter is present but empty. This causes the plugin to use <strong>all</strong> of the terms in the random taxonomy to populate the list. The <code>random_term</code> parameter is also present but empty. This causes the plugin to use <strong>all</strong> of the terms in the random term list to populate the gallery:</p>
<blockquote><code>
&lt;h3&gt;Portfolio Samples&lt;/h3&gt;<br />
[mla_gallery posts_per_term=1]<br />
random_term_list=''<br />
random_term=''<br />
[/mla_gallery]
</code></blockquote>
<p>
This example uses the &ldquo;alternate enclosing shortcode&rdquo; format to separate each parameter on its own line for readability. You can mix the enclosing syntax with the standard syntax (the <code>posts_per_term</code> parameter) in the same shortcode. Note that if you have multiple shortcodes on the post/page and use the enclosing syntax you must use it for <strong>all</strong> of the shortcodes adding the closing <code>[/mla_gallery]</code> to each shortcode.
</p>
<p>
Our final example shows a less common use case:</p>
<blockquote><code>
&lt;h3&gt;Default taxonomy and list parameters&lt;/h3&gt;<br />
[mla_gallery random_term=category-one posts_per_page=3]<br />
&lt;h3&gt;New taxonomy and list parameters&lt;/h3&gt;<br />
[mla_gallery random_taxonomy=attachment_tag random_term_list='' random_term=third-tag posts_per_term=2]<br />
&lt;h3&gt;New term, existing taxonomy and list parameters&lt;/h3&gt;<br />
[mla_gallery random_term=fourth-tag posts_per_term=2]
</code></blockquote>
<p>
When the first shortcode is encountered the default random taxonomy and <code>random_term_list</code> parameter are used to find all items assigned to the specified terms in the <code>attachment_category</code> taxonomy. Then, the first shortcode displays three random items assigned to Category One. The second shortcode uses a different taxonomy and it changes the random term list to use all terms in the new taxonomy. The final shortcode displays another term from the attachment_tag term list.
</p>
<p>
Again, the current version of the plugin generates the random term list and galleries when the first shortcode is processed. Subsequent shortcodes must draw items from the random term list generated by the first shortcode unless they specify a new taxonomy and (probably) a new random term list. When either of these parameters changes, the plugin regenerates the random term list and galleries.
</p>
</div>