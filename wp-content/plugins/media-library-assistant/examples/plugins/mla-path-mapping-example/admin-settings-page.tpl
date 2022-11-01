<!-- template="page" -->
<a name="backtotop"></a>
&nbsp;
<div class="wrap">
<h1 class="wp-heading-inline">MLA Path Mapping Example [+version+] Settings</h1>
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
    <input name="mla_path_mapping_options[assign_parents]" id="mla_path_mapping_assign_parents" type="checkbox" [+assign_parents_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Assign parent terms</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to assign all terms in path, not just the last (leaf) term.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <input name="mla_path_mapping_options[assign_rule_parent]" id="mla_path_mapping_assign_rule_parent" type="checkbox" [+assign_rule_parent_checked+] value="1" />
  </td>
  <td>
    &nbsp;<strong>Assign Rule Parent term</strong>
    <div class="mla-settings-help">&nbsp;&nbsp;Check this option to assign the Rule Parent (if any) in addition to terms in path.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Path Delimiter</strong>
  </td>
  <td>
    <input name="mla_path_mapping_options[path_delimiter]" id="mla_path_mapping_path_delimiter" type="text" size="1" maxlength="1"value="[+path_delimiter+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the delimiter that separates path components.</div>
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
<p>In this tab you can define the path delimiter and parent-term handling options for mapping terms in hierarchical taxonomies.</p>
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
<li><a href="#assigning-ids"><strong>Assigning Term ID Values</strong></a></li>
<li><a href="#names-vs-slugs"><strong>Term Names Vs Slugs</strong></a></li>
<li><a href="#flat-taxonomies"><strong>Path Specifications for Flat Taxonomies</strong></a></li>
<li><a href="#matching"><strong>Matching on Term Name</strong></a></li>
<li><a href="#creating"><strong>Creating New Terms</strong></a></li>
<li><a href="#multi-level"><strong>Assigning Multi-level Terms</strong></a></li>
<li><a href="#mapping-multiple"><strong>Mapping Multiple Items</strong></a></li>
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
The current MLA version provides mapping rules to assign taxonomy terms to Media Library items from IPTC, EXIF and XMP metadata values embedded in the image files. For hierarchical taxonomies, the current version allows new terms to be added under a specified &ldquo;Parent&rdquo; term, but does not support more general mapping of terms within multiple levels.
</p>
<p>
The current version allows the specification of &ldquo;Delimiters&rdquo; (including a space character) to separate multiple terms within a metadata value. This example plugin adds a &ldquo;Path Delimiter&rdquo; that allows term parent and higher-level ancestor values to be specified, more precisely placing a term in the hierarchy. For example, a value such as &ldquo;/grand parent/parent/child&rdquo; denotes a specific term within a three-level hierarchy. In this example the delimiter at the start of the value means that &ldquo;grand parent&rdquo; must be a root term, i.e., it appears at the highest level and has no ancestors. This is an absolute path specification. A path such as &ldquo;parent/child&rdquo; is a relative path specification, starting wherever &ldquo;parent&rdquo; appears in the hierarchy.
<a name="assigning-ids"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Assigning Term ID Values</h3>
<p>
Because a given Term Name can exist in multiple taxonomies and in more than one place in a given hierarchical taxonomy, Term ID is an important part of organizing and finding terms within a taxonomy. Early versions of WordPress supported &ldquo;shared terms&rdquo;, which required a separate Term-taxonomy ID to uniquely distinguish among multiple occurrences of the same Term Name; see the Appendix for more information. Shared terms no longer occur in more recent WordPress versions (4.3+) so Term ID is now unique.
</p>
<p>
Term ID values start at 1 (assigned to the WordPress &ldquo;Uncategorized&rdquo; category) and are incremented each time a new term is created. They are never reused, so if terms are deleted there will be gaps in the remaining ID values.
</p>
<p>
If the same Term Name is used more than once, how do you decide which term is specified by an unqualified Term Name? WordPress matches an unqualified Term Name to the term with the lowest Term ID, i.e., the &ldquo;oldest&rdquo; term defined with that name. See the &ldquo;Matching on Term Name&rdquo; section for more information. 
<a name="names-vs-slugs"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Term Names Vs Slugs</h3>
<p>
When terms are added to a taxonomy WordPress accepts a term Name, an optional Slug, an optional Parent and an optional Description. WordPress will generate a Slug from the Name and will always add whatever is needed to make the slug unique within the taxonomy. For example, consider these terms:
<table cellpadding="5">
<tr>
<td><strong>Name</strong></td>
<td><strong>Slug</strong></td>
<td><strong>Name</strong></td>
<td><strong>Slug</strong></td>
<tr>
<td>Grand Parent One (08)</td>
<td>grand-parent-one</td>
<td>Grand Parent Two (02)</td>
<td>grand-parent-two</td>
</tr>
<tr>
<td>&mdash; Parent Two (10)</td>
<td>parent-two-grand-parent-one</td>
<td>&mdash; Parent Three (05)</td>
<td>parent-three</td>
</tr>
<tr>
<td>&mdash; &mdash; Child Three (13)</td>
<td>child-three-parent-two-grand-parent-one</td>
<td>&mdash; &mdash; Child Five (07)</td>
<td></td>
</tr>
<tr>
<td>&mdash; &mdash; Child Two (12)</td>
<td>child-two</td>
<td>&mdash; &mdash; Child Three (06)</td>
<td>child-three</td>
</tr>
<tr>
<td>&mdash; Parent One (09)</td>
<td>parent-one</td>
<td>&mdash; Parent Two (03)</td>
<td>parent-two</td>
</tr>
<tr>
<td>&mdash; &mdash; Child One (11)</td>
<td>child-one</td>
<td>&mdash; &mdash; Child Four (04)</td>
<td>child-four</td>
</tr>
</table>
In this example, the &ldquo;Grand Parent Two&rdquo; hierarchy was added first and the slugs in those terms are obvious. The &ldquo;Parent Two&rdquo; and &ldquo;Child Three&rdquo; terms under &ldquo;Grand Parent One&rdquo; have modified slugs to avoid duplication with the terms added earlier.
There is no prohibition against the same slug appearing in two different taxonomies.
<a name="flat-taxonomies"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Path Specifications for Flat Taxonomies</h3>
<p>
The plugin ignores mapping rules for non-hierarchical, or &ldquo;flat&rdquo;, taxonomies such as Att. Tags. Any path specifications found will be processed as a simple term name, e.g., &ldquo;/parent/child&rdquo; will become a simple root-level term, not separate &ldquo;parent&rdquo; and &ldquo;child&rdquo; terms.
<a name="matching"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Matching on Term Name</h3>
<p>
Because the term Slug cannot be known in advance, MLA mapping rules match term values in item metadata against the term Name. When duplicate term Names exist in a taxonomy MLA will match the term defined earliest, i.e., with the lowest term_id value. In the above example &ldquo;Child Three&rdquo; will match term_id &ldquo;6&rdquo;, under &ldquo;Grand Parent Two/Parent Three&rdquo;. Several examples of WordPress &ldquo;search by term Name&rdquo; functions and their results are in the Appendix.
</p>
<p>
When duplicate term Names exist in a taxonomy they can always be disambiguated by specifying the complete, absolute path to the desired term. Starting a relative path with a unique ancestor name will also work.</p>
<p>
Finally, the &ldquo;Parent&rdquo; value <strong>in the mapping rule</strong> (a rule Parent) can restrict the search to a subset of the term tree and change the handling of relative paths. For example:
</p>
<ul class="mla_settings">
<li>if the <strong>rule</strong> Parent is &ldquo;Grand Parent One&rdquo;, then a <strong>relative path</strong> such as &ldquo;Child Three&rdquo; will be matched as &ldquo;/Grand Parent One/Child Three&rdquo;. It will not match any other &ldquo;Child One&rdquo; values lower down in the subtree.</li>
<li>if the <strong>rule</strong> Parent is &ldquo;Grand Parent One/Parent Two&rdquo;, then a <strong>relative path</strong> such as &ldquo;Child Three&rdquo; will be matched as &ldquo;/Grand Parent One/Parent Two/Child Three&rdquo;.</li>
</ul>
<p>
An absolute path will ignore the rule Parent and start from the root level of the term hierarchy.
For more examples, here is a hierarchical taxonomy sorted by the order in which the terms were created (Term ID order):
</p>
<code>
Grand Parent Two (02)<br />
Grand Parent Two/Parent Two (03)<br />
Grand Parent Two/Parent Two/Child Four (04)<br />
Grand Parent Two/Parent Three (05)<br />
Grand Parent Two/Parent Three/Child Three (06)<br />
Grand Parent Two/Parent Three/Child Five (07)<br />
Grand Parent One (08)<br />
Grand Parent One/Parent One (09)<br />
Grand Parent One/Parent Two (10)<br />
Grand Parent One/Parent One/Child One (11)<br />
Grand Parent One/Parent Two/Child Two (12)<br />
Grand Parent One/Parent Two/Child Three (13)
</code>
<p>
Here&rsquo;s the way the taxonomy appears in the Edit Taxonomy admin submenu:
</p>
<code>
Grand Parent One (08)<br />
— Parent One (09)<br />
— — Child One (11)<br />
— Parent Two (10)<br />
— — Child Three (13)<br />
— — Child Two (12)<br />
Grand Parent Two (02)<br />
— Parent Three (05)<br />
— — Child Five (07)<br />
— — Child Three (06)<br />
— Parent Two (03)<br />
— — Child Four (04)
</code>
<p>
Here are the results of matching ambiguous unqualified names to taxonomy terms (assuming no rule Parent):
</p>
<ul>
<li>&ldquo;<strong>Child Three</strong>&rdquo; => Grand Parent Two/Parent Three/<strong>Child Three</strong> (06)</li>
<li>&ldquo;<strong>Parent Two</strong>&rdquo; => Grand Parent Two/<strong>Parent Two</strong> (03)</li>
</ul>
<p>
You can&rsquo;t read down the displayed list and easily predict how the match will come out!
</p>
<p>
Here are the unexpected results of matching relative paths with ambiguous initial names (assuming no rule Parent):
</p>
<ul>
<li>&ldquo;<strong>Parent Two/Child Four</strong>&rdquo; => Grand Parent Two/<strong>Parent Two/Child Four</strong> (04)</li>
<li>&ldquo;<strong>Parent Two/Child Three</strong>&rdquo; => <strong>NO MATCH</strong> - creates Grand Parent Two/<strong>Parent Two/Child Three</strong> (14)
</li>
</ul>
<p>
In the second case, to match the existing &ldquo;Parent Two/Child Three&rdquo; the ambiguous &ldquo;Parent Two&rdquo; must be made unique by adding &ldquo;Grand Parent One&rdquo;. To avoid this issue always start a relative path with a unique name.
<a name="creating"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3><strong>Creating New Terms</strong></h3>
<p>
When a term embedded in the metadata in an item&rsquo;s file is not found in the taxonomy it must be added to the taxonomy&rsquo;s terms in the proper location.
</p>
<p>
Single-level terms are straightforward. If a rule Parent is specified they will be added just under the Parent. If the rule Parent is not specified they will be added a new root-level terms.
</p>
<p>
Multi-level terms are more challenging, since any of the path elements might be new to the taxonomy. Consider these additions to the above example taxonomy:
</p>
<ol>
<li>&ldquo;<strong>Parent Three/Child Six</strong>&rdquo; – a new &ldquo;Child Six&rdquo; term will be added just under the existing &ldquo;Parent Three&rdquo;.</li>
<li>&ldquo;<strong>Parent Four/Child One</strong>&rdquo; – both of the path elements are new to the taxonomy. &ldquo;Parent Four&rdquo; will be added as a new root-level term (no rule Parent) or just under the rule Parent. Then, &ldquo;Child One&rdquo; will be added just under the new &ldquo;Parent Four&rdquo;.</li>
<li>&ldquo;<strong>Grand Parent Two/Parent Four/Child One</strong>&rdquo; – &ldquo;Parent Four&rdquo; will be added as a new term just under the existing &ldquo;Grand Parent Two&rdquo;. Then, &ldquo;Child One&rdquo; will be added just under the new &ldquo;Parent Four&rdquo;.</li>
<li>&ldquo;<strong>/Parent Four/Child Six</strong>&rdquo; – &ldquo;Parent Four&rdquo; will be added as a new root-level term. Then, a new &ldquo;Child Six&rdquo; term will be added just under the new &ldquo;/Parent Four&rdquo;.</li>
</ol>
<p>
In general, multi-level terms are processed one level at a time from left to right. Relative paths take the rule Parent into account, absolute paths always start form the root level.
<a name="multi-level"></a>
&nbsp;
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3><strong>Assigning Multi-level Terms</strong></h3>
<p>
WordPress allows you to assign terms at any level in any combination. In other words, given the absolute path &ldquo;/Grand Parent One/Parent Two/Child Three&rdquo; you can assign one, two or all three of the path elements to the item. If you choose to assign only lowest-level &ldquo;Child Three&rdquo; you can still associate the item with &ldquo;Grand Parent One&rdquo; by including &ldquo;children&rdquo; in a term query.
</p>
<p>
The two common choices seem to be &ldquo;assign all levels&rdquo; or &ldquo;assign lowest level&rdquo;. Adding syntax to assign or skip individual path elements seems overly complicated. For this reason, a simple checkbox option will select one of the two common choices on a rule-by-rule basis.
</p>
<p>
When a Rule Parent is specified, a second checkbox option will select whether the Rule Parent term is also assigned to the item. The Rule Parent term is only assigned when one or more other terms are assigned as well, so items with no explicit term assignments do not get assigned to the Rule Parent.
<a name="mapping-multiple"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Mapping Multiple Items</h3>
<p>
All of MLA&rsquo;s mapping operations are performed on each item in isolation. If you are mapping two or more items in an upload, a batch edit or a &ldquo;map all items&rdquo; operation be aware that the order in which the items are processed can affect the placement of new terms within the hierarchy. For example, consider these two items and their terms:
</p>
<ol>
<li>Item A, terms &ldquo;/Grand Parent One/Parent Two/Child Three&rdquo;</li>
<li>Item B, terms &ldquo;Parent Two/Child Three&rdquo;</li>
</ol>
<p>
If these items are processed in order there will be a single &ldquo;Parent Two&rdquo; term under &ldquo;Grand Parent One&rdquo;. If, however, Item B is processed before Item A there will be a root-level &ldquo;Parent Two&rdquo; term from Item B and a separate &ldquo;/Grand Parent One/Parent Two&rdquo; term from Item A. The best defense is unique names for every term!
</p>
</div>