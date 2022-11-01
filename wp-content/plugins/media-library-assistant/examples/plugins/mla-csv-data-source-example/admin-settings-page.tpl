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
    <strong>Source file</strong>
  </td>
  <td>
    <select name="[+slug_prefix+]_options[source]" id="mla-csv-data-source-source">
[+source_options+]
    </select>
    <div class="mla-settings-help">&nbsp;&nbsp;Select the CSV file to use for the data sources.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Match on</strong>
  </td>
  <td>
    <select name="[+slug_prefix+]_options[match]" id="mla-csv-data-source-match">
		<option [+id_selected+] value="id">ID (or .ID)</option>
		<option [+base_file_selected+] value="base_file">Base File</option>
		<option [+file_name_selected+] value="file_name">File Name (only)</option>
    </select>
    <div class="mla-settings-help">&nbsp;&nbsp;Select the key used to match the item to the CSV variables.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Delimiter</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[delimiter]" id="mla-csv-data-source-delimiter" type="text" size="1" maxlength="1" value="[+delimiter+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the (one character) delimiter that separates variables.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Enclosure</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[enclosure]" id="mla-csv-data-source-enclosure" type="text" size="1" maxlength="1" value="[+enclosure+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the (one) character that encloses a variable.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Escape</strong>
  </td>
  <td>
    <input name="[+slug_prefix+]_options[escape]" id="mla-csv-data-source-escape" type="text" size="1" maxlength="1" value="[+escape+]" />
    <div class="mla-settings-help">&nbsp;&nbsp;Enter the (one) character that escapes special characters within a variable.</div>
  </td>
</tr>
<tr valign="top">
  <td class="textright">
    <strong>Exports</strong>
  </td>
  <td>
    <textarea name="[+slug_prefix+]_options[exports]" id="mla-csv-data-source-exports" rows="4" cols="80" >[+exports+]</textarea>
    <div class="mla-settings-help">&nbsp;&nbsp;Enter data sources, substitution parameters or content templates; one entry per line.<br />&nbsp;&nbsp;See Documentation tab for details.</div>
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
<p>In this tab you can select the file containing the data you want, the variable used to match the variables to Media Library items and the special characters used to format the file.</p>
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
<h3>Export Item Values</h3>
<p>Click the button below to download a CSV file containing the ID, Base File and File Name values for all the items currently in your Media Library.</p>
		<span class="submit mla-settings-submit">
		<input name="[+slug_prefix+]_options_export" class="button-primary" id="mla-csv-data-source-export" type="submit" value="Export Item Values" />
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
<li><a href="#selecting-a-source-file"><strong>Selecting a Source File</strong></a></li>
<li><a href="#source-file-format"><strong>Source File Format</strong></a></li>
<li><a href="#matching-csv-rows"><strong>Matching CSV rows to Media Library Items</strong></a></li>
<li><a href="#delimiter-enclosure-escape"><strong>Delimiter, Enclosure and Escape</strong></a></li>
<li><a href="#accessing-csv-values"><strong>Accessing CSV Values</strong></a></li>
<li><a href="#assigning-hierarchical-terms"><strong>Assigning Hierarchical Terms</strong></a></li>
<li><a href="#exporting-item-values"><strong>Exporting Item Values</strong></a></li>
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
MLA provides access to a wealth of data about Media Library items through its <a href="[+settingsURL+]?page=mla-settings-menu-documentation&mla_tab=documentation#mla_variable_parameters" target="_blank">Field-level substitution parameters</a> and <a href="[+settingsURL+]?page=mla-settings-menu-documentation&mla_tab=documentation#field_level_data_sources" target="_blank">Field-level data sources</a>. You can use these data sources in the Media/Assistant and Media/Add New (Upload New Media) Bulk Edit areas to compose values for Standard Fields like Title and Caption, taxonomy term assignments and custom field values. You can also use them in IPTC/EXIF and Custom Field mapping rules in the same fashion. Mapping rules were originally created to extract and use the IPTC, EXIF and XMP metadata embedded in image files, PDF documents and Microsoft Office files. However, many files do not contain metadata values and some file formats do not support the metadata standards.
</p>
<p>
This plugin was developed to support Comma-separated Variable (CSV) files as an alternative source for field-level data sources. It uses the Field-level substitution parameter filters (Hooks) MLA provides to do this. In the sections below you will find information on creating compatible CSV files, matching the CSV values to Media Library items, accessing the values and some notes on assigning terms in hierarchical taxonomies.
<a name="selecting-a-source-file"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Selecting a Source File</h3>
<p>
The first control on this plugin's General tab is a dropdown control containing the Title values for every item in the Media Library that has a MIME Type of "text/csv". The type is assigned to files with a ".csv" extension.
</p>
<p>
This means that the first step of the process is, of course, uploading the CSV file (or files) you want to use to your Media Library. You can have as many files as you like, although only one file can be active at any one time.
<a name="source-file-format"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Source File Format</h3>
<p>
A comma-separated values (CSV) file is a delimited text file that uses a comma to separate values. Each line of the file is a data record. Each record consists of one or more fields, separated by commas. The use of the comma as a field separator is the source of the name for this file format. A CSV file typically stores tabular data (numbers and text) in plain text, in which case each line will have the same number of fields. You can find much more information in the <a href="https://en.wikipedia.org/wiki/Comma-separated_values" title="Comma-separated values Wikipedia article" target="_blank">Comma-separated values</a> article on Wikipedia.
</p>
<p>
To be used with this plugin the first record in the CSV file <strong>must</strong> be a header record, containing the names of the variables in the file. Like WordPress custom field names, the variable names can contain spaces and some puncuation characters. Variable names are case-specific, e.g., "the name" and "The Name" are <strong>not</strong> the same. A few special names are defined in the next section.
</p>
<p>
This plugin uses a standard PHP function, <a href="http://php.net/manual/en/function.fgetcsv.php" title="PHP Manual page for fgetcsv" target="_blank">fgetcsv</a>, to read and parse CSV files. Your file must be compatible with that function to be used in this plugin.
</p>
<p>
This plugin loads the entire file into memory for quick access to all records. This should be fine for files with hundreds or a few thousand records, but you may need to increase the memory available to WordPress for really large files.
<a name="matching-csv-rows"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Matching CSV rows to Media Library Items</h3>
<p>
To match a record containing CSV variables to the correct Media Library item the CSV variables must include one or more of the following (identified by its names in the header record):
</p>
<ul class="mla_settings">
<li><strong>ID (or .ID)</strong> - the ID of the corresponding item in the <strong>destination site's</strong> Media Library. This is the most efficient match variable, but it may be unavailable for some applications. Also, it will <strong>not</strong> be available for matching new items as they are uploaded to the Media Library.<br />&nbsp;<br />If you are exporting from one site and importing to another site, <strong>do NOT use</strong> the ID values from the site you are exporting from; they will not be correct!<br />&nbsp;<br />If your ID value is the first column/variable in the file you must use the alternate ".ID" name because "ID" at the very beginning of the file is reserved for another Microsoft file format and will cause all sorts of trouble for CSV files.</li>
<li><strong>Base Name</strong> - the relative path and file name within the uploads directory. If your Settings/Media options include the "Organize my uploads into month- and year-based folders" option the Base Name will prepend the year and month to the file name, e.g., something like "2020/03/picture.jpg". If the option is not set it will be the same as the File Name, e.g., "picture.jpg".</li>
<li><strong>File Name</strong> - the file name (only) within the uploads directory, e.g., "picture.jpg". If your file names are unique this will work well. If you have files of the same name in different year/month subdirectories all of them will get the same CSV values unless you use the Base Name to differentiate them.</li>
</ul>
<p>
If you select a file that does not contain the match variable you select on the General tab you will see a warning message at the top of the screen. None of the variables in the file will be accessible until you select a match variable that the file contains.
</p>
<p>
You can use the "Export Item Values" button at the bottom of the General tag to create and download a CSV file containing all three match key values of the items in the destination site. Study the "<a href="#exporting-item-values">Exporting Item Values</a>" section below for more information.
<a name="delimiter-enclosure-escape"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Delimiter, Enclosure and Escape</h3>
<p>
As described in the Wikipedia article, most CSV files will use a comma to separate variables and double-quotes to enclose variables containing special characters (such as commas and double-quotes). Occasionally you might encounter a fle that uses, for example, a semicolon to separate variables. For these unusual situations you can specify the characters used in these three ways:
</p>
<ul class="mla_settings">
<li><strong>Delimiter</strong> - the character used to separate variables within a record. Defaults to a comma.</li>
<li><strong>Enclosure</strong> - enclose variables containing special characters (such as commas and double-quotes). Defaults to a double-quote.</li>
<li><strong>Escape</strong> - optional; sets the escape character (at most one character). An empty string disables the proprietary escape mechanism.<br />&nbsp;<br /><strong>Note</strong>: Usually an enclosure character is escaped inside a field by doubling it; however, the escape character can be used as an alternative. So for the default parameter values "" and \" have the same meaning. Other than allowing to escape the enclosure character the escape character has no special meaning; it isn't even meant to escape itself. </li>
</ul>
<p>
You can enter at most one character in each text box on the General tab. If you enter an empty value for the Delimiter and/or Enclosure the default values will be substituted.
<a name="accessing-csv-values"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Accessing CSV Values</h3>
<p>
The advantage of implementing CSV access as a custom data source is that it takes advantage of MLA's many existing features, e.g., IPTC/EXIF and Custom Field mapping rules, the Media/Assistant Bulk Edit area, Content Templates and the field-level substitution parameters in shortcodes such as <code>[mla_gallery]</code>. Once a CSV record is matched to an item, simply use the <code>csv:</code> prefix and the variable name from the file's header record, e.g., "[+csv:File Name+]". Variable names are case sensitive, so the name you enter must match the variable name in the file exactly.
</p>
<p>
You can use one or more CSV variables in a <a href="[+settingsURL+]?page=mla-settings-menu-documentation&mla_tab=documentation#mla_template_parameters" target="_blank">Content Template</a> to compose a value from several CSV variables and other data sources, literal strings and other material. You can use <a href="[+settingsURL+]?page=mla-settings-menu-documentation&mla_tab=documentation#field_level_formats" target="_blank">Field-level option/format values</a> to specify the treatment of fields with multiple values or to change the format of a field for display/mapping purposes.
<a name="assigning-hierarchical-terms"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Assigning Hierarchical Terms</h3>
<p>
The current MLA version provides mapping rules to assign taxonomy terms to Media Library items from IPTC, EXIF and XMP metadata values embedded in the image files. For hierarchical taxonomies, the current version allows new terms to be added under a specified &ldquo;Parent&rdquo; term, but does not support more general mapping of terms within multiple levels.
</p>
<p>
To accurately import hierarchical term assignments you have (at least) two alternatives. First, if your term names are unique you can simply define all of the terms and their relationships before you import your CSV data. Given an existing term with a unique name, MLA can find the correct term for each assignment. Second, you can encode the hierarchicy information in the CSV file and use an MLA example plugin to set up the hierarchy as the CSV data is imported.
</p>
<p>
The <a title="Find the Path Mapping Example" href="[+example_url+]&amp;mla-example-search=Search+Plugins&amp;s=%22MLA+Path+Mapping%22" target="_blank" class="mla-doc-bold-link">MLA Path Mapping Example</a>
 plugin adds a &ldquo;Path Delimiter&rdquo; that allows term parent and higher-level ancestor values to be specified, more precisely placing a term in the hierarchy. For example, a value such as &ldquo;/grand parent/parent/child&rdquo; denotes a specific term within a three-level hierarchy. In this example the delimiter at the start of the value means that &ldquo;grand parent&rdquo; must be a root term, i.e., it appears at the highest level and has no ancestors. This is an absolute path specification. A path such as &ldquo;parent/child&rdquo; is a relative path specification, starting wherever &ldquo;parent&rdquo; appears in the hierarchy.
</p>
<p>
The MLA Path Mapping Example plugin includes a Settings/MLA Path Mapping Documentation tab with a detailed discussion of hierarchical term implementation and how to code term path names in your CSV file.
<a name="exporting-item-values"></a>
</p>
<p>
<a href="#backtotop">Go to Top</a>
</p>
<h3>Exporting Item Values</h3>
<p>
You can use the "Export Item Values" button at the bottom of the General tag to create and download a CSV file containing all three match key values of all Media Library items in the destination site. You can use this file to match the File Name or the Base Name to the correct destination site ID values and update your source file with the correct ID values.
</p>
<p>
At a minimum the file will contain three columns - Base Name, File Name and ID. You can add additional columns to the file by defining them in the "Exports" text area on the General tab. In the text area you can define multiple columns, entering <strong>one definition per line</strong>. Each definition contains an optional column name and a Field-level Substitution Parameter specifying the column content.
</p>
<p>
If you do not add a column name to your definition the column content specification will be used as a default name. For simple specifications this is often a good choice. If your specification is long or complex you will want to give the column a more practical name. To add a name, start the specification with a double-quote ('<strong>"</strong>'), enter the name and end the name with a double-quote followed by a comma. For example, <code>"Title",post_title</code> or <code>"Date Taken",[+exif:DateTimeOriginal+]</code>.
</p>
<p>
The column content specification can use almost all of the features described in the "Field-level Substitution Parameters" section of MLA's Settings/Media Library Assistant Documentation tab. The <code>query:</code> prefix is not available because it only applies to the <code>[mla_gallery]</code> shortcode. For the same reason the substitution parameters described in the "Style and Markup Templates" section are not available. In particular, the "Attachment-specific substitution parameters for the markup template Item part" are not available; use the equivalent "Field-level data sources" instead.
</p>
<p>
For convenience you can omit the enclosing "[+" and "+]" delimiters from the specification. The plugin will use the specification as the default column name and add enclosing delimiters, e.g., <code>post_title</code> becomes <code>"post_title",[+post_title+]</code>. You can add one of the "Field-level option/format values" or use a prefix as well, e.g., <code>exif:DateTimeOriginal,date('j F, Y')</code> becomes <code>"exif:DateTimeOriginal,date('j F, Y')",[+exif:DateTimeOriginal,date('j F, Y')+]</code>. Of course, you can add your own column name if desired.
</p>
<p>
You can use a Content Template for more complex requirements. The conditional and choice elements are the key to templates' power, particularly with custom fields and metadata such as IPTC and EXIF. With the conditional element you can combine literal text with a substitution value and eliminate the text if the value is missing. With the choice element you can specify multiple sources for a value and decide the order in which they are tested. For example:<br />&nbsp;<br />
<code>"Date Taken",template:([+exif:DateTimeOriginal,date('j F, Y')+])|([+iptc:DateCreated,date('j F, Y')+])|([+xmp:CreateDate,date('j F, Y')+])</code>
<br />&nbsp;<br />
The above example uses the value from any of three sources and translates the result into a common format.
</p>
</div>