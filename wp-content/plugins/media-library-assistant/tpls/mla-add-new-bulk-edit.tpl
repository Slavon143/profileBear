<!-- template="page" -->
<div class="edit-fields-div" id="mla-preset-add-new-bulk-edit-div" style="display: none;">
[+preset_div_content+]
<!-- mla-preset-add-new-bulk-edit-div --></div>
<div class="edit-fields-div" id="mla-blank-add-new-bulk-edit-div" style="display: none;">
[+blank_div_content+]
<!-- mla-blank-add-new-bulk-edit-div --></div>
<div class="edit-fields-div" id="mla-add-new-bulk-edit-div" style="display: none;">
<input id="bulk-edit-toggle" title="[+Toggle+]" class="button-primary alignright" type="button" name="bulk_edit_toggle" value="[+Toggle+]" />
<input id="bulk-edit-reset" title="[+Reset+]" class="button-secondary alignright" type="button" name="bulk_edit_reset" value="[+Reset+]" style="display:none" />
<input id="bulk-edit-import" title="[+Import+]" class="button-secondary alignright" type="button" name="bulk_edit_import" value="[+Import+]" style="display:none" />
<input id="bulk-edit-export" title="[+Export+]" class="button-secondary alignright" type="button" name="bulk_edit_export" value="[+Export+]" style="display:none" />
<strong>[+NOTE+]</strong><span class="spinner"></span><br />
[+initial_div_content+]
    <input type="hidden" name="page" value="media-new.php" />
    <input type="hidden" name="screen" value="async-upload" />
<div class="clear" style="border-bottom: thin solid #bbb"></div>
<div id="ajax-response"></div>
<!-- mla-add-new-bulk-edit-div --></div>
[+set_parent_form+]
