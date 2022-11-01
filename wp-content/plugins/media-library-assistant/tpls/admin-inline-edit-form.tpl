<!-- template="page" -->
<form>
  <table width="99%" style="display: none">
    <tbody id="inlineedit">
      <tr id="inline-edit" class="inline-edit-row inline-edit-row-attachment inline-edit-attachment quick-edit-row quick-edit-row-attachment quick-edit-attachment" style="display: none">
        <td colspan="[+colspan+]" class="colspanchange">
		<div class="edit-fields-div" id="inline-edit-fields-div">
          <fieldset class="inline-edit-col-left">
            <div class="inline-edit-col">
              <h4>[+Quick Edit+]</h4>
			  <div id="item_thumbnail"></div>
              <label> <span class="title">[+Title+]</span> <span class="input-text-wrap">
                <input type="text" name="post_title" class="ptitle" value="" />
                </span> </label>
              <label> <span class="title">[+Name/Slug+]</span> <span class="input-text-wrap">
                <input type="text" name="post_name" value="" />
                </span> </label>
              <label> <span class="title">[+Caption+]</span> <span class="input-text-wrap">
                <input type="text" name="post_excerpt" value="" />
                </span> </label>
              <label> <span class="title">[+Description+]</span> <span class="input-text-wrap">
                <textarea class="widefat" name="post_content"></textarea>
                </span> </label>
              <label class="inline-edit-image-alt"> <span class="title">[+ALT Text+]</span> <span class="input-text-wrap">
                <input type="text" name="image_alt" value="" />
                </span> </label>
              <div class="inline-edit-group">
[+Uploaded on+]
              </div>
              <div class="inline-edit-group">
                <label class="inline-edit-post-parent alignleft"> <span class="title">[+Parent ID+]</span> <span class="input-text-wrap">
                  <input type="text" name="post_parent" value="" />
                  </span> </label>
                <label class="inline-edit-post-parent-title"> <span class="">
                  <input type="text" readonly="readonly" disabled="disabled" name="post_parent_title" value="" />
                  </span> </label>
                  <input id="inline-edit-post-set-parent" title="[+Select+]" class="button-primary parent" type="button" name="post_parent_set" value="[+Select+]" />
              </div>
              <div class="inline-edit-group">
                <label class="inline-edit-menu-order alignleft"> <span class="title">[+Menu Order+]</span> <span class="input-text-wrap">
                  <input type="text" name="menu_order" value="" />
                  </span> </label>
[+authors+]
              </div>
            </div>
          </fieldset>
[+quick_middle_column+]
[+quick_right_column+]
          <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
[+custom_fields+]
            </div>
          </fieldset>
		  </div> <!-- inline-edit-fields-div -->
          <div id="qerow-ajax-response" style="font-weight:bold"></div>
          <p class="submit inline-edit-save">
		  	<a accesskey="c" href="#inline-edit" title="[+Cancel+]" class="button-secondary cancel alignleft">[+Cancel+]</a>
		  	<a accesskey="s" href="#inline-edit" title="[+Update+]" class="button-primary save alignright">[+Update+]</a>
			<span class="spinner"></span>
            <input type="hidden" name="page" value="mla-menu" />
            <input type="hidden" name="screen" value="media_page_mla-menu" />
			<br class="clear" />
            <span class="error" style="display:none"></span>
          </p>
        </td>
      </tr>
      <tr id="preset-bulk-edit" class="inline-edit-row inline-edit-row-attachment inline-edit-attachment bulk-edit-row bulk-edit-row-attachment bulk-edit-attachment" style="display: none">
        <td colspan="[+colspan+]" class="colspanchange">
		<div class="edit-fields-div" id="preset-bulk-edit-fields-div">
[+preset_div_content+]
		<!-- preset-bulk-edit-fields-div --></div>
        </td>
      </tr>
      <tr id="blank-bulk-edit" class="inline-edit-row inline-edit-row-attachment inline-edit-attachment bulk-edit-row bulk-edit-row-attachment bulk-edit-attachment" style="display: none">
        <td colspan="[+colspan+]" class="colspanchange">
		<div class="edit-fields-div" id="blank-bulk-edit-fields-div">
[+blank_div_content+]
		<!-- blank-bulk-edit-fields-div --></div>
        </td>
      </tr>
      <tr id="bulk-edit" class="inline-edit-row inline-edit-row-attachment inline-edit-attachment bulk-edit-row bulk-edit-row-attachment bulk-edit-attachment" style="display: none">
        <td colspan="[+colspan+]" class="colspanchange">
          <div id="bulkrow-ajax-response" style="font-weight:bold"></div>
          <div class="edit-fields-div" id="bulk-edit-fields-div">
          <fieldset class="inline-edit-col-left">
            <div class="inline-edit-col">
              <h4>[+Bulk Edit+]</h4>
              <div id="bulk-title-div">
                <div id="bulk-titles"></div>
              </div>
		  	<a accesskey="c" href="#inline-edit" title="[+Cancel+]" class="button-secondary cancel alignleft">[+Cancel+]</a>
		  	<a accesskey="r" href="#inline-edit" title="[+Reset+]" class="button-secondary reset alignleft">[+Reset+]</a>
		  	<a accesskey="c" href="#inline-edit" title="[+Import+]" class="button-secondary import alignleft">[+Import+]</a>
		  	<a accesskey="r" href="#inline-edit" title="[+Export+]" class="button-secondary export alignleft">[+Export+]</a>
			<span class="spinner"></span>
            </div>
          </fieldset>
[+initial_div_content+]
          <!-- bulk-edit-fields-div --></div>
          <p class="submit inline-edit-save">
		  	<a accesskey="c" href="#inline-edit" title="[+Cancel+]" class="button-secondary cancel alignleft">[+Cancel+]</a>
            <input accesskey="s" type="submit" name="bulk_edit" id="bulk_edit" class="button-primary alignright" value="[+Update+]"  />
            <input style="[+bulk_map_style+]" accesskey="i" type="submit" name="bulk_map" id="bulk_map" class="button-secondary alignright" value="[+Map IPTC/EXIF metadata+]" />
            <input style="[+bulk_custom_field_map_style+]" accesskey="m" type="submit" name="bulk_custom_field_map" id="bulk_custom_field_map" class="button-secondary alignright" value="[+Map Custom Field metadata+]" />
            <input type="hidden" name="page" value="mla-menu" />
            <input type="hidden" name="screen" value="media_page_mla-menu" />
            <span class="error" style="display:none"></span> <br class="clear" />
          </p>
        </td>
      </tr>
      <tr id="bulk-progress" class="inline-edit-row inline-edit-row-attachment inline-edit-attachment bulk-edit-row bulk-edit-row-attachment bulk-edit-attachment" style="display: none">
        <td colspan="[+colspan+]" class="colspanchange">
          <fieldset class="inline-edit-col-left">
            <div class="inline-edit-col">
              <h4>[+Bulk Edit+] [+Bulk Waiting+]</h4>
              <div id="bulk-progress-waiting-div">
                <div class="bulk-progress-titles" id="bulk-progress-waiting"></div>
              </div>
            </div>
          </fieldset>
          <fieldset class="inline-edit-col-center">
		    <div class="inline-edit-col">
              <h4>[+Bulk Running+]</h4>
              <div id="bulk-progress-running-div">
                <div class="bulk-progress-titles" id="bulk-progress-running"></div>
              </div>
		    </div>
		  </fieldset>
          <fieldset class="inline-edit-col-right">
            <div class="inline-edit-col">
		    <div class="inline-edit-col">
              <h4>[+Bulk Complete+]</h4>
              <div id="bulk-progress-complete-div">
                <div class="bulk-progress-titles" id="bulk-progress-complete"></div>
              </div>
		    </div>
            </div>
          </fieldset>
          <p class="submit inline-edit-save">
		  	<a accesskey="c" href="#inline-edit" title="[+Cancel+]" class="button-secondary cancel alignleft">[+Cancel+]</a>
            <input accesskey="r" type="submit" name="bulk_refresh" id="bulk_refresh" class="button-primary alignright" value="[+Refresh+]"  />
			<span class="spinner"></span>
            <input type="hidden" name="page" value="mla-menu" />
            <input type="hidden" name="screen" value="media_page_mla-menu" />
            <span class="error" style="display:none"></span> <br class="clear" />
          </p>
        </td>
      </tr>
    </tbody>
  </table>
</form>
[+set_parent_form+]
