=== Media Library Assistant ===
Contributors: dglingren
Donate link: http://davidlingren.com/#donate
Tags: categories, gallery, images, media, media library, tags
Requires at least: 3.5.0
Tested up to: 6.0.2
Requires PHP: 5.3
Stable tag: 3.04
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Enhances the Media Library; powerful [mla_gallery] [mla_tag_cloud] [mla_term_list], taxonomy support, IPTC/EXIF/XMP/PDF processing, bulk/quick edit.

== Description ==

The Media Library Assistant provides several enhancements for managing the Media Library, including:

* The **`[mla_gallery]` shortcode**, used in a post, page or custom post type to add a gallery of images and/or other Media Library items (such as PDF documents). MLA Gallery is a superset of the WordPress `[gallery]` shortcode; it is compatible with `[gallery]` and provides many enhancements. These include: 1) full query and display support for WordPress categories, tags, custom taxonomies and custom fields, 2) support for all post_mime_type values, not just images 3) media Library items need not be "attached" to the post, and 4) control over the styles, markup and content of each gallery using Style and Markup Templates. **Twenty-eight hooks** are provided for complete gallery customization from your theme or plugin code.

* The **`[mla_tag_cloud]` shortcode**, used in a post, page, custom post type or widget to display the "most used" terms in your Media Library where the size of each term is determined by how many times that particular term has been assigned to Media Library items. **Twenty-five hooks** are provided for complete cloud customization from your theme or plugin code.

* The **`[mla_term_list]` shortcode**, used in a post, page, custom post type or widget to display hierarchical (and flat) taxonomy terms in list, dropdown control or checklist formats. **Twenty hooks** are provided for complete list customization from your theme or plugin code.

* Support for **[WPML](https://wpml.org/)** and **Polylang** multi-language CMS plugins. MLA has earned a place on [WPML's List of Recommended Plugins](https://wpml.org/plugin/media-library-assistant/).

* **Integrates with Photonic Gallery, Jetpack and other plugins**, so you can add slideshows, thumbnail strips and special effects to your `[mla_gallery]` galleries.

* Works with **[WordPress Real Media Library: Media Library Folder & File Manager](https://wordpress.org/plugins/real-media-library-lite/)** (Lite and Pro) to organize your files into folders, collections and galleries. This combination enhances both the Media/Assistant admin submenu and the `[mla_gallery]` shortcode.

* Powerful **Content Templates**, which let you compose a value from multiple data sources, mix literal text with data values, test for empty values and choose among two or more alternatives or suppress output entirely.

* **Attachment metadata** such as file size, image dimensions and where-used information can be assigned to WordPress custom fields. You can then use the custom fields in your `[mla_gallery]` display and you can add custom fields as sortable, searchable columns in the Media/Assistant submenu table. You can also **modify the WordPress `_wp_attachment_metadata` contents** to suit your needs.

* **IPTC**, **EXIF (including GPS)**, **XMP** and **PDF** metadata can be assigned to standard WordPress fields, taxonomy terms and custom fields. You can update all existing attachments from the Settings page IPTC/EXIF tab, groups of existing attachments with a Bulk Action or one existing attachment from the Edit Media/Edit Single Item screen. Display **IPTC**, **EXIF**, **XMP** and **PDF** metadata with `[mla_gallery]` custom templates. **Twelve hooks** provided for complete mapping customization from your theme or plugin code.

* Complete control over **Post MIME Types, File Upload extensions/MIME Types and file type icon images**. Fifty four (54) additional upload types, 112 file type icon images and a searchable list of over 1,500 file extension/MIME type associations.

* **Enhanced Search Media box**. Search can be extended to the name/slug, ALT text and caption fields. The connector between search terms can be "and" or "or". Search by attachment ID or Parent ID is supported, and you can search on keywords in the taxonomy terms assigned to Media Library items. Works in the Media Manager Modal Window, too.

* **Where-used reporting** shows which posts use a media item as the "featured image", an inserted image or link, an entry in a `[gallery]` and/or an entry in an `[mla_gallery]`.

* **Complete support for ALL taxonomies**, including the standard Categories and Tags, your custom taxonomies and the Assistant's pre-defined Att. Categories and Att. Tags. You can add taxonomy columns to the Assistant listing, filter on any taxonomy, assign terms and list the attachments for a term.

* Taxonomy and custom field support in the ATTACHMENT DETAILS pane of the Media Manager Modal Window and Media/Library Grid view.

* An inline **"Bulk Edit"** area; update author, parent and custom fields, add, remove or replace taxonomy terms for several attachments at once. Works on the Media/Add New screen as well.

* An inline **"Quick Edit"** action for many common fields and for custom fields

* Displays more attachment information such as parent information, file URL and image metadata. Provides many more listing columns (more than 20) to choose from.

* Allows you to edit the post_parent, the menu_order and to "unattach" items

* Provides additional view filters for MIME types and taxonomies, and features to cmpose custom views of your own.

* Works with the popular [Admin Columns](https://wordpress.org/plugins/codepress-admin-columns/) plugins for even more Media/Assistant screen customization.

The Assistant is designed to work like the standard Media Library pages, so the learning curve is short and gentle. Contextual help is provided on every new screen to highlight new features.

**I do not solicit nor accept personal donations in support of the plugin.** WordPress and its global community means a lot to me and I am happy to give something back.

If you find the Media Library Assistant plugin useful and would like to support a great cause, consider a [tax-deductible donation](http://webgw.alsa.org/goto/ChateauSeaviewFund) to our [Chateau Seaview Fund](http://webgw.alsa.org/goto/ChateauSeaviewFund) at the Golden West Chapter of the ALS Association. Every dollar of the fund goes to make the lives of people with ALS, their families and caregivers easier. Thank you!

== Installation ==

1. Upload `media-library-assistant` and its subfolders to your `/wp-content/plugins/` directory, **OR** Visit the Plugins/Add New page and search for "Media Library Assistant"; click "Install Now" to upload it

1. Activate the plugin through the "Plugins" menu in WordPress

1. Visit the Settings/Media Library Assistant page to customize taxonomy (e.g., category and tag) support

1. Visit the Settings/Media Library Assistant Custom Fields and IPTC/EXIF tabs to map metadata to attachment fields

1. Visit the "Assistant" submenu in the Media admin section

1. Click the Screen Options link to customize the display

1. Use the enhanced Edit, Quick Edit and Bulk Edit pages to assign categories and tags

1. Use the `[mla_gallery]` shortcode to add galleries of images, documents and more to your posts and pages

1. Use the `[mla_tagcloud]` and `[mla_term_list]` shortcodes to add clickable lists of taxonomy terms to your posts and pages

== Frequently Asked Questions ==

= How can I sort the Media/Assistant submenu table on values such as File Size? =

You can add support for many attachment metadata values such as file size by visiting the Custom Fields tab on the Settings page. There you can define a rule that maps the data to a WordPress custom field and check the "MLA Column" box to make that field a sortable column in the Media/Assistant submenu table. You can also use the field in your `[mla_gallery]` shortcodes. For example, this shortcode displays a gallery of the ten largest images in the "general" category, with a custom caption:

`
[mla_gallery category="general" mla_caption="{+caption+}<br>{+custom:File Size+}" meta_key="File Size" orderby="meta_value" order="DESC" numberposts=10]
`

= How can I use Categories, Tags and custom taxonomies to select images for display in my posts and pages? =

The powerful `[mla_gallery]` shortcode supports almost all of the query flexibility provided by the WP_Query class. You can find complete documentation in the Settings/Media Library Assistant Documentation tab. A simple example is in the preceding question. Here's an example that displays PDF documents with Att. Category "fauna" or Att. Tag "animal":

`
[mla_gallery post_mime_type="application/pdf" size=icon mla_caption="{+title+}" tax_query="array(array('taxonomy'=>'attachment_category','field'=>'slug','terms'=>'fauna'),array('taxonomy'=>'attachment_tag','field'=>'slug','terms'=>'animal'),'relation'=>'OR')"]
`

= Can I use [mla_gallery] for attachments other than images? =

Yes! The `[mla_gallery]` shortcode supports all MIME types when you add the post_mime_type parameter to your query. You can build a gallery of your PDF documents, plain text files and other attachments. You can mix images and other MIME types in the same gallery, too. Here's an example that displays a gallery of PDF documents, using Imagick and Ghostscript to show the first page of each document as a thumbnail:

`
[mla_gallery post_mime_type=application/pdf post_parent=all link=file mla_viewer=true columns=1 orderby=date order=desc]
`

= Can I attach an image to more than one post or page? =

No; that's a structural limitation of the WordPress database. However, you can use Categories, Tags and custom taxonomies to organize your images and associate them with posts and pages in any way you like. The `[mla_gallery]` shortcode makes it easy. You can also use the `ids=` parameter to compose a gallery from a list of specific images.

= Can the Assistant use the standard WordPress post Categories and Tags? =

Yes! You can activate or deactivate support for Categories and Tags at any time by visiting the Media Library Assistant Settings page.

= Do I have to use the WordPress post Categories and Tags? =

No! The Assistant supplies pre-defined Att. Categories and Att. Tags; these are WordPress custom taxonomies, with all of the API support that implies. You can activate or deactivate the pre-defined taxonomies at any time by visiting the Media Library Assistant Settings page.

= Can I add my own custom taxonomies to the Assistant? =

Yes. Any custom taxonomy you register with the Attachment post type will appear in the Assistant UI. Use the Media Library Assistant Settings page to add support for your taxonomies to the Assistant UI.

= Can I use Jetpack Tiled Gallery or a lightbox plugin to display my gallery? =
You can use other gallery-generating shortcodes to give you the data selection power of [mla_gallery] and the formatting/display power of popular alternatives such as the WordPress.com Jetpack Carousel and Tiled Galleries modules. Any shortcode that accepts "ids=" or a similar parameter listing the attachment ID values for the gallery can be used. Here's an example of a Jetpack Tiled gallery for everything except vegetables:

`
[mla_gallery attachment_category=vegetable tax_operator="NOT IN" mla_alt_shortcode=gallery type="rectangular"]
`

Most lightbox plugins use HTML `class=` and/or `rel=` tags to activate their features. `[mla_gallery]` lets you add this tag information to your gallery output. Here's an example that opens PDF documents in a shadowbox using Easy Fancybox:

`
[mla_gallery post_mime_type=application/pdf post_parent=all link=file size=icon mla_caption='<a class="fancybox-iframe fancybox-pdf" href={+filelink_url+} target=_blank>{+title+}</a>' mla_link_attributes='class="fancybox-pdf fancybox-iframe"']
`

In the example, the `mla_caption=` parameter turns the document title into a link to the shadowbox display so you can click on the thumbnail image or the caption to activate the display.

= Why don't the "Posts" counts in the taxonomy edit screens match the search results when you click on them? =

This is a known WordPress problem with multiple support tickets already in Trac, e.g., 
Ticket #20708(closed defect (bug): duplicate) Wrong posts count in taxonomy table,
Ticket #14084(assigned defect (bug)) Custom taxonomy count includes draft & trashed posts,
and Ticket #14076(closed defect (bug): duplicate) Misleading post count on taxonomy screen.

For example, if you add Tags support to the Assistant and then assign tag values to your attachments, the "Posts" column in the "Tags" edit screen under the Posts admin section includes attachments in the count. If you click on the number in that column, only posts and pages are displayed. There are similar issues with custom post types and taxonomies (whether you use the Assistant or not). The "Attachments" column in the edit screens added by the Assistant shows the correct count because it works in a different way.

= How do I "unattach" an item? =

Hover over the item you want to modify and click the "Edit" or "Quick Edit" action. Set the ID portion of the Parent Info field to zero (0), then click "Update" to record your changes. If you change your mind, click "Cancel" to return to the main page without recording any changes. You can also click the "Select" button to bring up a list of posts//pages and select one to be the new parent for the item. The "Set Parent" link in the Media/Assistant submenu table also supports changing the parent and unattaching an item.

= The Media/Assistant submenu seems sluggish; is there anything I can do to make it faster? =

Some of the MLA features such as where-used reporting and ALT Text sorting/searching require a lot of database processing. If this is an issue for you, go to the Settings page and adjust the **"Where-used database access tuning"** settings. For any where-used category you can enable or disable processing. For the "Gallery in" and "MLA Gallery in" you can also choose to update the results on every page load or to cache the results for fifteen minutes between updates. The cache is also flushed automatically when posts, pages or attachments are inserted or updated.

= Do custom templates and option settings survive version upgrades? =

Rest assured, custom templates and all of your option settings persist unchanged whenever you update to a new MLA version.

You can also back a backup of your templates and settings from the Settings/Media Library Assistant General tab. Scroll to the bottom of the page and click "Export ALL Settings" to create a backup file. You can create as many files as you like; they are date and time stamped so you can restore the one you want later.

In addition, you can deactivate and even delete the plugin without losing the settings. They will be there when you reinstall and activate in the future.

You can permanently delete the settings and (optionally) the backup files if you are removing MLA for good. The "Uninstall (Delete)" Plugin Settings section of the General tab enables these options.

= Are other language versions available? =

Not many, but all of the internationalization work in the plugin source code has been completed and there is a Portable Object Template (.POT) available in the "/languages" directory. I don't have working knowledge of anything but English, but if you'd like to volunteer to produce a translation, I would be delighted to work with you to make it happen. Have a look at the "MLA Internationalization Guide.pdf" file in the languages directory and get in touch.

= What's in the "phpDocs" directory and do I need it? =

All of the MLA source code has been annotated with "DocBlocks", a special type of comment used by phpDocumentor to generate API documentation. If you'd like a deeper understanding of the code, navigate to the [MLA phpDocs web page](http://davidlingren.com/assets/phpDocs/index.html "Read the API documentation") and have a look. Note that these pages require JavaScript for much of their functionality.

== Screenshots ==

1. The Media/Assistant submenu table showing the available columns, including "Featured in", "Inserted in", "Att. Categories" and "Att. Tags"; also shows the Quick Edit area.
2. The Media/Assistant submenu table showing the Bulk Edit area with taxonomy Add, Remove and Replace options; also shows the tags suggestion popup.
3. A typical edit taxonomy page, showing the "Attachments" column.
4. The enhanced Edit page showing additional fields, categories and tags.
5. The Settings page General tab, where you can customize support of Att. Categories, Att. Tags and other taxonomies, where-used reporting and the default sort order.
6. The Settings page MLA Gallery tab, where you can add custom style and markup templates for `[mla_gallery]` shortcode output.
7. The Settings page IPTC &amp; EXIF Processing Options screen, where you can map image metadata to standard fields (e.g. caption), taxonomy terms and custom fields.
8. The Settings page Custom Field Processing Options screen, where you can map attachment metadata to custom fields for display in [mla_gallery] shortcodes and as sortable, searchable columns in the Media/Assistant submenu.
9. The Media Manager popup modal window showing additional filters for date and taxonomy terms. Also shows the enhanced Search Media box and the full-function taxonomy support in the ATTACHMENT DETAILS area.

== Changelog ==

= 3.04 =
* Fix: When Photo Engine (WP/LR Sync) is active, a PHP Fatal Error with mapping rules during "sync" operations has been corrected.

= 3.03 =
* Fix: For the `[mla_gallery]` shortcode, a defect (introduced in v3.02) in expanding template values has been corrected.

= 3.02 =

* New: The new "MLA WFU Data Source" example plugin provides enhanced access to the "additional fields" created by the WordPress File Upload plugin. The Settings/MLA WFU Data Documentation tab has details.
* New: For the Media/Assistant Bulk Edit area and the Bulk Edit on Upload area, the new "mla_get_bulk_edit_form_presets" filter lets you customize preset fields and content.
* Fix: When Polyang is active, a defect with the "Language" dropdown for Quick Translate, Quick Edit and Bulk Edit has been corrected.
* Fix: When Photo Engine (WP/LR Sync) is active, a defect with mapping rules during "sync" operations has been corrected.
* Fix: For the Media/Assistant Search Media function, the enter key now triggers a search when the text box is not empty.
* Fix: When WPML is active, the "Make media available in all languages" dropdown control has been restored to the Bulk Edit on Upload area.
* Fix: When WPML is active, term synchronization has been restored when new items are uploaded.
* Fix: For the Media/Assistant Bulk Edit area and the Bulk Edit on Upload area, custom field values are now retained as presets for the export/import actions.
* Fix: For the Media/Assistant admin submenu, Fatal PHP errors when Bulk Editing the Uploaded on values on a site with a positive UTC offset Timezone have been avoided.
* Fix: For the "MLA CSV Data Source Example" plugin, links to the Settings/Media Library Assistant Documentation tab have been restored.
* Fix: HTML markup is preserved when editing the source of an "MLA Text" widget, subject to a `current_user_can('unfiltered_html')` check.
* Fix: For the Media/Assistant admin submenu, Fatal PHP errors when pressing the "Terms Search" button before the page load is finished have been avoided.

= 3.01 =

* Fix: For the Media/Assistant Bulk Edit feature, AJAX errors have been corrected.

= 3.00 =

* New: For the "MLA UI Elements Example" plugin, HTML attributes can be added to the `[muie_terms_search]` and `[muie_keyword_search]` shortcodes. The Settings/MLA UI Elements Documentation tab has details.
* New: The "MLA UI Elements Example" plugin has enhanced `[muie_terms_search]` and `[muie_keyword_search]` shortcodes that support multiple independent search boxes on a single post/page. The Settings/MLA UI Elements Documentation tab has details.
* New: The new "MLA Preset Terms Example" plugin adds "Preset Terms" to Media/Assistant rollover actions, redirecting to Media/Add New (Upload New Media) screen with terms copied from the selected item.
* New: The "MLA Substitution Parameter Hooks Example" plugin now provides a `computed_orientation` custom data source that derives "Landscape" or "Portrait" from image height and width values.
* New: For `[mla_gallery]`, a new `mla_use_featured` parameter lets you substitute an item&rsquo;s Featured Image in a gallery display even if it has a native thumbnail image.
* New: The "MLA Multisite Extensions" example plugin now supports the "Multisite Global Media" plugin, which adds a "Global Media" tab to the Media Manager Modal (popup) Window.
* New: The `file_size` and `size_bytes` data sources take advantage of the `filesize` value added to attachment metadata in WP 6.0.
* Fix: For `[mla_term_list]` and `[mla_tag_cloud]`, the `include=` parameter can be used to filter the results of an `ids=` parameter.
* Fix: For `[mla_gallery]`, adding attributes to thumbnail links will work if other attributes are already present before the `href=` attribute.
* Fix: For the "MLA UI Elements Example" plugin, `mla_control_name` more completely overrides `tax_input`.
* Fix: The WordPress `wptexturize()` function is no longer applied to the data sources `absolute_path`, `path`, `absolute_file_name`, `file_name`, `name_only` and `extension`.
* Fix: To increase security, all Media/Assistant bulk actions must pass a NONCE validation.
* Fix: To increase security, file, example plugin and error log downloads now have unique NONCE action values.
* Fix: The Media/Assistant "Download" bulk action now rejects a missing list of attachment IDs.
* Fix: Some problems with pagination links when `mla_page-parameter` is specified have been corrected.
* Fix: The `class-mla-file-downloader.php` file now exits silently when called outside the WordPress context, avoiding PHP Warning messages in the error log.
* Fix: A PHP8.x deprecated array element notation in `mla_hooks-example.php` has been corrected.
* Fix: An "undefined variable" defect in support for Jordy Meow's Media File Renamer plugin has been corrected.
* Fix: Improved parsing of XMP metadata in PDF documents that do not strictly conform to the standard.
* Fix: For the Media/Assistant submenu table, filtering by a specific MIME type, e.g., `application/pdf` within a general MIME type view, e.g. `application`, has been corrected.
* Fix: On the Media/Edit Media screen, the "Attachment File Metadata" text box now allows "invalid code points" (characters), replacing them with a harmless Unicode Replacement Character.
* Fix: For the Media Manager Modal (popup) Window, thumbnails in the "Uploaded to this post" can be re-ordered by drag and drop.
* Fix: For the Media/Assistant Bulk Edit Area, failure to update a field to a value of '0' has been corrected.
* Fix: For the "MLA Simple Mapping Hooks Example" plugin, the plugin is now active when media item inserts are performed in Gutenberg blocks.

= 2.90 - 2.99 =
* 2.99 - WordPress 5.9 support. Bulk Edit values save/restore, current date/time data sources, custom field Library views filtered by MIME type. Four enhancements in all, fifteen fixes.
* 2.98 - New "Attachment File Metadata" meta box on the Media/Edit Media screen. Enhanced "MLA Advanced Custom Fields Example" and new "MLA Filename Issues Example" plugins.  Five enhancements in all, eleven fixes.
* 2.97 - IMPORTANT: [mla_gallery] PHP "Warning: array_key_exists()..." messages have been eliminated. WP 5.8, cropping of MMMW top row image thubmnails fixed. Description element added to mapping rules. Four enhancements in all, four fixes.
* 2.96 - WordPress 5.8 support! New [muie_archive_list] shortcode. CSV export item values. Support for Enhanced Media Library plugin. Donation links are back. Thirteen enhancements in all, fourteen fixes.
* 2.95 - Support for Real Media Library plugin in Media/Assistant and `[mla_gallery]`, MLA Insert Fixit improvements, `[mla_gallery]` simple date parameters, "Mine" filter/view. Four enhancements in all, twelve fixes.
* 2.94 - For [mla_gallery], icon handling, mla_viewer, and performance fixes. New and enhanced example plugins. Three enhancements in all, nine fixes.
* 2.93 - Correct defects in handling array values, e.g., tax_input, in request: parameters and pagination controls. Example plugin enhancements. Two enhancements in all, three fixes.
* 2.92 - Correct Media/Assistant Quick Edit error that deleted term assignments in the WordPress Categories taxonomy.
* 2.91 - Correct PHP Fatal Error in Media Manager Search Media operations. Correct defect that caused Media Manager "Edit Gallery" operations to lose existing items. Two fixes in all.
* 2.90 - WP 5.6 support, thorough review and update of all files for validating, sanitizing and escaping user data to reduce the risk of security exploits, two new example plugins. Seven enhancements in all, seven fixes.

= 2.80 - 2.84 =
* 2.84 - WP 5.5 support, new Always Use MLA MIME Type option, CSV Data Source example plugin, Support for PaidMembershipsPro, Simple Taxonomy Ordering, Simple Custom Post Order, Featured Image from URL plugins. Thirteen enhancements in all, twelve fixes.
* 2.83 - Avoid "Fatal error:" with Admin Columns Pro version 5.1+. Fix Attachments area positioning in the Media Manager Modal (popup) Window. Two fixes in all.
* 2.82 - Compatibility updates for WordPress 5.4. Security fixes to prevent three categories of attacks. New tools for "MLA Insert Fixit" and "WooCommerce Fixit Tools" example plugins. Fixes for `[mla_gallery]` and Media Manager Modal Window.  Seven enhancements in all, eleven fixes.
* 2.81 - Compatibility updates for WordPress 5.3. New "mso:" prefix gives access to the Document Properties embedded in Office Open XML file formats (e.g., docx, xlsx, pptx). Three enhancements, eight fixes.
* 2.80 - A new "MLA Phoenix Media Rename Example" plugin supports the Phoenix Media Rename plugin. MLA Insert Fixit example plugin improvements, improved support for other plugins and themes. One enhancement, twelve fixes. 

= 2.70 - 2.79 =
* 2.79 - [mla_gallery] keyword/term search "exclude" logic, [mla_tag_cloud] performance improvements, Media/Assistant Download bulk action. Nine enhancements in all, six fixes.
* 2.78 - Support Admin Columns Pro v4.5.x, add "Search Media" and "Terms Search" exclude logic, Eliminate PHP messages for Polylang and some AJAX actions. One enhancement, five fixes.
* 2.77 - Preserve current term assignments for checklist-style taxonomies when opening the Media/Assistant Quick Edit area. This defect was introduced in v2.76.
* 2.76 - "Checklist-style" term search and additions in the Bulk and Quick Edit areas. New and improved example plugins. Seven enhancements, four fixes.
* 2.75 - Admin Columns (and Pro) fixes to eliminate PHP messages. Five fixes in all.
* 2.74 - Cross-Site Scripting vulnerabilities have been removed from the Media/Assistant and Settings/Media Library assistant admin submenu screens. One enhancement, seven fixes.
* 2.73 - Checklist-style flat taxonomy improvements, Admin Columns Pro fix, new and improved example plugins, e.g., "parent search". Five enhancements, four fixes.
* 2.72 - Remove "Circular Reference" PHP Warnings in class-mla-mime-types.php.
* 2.71 - Admin Columns Pro CSV Export support, Uploaded on date editing, regular expression match/replace functions in shortcodes, new and improved example plugins. Thirteen enhancements, seven fixes.
* 2.70 - Improved file download security, Polylang fixes. Two enhancements, seven fixes.

= 2.60 - 2.65 =
* 2.65 - Corrects an "ajax.fail error" in the Media/Assistant "Set Parent" function and the Media/Edit Media screen. One other enhancement, two other fixes.
* 2.64 - For `[mla_gallery]`, corrects v2.63 problem that applied `mla_named_transfer` to all `link=file` and `link=download` galleries.
* 2.63 - Download by name and pretty links, str_replace format option, disable mapping rules, debug tab. Eight enhancements in all, nine fixes.
* 2.62 - Corrects a PHP Fatal Error when loading MLA Media Manager enhancements for "front-end" use.
* 2.61 - Several new example plugins and hooks, new shortcode parameters. Thirteen enhancements in all, thirteen fixes.
* 2.60 - Completely new Settings/IPTC/EXIF tab and example plugin enhancements. Five enhancements in all, five 

= 2.50 - 2.54 =
* 2.54 - Admin Columns/PHP 7.1.x Fix, thumbnail generation enhancements, [mla_term_list] fix and Settings/Shortcodes tab updates. Six fixes in all.
* 2.53 - Correct PHP Fatal Error defect for users of Admin Columns (free version).
* 2.52 - Improved Admin Columns Pro integration, better PHP 7 support. Example plugin improvements. Six enhancements in all, eight fixes.
* 2.51 - Change "primary column" handling for WP 4.3+ to be more like Media/Library submenu table. Some MLA UI Elements Example plugin fixes.
* 2.50 - Completely new Settings/Custom Fields tab, [mla_term_list] and example plugin enhancements. Fourteen enhancements in all, sixteen fixes.

= 2.40 - 2.41 =
* 2.41 - Updated example plugins, EXIF improvements, PHP 5.3 compatibility, Polylang fix. Five enhancements in all, ten fixes.
* 2.40 - Generate WP 4.7 PDF thumbnails, new Shortcodes tab, requires less admin-mode memory, Examples tab "View" action and "Installed" view. Seventeen other enhancements, fourteen fixes.

= 2.30 - 2.33 =
* 2.33 - Fix WordPress "The plugin does not have a valid header" errors on initial MLA installs. Two enhancements, one other fix.
* 2.32 - New Documentation/Example Plugins submenu and installer, WordPress 4.6 fixes and several new example plugins. Sixteen enhancements in all, nine fixes.
* 2.31 - Remove call to `xdebug_get_function_stack()` causing fatal PHP error.
* 2.30 - New [mla_term-list] shortcode for hierarchical taxonomy display, Uncaught RangeError fix, custom data sources. Thirteen enhancements in all, six fixes.

= 2.00 - 2.25 =
* 2.25 - Default shortcode parameters in templates, list/grid view switcher, delete option settings, better XML parsing. Eight enhancements in all, eleven fixes.
* 2.24 - Corrects the MLA error that suppressed Admin Columns functions for Posts, Pages, Custom Post Types, Users and Comments.
* 2.23 - Admin Columns 2.4.9 fixes, EXIF/XMP/PDF improvements, Posts, Pages and custom Post Types in `[mla_gallery]` display. Seven enhancements in all, six fixes.
* 2.22 - Support for the "Admin Columns" plugin, PHP7 and "enclosing shortcode" syntax. Better performance, new filters and examples. Eight enhancements in all, eight fixes.
* 2.21 - Fix for "empty grid", "No media attachments found", "No items found" and "Unknown column" symptoms. Thanks to all who quickly alerted me to the problem. One other fix for "Featured Image" handling of size=none.
* 2.20 - Reduced memory/time footprint, default setting changes, WPML/Polylang IPTC/EXIF mapping fixes, partial German translation. Nine other enhancements, thirteen fixes.
* 2.15 - Bulk Edit Reset button, Debug tab enhancements, Quick Edit thumbnails, new examples and hooks. Sixteen enhancements in all, sixteen fixes.
* 2.14 - Final WordPress 4.3 updates. New Debug tab features. Updated Dutch translation. Four other fixes.
* 2.13 - WordPress 4.3 updates. PDF Thumbnail image generator. Wildcard keyword/term searching. Several WPML and Polylang fixes. Dutch and Swedish translations! Twelve other enhancements, twelve other fixes.
* 2.12 - Fixes a defect in [mla_gallery] handling of the mla_caption parameter. Adds mla_debug=log option.
* 2.11 - Enhanced WPML and new Polylang support. "Attached" Media/Assistant table view. Eight other enhancements, fifteen fixes.
*2.10 - mla_viewer is back, with a Featured Image option! XMP support for image meta data. Eight other enhancements, twelve fixes.
* 2.02 - Bulk Edit on Media/Add New, pause/restart IPTC/EXIF mapping, EXIF CAMERA fields, "timestamp", "date" and "fraction" format options. Six other enhancements, twelve fixes.
* 2.01 - Google File Viewer (mla_viewer) disabled. IPTC/EXIF mapping performance gains. Four other enhancements, five fixes.
* 2.00 - Requires WP v3.5+. Ajax-powered bulk edit and mapping, front-end "terms search" for [mla_gallery]. Five other enhancements, two fixes.

= 1.00 - 1.95 =
* 1.95: New [mla_gallery] parameters, Download rollover action, Media/Assistant submenu filters. Eleven enhancements, seven fixes.
* 1.94: Media Manager fixes and new "current-item" parameters for [mla_tag_cloud]. Two other enhancements, seven fixes.
* 1.93: WordPress 4.0 Media Grid enhancements (optional) and compatibility fixes. New auto-fill option for Media Manager taxonomy meta boxes. One other enhancement, three other fixes.
* 1.92: Three bug fixes, one serious.
* 1.91: WordPress 4.0 support! New "Edit Media meta box" and "Media Modal Initial Values" filters and example plugins. Four other enhancements, six fixes.
* 1.90: New "Terms Search" popup window and Search Media "Terms" checkbox. Post Type filter and pagination for "Select Parent" popup. Ten other enhancements, five fixes.
* 1.83: Corrects serious defect, restoring Quick Edit, Bulk Edit and Screen Options to Media/Assistant submenu. Three other fixes.
* 1.82: "Select Parent" popup window (Media/Edit Media, Attached to column, Quick Edit area), SVG support and several new filter examples. Five other enhancements, three other fixes.
* 1.81: Corrects serious defect in Media Manager Modal Window file uploading. Adds item-specific tag clouds. One other enhancement, five other fixes.
* 1.80: Full taxonomy meta box support in the Media Manager Modal Window. Checkbox-style meta box for flat taxonomies. Fourteen other enhancements, nine fixes.
* 1.71: Searchable Category meta boxes for the Media/Edit Media screen. Support for the WordPress "Attachment Display Settings". Six fixes.
* 1.70: Internationalization and localization support! Custom Field and IPTC/EXIF Mapping hooks. One other enhancement, six fixes.
* 1.61: Three fixes, including one significant fix for item-specific markup substitution parameters. Tested for compatibility with WP 3.8.
* 1.60: New [mla_tag_cloud] shortcode and shortcode-enabled MLA Text Widget. Five other enhancements, four fixes.
* 1.52: Corrected serious defect in [mla_gallery] that incorrectly limited the number of items returned for non-paginated galleries. One other fix.
* 1.51: Attachment Metadata mapping/updating, [mla_gallery] "apply_filters" hooks, multiple paginated galleries per page, "ALL_CUSTOM" pseudo value. Three other enhancements, six fixes.
* 1.50: PDF and GPS Metadata support. Content Templates; mix literal text with data values, test for empty values and choose among two or more alternatives for [mla_gallery] and data mapping. Four other enhancements, seven fixes.
* 1.43: Generalized pagination support with "mla_output=paginate_links". One other enhancement, four fixes.
* 1.42: Pagination support for [mla_gallery]! Improved CSS width (itemwidth) and margin handling. Eight other enhancements, six fixes.
* 1.41: New [mla_gallery] "previous link" and "next link" output for gallery navigation. New "request" substitution parameter to access $_REQUEST variables. Three other enhancements, seven fixes.
* 1.40: Better performance! New custom table views, Post MIME Type and Upload file/MIMEs control; 112 file type icons to choose from. Four new Gallery Display Content parameters. four other enhancements, twelve fixes.
* 1.30: New "mla_alt_shortcode" parameter combines [mla_gallery] with other gallery display shortcodes, e.g., Jetpack Carousel and Tiled Mosaic. Support for new 3.6 audio/video metadata. One other enhancement, eight fixes.
* 1.20: Media Manager (Add Media, etc.) enhancements: filter by more MIME types, date, taxonomy terms; enhanced search box for name/slug, ALT text, caption and attachment ID. New [mla_gallery] sort options. Four other enhancements, four fixes.
* 1.14: New [mla_gallery] mla_target and tax_operator parameters, tax_query cleanup and ids/include fix. Attachments column fix. IPTC/EXIF and Custom Field mapping fixes. Three other fixes.
* 1.13: Add custom fields to the quick and bulk edit areas; sort and search on them in the Media/Assistant submenu. Expanded EXIF data access, including COMPUTED values. Google File Viewer support, two other enhancements and two fixes.
* 1.11: Search by attachment ID, avoid fatal errors and other odd results when adding taxonomy terms. One other fix.
* 1.10: Map attachment metadata to custom fields; add them to [mla_gallery] display and as sortable columns on the Media/Assistant submenu table. Get Photonic Gallery (plugin) integration and six other fixes.
* 1.00: Map IPTC and EXIF metadata to standard fields, taxonomy terms and custom fields. Improved performance for where-used reporting. Specify default `[mla_gallery]` style and markup templates. Five other fixes.

= 0.11 - 0.90 =
* `[mla_gallery]` support for custom fields, taxonomy terms and IPTC/EXIF metadata. Updated for WordPress 3.5!
* Improved default Style template, `[mla_gallery]` parameters "mla_itemwidth" and "mla_margin" for control of gallery item spacing. Quick edit support of WordPress standard Categories taxonomy has been fixed.
* MLA Gallery Style and Markup Templates for control over CSS styles, HTML markup and data content of `[mla_gallery]` shortcode output. Eight other enhancements and four fixes.
* Removed (!) Warning displays for empty Gallery in and MLA Gallery in column entries.
* New "Gallery in" and "MLA Gallery in" where-used reporting to see where items are returned by the `[gallery]` and `[mla_gallery]` shortcodes. Two other enhancements and two fixes.
* Enhanced Search Media box. Extend search to the name/slug, ALT text and caption fields. Connect search terms with "and" or "or". Five other enhancements and two fixes.
* New `[mla_gallery]` shortcode, a superset of the `[gallery]` shortcode that provides many enhancements. These include taxonomy support and all post_mime_type values (not just images). Media Library items need not be "attached" to the post.
* SQL View (supporting ALT Text sorting) now created for automatic plugin upgrades
* Bulk Edit area; add, remove or replace taxonomy terms for several attachments at once. Sort your media listing on ALT Text, exclude revisions from where-used reporting.
* Support ALL taxonomies, including the standard Categories and Tags, your custom taxonomies and the Assistant's pre-defined Att. Categories and Att. Tags. Add taxonomy columns to the Assistant admin screen, filter on any taxonomy, assign terms and list the attachments for a term. 
* Quick Edit action for inline editing of attachment metadata
* Fixed "404 Not Found" errors when updating single items.

= 0.1 =
* Initial release.

== Upgrade Notice ==

= 3.04 =
Fix: When Photo Engine (WP/LR Sync) is active, a PHP Fatal Error with mapping rules during "sync" operations has been corrected.

== Other Notes ==

In this section, scroll down to see highlights from the documentation, including new and unique plugin features

**NOTE:** Complete documentation is included in the Documentation tab on the Settings/Media Library Assistant admin screen and the drop-down "Help" content in the admin screens.

== Acknowledgements ==

Media Library Assistant includes many images drawn (with permission) from the [Crystal Project Icons](http://www.softicons.com/free-icons/system-icons/crystal-project-icons-by-everaldo-coelho), created by [Everaldo Coelho](http://www.everaldo.com), founder of [Yellowicon](http://www.yellowicon.com).

<strong>Many thanks</strong> to Aurovrata Venet, Il'ya Karastel and Kristian Adolfsson for testing and advising on the multilingual support features!

<h4>The Example Plugins</h4>

The MLA example plugins have been developed to illustrate practical applications that use the hooks MLA provides to enhance the admin-mode screens and front-end content produced by the MLA shortcodes. Most of the examples are drawn from topics in the MLA Support Forum.

The Documentation/Example Plugins submenu lets you browse the list of MLA example plugins, install or update them in the Plugins/Installed Plugins area and see which examples you have already installed. To activate, deactivate or delete the plugins you must go to the Plugins/Installed Plugins admin submenu.

The Example plugins submenu lists all of the MLA example plugins and identifies those already in the Installed Plugins area. In the submenu:

* the "Screen Options" dropdown area lets you choose which columns to display and how many items appear on each page
* the "Help" dropdown area gives you a brief explanation of the submenu content and functions
* the "Search Plugins" text box lets you filter the display to items containing one or more keywords or phrases
* bulk and rollover actions are provided to install or update example plugins
* the table can be sorted by any of the displayed columns

Once you have installed an example plugin you can use the WordPress Plugins/Editor submenu to view the source code and (with extreme caution) make small changes to the code. **Be very careful if you choose to modify the code!** Making changes to active plugins is not recommended. If your changes cause a fatal error, the plugin will be automatically deactivated. It is much safer to download the file(s) or use FTP access to your site to modify the code offline in a more robust HTML/PHP editor.

You can use the "Download" rollover action to download a plugin to your local system. Once you have made your modifications you can copy the plugin to a compressed file (ZIP archive) and then upload it to your server with the Plugins/Add New (Upload Plugin) admin submenu. 

If you do make changes to the example plugin code the best practice is to save the modified file(s) under a different name, so your changes won't be lost in a future update. If you want to retain the file name, consider changing the version number, e.g. adding 100 to the MLA value, so you can more easily identify the plugins you have modified. 

<h4>MLA Term List Shortcode</h4>

The `[mla_term_list]` shortcode function displays hierarchical taxonomy terms in a variety of formats; link lists, dropdown controls and checkbox lists. The list works with both flat (e.g., Att. Tags) and hierarchical taxonomies (e.g., Att. Categories) MLA Term List enhancements for lists and controls include: 

* Full support for WordPress categories, tags and custom taxonomies. You can select from any taxonomy or list of taxonomies defined in your site.
* Several display formats, including "flat", "list", "dropdown" and "checklist".
* Control over the styles, markup and content of each list using Style and Markup Templates. You can customize the "list" formats to suit any need.
* Access to a wide range of content using the term-specific and Field-level Substitution parameters. A powerful Content Template facility lets you assemble content from multiple sources and vary the results depending on which data elements contain non-empty values for a given term.
* Display Style and Display Content parameters for easy customization of the list display and the destination/value behind each term. 
* A comprehensive set of filters gives you access to each step of the list generation process from PHP code in your theme or other plugins. 

The `[mla_term_list]` shortcode has many parameters and some of them have a complex syntax; it can be a challenge to build a correct shortcode. The WordPress Shortcode API has a number of limitations that make techniques such as entering HTML or splitting shortcode parameters across multiple lines difficult. Read and follow the rules and guidelines in the "Entering Long/Complex Shortcodes" Documentation section to get the results you want. 

Many of the `[mla_term_list]` concepts and shortcode parameters are modeled after the [mla_gallery] and [mla_tag_cloud] shortcodes, so the learning curve is shorter. Differences and parameters unique to the list are given in the sections below. 

<h4>Support for the "Admin Columns" Plugin</h4>

The [Admin Columns plugin](https://wordpress.org/plugins/codepress-admin-columns/ "Admin Columns free version") allows you to customize columns on several admin-mode screens, including the MLA Media/Assistant submenu screen. All you have to do is install the plugin; MLA will detect its presence and automatically register the Media/Assistant submenu screen for support. With Admin Columns, you can:

* Reorder columns with a simple drag & drop interface.
* Re-size columns to give more or less space to a column.
* Remove (not just hide) columns from the submenu table.
* Add new columns for custom fields and additional information.
* The Admin Columns "Pro" version adds support for ACF fields and other capabilities.

When Admin Columns is present you will see a new "Edit Columns" button just above the Media/Assistant submenu table. Click the button to go to the Settings/Admin Columns configuration screen. There you will see "Media Library Assistant" added to the "Others:" list. Click on it to see the configuration of the Media/Assistant submenu screen. 

You can find detailed configuration instructions at the [Admin Columns web site Documentation page](http://admincolumns.com/documentation/ "Admin Columns Documentation"). 

When you have completed your configuration changes, click "Update Media Library Assistant" in the Store Settings metabox at the top-right of the screen. You can also click "Restore Media Library Assistant columns" to remove your changes and go back to the MLA default settings. Click the "View" button at the right of the Media Library Assistant heading to return to the Media/Assistant submenu screen and see your changes. 

<h4>WPML &amp; Polylang Multilingual Support; the MLA Language Tab</h4>

Media Library Assistant provides integrates support for two popular "Multilanguage/ Multilingual/ Internationalization" plugins; [WPML](https://wpml.org/ "WPML - The WordPress Multilingual Plugin") and [Polylang](https://wordpress.org/plugins/polylang/ "Polylang - Making WordPress multilingual"). These plugins let you write posts and pages in multiple languages and make it easy for a visitor to select the language in which to view your site. MLA works with the plugins to make language-specific Media library items easy to create and manage.

MLA detects the presence of either plugin and automatically adds several features that work with them:

* <strong>Language-specific filtering</strong> of the <code>[mla_gallery]</code> and <code>[mla_tag_cloud]</code> shortcodes.
* <strong>Media/Assistant submenu table enhancements</strong> for displaying and managing item translations.
* <strong>Term Assignment and Term Synchronization</strong>, to match terms to language-specific items and automatically keep all translations for an item in synch.
* <strong>Term Mapping Replication</strong>, to manage the terms created when mapping taxonomy terms from IPTC/EXIF metadata.

<strong>Items, Translations and Terms</strong>

Each Media Library item can have one or more "translations". The item translations are linked and they use the same file in the Media Library. The linkage lets us know that "&iexcl;Hola Mundo!" (Spanish), "Bonjour Monde" (French) and "Hello world!" (English) are all translations of the same post/page. Post/page translation is optional; some posts/pages may not be defined for all languages. The language of the first translation entered for a post/page is noted as the "source language".

Taxonomy terms can also have one or more translations, which are also linked. The linkage lets us know that "Accesorio Categor&iacute;a" (Spanish), "Cat&eacute;gorie Attachement" (French) and "Attachment Category" (English) are all translations of the same term. Term translation is optional; some terms may not be defined for all languages. The language of the first translation entered for a term is noted as the "source language".

When an item is uploaded to the Media Library it is assigned to the current language (note: <strong>avoid uploading items when you are in "All Languages"/"Show all languages" mode</strong>; bad things happen). WPML provides an option to duplicate the new item in all active languages; Polylang does not. MLA makes it easy to add translations to additional languages with the Translations column on the Media/Assistant submenu table. For Polylang, MLA provides Quick Translate and Bulk Translate actions as well.

Assigning language-specific terms to items with multiple translations can be complex. MLA's <strong>Term Assignment</strong> logic assures that every term you assign on any of the editing screens (Media/Add New Bulk Edit, Media/Edit, Media/Assistant Quick Edit and Bulk Edit, Media Manager ATTACHMENT DETAILS pane) will be matched to the language of each item and translation. MLA's <strong>Term Synchronization</strong> logic ensures that changes made in one translation are replicated to all other translations that have an equivalent language-specific term.
<strong>Shortcode Support</strong>

The <code>[mla_gallery]</code> shortcode selects items using the WordPress <code>WP_Query</code> class. Both WPML and Polylang use the hooks provided by <code>WP_Query</code> to return items in the current language. If you use taxonomy parameters in your shortcode you must make sure that the term name, slug or other value is in the same language as the post/page in which it is embedded. This is easily done when the post/page content is translated from one language to another.

The <code>[mla_tag_cloud]</code> shortcode selects terms using the WordPress <code>wpdb</code> class. MLA adds language qualifiers to the database queries that compose the cloud so all terms displated are appropriate for the current language. No special coding or shortcode modification is required.

<strong>Media/Assistant submenu table</strong>

Two columns are added to the table when WPML or Polylang is active:

* <strong>Language</strong> - displays the language of the item. This column is only present when "All languages/Show all languages" is selected in the admin toolbar at the top of the screen.
* <strong>"Translations"</strong> - displays the translation status of the item in all active languages. The column header displays the flag icon for the language. The column content will have a checkmark icon for the item's language, a pencil icon for an existing translation or a plus icon for a translation that does not exist. You can click any icon to go directly to the Media/Edit Media screen for that translation. If you click a plus icon, a new translation will be created and initialized with content and terms from the current item and you will go to the Media/Edit Media screen for the new translation.

When Polylang is active, several additional features are available:

* <strong>A Language dropdown control</strong> is added to the Quick Edit and Bulk Edit areas. You can change the language of one or more items by selecting a new value in the dropdown and clicking Update. The new language must not have an exising translation; if a translation already exists the change will be ignored.
* <strong>Translation status links</strong> are added to the Quick Edit area, just below the Language dropdown control. If you click one of the pencil/plus translation status links, a new Quick Edit area will open for the translation you selected. A new translation is created if you click a plus status icon.
* <strong>A Quick Translate rollover action</strong> can be added to each item (the default option setting is "unchecked"). If you activate this option, when you click the "Quick Translate" rollover action for an item the Quick Translate area opens, showing the Language dropdown control and the translation status links. From there, click "Set Language" to change the language assigned to the item or click one of the pencil/plus translation status links. A new Quick Edit area will open for the translation you selected. A new translation is created if you click a plus status icon.
* <strong>A Translate action</strong> is added to the Bulk Actions dropdown control. If you click the box next to one or more items, select Translate in the Bulk Actions dropdown and click Apply, the Bulk Translate area will open. The center column contains a checkbox for each active language and an "All Languages" checkbox. Check the box(es) for the languages you want and then click "Bulk Translate". The Media/Assistant submenu table will be refreshed to display only the items you selected in the language(s) you selected. Existing translations will be displayed, and <strong>new translations will be created</strong> as needed so every item has a translation in every language selected.

<strong>Term Management</strong>

Taxonomy terms are language-specific, and making sure the right terms are assigned to all items and translations can be a challenge. Terms can change when an item is updated in any of five ways:

1. <strong>Individual edit</strong> - this is the full-screen Media/Edit Media submenu provided by WordPress. Taxonomies are displayed and updated in meta boxes along the right side of the screen. When "Update" is clicked whatever terms have been selected/entered are assigned to the item; they replace any old assignments.
1. <strong>Media Manager Modal Window</strong> – this is the popup window provided by WordPress' "Add Media" and "Select Featured Image" features. Taxonomies are displayed and updated in the ATTACHMENT DETAILS meta boxes along the right side of the window. Whatever terms are selected/entered here are assigned to the item; they replace any old assignments.
1. <strong>Quick Edit</strong> - this is a row-level action on the Media/Assistant screen. When "Update" is clicked whatever terms have been selected/entered are assigned to the item; they replace any old assignments.
1. <strong>Bulk edit</strong> - this is a bulk action on the Media/Assistant screen, and is also available on the Media/Upload New Media screen. In the Bulk Edit area, terms can be added or removed or all terms can be replaced. The bulk edit can be applied to multiple item translations in one or more languages.
1. <strong>IPTC/EXIF Metadata Mapping</strong> - this is done by defining rules in the "Taxonomy term mapping" section of the IPTC &amp; EXIF Processing Options. The mapping rules can be run when new items are added to the Media Library, from the Settings/Media Library Assistant IPTC/EXIF tab, from the Media/Assistant Bulk Edit area or from the Media/Edit Media submenu screen.

When terms change in any of the above ways there are two tasks that require rules:

1. How should language-specific terms be assigned to items selected? This is "Term Assignment".
1. How should terms assigned to one translation of an item be used to update other translations of the same item? This is "Term Synchronization".
